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
     * @ORM\Column(type="string", length=4, nullable=false)
     */
    private $year;

    /**
     * @var null|UploadedFile
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
    public function setId($id): void
    {
        $this->id = $id;
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
    public function setYear($year): void
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
    public function setLicenseFile($licenseFile): void
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