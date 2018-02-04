<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 18:05
 */

namespace App\Controller;

use App\Entity\Association;
use App\Entity\Event;
use App\Entity\User;
use App\Form\AssociationType;
use App\Form\EventType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 *
 * @Route("/admin")
 * @package App\Controller
 */
class AdminController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * AdminController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route(path="/", name="admin.index")
     */
    public function index()
    {
        return $this->forward('App\Controller\AdminController:members');
    }

    /**
     * @Route("/members", name="admin.members")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function members()
    {
        $membersList = $this->em->getRepository(User::class)->findAll();

        return $this->render('admin/members.html.twig', [
            'membersList' => $membersList
        ]);
    }

    /**
     * @Route("/member/{id}/update", name="admin.update_member")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMember(Request $request, User $user)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('admin.index');
        }

        return $this->render('admin/members/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/{id}/delete", name="admin.delete_member")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMember(Request $request, User $user)
    {
        if ($this->getUser() === $user) {
            return $this->redirectToRoute('admin.index');
        }

        $form = $this->createFormBuilder()->setMethod("DELETE")->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($user);
            $this->em->flush();

            return $this->redirectToRoute("admin.index");
        }

        return $this->render('admin/members/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/content", name="admin.content")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function content(Request $request)
    {
        $association = $this->em->getRepository(Association::class)->find(1);

        if (is_null($association)) {
            $association = new Association();
        }

        $form = $this->createForm(AssociationType::class, $association);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($association);
            $this->em->flush();

            return $this->redirectToRoute('admin.content');
        }

        return $this->render('admin/content.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * List all the events
     *
     * @Route("/event", name="admin.event")
     * @return Response
     */
    public function events()
    {
        $eventsRepository = $this->em->getRepository(Event::class);

        $upcomingEvents = $eventsRepository
            ->getBaseQuery('evt')
            ->orderBy('evt.startingDate', 'ASC')
            ->andWhere('evt.startingDate > CURRENT_DATE()')
            ->getQuery()
            ->getResult()
        ;

        $pastEvents = $eventsRepository
            ->getBaseQuery('evt')
            ->orderBy('evt.startingDate', 'ASC')
            ->andWhere('evt.startingDate < CURRENT_DATE()')
            ->getQuery()
            ->getResult()
        ;

        $eventsInProgress = $eventsRepository
            ->getBaseQuery('evt')
            ->andWhere('evt.startingDate <= CURRENT_DATE()')
            ->andWhere('evt.endingDate >= CURRENT_DATE()')
            ->orderBy('evt.endingDate', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $this->render('admin/event/events.html.twig', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
            'eventsInProgress' => $eventsInProgress
        ]);
    }

    /**
     * Create an event
     *
     * @Route("/event/create", name="admin.event_create")
     * @param Request $request
     * @return Response
     */
    public function createEvent(Request $request): Response
    {
        return $this->setEvent($request, new Event());
    }

    /**
     * Update an event
     *
     * @Route("/event/{id}/update", name="admin.event_update", requirements={"id" = "\d+"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function updateEvent(Request $request, Event $event): Response
    {
        return $this->setEvent($request, $event);
    }

    /**
     * Delete an event
     *
     * @Route("/event/{id}/delete", name="admin.event_delete", requirements={"id" = "\d+"})
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function deleteEvent(Request $request, Event $event): Response
    {
        $availableRedirectRoutes = ['event.index', 'admin.event'];
        $redirectRoute = $request->query->get('redirect', 'admin.event');
        $redirectRoute = in_array($redirectRoute, $availableRedirectRoutes) ? $redirectRoute : $availableRedirectRoutes[0];

        $form = $this->getDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            $this->em->remove($event);
            $this->em->flush();

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render('admin/event/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Used for event creation or edition
     *
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

            return $this->redirectToRoute('admin.event');
        }

        return $this->render('admin/event/set.html.twig', [
            'form' => $form->createView(),
            'isNewEvent' => $isNewEvent,
        ]);
    }

    public function news()
    {
        return $this->render('admin/news.html.twig');
    }

    public function team()
    {
        return $this->render('admin/team.html.twig');
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