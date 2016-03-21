<?php

namespace Zooom\ZoatAjaxLogin\Validation\Validator;

class CustomRegularExpressionValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    public function isValid($value)
    {
        $setup = Tx_Ajaxlogin_Utility_TypoScript::getSetup();
        $setup = \TYPO3\CMS\Extbase\Utility\TypoScriptUtility::convertTypoScriptArrayToPlainArray($setup);

        $object = trim($this->options['object']);
        $property = trim($this->options['property']);

        $pattern = trim($setup['settings']['validation'][$object][$property]['pattern']);
        $message = trim($setup['settings']['validation'][$object][$property]['message']);

        if (!preg_match($pattern, $value)) {
            $this->addError($message, 1307626687);

            return false;
        }

        return true;
    }
}
