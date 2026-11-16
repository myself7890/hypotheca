<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\elements\GlobalSet as GlobalSetElement;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\fields\Assets;
use craft\fields\PlainText;
use craft\fs\Local;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\models\Volume;

/**
 * Modèle de contenu initial : page d'accueil (single), services (channel) et coordonnées (global).
 */
class m261116_142958_seed_content extends Migration
{
    private function plain(string $name, string $handle, bool $multiline = false): PlainText
    {
        $field = new PlainText([
            'name' => $name,
            'handle' => $handle,
            'multiline' => $multiline,
            'initialRows' => 4,
        ]);
        if (!Craft::$app->getFields()->saveField($field)) {
            throw new \RuntimeException("Champ $handle : " . json_encode($field->getErrors()));
        }
        return $field;
    }

    private function layout(string $class, array $fields, bool $title = false): FieldLayout
    {
        $layout = new FieldLayout(['type' => $class]);
        $tab = new FieldLayoutTab(['name' => 'Contenu', 'layout' => $layout]);
        $elements = [];
        if ($title) {
            $elements[] = new EntryTitleField();
        }
        foreach ($fields as $field) {
            $elements[] = new CustomField($field);
        }
        $tab->setElements($elements);
        $layout->setTabs([$tab]);
        return $layout;
    }

    public function safeUp(): bool
    {
        $site = Craft::$app->getSites()->getPrimarySite();

        // --- Volume d'images ---
        $fs = new Local([
            'name' => 'Images',
            'handle' => 'images',
            'hasUrls' => true,
            'url' => '@web/uploads',
            'path' => '@webroot/uploads',
        ]);
        Craft::$app->getFs()->saveFilesystem($fs);
        $volume = new Volume(['name' => 'Images', 'handle' => 'images', 'fsHandle' => 'images']);
        Craft::$app->getVolumes()->saveVolume($volume);

        $image = function (string $name, string $handle) use ($volume) {
            $field = new Assets([
                'name' => $name,
                'handle' => $handle,
                'maxRelations' => 1,
                'allowedKinds' => ['image'],
                'sources' => ['volume:' . $volume->uid],
                'defaultUploadLocationSource' => 'volume:' . $volume->uid,
                'restrictLocation' => false,
            ]);
            if (!Craft::$app->getFields()->saveField($field)) {
                throw new \RuntimeException("Champ $handle : " . json_encode($field->getErrors()));
            }
            return $field;
        };

        // --- Page d'accueil (single) ---
        $homeFields = [
            $this->plain('Nom de la courtière', 'brokerName'),
            $this->plain('Titre principal', 'heroTitle'),
            $this->plain('Présentation', 'brokerBio', true),
            $this->plain('Mention de permis', 'licenseNotice'),
            $this->plain('Texte d’introduction', 'introText', true),
            $this->plain('Libellé du bouton', 'ctaLabel'),
            $image('Photo de la courtière', 'brokerPhoto'),
        ];
        $homeType = new EntryType(['name' => 'Accueil', 'handle' => 'home', 'hasTitleField' => true]);
        $homeType->setFieldLayout($this->layout(Entry::class, $homeFields, true));
        Craft::$app->getEntries()->saveEntryType($homeType);

        $home = new Section([
            'name' => 'Accueil',
            'handle' => 'home',
            'type' => Section::TYPE_SINGLE,
            'siteSettings' => [
                $site->id => new Section_SiteSettings([
                    'siteId' => $site->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => '__home__',
                    'template' => 'index.twig',
                ]),
            ],
        ]);
        $home->setEntryTypes([$homeType]);
        if (!Craft::$app->getEntries()->saveSection($home)) {
            throw new \RuntimeException('Section home : ' . json_encode($home->getErrors()));
        }

        // --- Services (channel) ---
        $serviceFields = [
            $this->plain('Description', 'serviceDescription', true),
            $image('Image', 'serviceImage'),
        ];
        $serviceType = new EntryType(['name' => 'Service', 'handle' => 'service', 'hasTitleField' => true]);
        $serviceType->setFieldLayout($this->layout(Entry::class, $serviceFields, true));
        Craft::$app->getEntries()->saveEntryType($serviceType);

        $services = new Section([
            'name' => 'Services',
            'handle' => 'services',
            'type' => Section::TYPE_STRUCTURE,
            'maxLevels' => 1,
            'siteSettings' => [
                $site->id => new Section_SiteSettings([
                    'siteId' => $site->id,
                    'enabledByDefault' => true,
                    'hasUrls' => false,
                ]),
            ],
        ]);
        $services->setEntryTypes([$serviceType]);
        if (!Craft::$app->getEntries()->saveSection($services)) {
            throw new \RuntimeException('Section services : ' . json_encode($services->getErrors()));
        }

        // --- Coordonnées (global) ---
        $contactFields = [
            $this->plain('Nom de la courtière', 'contactName'),
            $this->plain('Téléphone', 'phone'),
            $this->plain('Courriel', 'email'),
        ];
        $contact = new GlobalSetElement(['name' => 'Coordonnées', 'handle' => 'contact']);
        $contact->setFieldLayout($this->layout(GlobalSetElement::class, $contactFields));
        if (!Craft::$app->getGlobals()->saveSet($contact)) {
            throw new \RuntimeException('Global contact : ' . json_encode($contact->getErrors()));
        }

        // --- Contenu de démarrage (texte provisoire de la maquette) ---
        $homeEntry = Entry::find()->section('home')->status(null)->one();
        $homeEntry->title = 'Accueil';
        $homeEntry->setFieldValues([
            'brokerName' => '[Prénom Nom]',
            'heroTitle' => 'Pour une hypothèque qui vous convient.',
            'brokerBio' => '[Paragraphe de présentation de la courtière : son parcours, son expérience et son approche auprès des clients.]',
            'licenseNotice' => '[Mention de permis à ajouter]',
            'introText' => 'Que ce soit pour un refinancement, un transfert ou subrogation, un renouvellement, une marge de crédit hypothécaire ou une préapprobation, laissez-moi vous aider à trouver la meilleure hypothèque pour vos besoins, et ce, tout à fait gratuitement (nous sommes payés par les banques).',
            'ctaLabel' => 'Faire une demande en ligne',
        ]);
        Craft::$app->getElements()->saveElement($homeEntry);

        $serviceSection = Craft::$app->getEntries()->getSectionByHandle('services');
        $items = [
            ['Refinancement', '[Texte sur le refinancement, à fournir par la courtière.]'],
            ['Transfert ou subrogation', '[Texte sur le transfert ou la subrogation, à fournir par la courtière.]'],
            ['Renouvellement', '[Texte sur le renouvellement, à fournir par la courtière.]'],
            ['Marge de crédit hypothécaire', '[Texte sur la marge de crédit hypothécaire, à fournir par la courtière.]'],
            ['Préapprobation', '[Texte sur la préapprobation, à fournir par la courtière.]'],
        ];
        foreach ($items as [$title, $text]) {
            $entry = new Entry();
            $entry->sectionId = $serviceSection->id;
            $entry->typeId = $serviceType->id;
            $entry->title = $title;
            $entry->setFieldValues(['serviceDescription' => $text]);
            if (!Craft::$app->getElements()->saveElement($entry)) {
                throw new \RuntimeException("Service $title : " . json_encode($entry->getErrors()));
            }
        }

        $globalSet = Craft::$app->getGlobals()->getSetByHandle('contact');
        $globalSet->setFieldValues([
            'contactName' => '[Prénom Nom], courtière hypothécaire',
            'phone' => '[Téléphone]',
            'email' => '[Courriel]',
        ]);
        Craft::$app->getElements()->saveElement($globalSet);

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261116_142958_seed_content ne peut pas être annulée.\n";
        return false;
    }
}
