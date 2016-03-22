<?php

namespace Zooom\ZoatAjaxlogin;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageNotFoundHandler
{
    public function handleError($params, tslib_fe $pObj)
    {
        if (isset($params['pageAccessFailureReasons']['fe_group']) && !isset($params['pageAccessFailureReasons']['hidden'])
            && current($params['pageAccessFailureReasons']['fe_group']) !== 0) { // make sure realurl does't issue this 401
            $code = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['zoat_ajaxlogin']['unauthorized_handling'];
            $header = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['zoat_ajaxlogin']['unauthorized_handling_statheader'];

            if (GeneralUtility::isFirstPartOfStr($code, 'REDIRECT:')) {
                $appendQueryString = 'redirect_url=' . rawurlencode(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));

                if (strpos($code, '?') === false) {
                    $code .= '?' . $appendQueryString;
                } else {
                    $code .= '&' . $appendQueryString;
                }
            }
        } else {
            $code = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['zoat_ajaxlogin']['pageNotFound_handling'];
            $header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
        }

        $pObj->pageErrorHandler($code, $header, $params['reasonText']);
    }
}
