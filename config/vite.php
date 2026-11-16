<?php

use craft\helpers\App;

return [
    'useDevServer' => App::env('CRAFT_ENVIRONMENT') === 'dev',
    'manifestPath' => '@webroot/dist/.vite/manifest.json',
    'devServerPublic' => App::env('VITE_DEV_SERVER_PUBLIC'),
    'serverPublic' => '/dist/',
    'errorEntry' => 'src/main.js',
    'cacheKeySuffix' => '',
    'devServerInternal' => App::env('VITE_DEV_SERVER_INTERNAL'),
    'checkDevServer' => true,
];
