<?php

class Tx_Ajaxlogin_Utility_UserImage {
	public static function renderFromHash($hash) {
		return 'https://typo3.org/fileadmin/userimages/' . $hash . '-small.jpg';
	}
}

?>