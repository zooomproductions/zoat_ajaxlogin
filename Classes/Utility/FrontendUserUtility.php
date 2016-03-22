<?php

namespace Zooom\ZoatAjaxlogin\Utility;

class FrontendUserUtility
{
    public static function signin(\Zooom\ZoatAjaxlogin\Domain\Model\FrontendUser $user)
    {
        $GLOBALS ['TSFE']->fe_user;
        unset($GLOBALS ['TSFE']->fe_user->user);
        $GLOBALS ['TSFE']->fe_user->createUserSession($user->_getProperties());
        $GLOBALS ['TSFE']->fe_user->loginSessionStarted = true;
        $GLOBALS ['TSFE']->fe_user->user = $GLOBALS ['TSFE']->fe_user->fetchUserSession();
        $GLOBALS ['TSFE']->loginUser = 1;
    }
}
