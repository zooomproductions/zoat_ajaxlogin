<?php

namespace Zooom\ZoatAjaxlogin\ViewHelpers;

class HasFormErrorViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Render the element.
     *
     * @param string $for              The name of the error name (e.g. argument name or property name). This can also be a property path (like blog.title), and will then only display the validation errors of that property.
     * @param string $displayIfError
     * @param string $displayIfNoError
     *
     * @return string
     */
    public function render($for = '', $displayIfError = 'f3-error', $displayIfNoError = '')
    {
        $errors = $this->controllerContext->getRequest()->getErrors();
        if ($for !== '') {
            $propertyPath = explode('.', $for);
            foreach ($propertyPath as $currentPropertyName) {
                $errors = $this->getErrorsForProperty($currentPropertyName, $errors);
            }
        }

        if (count($errors)) {
            return $displayIfError;
        } else {
            return $displayIfNoError;
        }
    }

    /**
     * Find errors for a specific property in the given errors array.
     *
     * @param string $propertyName The property name to look up
     * @param array  $errors       An array of Tx_Fluid_Error_Error objects
     *
     * @return array An array of errors for $propertyName
     *
     * @author Christopher Hlubek <hlubek@networkteam.com>
     */
    protected function getErrorsForProperty($propertyName, $errors)
    {
        foreach ($errors as $error) {
            if ($error instanceof \TYPO3\CMS\Extbase\Validation\PropertyError) {
                if ($error->getPropertyName() === $propertyName) {
                    return $error->getErrors();
                }
            }
        }

        return array();
    }
}
