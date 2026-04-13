<?php

defined('TYPO3') or die();

/**
 * Add MFA enabled checkbox to fe_users records in the TYPO3 backend.
 * This allows admins to enable/disable email MFA per user.
 */

$temporaryColumns = [
    'tx_dpvmfaemail_enabled' => [
        'exclude' => true,
        'label' => 'E-Mail 2FA aktiviert',
        'description' => 'Aktiviert die Zwei-Faktor-Authentifizierung per E-Mail fuer diesen Benutzer.',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => false,
                ],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'tx_dpvmfaemail_enabled',
    '',
    'after:disable'
);
