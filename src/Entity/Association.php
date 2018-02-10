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
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="text", length=500, nullable=true)
     * @Assert\Length(
     *     max="500",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $textIntro;

    /**
     * @ORM\Column(type="text", length=500, nullable=true)
     * @Assert\Length(
     *     max="500",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $textInfo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex(
     *     "/^0[1-8]([-. ]?[0-9]{2}){4}$/",
     *     match=true,
     *     message="Les chiffres peuvent être attachés ou séparés par des espaces, des tirets, des points. Il doit être composé de 10 chiffres."
     * )
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(
     *     max="255",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(message="Il ne s'agit pas d'une adresse email valide.")
     */
    private $mail;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     * @Assert\Length(
     *     max="255",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $aboutUs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TeamMember", mappedBy="association", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $teamMembers;

    /**
     * Association constructor.
     */
    public function __construct()
    {
        $this->teamMembers = new ArrayCollection();
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
    public function getTeamMembers()
    {
        return $this->teamMembers;
    }

    /**
     * @param mixed $teamMembers
     * @return Association
     */
    public function setTeamMembers($teamMembers)
    {
        $this->teamMembers = $teamMembers;
        return $this;
    }

    /**
     * @param TeamMember $teamMember
     * @return $this
     */
    public function addTeamMember(TeamMember $teamMember)
    {
        $teamMember->setAssociation($this);
        $this->teamMembers->add($teamMember);
        return $this;
    }
}
