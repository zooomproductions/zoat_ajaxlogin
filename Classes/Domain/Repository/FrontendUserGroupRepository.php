<?php

namespace Zooom\ZoatAjaxLogin\Domain\Repository;

class FrontendUserGroupRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
{
    /**
     * @param array $uidArray
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUidArray(array $uidArray)
    {
        $query = $this->createQuery();

        $query->matching($query->in('uid', $uidArray));

        return $query->execute();
    }
}
