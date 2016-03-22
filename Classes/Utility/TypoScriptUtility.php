<?php

namespace Zooom\ZoatAjaxlogin\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

/**
 * Utility to get the TypoScript setup of this extension.
 *
 * @author Arno Schoon <arno@maxserv.nl>
 * @author Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 */
class TypoScriptUtility
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected static $configurationManager;

    protected static function initialize()
    {
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        self::$configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager');
    }

    public static function getSetup()
    {
        if (empty(self::$configurationManager)) {
            self::initialize();
        }

        $setup = self::$configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        if (empty($setup['plugin.']['tx_zoatajaxlogin.'])) {
            return array();
        }

        return $setup['plugin.']['tx_zoatajaxlogin.'];
    }
}
