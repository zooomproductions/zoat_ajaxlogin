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
		} else if (t3lib_extMgm::isLoaded('t3sec_saltedpw')) { 
			require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/staticlib/class.tx_t3secsaltedpw_div.php'; 
			if (tx_t3secsaltedpw_div::isUsageEnabled()) { 
				require_once t3lib_extMgm::extPath('t3sec_saltedpw').'res/lib/class.tx_t3secsaltedpw_phpass.php'; 
				$objPHPass = t3lib_div::makeInstance('tx_t3secsaltedpw_phpass'); 
				$string = $objPHPass->getHashedPassword($string); 
			} 
		} 
			
		return $string;
	}
}
?>