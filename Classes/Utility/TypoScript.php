<?php

namespace Zooom\ZoatAjaxLogin\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Utility to get the TypoScript setup of this extension
 *
 * @author Arno Schoon <arno@maxserv.nl>
 * @author Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
 */
class TypoScript {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	static protected $configurationManager;

	protected static function initialize() {
		$objectManager = GeneralUtility::makeInstance('Tx_Extbase_Object_ObjectManager');
		self::$configurationManager = $objectManager->get('Tx_Extbase_Configuration_ConfigurationManager');
	}

	public static function getSetup() {
		if(empty(self::$configurationManager)) {
			self::initialize();
		}

		$setup = self::$configurationManager->getConfiguration(
			Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);

		if (empty($setup['plugin.']['tx_ajaxlogin.'])) {
			return array();
		}

		return $setup['plugin.']['tx_ajaxlogin.'];
	}
}

?>
