<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:44
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="association")
 */
class Association
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $textIntro;

    /**
     * @ORM\Column(type="text")
     */
    private $textInfo;

    /**
     * @ORM\Column(type="string")
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     */
    private $mail;

    /**
     * @ORM\Column(type="text")
     */
    private $aboutUs;

    /**
     * @ORM\Column(type="string")
     */
    private $team;

    /**
     * Association constructor.
     */
    public function __construct()
    {
        $this->team = new ArrayCollection();
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
     * @return Association
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextIntro()
    {
        return $this->textIntro;
    }

    /**
     * @param mixed $textIntro
     * @return Association
     */
    public function setTextIntro($textIntro)
    {
        $this->textIntro = $textIntro;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTextInfo()
    {
        return $this->textInfo;
    }

    /**
     * @param mixed $textInfo
     * @return Association
     */
    public function setTextInfo($textInfo)
    {
        $this->textInfo = $textInfo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     * @return Association
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     * @return Association
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param mixed $mail
     * @return Association
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAboutUs()
    {
        return $this->aboutUs;
    }

    /**
     * @param mixed $aboutUs
     * @return Association
     */
    public function setAboutUs($aboutUs)
    {
        $this->aboutUs = $aboutUs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     * @return Association
     */
    public function setTeam($team)
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addToTeam(User $user)
    {
        $this->team->add($user);
        return $this;
    }
}