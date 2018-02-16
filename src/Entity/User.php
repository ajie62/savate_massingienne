<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:32
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @UniqueEntity(fields={"username"}, message="Ce nom d'utilisateur existe déjà.")
 * @UniqueEntity(fields={"email"}, message="Cette adresse mail est déjà utilisée.")
 * @UniqueEntity(fields={"licenseNumber"}, message="Ce numéro de licence est déjà utilisé.")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const USER = "ROLE_USER";
    const MODERATEUR = "ROLE_MODERATEUR";
    const ADMIN = "ROLE_SUPER_ADMIN";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="2",
     *     minMessage="Ce champ doit être composé de {{ limit }} caractères minimum.",
     *     max="20",
     *     maxMessage="Ce champ doit être composé de {{ limit }} caractères maximum.",
     * )
     */
    private $username;

    /**
     * @ORM\Column(type="string", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="2",
     *     minMessage="Ce champ doit être composé de {{ limit }} caractères minimum.",
     *     max="20",
     *     maxMessage="Ce champ doit être composé de {{ limit }} caractères maximum.",
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="2",
     *     minMessage="Ce champ doit être composé de {{ limit }} caractères minimum.",
     *     max="20",
     *     maxMessage="Ce champ doit être composé de {{ limit }} caractères maximum.",
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @ORM\Column(type="string", type="string", length=64)
     * @Assert\Length(
     *     min="8",
     *     minMessage="Votre mot de passe doit comporter {{ limit }} caractères minimum.",
     * )
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\License", mappedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="license_id", referencedColumnName="id", nullable=true)
     */
    private $licenses;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(
     *     min="5",
     *     minMessage="Le numéro de licence est composé de {{ limit }} chiffres minimum.",
     *     max="10",
     *     maxMessage="Le numéro de licence est composé de {{ limit }} chiffres maximum."
     * )
     * @Assert\Regex(pattern="/^\d+$/", match=true, message="Le numéro de licence ne doit contenir que des chiffres.")
     */
    private $licenseNumber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $subscribedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="users")
     */
    private $events;

    /**
     * @Assert\Image(
     *     groups={"img_edition"},
     *     mimeTypes={"image/jpg", "image/jpeg", "image/gif", "image/png"},
     *     mimeTypesMessage="L'image doit être au format jpg, jpeg, png ou gif."
     * )
     */
    private $uploadedFile;

    /**
     * @var string
     *
     * @ORM\Column(name="image_path", type="string", nullable=true)
     */
    private $imagePath;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = [self::USER];
        $this->subscribedAt = new \DateTime();
        $this->licenses = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->roles === [self::ADMIN] ? $this->isActive = true : $this->isActive = false;
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
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param mixed $firstname
     */
    public function setFirstname($firstname): void
    {
        $this->firstname = $firstname;
    }

    /**
     * @return mixed
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param mixed $lastname
     */
    public function setLastname($lastname): void
    {
        $this->lastname = $lastname;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param mixed $licenses
     * @return User
     */
    public function setLicenses($licenses)
    {
        $this->licenses = $licenses;
        return $this;
    }

    /**
     * @param License $license
     * @return User
     */
    public function addLicense(License $license): self
    {
        $license->setUser($this);
        $this->getLicenses()->add($license);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseNumber()
    {
        return $this->licenseNumber;
    }

    /**
     * @param mixed $licenseNumber
     * @return User
     */
    public function setLicenseNumber($licenseNumber)
    {
        $this->licenseNumber = $licenseNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubscribedAt()
    {
        return $this->subscribedAt;
    }

    /**
     * @param \DateTime $subscribedAt
     * @return User
     */
    public function setSubscribedAt($subscribedAt)
    {
        $this->subscribedAt = $subscribedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     * @return User
     */
    public function setEvents($events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    /**
     * @param mixed $uploadedFile
     * @return User
     */
    public function setUploadedFile($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        return $this;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * @param string $imagePath
     * @return User
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            $this->isActive
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
       list(
           $this->id,
           $this->username,
           $this->password,
           $this->isActive
           ) = unserialize($serialized)
       ;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isActive;
    }
}