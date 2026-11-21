<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fields\Table;

/**
 * Page des taux : définitions sous la grille et encadrés à droite (disposition de hypotheca.ca),
 * à la place du bloc FAQ créé au départ.
 */
class m261121_130000_rates_definitions extends Migration
{
    private function table(string $name, string $handle, string $instructions, string $add): Table
    {
        $field = new Table([
            'name' => $name,
            'handle' => $handle,
            'instructions' => $instructions,
            'addRowLabel' => $add,
            'columns' => [
                'col1' => ['heading' => 'Terme', 'handle' => 'term', 'type' => 'singleline'],
                'col2' => ['heading' => 'Texte', 'handle' => 'text', 'type' => 'multiline'],
            ],
            'defaults' => [],
        ]);
        if (!Craft::$app->getFields()->saveField($field)) {
            throw new \RuntimeException("Champ $handle : " . json_encode($field->getErrors()));
        }
        return $field;
    }

    public function safeUp(): bool
    {
        $entries = Craft::$app->getEntries();
        $help = 'Le terme est mis en gras là où il apparaît dans le texte (sinon, il est ajouté au début).';

        $definitions = $this->table('Définitions sous la grille', 'rateDefinitions', $help, 'Ajouter une définition');
        $asides = $this->table('Encadrés à droite de la grille', 'rateAsides', $help, 'Ajouter un encadré');

        // Ajout au type d'entrée, juste après la note sous la grille.
        $type = $entries->getEntryTypeByHandle('rates');
        $layout = $type->getFieldLayout();
        $tabs = $layout->getTabs();
        $elements = [];
        foreach ($tabs[0]->getElements() as $el) {
            $elements[] = $el;
            if ($el instanceof CustomField && $el->getField()->handle === 'ratesNote') {
                $elements[] = new CustomField($definitions);
                $elements[] = new CustomField($asides);
            }
        }
        $tabs[0]->setElements($elements);
        $layout->setTabs($tabs);
        $type->setFieldLayout($layout);
        if (!$entries->saveEntryType($type)) {
            throw new \RuntimeException('Type rates : ' . json_encode($type->getErrors()));
        }

        // Contenu : les définitions quittent le bloc FAQ.
        $entry = Entry::find()->section('rates')->status(null)->one();
        $entry->setFieldValues([
            'rateDefinitions' => [
                ['col1' => 'taux fixe', 'col2' => 'Le taux fixe reste le même pendant toute la durée du terme choisi. Vos paiements sont prévisibles et vous êtes à l’abri des hausses.'],
                ['col1' => 'taux variable', 'col2' => 'Le taux variable suit le marché tout au long du terme. Il est souvent plus bas que le taux fixe à la signature, mais il comporte plus de risque puisqu’il peut augmenter en tout temps.'],
                ['col1' => 'marge de crédit hypothécaire', 'col2' => 'La marge de crédit hypothécaire offre un taux plus bas qu’une marge de crédit ordinaire. Les fonds redeviennent disponibles à mesure que le solde de l’hypothèque diminue. Très flexible, elle demande une bonne gestion du budget.'],
            ],
            'rateAsides' => [
                ['col1' => 'taux affiché', 'col2' => 'Le taux affiché est celui que publient la plupart des grandes banques. Il est généralement négociable, mais bien des gens l’acceptent tel quel, surtout au renouvellement.'],
                ['col1' => 'taux Hypotheca', 'col2' => 'Le taux Hypotheca est celui que je peux obtenir pour la majorité de mes clients. Selon votre dossier, il est parfois possible de faire mieux : écrivez-moi pour qu’on l’évalue ensemble.'],
            ],
        ]);

        // Retrait du bloc FAQ « Comprendre les types de taux » créé par la migration précédente.
        $keep = [];
        foreach ($entry->getFieldValue('contentBlocks')->status(null)->all() as $block) {
            if (!($block->type->handle === 'faq' && $block->blockTitle === 'Comprendre les types de taux')) {
                $keep[] = $block->id;
            }
        }
        $entry->setFieldValue('contentBlocks', $keep);

        if (!Craft::$app->getElements()->saveElement($entry)) {
            throw new \RuntimeException('Entrée rates : ' . json_encode($entry->getErrors()));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261121_130000_rates_definitions ne peut pas être annulée.\n";
        return false;
    }
}
