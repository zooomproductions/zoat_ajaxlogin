<?php
class Tx_Ajaxlogin_Controller_UserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Ajaxlogin_Domain_Repository_UserRepository
	 */
	protected $userRepository;

	/**
	 * @var Tx_Ajaxlogin_Domain_Repository_UserGroupRepository
	 */
	protected $userGroupRepository;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->userRepository = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Repository_UserRepository');
		$this->userGroupRepository = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Repository_UserGroupRepository');
		$this->response->setHeader('X-Ajaxlogin-view', substr($this->actionMethodName, 0, -6));
	}
	
	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param Tx_Extbase_View_ViewInterface $view The view to be initialized
	 * 
	 * @return void
	 */
	protected function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		parent::initializeView($view);
		$view->assign('layout', ($GLOBALS['TSFE']->type>0)?'Widget':'Profile');
	}	

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		return false;
	}
		
	/**
	 * Displays the logged-in user's info 
	 * or forwards to the login form if a user is not logged in
	 * 
	 * @return void
	 */
	public function infoAction() {
		$user = $this->userRepository->findCurrent();

		if(!is_null($user)) {
			$this->view->assign('user', $user);
		} else {
				// needed in order to trigger the JS AJAX error callback
			$this->response->setStatus(401);
			$this->forward('login');
		}
	}

	/**
	 * Displays the login form
	 * 
	 * @return void
	 */
	public function loginAction() {
		$token = $this->getFormToken();
		$this->view->assign('formToken', $token);
		
		/* pass hidden field from e.g. rsaauth to the view */
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'])) {
			$_params = array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['felogin']['loginFormOnSubmitFuncs'] as $funcRef) { list($onSub, $hid) = t3lib_div::callUserFunction($funcRef, $_params, $this);
				$onSubmitAr[] = $onSub;
				$extraHiddenAr[] = $hid;
			}
		}
		$this->view->assign('additionalHiddenFields', implode("\n", $extraHiddenAr));
		$this->view->assign('onSubmitCode', implode(' ', $onSubmitAr));

		
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);
	}
	
	/**
	 * Gets called by the JS hijaxing the login form (see tx_ajaxlogin.api.User.authenticate in JS generated by the TS)
	 * 1. forwards to user info if user is logged in
	 * 2. forwards to the login form if a user didn't manage to log in
	 * 
	 * @return void
	 */
	public function authenticateAction() {
		$user = $this->userRepository->findCurrent();

		if (!is_null($user)) {
			$message = Tx_Extbase_Utility_Localization::translate('login_successful', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);

			$referer = t3lib_div::_GP('referer');
			$redirectUrl = t3lib_div::_GP('redirectUrl');
			$redirect_url = Tx_Ajaxlogin_Utility_RedirectUrl::findRedirectUrl($referer, $redirectUrl);
			if (!empty($redirect_url)) {
				$this->response->setHeader('X-Ajaxlogin-redirectUrl', $redirect_url);
			}
			$this->forward('info');
		} else {
				// needed in order to trigger the JS AJAX error callback
			$this->response->setStatus(401);
			$message = Tx_Extbase_Utility_Localization::translate('authentication_failed', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::ERROR);
			$this->forward('login');
		}
	}

	/**
	 * Displays a form for creating a new user
	 *
	 * @param Tx_Ajaxlogin_Domain_Model_User $newUser A fresh user object taken as a basis for the rendering
	 * @dontvalidate $user
	 * 
	 * @return void
	 */
	public function newAction(Tx_Ajaxlogin_Domain_Model_User $user = null) {
		if ($user) {
				// needed in order to trigger the JS AJAX error callback
			$this->response->setStatus(409);
		}
		if ($user && $user->getUid()) {
				// somehow the cHash got hacked, user should not have an uid
			$user = null;
		}
				
		$token = $this->getFormToken();
		$this->view->assign('formToken', $token);
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);

		$this->view->assign('user', $user);
	}

	/**
	 * Creates a new user
	 *
	 * @param Tx_Ajaxlogin_Domain_Model_User $user A fresh User object which has not yet been added to the repository
	 * @param string $password_check
	 * 
	 * @return void
	 */
	public function createAction(Tx_Ajaxlogin_Domain_Model_User $user, $password_check) {
		if ($user && $user->getUid()) {
			// somehow the cHash got hacked
			$this->forward('new');
		}
		if (t3lib_div::_GP('additionalInfo')) {
			// honeypot field was filled
			$this->forward('new');
		}
			// TODO: clean this up and move it to the proper validators!!!
			// this much of validation shouldn't have found its way into the controller
			
		// START of MOVE TO VALIDATOR task 
		$objectError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'user');
		$emailError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'email');
		$usernameError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'username');
		$passwordError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'password');

		$checkEmail = $this->userRepository->findOneByEmail($user->getEmail());
		$checkUsername = $this->userRepository->findOneByUsername($user->getUsername());

		if (!is_null($checkEmail)) {
			$emailError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Duplicate email address', 1320783534)
			));
		}

		if (!is_null($checkUsername)) {
			$usernameError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Duplicate username', 1320703758)
			));
		}

		if(strcmp($user->getPassword(), $password_check) != 0) {
			$passwordError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Password does not match', 1320703779)
			));
		}

		if(count($emailError->getErrors())) {
			$objectError->addErrors(array(
				$emailError
			));
		}

		if(count($usernameError->getErrors())) {
			$objectError->addErrors(array(
				$usernameError
			));
		}

		if(count($passwordError->getErrors())) {
			$objectError->addErrors(array(
				$passwordError
			));
		}

		if(count($objectError->getErrors())) {
			$requestErrors = $this->request->getErrors();

			$requestErrors[] = $objectError;

			$this->request->setErrors($requestErrors);

				// needed in order to trigger the JS AJAX error callback
			$this->response->setStatus(409);
			$this->forward('new');
		}
		// END of MOVE TO VALIDATOR task 

		$userGroups = $this->userGroupRepository->findByUidArray(t3lib_div::intExplode(',', $this->settings['defaultUserGroups']));

		$password = $user->getPassword();

		$password = Tx_Ajaxlogin_Utility_Password::salt($password);

		foreach ($userGroups as $userGroup) {
			$user->getUsergroup()->attach($userGroup);
		}

		$user->setPassword($password);

		// add a hash to verify the account by sending an e-mail
		$user->setVerificationHash(md5(t3lib_div::generateRandomBytes(64)));
		$user->setDisable(true);
		
		$this->userRepository->add($user);
		$this->userRepository->_persistAll();

		$message = Tx_Extbase_Utility_Localization::translate('signup_successful', 'ajaxlogin');
		$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);

		$this->view->assign('user', $user);

		$emailSubject = Tx_Extbase_Utility_Localization::translate('signup_notification_subject', 'ajaxlogin', array(
			t3lib_div::getIndpEnv('TYPO3_HOST_ONLY')
		));

		$emailBodyContent = $this->view->render();

		$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail->setFrom(array($this->settings['notificationMail']['emailAddress'] => $this->settings['notificationMail']['sender']));
		$mail->setTo(array($user->getEmail() => $user->getName()));
		$mail->setSubject($emailSubject);
		$mail->setBody($emailBodyContent);
		$mail->send();

		$referer = t3lib_div::_GP('referer');
		$redirectUrl = t3lib_div::_GP('redirectUrl');
		$redirect_url = Tx_Ajaxlogin_Utility_RedirectUrl::findRedirectUrl($referer, $redirectUrl);
		if (!empty($redirect_url)) {
			$this->response->setHeader('X-Ajaxlogin-redirectUrl', $redirect_url);
		}

		$this->forward('info');
	}

	/**
	 * Perfoms the user log out and redirects to the login form
	 * 
	 * @return void
	 */
	public function logoutAction() {
		$message = Tx_Extbase_Utility_Localization::translate('logout_successful', 'ajaxlogin');
		$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);

		$GLOBALS['TSFE']->fe_user->logoff();
		$this->forward('login');
	}
	
	/**
	 * Shows the logged-in user details
	 * 
	 * @return void
	 */
	public function showAction() {
		$user = $this->userRepository->findCurrent();

		$this->view->assign('user', $user);
	}

	/**
	 * Shows the user edit details form
	 *
	 * @param Tx_Ajaxlogin_Domain_Model_User $user
	 * @dontvalidate $user
	 * 
	 * @return void
	 */
	public function editAction(Tx_Ajaxlogin_Domain_Model_User $user = null) {
			// double check if the passed user is indeed currently logged in user
		$currentUser = $this->userRepository->findCurrent();
		
		if (!$user || $user->getUid() != $currentUser->getUid()) {
			$user = $currentUser;
		}
		
		$this->view->assign('user', $user);
	}

	/**
	 * Updates an existing user
	 *
	 * @param Tx_Ajaxlogin_Domain_Model_User
	 * 
	 * @return void
	 */
	public function updateAction(Tx_Ajaxlogin_Domain_Model_User $user) {
			// double check if the passed user is indeed currently logged in user
		$currentUser = $this->userRepository->findCurrent();
		
		if ($user->getUid() != $currentUser->getUid()) {
				// no way...
			$this->forward('edit');
		}
				
			// TODO: clean this up and move it to the proper validators!!!
			// this much of validation shouldn't have found its way into the controller
		// START of MOVE TO VALIDATOR task 
		$objectError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'user');
		$emailError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'email');

		$checkEmail = $this->userRepository->findOneByEmail($user->getEmail());

		if (!is_null($checkEmail) && $checkEmail->getUid() != $user->getUid()) {
			$emailError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Duplicate email address', 1320783534)
			));
		}

		if(count($emailError->getErrors())) {
			$objectError->addErrors(array(
				$emailError
			));
		}

		if(count($objectError->getErrors())) {
			$requestErrors = $this->request->getErrors();

			$requestErrors[] = $objectError;

			$this->request->setErrors($requestErrors);
			$this->forward('edit');
		}
		// END of MOVE TO VALIDATOR task
		
		$this->userRepository->update($user);
		$this->flashMessageContainer->add('User updated');
		$this->forward('show');
	}

	/**
	 * Activates an account based on the link in the activation mail
	 * 
	 * @param string $verificationHash
	 * @param string $email
	 * 
	 * @return void
	 */
	public function activateAccountAction($verificationHash = '', $email = '') {
		if(!empty($verificationHash) && !empty($email)) {
			$user = $this->userRepository->findOneByVerificationHashAndEmail($verificationHash, $email);
		}

		if(!is_null($user)) {
			$userGroups = $this->userGroupRepository->findByUidArray(t3lib_div::intExplode(',', $this->settings['defaultUserGroupsAfterVerification']));

			foreach ($userGroups as $userGroup) {
				$user->getUsergroup()->attach($userGroup);
			}

			$user->setVerificationHash(null);
			$user->setDisable(false);
				
			$this->userRepository->update($user);
			$this->userRepository->_persistAll();

				// automatically sign in the user
			Tx_Ajaxlogin_Utility_FrontendUser::signin($user);
	
			$message = Tx_Extbase_Utility_Localization::translate('account_activated', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);
			//$this->redirectToURI('/');
		} else {
			$message = Tx_Extbase_Utility_Localization::translate('invalid_activation_link', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::ERROR);
			//$this->response->setStatus(409);
		}
	}

	/**
	 * Shows the user/email form
	 * 
	 * @return void
	 */
	public function forgotPasswordAction() {
		$token = $this->getFormToken();
		$this->view->assign('formToken', $token);
		$this->response->setHeader('X-Ajaxlogin-formToken', $token);
	}

	/**
	 * Tries to find a user by the username or email
	 * 1. If found, resets the user's forgot password hash, sends an email with the reset link, and forwards to the login form
	 * 2. If not found, displays the error message and forwards to the forgot password form again
	 * 
	 * @param string $usernameOrEmail
	 * 
	 * @return void
	 */
	public function resetPasswordAction($usernameOrEmail = '') {
		$user = null;
		$usernameOrEmail = filter_var($usernameOrEmail, FILTER_SANITIZE_SPECIAL_CHARS);
		if(!empty($usernameOrEmail) && t3lib_div::validEmail($usernameOrEmail)) {
			$user = $this->userRepository->findOneByEmail($usernameOrEmail);
		} else if(!empty($usernameOrEmail)) {
			$user = $this->userRepository->findOneByUsername($usernameOrEmail);
		}

		if(!is_null($user)) {
			$user->setForgotHash(md5(t3lib_div::generateRandomBytes(64)));
			$user->setForgotHashValid((time() + (24 * 3600)));
			$this->view->assign('user', $user);

			$emailSubject = Tx_Extbase_Utility_Localization::translate('resetpassword_notification_subject', 'ajaxlogin', array(
				t3lib_div::getIndpEnv('TYPO3_HOST_ONLY')
			));

			$emailBodyContent = $this->view->render();

			$mail = t3lib_div::makeInstance('t3lib_mail_Message');
			$mail->setFrom(array($this->settings['notificationMail']['emailAddress'] => $this->settings['notificationMail']['sender']));
			$mail->setTo(array($user->getEmail() => $user->getName()));
			$mail->setSubject($emailSubject);
			$mail->setBody($emailBodyContent);
			$mail->send();

			$message = Tx_Extbase_Utility_Localization::translate('resetpassword_notification_sent', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);

			$this->forward('info');
		} else {
			//$this->response->setStatus(409);
			$message = Tx_Extbase_Utility_Localization::translate('user_notfound', 'ajaxlogin', array($usernameOrEmail));
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::ERROR);
			$this->forward('forgotPassword');
		}
	}

	/**
	 * @param string $forgotHash
	 * @param string $email
	 * @param Tx_Ajaxlogin_Domain_Model_User $user
	 * @dontvalidate $user
	 * 
	 * @return void
	 */
	public function editPasswordAction($forgotHash = '', $email = '', Tx_Ajaxlogin_Domain_Model_User $user = NULL) {		
		if(!empty($forgotHash) && !empty($email)) {
			$user = $this->userRepository->findOneByForgotHashAndEmail($forgotHash, $email);
		} elseif (!$user || get_class($user) !== 'Tx_Ajaxlogin_Domain_Model_User') {
			$user = $this->userRepository->findCurrent();
		}
		
		if(!is_null($user)) {
			$this->view->assign('user', $user);
			$this->view->assign('forgotHash', $forgotHash);
			$this->view->assign('notExpired', true);
		} else {
			//$this->response->setStatus(401);
			$message = Tx_Extbase_Utility_Localization::translate('link_outdated', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::WARNING);
		}
	}
	
	/**
	 * @param Tx_Ajaxlogin_Domain_Model_User $user
	 * @param array $password
	 * @validate $password Tx_Ajaxlogin_Domain_Validator_PasswordsValidator
	 * @param string $forgotHash
	 * @param string $email
	 *
	 * @return void
	 */
	public function updatePasswordAction(Tx_Ajaxlogin_Domain_Model_User $user, $password, $forgotHash = '', $email = '') {
		// double check if the passed user is indeed currently logged in user
		if(!empty($forgotHash) && !empty($email)) {
			$currentUser = $this->userRepository->findOneByForgotHashAndEmail($forgotHash, $email);
		}
		
		if ($user && $user->getUid() != $currentUser->getUid()) {
			// no way...
			$this->forward('editPassword');
		} else {		
			$saltedPW = Tx_Ajaxlogin_Utility_Password::salt($password['new']);
			$user->setPassword($saltedPW);
			$user->setForgotHash('');
			$user->setForgotHashValid(0);
		}
	}	
	
	/**
	 * Shows the close account confirmation page
	 * 
	 * @return void
	 */
	public function closeAccountAction() {
		$user = $this->userRepository->findCurrent();

		$this->view->assign('user', $user);
	}

	/**
	 * Disable currently logged in user and logout afterwards
	 * @param Tx_Ajaxlogin_Domain_Model_User
	 * 
	 * @return void
	 */
	public function disableAction(Tx_Ajaxlogin_Domain_Model_User $user) {
		// double check if the passed user is indeed currently logged in user
		$currentUser = $this->userRepository->findCurrent();
		
		if ($user->getUid() != $currentUser->getUid()) {
			// no way...
			$this->forward('close');
		} else {
			$this->userRepository->update($user);
			$GLOBALS['TSFE']->fe_user->logoff();
			
			$message = Tx_Extbase_Utility_Localization::translate('account_disabled', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);
			//$this->redirectToURI('/');
		}
	}
	
	/**
	 * @return string
	 */
	protected function getFormToken() {
		return 'tx-ajaxlogin-form' . md5 ( microtime() );
	}
}

?>