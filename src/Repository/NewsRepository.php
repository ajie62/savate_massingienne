<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 04/02/2018
 * Time: 18:21
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class NewsRepository extends EntityRepository
{
    public function getTwoLastNews()
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();
    }
}