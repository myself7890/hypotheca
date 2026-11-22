<?php
/**
 * Yii Application Config
 *
 * Edit this file at your own risk!
 *
 * The array returned by this file will get merged with
 * vendor/craftcms/cms/src/config/app.php and app.[web|console].php, when
 * Craft's bootstrap script is defining the configuration for the entire
 * application.
 *
 * You can define custom modules and system components, and even override the
 * built-in system components.
 *
 * If you want to modify the application config for *only* web requests or
 * *only* console requests, create an app.web.php or app.console.php file in
 * your config/ folder, alongside this one.
 *
 * Read more about application configuration:
 * @link https://craftcms.com/docs/5.x/reference/config/app.html
 */

use craft\helpers\App;

return [
    'id' => App::env('CRAFT_APP_ID') ?: 'CraftCMS',
    'modules' => [
        'hypotheca' => \modules\hypotheca\Module::class,
    ],
    'components' => [
        // En production (Railway), l'envoi passe par MAILER_DSN, par exemple
        // resend+api://CLÉ@default ; Railway bloque le SMTP sortant hors plan Pro.
        // Sans MAILER_DSN (DDEV), on garde les réglages de courriel du panneau.
        'mailer' => function() {
            $config = App::mailerConfig();
            if ($dsn = App::env('MAILER_DSN')) {
                // Transport::fromDsn() charge tous les ponts installés (Resend
                // compris), contrairement à la liste fixe du mailer de Yii.
                $config['transport'] = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
            }
            if ($fromEmail = App::env('MAIL_FROM_EMAIL')) {
                $config['from'] = [$fromEmail => App::env('MAIL_FROM_NAME') ?: 'Hypotheca'];
            }
            return Craft::createObject($config);
        },
    ],
];
