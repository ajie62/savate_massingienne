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
use App\Entity\News;
use App\Entity\User;
use App\Form\AdminUserType;
use App\Form\AssociationType;
use App\Form\EventType;
use App\Form\NewsType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\LogicException;

/**
 * Class AdminController
 * @Route("/admin")
 *
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
     * Admin index
     * @Route(path="/", name="admin.index")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can access this page.
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index()
    {
        return $this->forward('App\Controller\AdminController:members');
    }

    /**
     * Admin 'members' section
     * @Route("/members", name="admin.members")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can access this page.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function members()
    {
        $membersList = $this->em->getRepository(User::class)->findAll();

        return $this->render('admin/members/members.html.twig', [
            'membersList' => $membersList
        ]);
    }

    /**
     * Update members information
     * @Route("/member/{id}/update", name="admin.update_member")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can update members information.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param User $user
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMember(Request $request, User $user, AuthorizationCheckerInterface $authorizationChecker)
    {
        # Get the target user
        $targetUser = $user;
        # Get the active user
        $activeUser = $this->getUser();

        # If the active user role is ROLE_MODERATEUR and the target user's is ROLE_SUPER_ADMIN
        if ($activeUser->getRoles()[0] == User::MODERATEUR && $targetUser->getRoles()[0] == User::ADMIN) {
            # Throw a new exception: a ROLE_MODERATEUR can't update a ROLE_SUPER_ADMIN member
            throw new LogicException("Cannot update admin information.");
        }

        # Create form with options (used in AdminUserType)
        $form = $this->createForm(AdminUserType::class, $user, [
            'securityChecker' => $authorizationChecker,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Check if roles field exists
            if ($form->has('roles')) {
                # Get chosen role
                $role = $form->get('roles')->getData();
                # Assign role to user
                $user->setRoles([$role]);
            }

            # Set the updatedAt datetime
            $user->setUpdatedAt(new \DateTime());
            # Flush
            $this->em->flush();
            # Redirection to admin.index
            return $this->redirectToRoute('admin.index');
        }

        return $this->render('admin/members/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete a member
     * @Route("/member/{id}/delete", name="admin.delete_member")
     *
     * Members with ROLE_SUPER_ADMIN can delete a member.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMember(Request $request, User $user)
    {
        # Members with ROLE_SUPER_ADMIN can't delete their own account
        if ($this->getUser() === $user) {
            # Thus, they are redirected to admin.index
            return $this->redirectToRoute('admin.index');
        }

        # Building the delete form.
        $form = $this->createFormBuilder()->setMethod("DELETE")->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Removing the user
            $this->em->remove($user);
            # Flush
            $this->em->flush();

            # Redirection to admin.index
            return $this->redirectToRoute("admin.index");
        }

        return $this->render('admin/members/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Website's content management section
     * @Route("/content", name="admin.content")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can access this page.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function content(Request $request)
    {
        # Fetching unique Association entity from the database
        $association = $this->em->getRepository(Association::class)->find(1);

        # If no Association entity were found, create one
        if (is_null($association)) {
            $association = new Association();
        }

        # Form creation, based on AssociationType
        $form = $this->createForm(AssociationType::class, $association);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Persist
            $this->em->persist($association);
            # Flush
            $this->em->flush();

            # Redirection to the same page
            return $this->redirectToRoute('admin.content');
        }

        return $this->render('admin/content.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * List all the events
     * @Route("/event", name="admin.event")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can access this page.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function events()
    {
        # Getting the EventRepository
        $eventRepository = $this->em->getRepository(Event::class);

        # Fetching upcoming events
        $upcomingEvents = $eventRepository
            ->getBaseQuery('evt')
            ->orderBy('evt.startingDate', 'ASC')
            ->andWhere('evt.startingDate > CURRENT_DATE()')
            ->getQuery()
            ->getResult()
        ;

        # Fetching past events
        $pastEvents = $eventRepository
            ->getBaseQuery('evt')
            ->orderBy('evt.startingDate', 'ASC')
            ->andWhere('evt.startingDate < CURRENT_DATE()')
            ->getQuery()
            ->getResult()
        ;

        # Fetching events in progress
        $eventsInProgress = $eventRepository
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
     * @Route("/event/create", name="admin.event_create")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can create an event.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return Response
     */
    public function createEvent(Request $request): Response
    {
        return $this->setEvent($request, new Event());
    }

    /**
     * Update an event
     * @Route("/event/{id}/update", name="admin.event_update", requirements={"id" = "\d+"})
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can update an event.
     * @Security("is_granted('ROLE_ADMIN')")
     *
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
     * @Route("/event/{id}/delete", name="admin.event_delete", requirements={"id" = "\d+"})
     *
     * Members with ROLE_SUPER_ADMIN can delete an event.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
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

        # Getting the delete form
        $form = $this->getDeleteForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            # Remove event
            $this->em->remove($event);
            # Flush
            $this->em->flush();

            # Redirection to the redirect route
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
        # Check if the event (in the method argument) is new
        $isNewEvent = $event->getId() === null;

        # Form creation based on EventType
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Persist
            $this->em->persist($event);
            # Flush
            $this->em->flush();

            # Redirection to admin.event
            return $this->redirectToRoute('admin.event');
        }

        return $this->render('admin/event/set.html.twig', [
            'form' => $form->createView(),
            'isNewEvent' => $isNewEvent,
        ]);
    }

    /**
     * List all the news
     * @Route("/news", name="admin.news")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can access this page.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function news()
    {
        # Fetching all the news from the database
        $news = $this->em->getRepository(News::class)->findAll();

        return $this->render('admin/news/news.html.twig', ['listNews' => $news]);
    }

    /**
     * Create a news
     * @Route("/news/create", name="admin.news_create")
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can create a news.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->setNews($request, new News());
    }

    /**
     * Read a news
     * @Route("/news/{id}", name="admin.news_read", requirements={"id" = "\d+"})
     *
     * Mmbers with ROLE_SUPER_ADMIN and ROLE_ADMIN can read a news.
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param News $news
     * @return Response
     */
    public function read(News $news): Response
    {
        return $this->render('admin/news/read.html.twig', ['news' => $news]);
    }

    /**
     * Update a news
     * @Route("/news/{id}/update", name="admin.news_update", requirements={"id" = "\d+"})
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can update a news
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function update(Request $request, News $news): Response
    {
        return $this->setNews($request, $news);
    }

    /**
     * Delete a news
     * @Route("/news/{id}/delete", name="admin.news_delete", requirements={"id" = "\d+"})
     *
     * Members with ROLE_SUPER_ADMIN can delete a news.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function delete(Request $request, News $news): Response
    {
        # Getting the delete form
        $form = $this->getDeleteForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            # Remove the news
            $this->em->remove($news);
            # Flush
            $this->em->flush();

            # Redirection to admin.news
            return $this->redirectToRoute('admin.news');
        }

        return $this->render('admin/news/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Used for news creation or edition
     *
     * @param Request $request
     * @param News $news
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function setNews(Request $request, News $news)
    {
        $isNewNews = $news->getId() === null;

        $form = $this->createForm(NewsType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isNewNews) {
                $news->setUpdatedAt(new \DateTime());
            }

            $this->em->persist($news);
            $this->em->flush();

            return $this->redirectToRoute('admin.news_read', [
                'id' => $news->getId(),
            ]);
        }

        return $this->render('admin/news/set.html.twig', [
            'form' => $form->createView(),
            'isNewNews' => $isNewNews,
        ]);
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