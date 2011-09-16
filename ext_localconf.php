<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Profile',
	array(
		'User' => 'show,edit,update,editPassword'
	),
	array(
		'User' => 'show,edit,update,editPassword'
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Widget',
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword,updatePassword'
	),
	array(
		'User' => 'info,login,authenticate,logout,new,create,forgotPassword,resetPassword,updatePassword'
	)
);

$TYPO3_CONF_VARS['FE']['eID_include']['tx_ajaxlogin_utility'] = 'EXT:ajaxlogin/eid_utility.php';

$TYPO3_CONF_VARS['FE']['addRootLineFields'] .= ',tx_ajaxlogin_sectionreload';
?>