<?php

namespace Zooom\ZoatAjaxLogin\Domain\Model;

class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{
    /**
     * @var string
     * @validate \Zooom\ZoatAjaxLogin\Validation\Validator\CustomRegularExpressionValidator(object = FrontendUser, property = name)
     */
    protected $name;

    /**
     * @var bool
     */
    protected $disable;

    /**
     * @var string
     * @validate EmailAddress
     */
    protected $email;

    /**
     * @var string
     * @validate \Zooom\ZoatAjaxLogin\Validation\Validator\CustomRegularExpressionValidator(object = FrontendUser, property = username)
     */
    protected $username;

    /**
     * @var string
     * @validate \Zooom\ZoatAjaxLogin\Validation\Validator\CustomRegularExpressionValidator(object = FrontendUser, property = password)
     */
    protected $password;

    /**
     * @var string
     */
    protected $forgotHash;

    /**
     * @var string
     */
    protected $verificationHash;

    /**
     * @var DateTime
     */
    protected $forgotHashValid;

    public function __construct($username = '', $password = '')
    {
        $this->forgotHashValid = new DateTime();

        parent::__construct($username, $password);
    }

    /**
     * @return string
     */
    public function getForgotHash()
    {
        return $this->forgotHash;
    }

    /**
     * @return string
     */
    public function getVerificationHash()
    {
        return $this->verificationHash;
    }

    /**
     * @return string
     */
    public function getForgotHashValid()
    {
        return $this->forgotHashValid;
    }

    /**
     * @param string
     */
    public function setVerificationHash($verificationHash)
    {
        $this->verificationHash = $verificationHash;
    }

    /**
     * @param string
     */
    public function setForgotHash($forgotHash)
    {
        $this->forgotHash = $forgotHash;
    }

    /**
     * @param DateTime
     */
    public function setForgotHashValid($forgotHashValid)
    {
        $this->forgotHashValid = $forgotHashValid;
    }

    /**
     * @param bool $disable
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;
    }

    /**
     * @return bool
     */
    public function getDisable()
    {
        return $this->disable;
    }
}
