<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 15/02/2018
 * Time: 00:59
 */

namespace App\Service;

use App\Entity\Association;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\LogicException;

/**
 * Class AssociationService
 * @package App\Service
 */
class AssociationService
{
    /**
     * @var Association
     */
    private $association = null;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->loadAssociation();
    }

    public function loadAssociation()
    {
        $this->association = $this->em->getRepository(Association::class)->find(1);
    }

    public function getAddress()
    {
        if ($this->association === null) {
            throw new LogicException('Chargez d\'abord l\'association.');
        }

        return $this->association->getAddress();
    }

    public function getPhoneNumber()
    {
        if ($this->association === null) {
            throw new LogicException('Chargez d\'abord l\'association.');
        }

        return $this->association->getPhoneNumber();
    }

    public function getMail()
    {
        if ($this->association === null) {
            throw new LogicException('Chargez d\'abord l\'association.');
        }

        return $this->association->getMail();
    }

    public function getAboutUs()
    {
        if ($this->association === null) {
            throw new LogicException('Chargez d\'abord l\'association.');
        }

        return $this->association->getAboutUs();
    }
}