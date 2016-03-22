<?php

namespace Zooom\ZoatAjaxlogin\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

class NotifyMailUtility
{
    /**
     * Send a simple mail to a user, headers like "From" are set automatically.
     *
     * @param string $to
     * @param string $subject
     * @param string $message
     *
     * @return bool
     */
    public static function send($to, $subject, $message)
    {
        $setup = TypoScriptUtility::getSetup();
        $setup = TypoScriptService::convertTypoScriptArrayToPlainArray($setup);

        if (empty($setup['settings']['notificationMail']['from'])) {
            throw new \Zooom\ZoatAjaxlogin\Exception('[ZoatAjaxlogin]: No sender was set in the plugin TS setup', 876421);
        }

        $headers = array(
            'From: ' . $setup['settings']['notificationMail']['from'],
        );

        return array(
            $headers,
            $to,
            $subject,
            $message,
        );

        return GeneralUtility::plainMailEncoded($to, $subject, $message, implode(LF, $headers));
    }
}
