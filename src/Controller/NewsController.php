<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:58
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    /**
     * @Route("/news", name="news.index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('news/index.html.twig');
    }

    /**
     * @Route("/news/create", name="news.create")
     * @return Response
     */
    public function create(): Response
    {
        return $this->render('news/create.html.twig');
    }

    /**
     * @Route("/news/{id}", name="news.read", requirements={"id" = "\d+"})
     * @return Response
     */
    public function read(): Response
    {
        return $this->render('news/read.html.twig');
    }

    /**
     * @Route("/news/{id}/update", name="news.update", requirements={"id" = "\d+"})
     * @return Response
     */
    public function update(): Response
    {
        return $this->render('news/update.html.twig');
    }

    /**
     * @Route("/news/{id}/delete", name="news.delete", requirements={"id" = "\d+"})
     * @return Response
     */
    public function delete(): Response
    {
        return $this->render('news/delete.html.twig');
    }
}
