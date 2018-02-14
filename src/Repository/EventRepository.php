<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 04/02/2018
 * Time: 14:00
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    /**
     * Returns an array of upcoming events.
     *
     * @param User|null $user
     * @return array
     */
    public function getUpcomingEvents(User $user = null)
    {
        $q = $this->createQueryBuilder('evt')
            ->orderBy('evt.startingDate', 'ASC')
            ->andWhere('evt.startingDate >= CURRENT_TIME()')
            ->andWhere('evt.endingDate > evt.startingDate')
        ;

        # If the $user argument is given
        if(!is_null($user)) {
            # Find the upcoming events he subscribed to
            $q->andWhere(':user MEMBER OF evt.users')->setParameter('user', $user->getId());
        }

        return $q->getQuery()->getResult();
    }

    /**
     * Returns an array of events which have already taken place.
     *
     * @return array
     */
    public function getPastEvents()
    {
        return $this->createQueryBuilder('evt')
            ->orderBy('evt.endingDate', 'DESC')
            ->andWhere('evt.endingDate < CURRENT_DATE()')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function getEventsInProgress()
    {
        return $this->createQueryBuilder('evt')
            ->orderBy('evt.endingDate', 'ASC')
            ->andWhere('evt.startingDate <= CURRENT_DATE()')
            ->andWhere('evt.endingDate >= CURRENT_DATE()')
            ->getQuery()
            ->getResult();
    }

    public function getTwoLastEvents()
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.startingDate', 'ASC')
            ->andWhere('e.startingDate > CURRENT_DATE()')
            ->setMaxResults(2)
            ->getQuery()
            ->getResult();
    }
}