<?php

namespace modules\hypotheca\controllers;

use Craft;
use craft\helpers\App;
use craft\web\Controller;
use modules\hypotheca\models\RequestForm;
use yii\web\Response;

class RequestController extends Controller
{
    protected array|int|bool $allowAnonymous = ['submit'];

    /** Champ piège : invisible pour les humains, rempli par les robots. */
    private const HONEYPOT = 'website';

    public function actionSubmit(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $request = Craft::$app->getRequest();

        // Robot : on fait semblant que tout a fonctionné.
        if ($request->getBodyParam(self::HONEYPOT)) {
            return $this->asSuccess();
        }

        $form = new RequestForm();
        $form->setAttributes(array_map(
            fn($v) => is_scalar($v) ? (string) $v : '',
            array_intersect_key((array) $request->getBodyParam('request', []), $form->getAttributes()),
        ));

        if (!$form->validate()) {
            return $this->asFailure('Veuillez corriger les champs indiqués.', ['errors' => $form->getFirstErrors()]);
        }

        $contact = Craft::$app->getGlobals()->getSetByHandle('contact');
        $to = $contact?->email ?: App::mailSettings()->fromEmail;

        $message = Craft::$app->getMailer()->compose()
            ->setTo($to)
            ->setSubject('Demande en ligne : ' . $form->fullName())
            ->setTextBody($form->toText());
        if ($form->email) {
            $message->setReplyTo([$form->email => $form->fullName()]);
        }

        if (!$message->send()) {
            Craft::error('Échec de l’envoi de la demande en ligne de ' . $form->fullName(), __METHOD__);
            return $this->asFailure('L’envoi a échoué. Réessayez dans un moment ou appelez-moi directement.');
        }

        return $this->asSuccess();
    }
}
