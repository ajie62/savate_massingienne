<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 05/02/2018
 * Time: 23:49
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class LicenseRepository extends EntityRepository
{
    /**
     * Get the user's last license
     *
     * @param User $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastUserLicense(User $user)
    {
        $lastLicense = $this->createQueryBuilder('l')
            ->orderBy('l.year', 'DESC')
            ->andWhere('l.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $lastLicense;
    }
}