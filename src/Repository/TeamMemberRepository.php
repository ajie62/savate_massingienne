<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 08/02/2018
 * Time: 00:27
 */

namespace App\Repository;

use App\Entity\TeamMember;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;

class TeamMemberRepository extends EntityRepository
{
    public function deleteTeamMember(TeamMember $teamMember)
    {
        # Entity Manager
        $em = $this->getEntityManager();

        # Handling exception
        try {
            # Remove the team member
            $em->remove($teamMember);
            # Delete his picture
            @unlink($teamMember->getImagePath());
            # Flush
            $em->flush();

            return true;
        } catch (ORMException $e) {
            return false;
        }
    }
}