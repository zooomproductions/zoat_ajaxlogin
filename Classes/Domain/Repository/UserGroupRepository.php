<?php

class Tx_Ajaxlogin_Domain_Repository_UserGroupRepository extends Tx_Extbase_Domain_Repository_FrontendUserGroupRepository {
	
	/**
	 * @param array $uidArray
	 * @return Tx_Extbase_Persistence_QueryResult
	 */
	public function findByUidArray(array $uidArray) {
		$query = $this->createQuery();
		
		$query->matching($query->in('uid', $uidArray));
		
		return $query->execute();
	}

}

?>