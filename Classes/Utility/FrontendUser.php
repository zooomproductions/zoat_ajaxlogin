<?php

class Tx_Ajaxlogin_Utility_FrontendUser {
	
	public static function signin(Tx_Ajaxlogin_Domain_Model_User $user) {
		$GLOBALS ['TSFE']->fe_user;
		unset ( $GLOBALS ['TSFE']->fe_user->user );
		$GLOBALS ['TSFE']->fe_user->createUserSession ( $user->_getProperties() );
		$GLOBALS ['TSFE']->fe_user->loginSessionStarted = TRUE;
		$GLOBALS ['TSFE']->fe_user->user = $GLOBALS ['TSFE']->fe_user->fetchUserSession ();
		$GLOBALS ["TSFE"]->loginUser = 1;
	}

}

?>