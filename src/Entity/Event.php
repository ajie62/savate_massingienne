<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:38
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 * @ORM\Table(name="event")
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startingDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endingDate;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    public function __construct()
    {
        $this->startingDate = new \DateTime();
        $this->endingDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Event
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartingDate(): \DateTime
    {
        return $this->startingDate;
    }

    /**
     * @param mixed $startingDate
     * @return Event
     */
    public function setStartingDate($startingDate)
    {
        $this->startingDate = $startingDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndingDate(): \DateTime
    {
        return $this->endingDate;
    }

    /**
     * @param mixed $endingDate
     * @return Event
     */
    public function setEndingDate($endingDate)
    {
        $this->endingDate = $endingDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
}