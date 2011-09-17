<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$tempColumns = array (
	'tx_ajaxlogin_forgotHash' => array (
		'config' => array (
			'type' => 'passthrough',
		)
	),
	'tx_ajaxlogin_forgotHashValid' => array (
		'config' => array (
			'type' => 'passthrough',
		)
	)
);

t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users", $tempColumns, 1);

$pagesTempColumns = array (
	'tx_ajaxlogin_sectionreload' => array (
		'label' => 'After signing in or out',
		'config' => array (
			'type' => 'check',
			'items' => array(
				array('Reload the pages in this section', 1)
			)
		)
	)
);

t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages", $pagesTempColumns, 1);
t3lib_extMgm::addFieldsToPalette('pages', 'miscellaneous', 'tx_ajaxlogin_sectionreload');

Tx_Extbase_Utility_Extension::registerPlugin(
    $_EXTKEY,// The extension name (in UpperCamelCase) or the extension key (in lower_underscore)
    'Profile',                // A unique name of the plugin in UpperCamelCase
    'Ajaxlogin: Userprofile'    // A title shown in the backend dropdown field
);
$extensionName = strtolower(t3lib_div::underscoredToUpperCamelCase($_EXTKEY));

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$extensionName . '_profile'] = 'pages,recursive,layout,select_key';

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'AJAX login');
?>