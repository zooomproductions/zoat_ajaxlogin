<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2010-2011 Stefan Galinski <stefan.galinski@gmail.com>
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

if (!defined('PATH_typo3conf')) {
	die('Could not access this script directly!');
}

$gp = t3lib_div::_GP('tx_ajaxlogin_utility');

switch($gp['action']) {
	case 'createEncryptionkey':
		require_once(t3lib_extMgm::extPath('rsaauth') . 'sv1/backends/class.tx_rsaauth_backendfactory.php');
		require_once(t3lib_extMgm::extPath('rsaauth', 'sv1/storage/class.tx_rsaauth_storagefactory.php'));
		// If we can get the backend, we can proceed
		$backend = tx_rsaauth_backendfactory::getBackend();
		if (!is_null($backend)) {
			// Generate a new key pair
			$keyPair = $backend->createNewKeyPair();

			// Save private key
			$storage = tx_rsaauth_storagefactory::getStorage();
			/* @var $storage tx_rsaauth_abstract_storage */
			$storage->put($keyPair->getPrivateKey());

			// Add RSA hidden fields
			$response = array(
				'n' => htmlspecialchars($keyPair->getPublicKeyModulus()),
				'e' => sprintf('%x', $keyPair->getExponent())
			);
		}
	break;
	default:
		$response = null;
	break;
}

header('Content-type: application/json');
echo json_encode($response);

?>