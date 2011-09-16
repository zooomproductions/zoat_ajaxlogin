<?php 
class Tx_Ajaxlogin_Utility_RSA {
	
	/**
	 * Decrypts the given string using the stored RSA key
	 * 
	 * @param string $string
	 * @return string
	 */
	public static function decrypt($string) {
		$backend = tx_rsaauth_backendfactory::getBackend();
		$storage = tx_rsaauth_storagefactory::getStorage();
		
		$key = $storage->get();
		
		if(!is_null($key) && substr($string, 0, 4) == 'rsa:') {			
			$string = $backend->decrypt($key, substr($string, 4));
		}
		
		return $string;
	}
}
?>