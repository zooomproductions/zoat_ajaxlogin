<?php 
class Tx_Ajaxlogin_Utility_Password {
	
	/**
	 * Encrypts the new password before storing in database
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function salt($string) {
		if(t3lib_extMgm::isLoaded('saltedpasswords')){
			if(tx_saltedpasswords_div::isUsageEnabled('FE')) {
				$saltingInstance = tx_saltedpasswords_salts_factory::getSaltingInstance();
				$string = $saltingInstance->getHashedPassword($string);
			}
		}
			
		return $string;
	}
}
?>