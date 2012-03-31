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

	public function initializeAction() {
		$this->userRepository = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Repository_UserRepository');
		$this->userGroupRepository = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Repository_UserGroupRepository');
	}

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 * @api
	 */
	protected function getErrorFlashMessage() {
		return false;
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
			$this->response->setStatus(401);
			$message = Tx_Extbase_Utility_Localization::translate('authentication_failed', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::ERROR);
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
		if (!is_null($user)) {
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
	 * @param string $password_check
	 * @return void
	 */
	public function createAction(Tx_Ajaxlogin_Domain_Model_User $user, $password_check) {
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
			$this->forward('new', null, null, $this->request->getArguments());
		}

		$userGroups = $this->userGroupRepository->findByUidArray(t3lib_div::intExplode(',', $this->settings['defaultUserGroups']));

		$password = $user->getPassword();

		$password = Tx_Ajaxlogin_Utility_Password::salt($password);

		foreach ($userGroups as $userGroup) {
			$user->getUsergroup()->attach($userGroup);
		}

		$user->setPassword($password);

		// add a hash to verify the account by sending an e-mail
		$user->setVerificationHash(md5(t3lib_div::generateRandomBytes(64)));

		$this->userRepository->add($user);
		$this->userRepository->_persistAll();

		Tx_Ajaxlogin_Utility_FrontendUser::signin($user);

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

	public function logoutAction() {
		$message = Tx_Extbase_Utility_Localization::translate('logout_successful', 'ajaxlogin');
		$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);

		$GLOBALS['TSFE']->fe_user->logoff();
		$this->forward('login');
	}

	public function showAction() {
		$user = $this->userRepository->findCurrent();

		$this->view->assign('user', $user);
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
			$this->forward('edit', null, null, $this->request->getArguments());
		}

		$this->userRepository->update($user);
		$this->flashMessageContainer->add('User updated');
		$this->redirect('show');
	}

	/**
	 * @param string $verificationHash
	 * @param string $email
	 */
	public function verifyAction($verificationHash = '', $email = '') {
		if(!empty($verificationHash) && !empty($email)) {
			$user = $this->userRepository->findOneByVerificationHashAndEmail($verificationHash, $email);
		}

		if(!is_null($user)) {
			$userGroups = $this->userGroupRepository->findByUidArray(t3lib_div::intExplode(',', $this->settings['defaultUserGroupsAfterVerification']));

			foreach ($userGroups as $userGroup) {
				$user->getUsergroup()->attach($userGroup);
			}

			$user->setVerificationHash(null);

			$this->userRepository->update($user);
			$this->userRepository->_persistAll();

			Tx_Ajaxlogin_Utility_FrontendUser::signin($user);
			$this->redirectToURI('/');
		} else {
			$this->response->setStatus(409);
		}
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
			$this->response->setStatus(409);
			$message = Tx_Extbase_Utility_Localization::translate('user_notfound', 'ajaxlogin', array($usernameOrEmail));
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::NOTICE);
			$this->redirect('forgotPassword');
		}
	}

	/**
	 * @param string $forgotHash
	 * @param string $email
	 * @param obj $user
	 */
	public function editPasswordAction($forgotHash = '', $email = '', $user=NULL) {		
		if(!empty($forgotHash) && !empty($email)) {
			$user = $this->userRepository->findOneByForgotHashAndEmail($forgotHash, $email);
		} elseif (!$user || get_class($user) !== 'Tx_Ajaxlogin_Domain_Model_User') {
			$user = $this->userRepository->findCurrent();
		}
		if(!is_null($user)) {
			$this->view->assign('user', $user);
		} else {
			$this->response->setStatus(401);
			$message = Tx_Extbase_Utility_Localization::translate('link_outdated', 'ajaxlogin');
			$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::WARNING);
		}
	}

	public function closeAccountAction() {
		$this->view->assign('user', $this->userRepository->findCurrent());
	}

	/**
	 * Disable currently logged in user and logout afterwards
	 * @param Tx_Ajaxlogin_Domain_Model_User
	 * @return void
	 */
	public function disableAction(Tx_Ajaxlogin_Domain_Model_User $user) {
		$this->userRepository->update($user);
		$GLOBALS['TSFE']->fe_user->logoff();
		$this->redirectToURI('/');
	}

	/**
	 * @param array $user
	 * @param array $password
	 * @return void
	 */
	public function updatePasswordAction($user, $password) {
		$user = $this->userRepository->findByUid($user['__identity']);
			
		$objectError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'user');
		$passwordError = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', 'password');
		$passwordValidator = t3lib_div::makeInstance('Tx_Ajaxlogin_Domain_Validator_CustomRegularExpressionValidator');

		$passwordValidator->setOptions(array(
			'object' => 'User',
			'property' => 'password'
		));

		if (empty($password['new'])) {
			$passwordError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Password can not be blank', 1320792347)
			));
		}

		if (strcmp($password['new'], $password['check']) != 0) {
			$passwordError->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Error_Error', 'Password does not match', 1320703779)
			));
		}

		if (!$passwordValidator->isValid($password['new'])) {
			$passwordError->addErrors($passwordValidator->getErrors());
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
			$arguments = $this->request->getArguments();
			$arguments['user'] = $user;
			$this->forward('editPassword', null, null, $arguments);
		}

		$saltedPW = Tx_Ajaxlogin_Utility_Password::salt($password['new']);
		$user->setPassword($saltedPW);
		$user->setForgotHash('');
		$user->setForgotHashValid(0);
		$message = Tx_Extbase_Utility_Localization::translate('password_updated', 'ajaxlogin');
		$this->flashMessageContainer->add($message, '', t3lib_FlashMessage::OK);
		
	}
}

?>