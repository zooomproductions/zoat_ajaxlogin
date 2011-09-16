<?php
/* 
 * Register necessary class names with autoloader
 *
 * $Id: $
 */
return array(
	'tx_ajaxlogin_controller_passwordcontroller' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Controller/PasswordController.php'),
	'tx_ajaxlogin_controller_usercontroller' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Controller/UserController.php'),
	'tx_ajaxlogin_domain_model_user' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Domain/Model/User.php'),
	'tx_ajaxlogin_domain_repository_userrepository' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Domain/Repository/UserRepository.php'),
	'tx_ajaxlogin_domain_validator_customregularexpressionvalidator' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Domain/Validator/CustomRegularExpressionValidator.php'),
	'tx_ajaxlogin_domain_validator_uniqueusernamevalidator' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Domain/Validator/UniqueUsernameValidator.php'),
	'tx_ajaxlogin_report_encryptionstatus' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Report/EncryptionStatus.php'),
	'tx_ajaxlogin_utility_password' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Utility/Password.php'),
	'tx_ajaxlogin_utility_rsa' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Utility/RSA.php'),
	'tx_ajaxlogin_utility_typoscript' => t3lib_extMgm::extPath('ajaxlogin', 'Classes/Utility/TypoScript.php'),

	'tx_rsaauth_backendfactory' => t3lib_extMgm::extPath('rsaauth', 'sv1/backends/class.tx_rsaauth_backendfactory.php'),
	'tx_rsaauth_storagefactory' => t3lib_extMgm::extPath('rsaauth', 'sv1/storage/class.tx_rsaauth_storagefactory.php')
);
?>
