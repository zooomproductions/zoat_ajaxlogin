<?php

class Tx_Ajaxlogin_Domain_Repository_UserRepository extends Tx_Extbase_Domain_Repository_FrontendUserRepository {
	
	/**
	 * Find an Object using the UID of the current fe_user
	 * @return Tx_Ajaxlogin_Domain_Model_User the current fe_user or null if none
	 */
	public function findCurrent() {
		$fe_user = $GLOBALS['TSFE']->fe_user->user;
		
		if(!empty($fe_user)) {
			$query = $this->createQuery();
			$query->matching($query->equals('uid', intval($fe_user['uid'])));
			
			return $query->execute()->getFirst();
		}
		
		return null;
	}
	
	/**
	 * Find an Object using the UID of the current fe_user
	 * @return Tx_Ajaxlogin_Domain_Model_User
	 */
	public function findOneByForgotHashAndEmail($forgotHash, $email) {
		$query = $this->createQuery();
		
		$constraints = array(
			$query->equals('forgotHash', $forgotHash),
			$query->equals('email', $email)
		);
		
		$query->matching($query->logicalAnd($constraints));
			
		return $query->execute()->getFirst();
	}
}

?>