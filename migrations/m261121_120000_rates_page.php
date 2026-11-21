<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\fields\PlainText;
use craft\fields\Table;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;

/**
 * Page « Taux hypothécaires » (single facultative) : grille de taux modifiable + calculateur.
 * Désactiver l'entrée dans le panneau retire la page du site et du menu.
 */
class m261121_120000_rates_page extends Migration
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
            'initialRows' => 3,
        ]));
    }

    public function safeUp(): bool
    {
        $fields = Craft::$app->getFields();
        $entries = Craft::$app->getEntries();
        $site = Craft::$app->getSites()->getPrimarySite();

        // --- Champs ---
        $kicker = $this->plain('Surtitre', 'kicker', false, 'Petite phrase manuscrite au-dessus du titre.');
        $rates = $this->save(new Table([
            'name' => 'Grille des taux',
            'handle' => 'rateItems',
            'instructions' => 'Un terme par ligne. Les taux sont en pourcentage (ex. : 4,79). Laissez « Taux affiché » vide pour masquer cette colonne.',
            'addRowLabel' => 'Ajouter un terme',
            'columns' => [
                'col1' => ['heading' => 'Terme', 'handle' => 'term', 'type' => 'singleline'],
                'col2' => ['heading' => 'Taux Hypotheca (%)', 'handle' => 'rate', 'type' => 'number'],
                'col3' => ['heading' => 'Taux affiché (%)', 'handle' => 'postedRate', 'type' => 'number'],
            ],
            'defaults' => [],
        ]));
        $note = $this->plain('Note sous la grille', 'ratesNote', true);
        $bandKicker = $this->plain('Encadré contact : accroche', 'contactKicker');
        $bandText = $this->plain('Encadré contact : texte', 'contactText', true);
        $intro = $fields->getFieldByHandle('introText');
        $blocks = $fields->getFieldByHandle('contentBlocks');

        // --- Type d'entrée ---
        $type = new EntryType(['name' => 'Taux hypothécaires', 'handle' => 'rates', 'hasTitleField' => true]);
        $layout = new FieldLayout(['type' => Entry::class]);
        $tab = new FieldLayoutTab(['name' => 'Contenu', 'layout' => $layout]);
        $tab->setElements([
            new EntryTitleField(),
            new CustomField($kicker),
            new CustomField($intro),
            new CustomField($rates),
            new CustomField($note),
            new CustomField($blocks),
            new CustomField($bandKicker),
            new CustomField($bandText),
        ]);
        $layout->setTabs([$tab]);
        $type->setFieldLayout($layout);
        if (!$entries->saveEntryType($type)) {
            throw new \RuntimeException('Type rates : ' . json_encode($type->getErrors()));
        }

        // --- Section (single) ---
        $section = new Section([
            'name' => 'Taux hypothécaires',
            'handle' => 'rates',
            'type' => Section::TYPE_SINGLE,
            'siteSettings' => [
                $site->id => new Section_SiteSettings([
                    'siteId' => $site->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => 'taux-hypothecaires',
                    'template' => 'rates.twig',
                ]),
            ],
        ]);
        $section->setEntryTypes([$type]);
        if (!$entries->saveSection($section)) {
            throw new \RuntimeException('Section rates : ' . json_encode($section->getErrors()));
        }

        // --- Contenu initial (taux repris de hypotheca.ca) ---
        $entry = Entry::find()->section('rates')->status(null)->one();
        $entry->title = 'Taux hypothécaires';
        $rows = [
            ['5 ans variable', 3.90, 4.70],
            ['1 an fixe', 4.79, 5.49],
            ['2 ans fixe', 4.54, 5.24],
            ['3 ans fixe', 4.44, 5.75],
            ['4 ans fixe', 4.54, 5.84],
            ['5 ans fixe', 4.64, 5.94],
            ['6 ans fixe', 4.94, 6.10],
            ['7 ans fixe', 5.09, 6.10],
            ['10 ans fixe', 5.24, 6.15],
        ];
        $entry->setFieldValues([
            'kicker' => 'Planifiez en toute confiance',
            'introText' => 'Consultez mes taux indicatifs, puis estimez votre paiement avec le calculateur. Mon travail : trouver le produit qui vous convient. Pour une offre personnalisée, écrivez-moi.',
            'rateItems' => array_map(fn($r) => ['col1' => $r[0], 'col2' => $r[1], 'col3' => $r[2]], $rows),
            'ratesNote' => 'Taux indicatifs, sujets à changement sans préavis et à l’approbation du prêteur. Certaines conditions s’appliquent.',
            'contentBlocks' => [
                'new1' => [
                    'type' => 'faq',
                    'fields' => [
                        'blockTitle' => 'Comprendre les types de taux',
                        'faqItems' => [
                            ['col1' => 'Taux fixe', 'col2' => 'Le taux reste le même pendant tout le terme choisi. Vos paiements sont prévisibles et vous êtes protégé contre les hausses.'],
                            ['col1' => 'Taux variable', 'col2' => 'Le taux suit le marché pendant le terme. Il est souvent plus bas qu’un taux fixe au départ, mais il peut augmenter en tout temps.'],
                            ['col1' => 'Marge de crédit hypothécaire', 'col2' => 'Un taux plus bas qu’une marge de crédit ordinaire, et des fonds disponibles à mesure que le solde de l’hypothèque diminue. Très flexible, mais demande une bonne gestion du budget.'],
                            ['col1' => 'Taux affiché', 'col2' => 'Le taux publié par les grandes banques. Il est généralement négociable, même si beaucoup de gens l’acceptent tel quel, surtout au renouvellement.'],
                            ['col1' => 'Taux Hypotheca', 'col2' => 'Le taux que je peux obtenir pour la plupart de mes clients. Selon votre dossier, un meilleur taux est parfois possible : écrivez-moi pour qu’on regarde ensemble.'],
                        ],
                    ],
                ],
            ],
            'contactKicker' => 'Une question sur votre paiement?',
            'contactText' => 'Écrivez-moi : je valide vos chiffres avec vous et je vous prépare une offre sur mesure.',
        ]);
        if (!Craft::$app->getElements()->saveElement($entry)) {
            throw new \RuntimeException('Entrée rates : ' . json_encode($entry->getErrors()));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261121_120000_rates_page ne peut pas être annulée.\n";
        return false;
    }
}
