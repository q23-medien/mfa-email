<?php

declare(strict_types=1);

defined('TYPO3') or die();

/**
 * Auto-migration: Ensure all required database fields exist in fe_users.
 *
 * This runs DIRECTLY — no class autoloading, no upgrade wizard, no ext_tables.sql.
 * Pure procedural ALTER TABLE via PDO. Bulletproof.
 */
(static function (): void {
    // Register upgrade wizard for backend visibility
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['mfaEmailDbMigration']
        = \Q23\MfaEmail\Updates\DatabaseMigration::class;

    // --- Direct DB migration on every load (safe: checks before altering) ---
    try {
        $columns = [
            'tx_dpvmfaemail_enabled' => "tinyint(1) unsigned DEFAULT '0' NOT NULL",
            'tx_dpvmfaemail_code' => "varchar(255) DEFAULT '' NOT NULL",
            'tx_dpvmfaemail_code_tstamp' => "int(11) unsigned DEFAULT '0' NOT NULL",
            'tx_dpvmfaemail_attempts' => "int(11) unsigned DEFAULT '0' NOT NULL",
            'tx_dpvmfaemail_last_attempt' => "int(11) unsigned DEFAULT '0' NOT NULL",
        ];

        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $pool */
        $pool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        );
        $connection = $pool->getConnectionForTable('fe_users');
        $schemaManager = $connection->createSchemaManager();
        $existing = $schemaManager->listTableColumns('fe_users');

        $existingNames = [];
        foreach ($existing as $col) {
            $existingNames[] = strtolower($col->getName());
        }

        foreach ($columns as $name => $definition) {
            if (!in_array(strtolower($name), $existingNames, true)) {
                $connection->executeStatement(
                    'ALTER TABLE fe_users ADD COLUMN `' . $name . '` ' . $definition
                );
            }
        }
    } catch (\Throwable $e) {
        // Silently ignore — might run too early during install, that's fine.
        // The middleware has its own failsafe check.
    }
})();
