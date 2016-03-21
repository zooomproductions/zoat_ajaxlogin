<?php

namespace Zooom\ZoatAjaxLogin\Validation\Validator;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Nikola Stojiljkovic <nikola.stojiljkovic@essentialdots.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 * @author Nikola Stojiljkovic <nikola.stojiljkovic@essentialdots.com>
 */
class PasswordsValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * Settings.
     *
     * @var array
     */
    protected $settings = array();

    /**
     * Configuration manager.
     *
     * @var Tx_Extbase_Configuration_ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Inject configuration manager.
     *
     * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
     */
    public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Check if the password recovery email can be sent.
     *
     * @param mixed $passwords The email address of the fe_user record
     *
     * @return bool TRUE if the value is valid, FALSE if an error occured
     */
    public function isValid($passwords)
    {
        $this->errors = array();
        $result = true;

        if (empty($passwords['new'])) {
            $this->addError('Password can not be blank.', 1320792347);
            $result = false;
        }

        if (strcmp($passwords['new'], $passwords['check']) != 0) {
            $this->addError('Passwords do not match.', 1320703779);
            $result = false;
        }

        if (strlen($passwords['new']) < $this->settings['validation']['passwordMinLength']) {
            $this->addError('Your password is too short.', 1307626687);
            $result = false;
        }

        return $result;
    }
}
