<?php
if (!defined ('TYPO3_MODE'))
	die ('Access denied.');

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Profile',
	array(
		'User' => 'show,edit,update,editPassword,updatePassword,closeAccount,disable,verify'
	),
	array(
		'User' => 'show,edit,update,editPassword,updatePassword,closeAccount,disable,verify'
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Widget',
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword'
	),
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword'
	)
);

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_ajaxlogin_sectionreload';

$TYPO3_CONF_VARS['EXTCONF']['ajaxlogin']['redirectUrl_postProcess'] = array();
?>