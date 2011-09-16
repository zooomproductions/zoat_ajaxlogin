<?php

class Tx_Ajaxlogin_Domain_Model_User extends Tx_Extbase_Domain_Model_FrontendUser {
	
	/**
	 * @var string
	 * @validate NotEmpty
	 */
	protected $name;
	
	/**
	 * @var string
	 * @validate EmailAddress
	 */
	protected $email;
	
	/**
	 * @var string
	 * @validate Tx_Ajaxlogin_Domain_Validator_CustomRegularExpressionValidator(object = User, property = username)
	 */
	protected $username;
	
	/**
	 * @var string
	 * @validate Tx_Ajaxlogin_Domain_Validator_CustomRegularExpressionValidator(object = User, property = password)
	 */
	protected $password;
	
	/**
	 * @var string
	 */
	protected $forgotHash;
	
	/**
	 * @var DateTime
	 */
	protected $forgotHashValid;
	
	public function __construct($username = '', $password = ''){
		$this->forgotHashValid = new DateTime();
		
		parent::__construct($username, $password);
	}
	
	/**
	 * @return string
	 */
	public function getForgotHash() {
		return $this->forgotHash;
	}
	
	/**
	 * @return string
	 */
	public function getForgotHashValid() {
		return $this->forgotHashValid;
	}
	
	/**
	 * @param string
	 */
	public function setForgotHash($forgotHash) {
		$this->forgotHash = $forgotHash;
	}
	
	/**
	 * @param DateTime
	 */
	public function setForgotHashValid($forgotHashValid) {
		$this->forgotHashValid = $forgotHashValid;
	}
}

?>