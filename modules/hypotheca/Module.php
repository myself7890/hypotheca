<?php

namespace modules\hypotheca;

use Craft;

/**
 * Module du site : traitement du formulaire « Demande en ligne ».
 * Action : POST /actions/hypotheca/request/submit
 */
class Module extends \yii\base\Module
{
    public function init(): void
    {
        Craft::setAlias('@modules/hypotheca', __DIR__);
        $this->controllerNamespace = 'modules\\hypotheca\\controllers';
        parent::init();
    }
}
