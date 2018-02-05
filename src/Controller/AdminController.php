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
use App\Form\AssociationType;
use App\Form\EventType;
use App\Form\NewsType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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

        return $this->render('admin/members/members.html.twig', [
            'membersList' => $membersList
        ]);
    }

    /**
     * @Route("/member/{id}/update", name="admin.update_member")
     * @param Request $request
     * @param User $user
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMember(Request $request, User $user, AuthorizationCheckerInterface $authorizationChecker)
    {
        # Building form with additional fields
        $form = $this->createForm(UserType::class, $user)->add('licenseNumber', TextType::class, ['required' => false]);

        # Check if the active user has ROLE_SUPER_ADMIN & the targeted user for update is not the active user
        if ($authorizationChecker->isGranted('ROLE_SUPER_ADMIN') && $user !== $this->getUser()) {
            # Add a field 'roles' to the form
            $form->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => "ROLE_USER",
                    'Modérateur' => 'ROLE_ADMIN',
                    'Administrateur' => 'ROLE_SUPER_ADMIN'
                ],
                'data' => $user->getRoles()[0] ?? null,
                'mapped' => false,
                'preferred_choices' => [
                    'Utilisateur' => 'ROLE_USER'
                ]
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            # Check if roles field exists (i.e user is logged in as ROLE_USER_ADMIN)
            if ($form->has('roles')) {
                # Get chosen role
                $role = $form->get('roles')->getData();
                # Assign role to user
                $user->setRoles([$role]);
            }

            # set the updatedAt datetime
            $user->setUpdatedAt(new \DateTime());

            # Flush
            $this->em->flush();
            # Redirection
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

    /**
     * List all the news
     *
     * @Route("/news", name="admin.news")
     * @return Response
     */
    public function news()
    {
        $news = $this->em->getRepository(News::class)->findAll();

        return $this->render('admin/news/news.html.twig', [
            'listNews' => $news
        ]);
    }

    /**
     * Create a news
     *
     * @Route("/news/create", name="admin.news_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->setNews($request, new News());
    }

    /**
     * Read a news
     *
     * @Route("/news/{id}", name="admin.news_read", requirements={"id" = "\d+"})
     * @param News $news
     * @return Response
     */
    public function read(News $news): Response
    {
        return $this->render('admin/news/read.html.twig', [
            'news' => $news
        ]);
    }

    /**
     * Update a news
     *
     * @Route("/news/{id}/update", name="admin.news_update", requirements={"id" = "\d+"})
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
     *
     * @Route("/news/{id}/delete", name="admin.news_delete", requirements={"id" = "\d+"})
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function delete(Request $request, News $news): Response
    {
        $form = $this->getDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            $this->em->remove($news);
            $this->em->flush();

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