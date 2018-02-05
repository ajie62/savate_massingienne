<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 05/02/2018
 * Time: 00:13
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserService
{
    private $entityManager;
    private $tokenStorage;

    /**
     * UserService constructor.
     * @param $entityManager
     * @param $tokenStorage
     */
    public function __construct($entityManager, $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get a user by username.
     *
     * @param null $username
     * @param $self
     * @return mixed
     */
    public function getUserByUsername($username = null, &$self)
    {
        # If the username is null, user is the logged one
        if (is_null($username)) {
            $user = $this->tokenStorage->getToken()->getUser();
            $self = true;
        } else {
            # fetch the user by username from the repository
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        }

        # Throw an exception if the user is null
        if (is_null($user)) {
            throw new NotFoundHttpException('404 NOT FOUND');
        }

        return $user;
    }
}
