<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 11/02/2018
 * Time: 23:59
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Allows to find users by status.
     *
     * @param $isActive
     * @return mixed
     */
    public function findByStatus($isActive)
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->andWhere('u.isActive = :isActive')
            ->setParameter('isActive', $isActive)
            ->getQuery()
            ->getResult()
        ;
    }
}