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
			$user->setForgotHash(md5(t3lib_div::generateRandomBytes(64)));
			$user->setForgotHashValid((time() + (24 * 3600)));
			$this->view->assign('user', $user);
			
			//<f:uri.action action="editPassword" arguments="{email:user.email,forgotHash:user.forgotHash}" absolute="true" />
			
			$uriBuilder = $this->controllerContext->getUriBuilder();
			$uri = $uriBuilder->reset()->setCreateAbsoluteUri(true)->uriFor('editPassword', array(
				'email' => $user->getEmail(),
				'forgotHash' => $user->getForgotHash()
			));
			
			$subject = Tx_Extbase_Utility_Localization::translate('resetpassword_notification_subject', 'ajaxlogin', array(
				t3lib_div::getIndpEnv('TYPO3_HOST_ONLY')
			));
			
			$message = Tx_Extbase_Utility_Localization::translate('resetpassword_notification_message', 'ajaxlogin', array(
				$user->getName(),
				$uri,
				strftime($this->settings['notificationMail']['strftimeFormat'])
			));
			
			Tx_Ajaxlogin_Utility_NotifyMail::send($user->getEmail(), $subject, $message);			
		} else {
			$this->response->setStatus(409);
			$message = Tx_Extbase_Utility_Localization::translate('user_notfound', 'ajaxlogin', array($usernameOrEmail));
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->forward('forgotPassword');
		}
	}
	
	/**
	 * @param string $forgotHash
	 * @param string $email
	 */
	public function editPasswordAction($forgotHash = '', $email = '') {
		if(!empty($forgotHash) && !empty($email)) {
			$user = $this->userRepository->findOneByForgotHashAndEmail($forgotHash, $email);
		} else {
			$user = $this->userRepository->findCurrent();
		}
		
		if(!is_null($user)) {
			$this->view->assign('user', $user);
		} else {
			$this->response->setStatus(401);
		}
	}
	
	/**
	 * @param array $password
	 * @param Tx_Ajaxlogin_Domain_Model_User $user
	 */
	public function updatePasswordAction($password, Tx_Ajaxlogin_Domain_Model_User $user) {
		$passwordValidator = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Validator_CustomRegularExpressionValidator');
		
		$passwordValidator->setOptions(array(
			'object' => 'User',
			'property' => 'password'
		));
		
		if(!empty($password['new']) && strcmp($password['new'], $password['check']) == 0 && $passwordValidator->isValid($password['new'])) {
			$saltedPW = Tx_Ajaxlogin_Utility_Password::salt($password['new']);
			$user->setPassword($saltedPW);
			$user->setForgotHash('');
			$user->setForgotHashValid(0);
			$message = Tx_Extbase_Utility_Localization::translate('password_updated', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->redirect('show');
		} else {
			$this->response->setStatus(409);
			$message = Tx_Extbase_Utility_Localization::translate('password_invalid', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->forward('editPassword');
		}
	}
}

?>