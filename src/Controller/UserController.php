<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:21
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user.index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('user/index.html.twig');
    }

    /**
     * @Route("/user/{username}", name="user.read")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function read()
    {
        return $this->render('user/read.html.twig');
    }

    /**
     * @Route("/user/{id}/update", name="user.update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update()
    {
        return $this->render('user/update.html.twig');
    }
}
