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
