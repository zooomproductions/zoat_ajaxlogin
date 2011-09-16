<?php

class Tx_Ajaxlogin_Utility_NotifyMail {
	
	/**
	 * Send a simple mail to a user, headers like "From" are set automatically
	 * 
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @return boolean
	 */
	public static function send($to, $subject, $message) {
		$setup = Tx_Ajaxlogin_Utility_TypoScript::getSetup();
		$setup = Tx_Extbase_Utility_TypoScript::convertTypoScriptArrayToPlainArray($setup);
		
		if(empty($setup['settings']['notificationMail']['from'])) {
			throw new Exception('[Ajaxlogin]: No sender was set in the plugin TS setup', 876421);
		}
		
		$headers = array(
			'From: ' . $setup['settings']['notificationMail']['from']
		);
		
		return array(
			$headers,
			$to,
			$subject,
			$message
		);
		
		return t3lib_div::plainMailEncoded($to, $subject, $message, implode(LF, $headers));
	}
}

?>