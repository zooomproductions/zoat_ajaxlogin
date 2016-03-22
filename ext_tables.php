<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$tempColumns = array(
    'tx_zoatajaxlogin_forgotHash' => array(
        'label' => 'LLL:EXT:zoat_ajaxlogin/Resources/Private/Language/locallang_db.xml:fe_users.tx_zoatajaxlogin_forgotHash',
        'displayCond' => 'HIDE_FOR_NON_ADMINS',
        'exclude' => true,
        'config' => array(
            'type' => 'input',
            'size' => 20,
            'max' => 32,
            'eval' => 'md5',
        ),
    ),
    'tx_zoatajaxlogin_verificationHash' => array(
        'label' => 'LLL:EXT:zoat_ajaxlogin/Resources/Private/Language/locallang_db.xml:fe_users.tx_zoatajaxlogin_verificationHash',
        'displayCond' => 'HIDE_FOR_NON_ADMINS',
        'exclude' => true,
        'config' => array(
            'type' => 'input',
            'size' => 32,
            'readOnly' => true,
        ),
    ),
    'tx_zoatajaxlogin_forgotHashValid' => array(
        'label' => 'LLL:EXT:zoat_ajaxlogin/Resources/Private/Language/locallang_db.xml:fe_users.tx_zoatajaxlogin_forgotHashValid',
        'displayCond' => 'HIDE_FOR_NON_ADMINS',
        'exclude' => true,
        'config' => array(
            'type' => 'input',
            'size' => '8',
            'eval' => 'datetime',
        ),
    ),
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('fe_users');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;Ajaxlogin,tx_zoatajaxlogin_verificationHash,tx_zoatajaxlogin_forgotHash,tx_zoatajaxlogin_forgotHashValid'
);

$pagesTempColumns = array(
    'tx_zoatajaxlogin_sectionreload' => array(
        'label' => 'After signing in or out',
        'config' => array(
            'type' => 'check',
            'items' => array(
                array('Reload the pages in this section', 1),
            ),
        ),
    ),
);

\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('pages');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $pagesTempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'miscellaneous', 'tx_zoatajaxlogin_sectionreload');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Zooom AJAX login');
