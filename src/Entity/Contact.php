<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 11/02/2018
 * Time: 18:31
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="contact")
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     message="Ce champ ne doit pas être vide."
     * )
     */
    private $firstname;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     message="Ce champ ne doit pas être vide."
     * )
     */
    private $lastname;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     message="Veuillez s'il vous plaît utiliser une adresse email valide."
     * )
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank(
     *     message="Ce champ ne doit pas être vide."
     * )
     * @Assert\Length(
     *     min="10",
     *     minMessage="Ce champ doit contenir {{ limit }} caractères minimum.",
     *     max="200",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotBlank(
     *     message="Ce champ ne doit pas être vide."
     * )
     * @Assert\Length(
     *     min="40",
     *     minMessage="Ce champ doit contenir {{ limit }} caractères minimum.",
     *     max="1000",
     *     maxMessage="Ce champ ne peut contenir que {{ limit }} caractères maximum."
     * )
     */
    private $content;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $sendAt;

    /**
     * Contact constructor.
     */
    public function __construct()
    {
        $this->sendAt = new \DateTime();
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
     * @return Contact
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return Contact
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return Contact
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Contact
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Contact
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @param \DateTime $sendAt
     * @return Contact
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;
        return $this;
    }
}
