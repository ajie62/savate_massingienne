<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 05/02/2018
 * Time: 23:47
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="license")
 * @ORM\Entity(repositoryClass="App\Repository\LicenseRepository")
 * @UniqueEntity(fields={"user", "year"})
 */
class License
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
     * @ORM\Column(type="string", length=4)
     * @Assert\NotNull(groups={"add_license"})
     * @Assert\Regex(pattern="/^[0-9]{4}$/", match=true, message="Format d'année non valide")
     */
    private $year;

    /**
     * @var null|UploadedFile
     *
     * @Assert\File(mimeTypes={"application/pdf"}, mimeTypesMessage="Le document doit être au format .pdf.")
     * @Assert\NotNull(message="Fichier PDF obligatoire")
     */
    private $licenseFile;

    /**
     * @ORM\Column(type="datetime")
     */
    private $uploadedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="licenses")
     */
    private $user;

    /**
     * License constructor.
     */
    public function __construct()
    {
        $this->setYear(date('Y'));
        $this->uploadedAt = new \DateTime();
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
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return License
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getLicenseFile()
    {
        return $this->licenseFile;
    }

    /**
     * @param mixed $licenseFile
     */
    public function setLicenseFile($licenseFile)
    {
        $this->licenseFile = $licenseFile;
    }

    /**
     * @return \DateTime
     */
    public function getUploadedAt()
    {
        return $this->uploadedAt;
    }

    /**
     * @param $uploadedAt
     */
    public function setUploadedAt($uploadedAt)
    {
        $this->uploadedAt = $uploadedAt;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return License
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
}