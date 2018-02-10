<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:58
 */

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
     * List of events in progress and upcoming events
     * @Route("/event", name="event.index")
     *
     * Everyone can see the events list.
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     *
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
     * Subscribe and unsubscribe to an event
     * @Route("/event/{id}/subscribe", name="event.subscribe", requirements={"id" = "\d+"})
     *
     * Members who are authenticated can subscribe and unsubscribe to an event.
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function subscribe(Event $event)
    {
        $today = date_timestamp_get(new \DateTime());
        $subscriptionLimit = strtotime('-2 days', $today);

        # Return HTTP 403 Exception if the user tries to
        # subscribe to an event after the subscription limit
        if ($event->getStartingDate()->getTimestamp() < $subscriptionLimit) {
            throw new AccessDeniedHttpException();
        }

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

    /**
     * List of past events
     * @Route("/event/archives", name="event.archives")
     *
     * Everyone can see the events archives.
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     *
     * @return Response
     */
    public function archives()
    {
        $pastEvents = $this->em->getRepository(Event::class)->getPastEvents();

        return $this->render('event/archives.html.twig', [
            'pastEvents' => $pastEvents,
        ]);
    }
}
