<?php

namespace Zooom\ZoatAjaxlogin\Validation\Validator;

use Zooom\ZoatAjaxlogin\Utility\TypoScriptUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

class CustomRegularExpressionValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    /**
      * @var array
      */
    protected $supportedOptions = array(
        'object' => array('', 'Name of the typoscript object to lookup in settings.validation', 'string'),
        'property' => array('', 'Name of the object property', 'string'),
    );

    public function isValid($value)
    {
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);

        $setup = TypoScriptUtility::getSetup();
        $setup = $typoScriptService->convertTypoScriptArrayToPlainArray($setup);

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
