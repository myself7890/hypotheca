<?php

namespace modules\hypotheca\models;

use craft\base\Model;

/**
 * Demande en ligne. Les règles reprennent celles du formulaire Vue (RequestForm.vue).
 */
class RequestForm extends Model
{
    public const REASONS = [
        'purchase' => 'Achat',
        'renewal' => 'Renouvellement',
        'refinance' => 'Refinancement',
        'heloc' => 'Marge de crédit hypothécaire',
        'other' => 'Autre',
    ];

    public const CONTACT_PREFS = [
        'call' => 'Un retour d’appel',
        'email' => 'Une réponse par courriel',
    ];

    // Demandeur
    public string $firstName = '';
    public string $lastName = '';
    public string $job = '';
    public string $location = '';
    // Codemandeur
    public string $coFirstName = '';
    public string $coLastName = '';
    public string $coJob = '';
    // Prêt
    public string $reason = 'purchase';
    public string $amount = '';
    // Coordonnées
    public string $phone = '';
    public string $email = '';
    public string $contactPref = 'call';
    public string $message = '';

    public function rules(): array
    {
        $required = 'Ce champ est obligatoire.';

        return [
            [array_keys($this->getAttributes()), 'trim'],
            [['firstName', 'lastName', 'job', 'location', 'phone'], 'required', 'message' => $required],
            ['email', 'required', 'message' => 'Indiquez votre courriel pour recevoir une réponse par courriel.',
                'when' => fn() => $this->contactPref === 'email'],
            ['email', 'email', 'message' => 'Ce courriel n’est pas valide.'],
            ['phone', 'match', 'pattern' => '/^(?=(?:\D*\d){10})[\d\s().+\-]{10,25}$/', 'message' => 'Ce numéro de téléphone n’est pas valide.'],
            ['amount', 'number', 'min' => 0, 'message' => 'Indiquez un montant en chiffres.', 'tooSmall' => 'Indiquez un montant en chiffres.'],
            ['reason', 'in', 'range' => array_keys(self::REASONS), 'message' => 'Choix invalide.'],
            ['contactPref', 'in', 'range' => array_keys(self::CONTACT_PREFS), 'message' => 'Choix invalide.'],
            [['firstName', 'lastName', 'job', 'location', 'coFirstName', 'coLastName', 'coJob', 'phone', 'email'], 'string', 'max' => 150],
            ['message', 'string', 'max' => 5000],
        ];
    }

    public function fullName(): string
    {
        return trim("$this->firstName $this->lastName");
    }

    /** Corps du courriel envoyé à la courtière (texte brut). */
    public function toText(): string
    {
        $amount = $this->amount !== '' ? number_format((float) $this->amount, 0, ',', ' ') . ' $' : '—';
        $lines = [
            'DEMANDEUR',
            'Nom : ' . $this->fullName(),
            "Emploi actuel : $this->job",
            "Code postal ou ville : $this->location",
            '',
        ];
        if ($this->coFirstName . $this->coLastName . $this->coJob !== '') {
            array_push($lines,
                'CODEMANDEUR',
                'Nom : ' . trim("$this->coFirstName $this->coLastName"),
                'Emploi actuel : ' . ($this->coJob ?: '—'),
                '',
            );
        }
        array_push($lines,
            'PRÊT',
            'Raison : ' . self::REASONS[$this->reason],
            "Montant approximatif : $amount",
            '',
            'COORDONNÉES',
            "Téléphone : $this->phone",
            'Courriel : ' . ($this->email ?: '—'),
            'Je désire : ' . self::CONTACT_PREFS[$this->contactPref],
            '',
            'MESSAGE',
            $this->message ?: '—',
        );

        return implode("\n", $lines);
    }
}
