<?php

class Tx_Ajaxlogin_PageNotFoundHandler {
	
	public function handleError($params, tslib_fe $pObj) {
		if(isset($params['pageAccessFailureReasons']['fe_group']) && !isset($params['pageAccessFailureReasons']['hidden'])) {
			//header(t3lib_utility_Http::HTTP_STATUS_401, true);
			
			$code = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['unauthorized_handling'];
			$header = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['unauthorized_handling_statheader'];
			
			if(t3lib_div::isFirstPartOfStr($code,'REDIRECT:')) {
				$appendQueryString = 'redirect_url=' . rawurlencode( t3lib_div::getIndpEnv('TYPO3_REQUEST_URL') );
				
				if(strpos($code, '?') === false) {
					$code .= '?' . $appendQueryString;
				} else {
					$code .= '&' . $appendQueryString;
				}
			}			
		} else {		
			$code = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ajaxlogin']['pageNotFound_handling'];
			$header = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
		}
		
		$pObj->pageErrorHandler($code, $header, $params['reasonText']);
	}
}

?>