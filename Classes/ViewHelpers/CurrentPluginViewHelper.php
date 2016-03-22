<?php

namespace Zooom\ZoatAjaxlogin\ViewHelpers;

class CurrentPluginViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Render the element.
     *
     * @return string
     */
    public function render()
    {
        return intval($GLOBALS['TSFE']->type) ? 'Widget' : 'Profile';
    }
}
