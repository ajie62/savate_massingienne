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

class EventController extends AbstractController
{
    /**
     * @Route("/event", name="event.index")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('event/index.html.twig');
    }

    /**
     * @Route("/event/create", name="event.create")
     * @return Response
     */
    public function create(): Response
    {
        return $this->render('event/create.html.twig');
    }

    /**
     * @Route("/event/{id}", name="event.read", requirements={"id" = "\d+"})
     * @return Response
     */
    public function read(): Response
    {
        return $this->render('event/read.html.twig');
    }

    /**
     * @Route("/event/{id}/update", name="event.update", requirements={"id" = "\d+"})
     * @return Response
     */
    public function update(): Response
    {
        return $this->render('event/update.html.twig');
    }

    /**
     * @Route("/event/{id}/delete", name="event.delete", requirements={"id" = "\d+"})
     * @return Response
     */
    public function delete(): Response
    {
        return $this->render('event/delete.html.twig');
    }
}
