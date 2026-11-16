<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Assets;
use craft\fields\Dropdown;
use craft\fields\Matrix;
use craft\fields\PlainText;
use craft\fields\Table;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;

/**
 * Remplace la section « Services » par un constructeur de blocs (champ Matrix) sur la page d'accueil.
 */
class m261116_144243_content_blocks extends Migration
{
    private function save($field)
    {
        if (!Craft::$app->getFields()->saveField($field)) {
            throw new \RuntimeException("Champ {$field->handle} : " . json_encode($field->getErrors()));
        }
        return $field;
    }

    private function plain(string $name, string $handle, bool $multiline = false, string $instructions = ''): PlainText
    {
        return $this->save(new PlainText([
            'name' => $name,
            'handle' => $handle,
            'instructions' => $instructions,
            'multiline' => $multiline,
            'initialRows' => 4,
        ]));
    }

    private function table(string $name, string $handle, string $add, array $cols): Table
    {
        $columns = [];
        foreach ($cols as $i => [$heading, $colHandle, $type]) {
            $columns['col' . ($i + 1)] = ['heading' => $heading, 'handle' => $colHandle, 'type' => $type];
        }
        return $this->save(new Table([
            'name' => $name,
            'handle' => $handle,
            'addRowLabel' => $add,
            'columns' => $columns,
            'defaults' => [],
        ]));
    }

    private function dropdown(string $name, string $handle, array $options): Dropdown
    {
        $opts = [];
        foreach ($options as $i => [$label, $value]) {
            $opts[] = ['label' => $label, 'value' => $value, 'default' => $i === 0];
        }
        return $this->save(new Dropdown(['name' => $name, 'handle' => $handle, 'options' => $opts]));
    }

    private function blockType(string $name, string $handle, string $titleFormat, array $fields): EntryType
    {
        $type = new EntryType([
            'name' => $name,
            'handle' => $handle,
            'hasTitleField' => false,
            'titleFormat' => $titleFormat,
        ]);
        $layout = new FieldLayout(['type' => Entry::class]);
        $tab = new FieldLayoutTab(['name' => 'Contenu', 'layout' => $layout]);
        $tab->setElements(array_map(fn($f) => new CustomField($f), $fields));
        $layout->setTabs([$tab]);
        $type->setFieldLayout($layout);
        if (!Craft::$app->getEntries()->saveEntryType($type)) {
            throw new \RuntimeException("Type $handle : " . json_encode($type->getErrors()));
        }
        return $type;
    }

    public function safeUp(): bool
    {
        $volume = Craft::$app->getVolumes()->getVolumeByHandle('images');

        // --- Champs partagés ---
        $title = $this->plain('Titre', 'blockTitle');
        $text = $this->plain('Texte', 'blockText', true);
        $image = $this->save(new Assets([
            'name' => 'Image',
            'handle' => 'blockImage',
            'maxRelations' => 1,
            'allowedKinds' => ['image'],
            'sources' => ['volume:' . $volume->uid],
            'defaultUploadLocationSource' => 'volume:' . $volume->uid,
            'restrictLocation' => false,
        ]));
        $position = $this->dropdown('Position de l’image', 'imagePosition', [['À gauche', 'left'], ['À droite', 'right']]);
        $tone = $this->dropdown('Couleur de fond de l’image', 'imageTone', [
            ['Automatique (alternée)', 'auto'], ['Bleu pâle', 'sky'], ['Vert pâle', 'sage'], ['Gris', 'mist'],
        ]);
        $quote = $this->plain('Citation', 'quoteText', true);
        $author = $this->plain('Auteur', 'quoteAuthor', false, 'Ex. : Marie T., Laval');
        $stats = $this->table('Chiffres', 'statItems', 'Ajouter un chiffre', [
            ['Chiffre', 'value', 'singleline'], ['Légende', 'label', 'singleline'],
        ]);
        $steps = $this->table('Étapes', 'stepItems', 'Ajouter une étape', [
            ['Titre', 'title', 'singleline'], ['Description', 'description', 'multiline'],
        ]);
        $faq = $this->table('Questions', 'faqItems', 'Ajouter une question', [
            ['Question', 'question', 'singleline'], ['Réponse', 'answer', 'multiline'],
        ]);
        $btnLabel = $this->plain('Libellé du bouton', 'buttonLabel');
        $btnUrl = $this->plain('Lien du bouton', 'buttonUrl', false, 'Ex. : /demande-en-ligne');

        // --- Types de blocs ---
        $types = [
            $this->blockType('Image + texte', 'imageText', '{blockTitle}', [$title, $text, $image, $position, $tone]),
            $this->blockType('Citation', 'quote', '{quoteAuthor}', [$quote, $author]),
            $this->blockType('Chiffres clés', 'stats', '{blockTitle}', [$title, $stats]),
            $this->blockType('Étapes', 'steps', '{blockTitle}', [$title, $steps]),
            $this->blockType('Foire aux questions', 'faq', '{blockTitle}', [$title, $faq]),
            $this->blockType('Appel à l’action', 'cta', '{blockTitle}', [$title, $text, $btnLabel, $btnUrl]),
        ];

        $matrix = new Matrix([
            'name' => 'Blocs de contenu',
            'handle' => 'contentBlocks',
            'instructions' => 'Ajoutez, réorganisez et supprimez les blocs de la page.',
            'minEntries' => 0,
        ]);
        $matrix->setEntryTypes($types);
        $this->save($matrix);

        // --- Ajout à la page d'accueil ---
        $entries = Craft::$app->getEntries();
        $homeType = $entries->getEntryTypeByHandle('home');
        $layout = $homeType->getFieldLayout();
        $tabs = $layout->getTabs();
        $elements = $tabs[0]->getElements();
        $elements[] = new CustomField($matrix);
        $tabs[0]->setElements($elements);
        $layout->setTabs($tabs);
        $homeType->setFieldLayout($layout);
        if (!$entries->saveEntryType($homeType)) {
            throw new \RuntimeException('Type home : ' . json_encode($homeType->getErrors()));
        }

        // --- Migration des services existants en blocs « Image + texte » ---
        $blocks = [];
        foreach (Entry::find()->section('services')->status(null)->all() as $i => $service) {
            $blocks['new' . ($i + 1)] = [
                'type' => 'imageText',
                'fields' => [
                    'blockTitle' => $service->title,
                    'blockText' => $service->serviceDescription,
                    'imagePosition' => 'left',
                    'imageTone' => 'auto',
                ],
            ];
        }
        $home = Entry::find()->section('home')->status(null)->one();
        $home->setFieldValue('contentBlocks', $blocks);
        if (!Craft::$app->getElements()->saveElement($home)) {
            throw new \RuntimeException('Accueil : ' . json_encode($home->getErrors()));
        }

        // --- Nettoyage de l'ancienne section « Services » ---
        $section = $entries->getSectionByHandle('services');
        $entries->deleteSection($section);
        $serviceType = $entries->getEntryTypeByHandle('service');
        if ($serviceType) {
            $entries->deleteEntryType($serviceType);
        }
        foreach (['serviceDescription', 'serviceImage'] as $handle) {
            if ($f = Craft::$app->getFields()->getFieldByHandle($handle)) {
                Craft::$app->getFields()->deleteField($f);
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261116_144243_content_blocks ne peut pas être annulée.\n";
        return false;
    }
}
