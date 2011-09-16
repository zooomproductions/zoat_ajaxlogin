<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Arno Schoon <arno@maxserv.nl>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * Provides an status report about whether encryption can be used
 *
 * @author	Arno Schoon <arno@maxserv.nl>
 * @package	TYPO3
 * @subpackage	ajaxlogin
 */
class Tx_Ajaxlogin_Report_EncryptionStatus implements tx_reports_StatusProvider {


	/**
	 * Checks whether the requirementsa for file indexing are met by the current
	 * web server.
	 *
	 * @see typo3/sysext/reports/interfaces/tx_reports_StatusProvider::getStatus()
	 */
	public function getStatus() {
		$reports  = array();
		$severity = tx_reports_reports_status_Status::OK;
		$value    = 'Using EXT:rsaauth to encrypt passwords send to the server.';

		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] != 'rsa') {

				// values for the case of no Fileinfo, but having mime_content_type()
			$severity = tx_reports_reports_status_Status::ERROR;
			$value    = '$TYPO3_CONF_VARS[\'FE\'][\'loginSecurityLevel\'] must be set to "rsa"';
			$message  = 'For security reasons ajaxlogin uses RSA encryption when sending passwords to the server.';
		}

		$reports[] = t3lib_div::makeInstance('tx_reports_reports_status_Status',
			'Password encryption',
			$value,
			$message,
			$severity
		);

		return $reports;
	}
}
?>