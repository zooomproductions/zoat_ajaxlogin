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
		$setup = Tx_Ajaxlogin_Utility_TypoScript::parse(Tx_Ajaxlogin_Utility_TypoScript::getSetup());
		
		$headers = array(
			'From: ' . $setup['mailFromName'] . ' <' . $setup['mailFrom'] . '>'
		);
		
		return t3lib_div::plainMailEncoded($to, $subject, $message, implode(LF, $headers));
	}
}

?>