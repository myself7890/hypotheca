<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\elements\Entry;
use craft\fieldlayoutelements\CustomField;
use craft\fieldlayoutelements\entries\EntryTitleField;
use craft\fields\PlainText;
use craft\models\EntryType;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\models\Section;
use craft\models\Section_SiteSettings;

/**
 * Page « Demande en ligne » (single) : formulaire envoyé par courriel à la courtière
 * (adresse des Coordonnées). Le formulaire est traité par modules\hypotheca.
 */
class m261121_140000_request_page extends Migration
{
    public function safeUp(): bool
    {
        $fields = Craft::$app->getFields();
        $entries = Craft::$app->getEntries();
        $site = Craft::$app->getSites()->getPrimarySite();

        // --- Champ ---
        $success = new PlainText([
            'name' => 'Message de confirmation',
            'handle' => 'requestSuccess',
            'instructions' => 'Affiché à la place du formulaire une fois la demande envoyée.',
            'multiline' => true,
            'initialRows' => 3,
        ]);
        if (!$fields->saveField($success)) {
            throw new \RuntimeException('Champ requestSuccess : ' . json_encode($success->getErrors()));
        }

        // --- Type d'entrée ---
        $type = new EntryType(['name' => 'Demande en ligne', 'handle' => 'request', 'hasTitleField' => true]);
        $layout = new FieldLayout(['type' => Entry::class]);
        $tab = new FieldLayoutTab(['name' => 'Contenu', 'layout' => $layout]);
        $tab->setElements([
            new EntryTitleField(),
            new CustomField($fields->getFieldByHandle('kicker')),
            new CustomField($fields->getFieldByHandle('introText')),
            new CustomField($success),
        ]);
        $layout->setTabs([$tab]);
        $type->setFieldLayout($layout);
        if (!$entries->saveEntryType($type)) {
            throw new \RuntimeException('Type request : ' . json_encode($type->getErrors()));
        }

        // --- Section (single) ---
        $section = new Section([
            'name' => 'Demande en ligne',
            'handle' => 'request',
            'type' => Section::TYPE_SINGLE,
            'siteSettings' => [
                $site->id => new Section_SiteSettings([
                    'siteId' => $site->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => 'demande-en-ligne',
                    'template' => 'request.twig',
                ]),
            ],
        ]);
        $section->setEntryTypes([$type]);
        if (!$entries->saveSection($section)) {
            throw new \RuntimeException('Section request : ' . json_encode($section->getErrors()));
        }

        // --- Contenu initial ---
        $entry = Entry::find()->section('request')->status(null)->one();
        $entry->title = 'Demande en ligne';
        $entry->setFieldValues([
            'kicker' => 'Parlons de votre projet',
            'introText' => 'Remplissez le formulaire ci-dessous : je reviens vers vous dans les meilleurs délais, sans frais ni engagement. Les champs marqués d’un astérisque sont obligatoires.',
            'requestSuccess' => 'Merci! Votre demande a bien été envoyée. Je communique avec vous dans les meilleurs délais.',
        ]);
        if (!Craft::$app->getElements()->saveElement($entry)) {
            throw new \RuntimeException('Entrée request : ' . json_encode($entry->getErrors()));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m261121_140000_request_page ne peut pas être annulée.\n";
        return false;
    }
}
