<?php

namespace Zooom\ZoatAjaxlogin\Utility;

class PasswordUtility
{
    /**
     * Encrypts the new password before storing in database.
     *
     * @param string $string
     *
     * @return string
     */
    public static function salt($string)
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
            if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')) {
                $saltingInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
                $string = $saltingInstance->getHashedPassword($string);
            }
        }

        return $string;
    }

    /**
     * Checks if the given plain-text and salted passwords match.
     *
     * @param string $plainTextPassword Plain test password.
     * @param string $encryptedPassword Salted password.
     *
     * @return bool Returns TRUE if plain-text and salted passwords match, else FALSE.
     */
    public static function validate($plainTextPassword, $encryptedPassword)
    {
        $status = false;

        $saltingInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
        if (is_object($saltingInstance)) {
            $status = $saltingInstance->checkPassword($plainTextPassword, $encryptedPassword);
        }

        return $status;
    }
}
