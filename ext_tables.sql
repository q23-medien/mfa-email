#
# Table structure for table 'fe_users'
# Adds fields for email-based MFA
#
CREATE TABLE fe_users (
    tx_dpvmfaemail_enabled tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tx_dpvmfaemail_code varchar(255) DEFAULT '' NOT NULL,
    tx_dpvmfaemail_code_tstamp int(11) unsigned DEFAULT '0' NOT NULL,
    tx_dpvmfaemail_attempts int(11) unsigned DEFAULT '0' NOT NULL,
    tx_dpvmfaemail_last_attempt int(11) unsigned DEFAULT '0' NOT NULL
);
