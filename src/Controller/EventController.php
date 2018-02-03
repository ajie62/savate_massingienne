<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:58
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/event", name="event.index")
     * @return Response
     */
    public function index(): Response
    {
        $events = $this->em->getRepository(Event::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/event/create", name="event.create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->setEvent($request, new Event());
    }

    /**
     * @Route("/event/{id}", name="event.read", requirements={"id" = "\d+"})
     * @param Event $event
     * @return Response
     */
    public function read(Event $event): Response
    {
        return $this->render('event/read.html.twig', [
            'event' => $event
        ]);
    }

    /**
     * @Route("/event/{id}/update", name="event.update", requirements={"id" = "\d+"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function update(Request $request, Event $event): Response
    {
        return $this->setEvent($request, $event);
    }

    /**
     * @Route("/event/{id}/delete", name="event.delete", requirements={"id" = "\d+"})
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function delete(Request $request, Event $event): Response
    {
        $form = $this->getDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            $this->em->remove($event);
            $this->em->flush();

            return $this->redirectToRoute('event.index');
        }

        return $this->render('event/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function setEvent(Request $request, Event $event)
    {
        $isNewEvent = $event->getId() === null;

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();

            return $this->redirectToRoute('event.read', [
                'id' => $event->getId(),
            ]);
        }

        return $this->render('event/set.html.twig', [
            'form' => $form->createView(),
            'isNewEvent' => $isNewEvent,
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getDeleteForm()
    {
        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();

        return $form;
    }
}
