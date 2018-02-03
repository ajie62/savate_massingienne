<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:12
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home.index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/home/read", name="home.read")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function read()
    {
        return $this->render('home/read.html.twig');
    }

    /**
     * @Route("/home/update", name="home.update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update()
    {
        return $this->render('home/update.html.twig');
    }
}