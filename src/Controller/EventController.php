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
        # Event Repository
        $eventRepo = $this->em->getRepository(Event::class);
        # Fetching upcoming events
        $upcomingEvents = $eventRepo->getUpcomingEvents();
        # Fetching events in progress
        $eventsInProgress = $eventRepo->getEventsInProgress();

        return $this->render('event/index.html.twig', [
            'upcomingEvents' => $upcomingEvents,
            'eventsInProgress' => $eventsInProgress
        ]);
    }

    /**
     * @Route("/event/{id}/subscribe", name="event.subscribe")
     *
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function subscribe(Event $event)
    {
        # Get the current user
        $user = $this->getUser();

        # If the user has already subscribed
        if ($event->getUsers()->contains($user)) {
            # Unsubscribe
            $event->removeUser($user);
        } else {
            # Subscribe
            $event->addUser($user);
        }

        # Persist
        $this->em->persist($event);
        # Flush
        $this->em->flush();

        # Redirection to event.index
        return $this->redirectToRoute('event.index');
    }
}
