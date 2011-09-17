<?php

class Tx_Ajaxlogin_Utility_RedirectUrl {
	
	public static function findRedirectUrl($url, $fallback = '') {
		$res = '';
			
		if(!empty($fallback)) {
			$res = $fallback;
		}
		
		if(!empty($url)) {
			$parts = parse_url($url);
			
			if(!empty($parts['query'])) {
				$query = t3lib_div::explodeUrl2Array($parts['query']);
				
				if(!empty($query['redirect_url'])) {
					$res = $query['redirect_url'];
				}
			}
		}
	
		if(!empty($res) && is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['redirectUrl_postProcess'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['redirectUrl_postProcess'] as $_funcRef) {
				$_params = array(
					'urlParts' => $parts,
					'queryParts' => $query,
					'redirect_url' => &$res
				);
				t3lib_div::callUserFunction($_funcRef, $_params, $this);
			}
		}
		
		return $res;
	}

}

?>