<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 04/02/2018
 * Time: 14:00
 */

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function getBaseQuery($alias)
    {
        return $this->createQueryBuilder($alias);
    }

    public function getUpcomingEvents($q = null, $alias = 'evt')
    {
        if($q === null) {
            $q = $this->getBaseQuery($alias);
        }

        $q->where('evt.startingDate > CURRENT_DATE()');
        return $q;
    }
}