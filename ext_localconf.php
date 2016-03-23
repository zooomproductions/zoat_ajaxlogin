<?php

if (!defined ('TYPO3_MODE'))
	die ('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Zooom.' . $_EXTKEY,
	'Widget',
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword'
	),
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword'
	)
);

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_zoatajaxlogin_sectionreload';

$TYPO3_CONF_VARS['EXTCONF']['zoat_ajaxlogin']['redirectUrl_postProcess'] = array();


# If the rsaauth extension is active then include the ajax version of the RSA form encryption
if (trim($GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel']) === 'rsa' &&
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rsaauth') ) {

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        $_EXTKEY,
        'setup', '
page.includeJSFooter.tx-zoatajaxlogin-rsaencryption = EXT:zoat_ajaxlogin/Resources/Public/JavaScript/FrontendLoginFormRsaEncryptionAjax.min.js
');

}
