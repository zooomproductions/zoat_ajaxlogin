<?php

namespace Zooom\ZoatAjaxlogin\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class RedirectUrlUtility
{
    /**
     * @static
     *
     * @param $url
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    protected static function sanitizeUrl($url)
    {

        // finds the main domain of the current host (www.typo3.org => typo3.org)
        $serverName = preg_replace('/.*(\.[\w\-_]+\.[\w]+)$/', '$1', GeneralUtility::getIndpEnv('HTTP_HOST'));
        $parts = parse_url($url);

        if ($parts['host'] && !preg_match('/' . $serverName . '$/', $parts['host'])) {
            throw new \InvalidArgumentException('Url is supposed to belong to ' . $serverName . ' but was: ' . $parts['host']);
        }

        return $url;
    }

    /**
     * @static
     *
     * @param $url
     * @param string $fallback
     *
     * @return mixed
     */
    public static function findRedirectUrl($url, $fallback = '')
    {
        $res = '';

        if (!empty($fallback)) {
            $res = $fallback;
        }

        if (!empty($url)) {
            $parts = parse_url($url);

            if (!empty($parts['query'])) {
                $query = GeneralUtility::explodeUrl2Array($parts['query']);

                if (!empty($query['redirect_url'])) {
                    $res = $query['redirect_url'];
                }
            }
        }

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['redirectUrl_postProcess'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['redirectUrl_postProcess'] as $_funcRef) {
                $_params = array(
                    'urlParts' => $parts,
                    'queryParts' => $query,
                    'redirect_url' => &$res,
                );
                GeneralUtility::callUserFunction($_funcRef, $_params, $this);
            }
        }

        return GeneralUtility::sanitizeLocalUrl($res);
    }
}
