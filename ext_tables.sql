#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_zoatajaxlogin_forgotHash  varchar(80) DEFAULT '' NOT NULL,
	tx_zoatajaxlogin_verificationHash  varchar(80) DEFAULT '' NOT NULL,
	tx_zoatajaxlogin_forgotHashValid int(11) DEFAULT '0' NOT NULL
);
#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_zoatajaxlogin_sectionreload tinyint(4) DEFAULT '0' NOT NULL
);
