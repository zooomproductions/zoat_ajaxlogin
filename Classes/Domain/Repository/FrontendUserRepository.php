<?php

namespace Zooom\ZoatAjaxlogin\Domain\Repository;

class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{
    /**
     * Find an Object using the UID of the current fe_user.
     *
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User the current fe_user or null if none
     */
    public function findCurrent()
    {
        $fe_user = $GLOBALS['TSFE']->fe_user->user;

        if (!empty($fe_user)) {
            $query = $this->createQuery();
            $query->matching($query->equals('uid', intval($fe_user['uid'])));

            return $query->execute()->getFirst();
        }

        return;
    }

    /**
     * Find an Object using the UID of the current fe_user.
     *
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User
     */
    public function findOneByForgotHashAndEmail($forgotHash, $email)
    {
        $query = $this->createQuery();

        $constraints = array(
            $query->equals('forgotHash', $forgotHash),
            $query->equals('email', $email),
        );

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }

    /**
     * Find an Object using the UID of the current fe_user.
     *
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User
     */
    public function findOneByVerificationHashAndEmail($verificationHash, $email)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectEnableFields(false);

        $constraints = array(
            $query->equals('deleted', 0),
            $query->equals('verificationHash', $verificationHash),
            $query->equals('email', $email),
        );

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }

    /**
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User
     */
    public function findOneByEmail($email)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectEnableFields(false);

        $constraints = array(
                $query->equals('deleted', 0),
                $query->equals('email', $email),
        );

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }

    /**
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User
     */
    public function findOneByUsername($username)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectEnableFields(false);
        $query->getQuerySettings()->setRespectStoragePage(false);

        $constraints = array(
            $query->equals('deleted', 0),
            $query->equals('username', $username),
        );

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }

    /**
     * Find an Object using the UID of the current fe_user.
     *
     * @return \Zooom\ZoatAjaxlogin\Domain\Model\User
     */
    public function findOneByEnableHash($enableHash)
    {
        $query = $this->createQuery();

        $constraints = array(
            $query->equals('enableHash', $enableHash),
            $query->equals('disable', 0),
        );

        $query->matching($query->logicalAnd($constraints));

        return $query->execute()->getFirst();
    }

    public function _persistAll()
    {
        $this->persistenceManager->persistAll();
    }
}
