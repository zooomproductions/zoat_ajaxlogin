<?php

class Tx_Ajaxlogin_ViewHelpers_PageTypeViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Render the element
	 * 
	 * @return string
	 */
	public function render()	 {
		return intval($GLOBALS['TSFE']->type);
	}
	
}

?>