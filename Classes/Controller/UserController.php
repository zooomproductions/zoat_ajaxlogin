<?php
class Tx_Ajaxlogin_Controller_UserController extends Tx_Extbase_MVC_Controller_ActionController {
	
	/**
	 * @var Tx_Ajaxlogin_Domain_Repository_UserRepository
	 */
	protected $userRepository;	
	
	public function initializeAction() {
		$this->userRepository = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Repository_UserRepository');
	}
	
	public function infoAction() {		
		$user = $this->userRepository->findCurrent();
		
		if(!is_null($user)) {
			$this->view->assign('user', $user);
		} else {
			$this->response->setStatus(401);
			$this->forward('login');
		}
	}
	
	public function loginAction() {
		$token = 'tx-ajaxlogin-form' . time();
		$this->view->assign('formToken', $token);
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);
	}
	
	public function authenticateAction() {
		$user = $this->userRepository->findCurrent();
		
		if(!is_null($user)) {
			$this->forward('info');
		} else {
			$this->response->setStatus(401);
			$message = Tx_Extbase_Utility_Localization::translate('authentication_failed', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->forward('login');
		}
	}
	
	/**
	 * Displays a form for creating a new blog
     *
     * @param Tx_Ajaxlogin_Domain_Model_User $newUser A fresh user object taken as a basis for the rendering
     * @return void
     * @dontvalidate $user
	 */
	public function newAction(Tx_Ajaxlogin_Domain_Model_User $user = null) {		
		if(!is_null($user)) {
			$this->response->setStatus(409);
		}
		
		$token = 'tx-ajaxlogin-form' . time();
		$this->view->assign('formToken', $token);
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);
		
		$this->view->assign('user', $user);
	}
	
	/**
	 * Creates a new user
     *
     * @param Tx_Ajaxlogin_Domain_Model_User $user A fresh User object which has not yet been added to the repository
     * @return void
	 */
	public function createAction(Tx_Ajaxlogin_Domain_Model_User $user) {
		$check = $this->userRepository->findOneByUsername($user->getUsername());
		
		if(!is_null($check)) {
			$message = Tx_Extbase_Utility_Localization::translate('duplicate_username', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::ERROR);
			$this->forward('new', null, null, $this->request->getArguments());
		}
		
		$password = $user->getPassword();
		
		$password = Tx_Ajaxlogin_Utility_RSA::decrypt($password);		
		$password = Tx_Ajaxlogin_Utility_Password::salt($password);

		$user->setPassword($password);
		
		$this->userRepository->add($user);
	}
	
	public function logoutAction() {
		$GLOBALS['TSFE']->fe_user->logoff();
		$this->forward('login');
	}
	
	public function showAction() {
		$user = $this->userRepository->findCurrent();
		
		if(!is_null($user)) {
			$this->view->assign('user', $user);
		} else {
			$this->response->setStatus(401);
		}
	}
	
	public function editAction() {
		$this->view->assign('user', $this->userRepository->findCurrent());
	}
	
	/**
	 * Updates an existing user
	 * 
	 * @param Tx_Ajaxlogin_Domain_Model_User
	 * @return void
	 */
	public function updateAction(Tx_Ajaxlogin_Domain_Model_User $user) {
		$this->userRepository->update($user);
		$this->flashMessageContainer->add('User updated');
		$this->redirect('show');
	}
	
	public function forgotPasswordAction() {
		$token = 'tx-ajaxlogin-form' . time();
		$this->view->assign('formToken', $token);
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $usernameOrEmail
	 */
	public function resetPasswordAction($usernameOrEmail = '') {
		$user = null;
		
		if(!empty($usernameOrEmail) && t3lib_div::validEmail($usernameOrEmail)) {
			$user = $this->userRepository->findOneByEmail($usernameOrEmail);
		} else if(!empty($usernameOrEmail)) {
			$user = $this->userRepository->findOneByUsername($usernameOrEmail);
		}
		
		if(!is_null($user)) {
			return var_export(md5($user->getUsername() . $user->getEmail() . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']), true);
		} else {
			$this->response->setStatus(409);
			$message = Tx_Extbase_Utility_Localization::translate('user_notfound', 'ajaxlogin', array($usernameOrEmail));
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->forward('forgotPassword');
		}
	}
}

?>