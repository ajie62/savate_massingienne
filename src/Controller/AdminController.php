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
use App\Entity\License;
use App\Entity\News;
use App\Entity\TeamMember;
use App\Entity\User;
use App\Form\AdminUserType;
use App\Form\AssociationType;
use App\Form\EventType;
use App\Form\LicenseType;
use App\Form\NewsType;
use App\Form\TeamMemberType;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
    private $em;
    private $licensesDir;
    private $imagesDir;
    private $teamMemberThumbnailDir;
    private $emailManager;

    /**
     * AdminController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param $licenses_dir
     * @param $images_dir
     * @param $team_member_thumbnail_dir
     * @param EmailManager $emailManager
     */
    public function __construct($entityManager, $licenses_dir, $images_dir, $team_member_thumbnail_dir, EmailManager $emailManager)
    {
        $this->em = $entityManager;
        $this->licensesDir = $licenses_dir;
        $this->imagesDir = $images_dir;
        $this->teamMemberThumbnailDir = $team_member_thumbnail_dir;
        $this->emailManager = $emailManager;
    }

    /**
     * Admin index
     * @Route(path="/", name="admin.index")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     */
    public function index()
    {
        return $this->forward('App\Controller\AdminController:members');
    }

    /**
     * Admin 'members' section
     * @Route("/members", name="admin.members")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function members()
    {
        $userRepository = $this->em->getRepository(User::class);
        $activeUsers = $userRepository->findByStatus(true);
        $inactiveUsers = $userRepository->findByStatus(false);
        $licensesManager = $this->em->getRepository(License::class);

        return $this->render('admin/members/members.html.twig', [
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'licensesManager' => $licensesManager
        ]);
    }

    /**
     * Update members information
     * @Route("/member/{id}/update", name="admin.update_member")
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param Request $request
     * @param User $user
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateMember(Request $request, User $user, AuthorizationCheckerInterface $authorizationChecker)
    {
        $targetUser = $user;
        $activeUser = $this->getUser();

        if ($activeUser->getRoles()[0] == User::ADMIN) {
            if ($targetUser->getRoles()[0] == User::MAINTENANCE) {
                throw new LogicException();
            }
            if ($targetUser->getRoles()[0] == User::ADMIN) {
                if ($targetUser !== $activeUser) {
                    throw new LogicException();
                }
            }
        }

        $form = $this->createForm(AdminUserType::class, $user, [
            'securityChecker' => $authorizationChecker,
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('roles')) {
                $role = $form->get('roles')->getData();
                $user->setRoles([$role]);
            }

            $user->setUpdatedAt(new \DateTime());
            $this->em->flush();
            $this->addFlash(
                'notice',
                'Les informations de '.ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()).' ont bien été mises à jour.'
            );

            return $this->redirectToRoute('admin.index');
        }

        return $this->render('admin/members/update.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Validate a subscription
     * @Route("/member/{id}/validate", name="admin.validate_subscription")
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function validateSubscription(User $user)
    {
        $user->setIsActive(true);
        $this->em->flush();

        $this->addFlash(
            'notice',
            'L\'inscription de '.ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()).' a été validée.'
        );

        $this->emailManager->sendSubscriptionEmail($user);

        return $this->redirectToRoute('admin.index');
    }

    /**
     * Reject a subscription
     * @Route("member/{id}/reject", name="admin.reject_subscription", requirements={"id", "/\d+/"})
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function rejectSubscription(User $user)
    {
        $this->em->remove($user);
        $this->emailManager->sendSubscriptionEmail($user);
        $this->em->flush();

        $this->addFlash(
            'notice',
            'L\'inscription de ' . ucfirst($user->getFirstname()) . ' ' . ucfirst($user->getLastname()) . ' a été rejetée.'
        );

        return $this->redirectToRoute('admin.index');
    }

    /**
     * Upload a license for a member
     * @Route("/member/{id}/new-license", name="admin.member.upload_license")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function uploadLicense(Request $request, User $user)
    {
        $license = new License();
        $license->setUser($user);
        $form = $this->createForm(LicenseType::class, $license);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $licenseFile */
            # Get the license file
            $licenseFile = $license->getLicenseFile();
            # Give it a unique name, ending with the .pdf format
            $license->setName(sha1(uniqid()).'.pdf');
            # Move it to the license directory with the new name
            $licenseFile->move($this->licensesDir, $license->getName());
            $user->addLicense($license);
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash(
                'notice',
                'Une licence a bien été ajoutée pour '.ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()).'.'
            );

            return $this->redirectToRoute('admin.members');
        }

        return $this->render('admin/members/uploadLicense.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Delete a member's license
     * @Route("/members/delete-license/{id}", name="admin.member.delete_license")
     * @ParamConverter(name="license", class="App\Entity\License", options={"id" = "id"})
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param License $license
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteLicense(License $license, Request $request)
    {
        $confirmationForm = $this->createFormBuilder();
        $form = $confirmationForm->getForm();
        $form->handleRequest($request);
        $user = $license->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $this->licensesDir.DIRECTORY_SEPARATOR.$license->getName();
            $this->em->remove($license);
            $this->em->flush();

            if (file_exists($filename))
                unlink($filename);

            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();

            # Flash message
            $this->addFlash(
                'notice',
                'La licence '.$license->getYear().'/'.($license->getYear() + 1).' de '. ucfirst($firstname) .' '. ucfirst($lastname) .' a bien été supprimée.'
            );

            return $this->redirectToRoute('admin.members');
        }

        return $this->render('admin/members/deleteLicense.html.twig', [
            'form' => $form->createView(),
            'license' => $license
        ]);
    }

    /**
     * Delete a member
     * @Route("/member/{id}/delete", name="admin.delete_member")
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteMember(Request $request, User $user)
    {
        if ($this->getUser() === $user)
            return $this->redirectToRoute('admin.index');

        $form = $this->createFormBuilder()->setMethod("DELETE")->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('notice', 'Le membre a bien été supprimé.');

            return $this->redirectToRoute("admin.index");
        }

        return $this->render('admin/members/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Website's content management section
     * @Route("/content", name="admin.content")
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function content(Request $request)
    {
        $association = $this->em->getRepository(Association::class)->find(1);
        $teamMembers = $this->em->getRepository(TeamMember::class)->findAll();

        # If no Association entity were found, create one
        if (is_null($association))
            $association = new Association();

        $teamMember = new TeamMember();
        $formAddTeamMember = $this->createForm(TeamMemberType::class, $teamMember);
        $formAddTeamMember->handleRequest($request);

        if ($formAddTeamMember->isSubmitted() && $formAddTeamMember->isValid()) {
            /** @var TeamMember $member */
            # Get the uploadedFile on each $member who was added
            $uploadedFile = $teamMember->getUploadedFile();
            if ($uploadedFile instanceof UploadedFile) {
                # Give it a unique name
                $name = sha1(uniqid(true)).'.'.$uploadedFile->guessExtension();
                # Move it into the images directory with its new name
                $uploadedFile->move($this->imagesDir, $name);
                # Set the image path and set the uploaded file to null
                $teamMember->setImagePath($name)->setUploadedFile(null);
            }

            $association->addTeamMember($teamMember);
            $this->em->flush();
            $this->addFlash('notice', 'Le membre d\'équipe a bien été ajoutée.');

            return $this->redirectToRoute('admin.content');
        }

        $form = $this->createForm(AssociationType::class, $association);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($association);
            $this->em->flush();
            $this->addFlash('notice', 'La mise à jour a bien été effectuée.');

            return $this->redirectToRoute('admin.content');
        }

        return $this->render('admin/content.html.twig', [
            'form' => $form->createView(),
            'formAddTeamMember' => $formAddTeamMember->createView(),
            'teamMembers' => $teamMembers
        ]);
    }

    /**
     * Delete a given team member
     * @Route("/content/{id}/delete-team-member", name="admin.delete-team-member")
     * @ParamConverter(name="teamMember", class="App\Entity\TeamMember", options={"id" = "id"})
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @param TeamMember $teamMember
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteTeamMember(TeamMember $teamMember)
    {
        $originalImage = $this->imagesDir . DIRECTORY_SEPARATOR . $teamMember->getImagePath();
        $cachedImage = $this->teamMemberThumbnailDir . DIRECTORY_SEPARATOR . $teamMember->getImagePath();

        if (file_exists($originalImage))
            @unlink($originalImage);

        if(file_exists($cachedImage))
            @unlink($cachedImage);

        $this->em->remove($teamMember);
        $this->em->flush();
        $this->addFlash('notice', 'Le membre d\'équipe a bien été supprimé.');

        return $this->redirectToRoute('admin.content');
    }

    /**
     * List all the events
     * @Route("/event", name="admin.event")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @return Response
     */
    public function events()
    {
        $eventRepository = $this->em->getRepository(Event::class);
        $upcomingEvents = $eventRepository->getUpcomingEvents();
        $pastEvents = $eventRepository->getPastEvents();
        $eventsInProgress = $eventRepository->getEventsInProgress();

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
     * @Security("is_granted('ROLE_MODERATEUR')")
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
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function updateEvent(Request $request, Event $event): Response
    {
        if ($event->getEndingDate() < new \DateTime()) {
            $this->addFlash('warning', 'Impossible d\'éditer un évènement passé.');

            return $this->redirectToRoute('admin.event');
        }

        return $this->setEvent($request, $event);
    }

    /**
     * Delete an event
     * @Route("/event/{id}/delete", name="admin.event_delete", requirements={"id" = "\d+"})
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function deleteEvent(Request $request, Event $event): Response
    {
        if ($event->getEndingDate() < new \DateTime()) {
            $this->addFlash('warning', 'Impossible de supprimer un évènement passé.');

            return $this->redirectToRoute('admin.event');
        }

        $availableRedirectRoutes = ['event.index', 'admin.event'];
        $redirectRoute = $request->query->get('redirect', 'admin.event');
        $redirectRoute = in_array($redirectRoute, $availableRedirectRoutes) ? $redirectRoute : $availableRedirectRoutes[0];
        $form = $this->getDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            $this->em->remove($event);
            $this->em->flush();
            $this->addFlash('notice', 'L\'évènement a bien été supprimé.');

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render('admin/event/delete.html.twig', [
            'form' => $form->createView(),
            'event' => $event
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
            if (!$isNewEvent)
                $event->setUpdatedAt(new \DateTime());

            $this->em->persist($event);
            $this->em->flush();

            if ($isNewEvent)
                $this->addFlash('notice', 'L\'évènement a été publié.');
            else
                $this->addFlash('notice', 'L\'évènement a bien été modifié.');

            return $this->redirectToRoute('admin.event');
        }

        return $this->render('admin/event/set.html.twig', [
            'form' => $form->createView(),
            'isNewEvent' => $isNewEvent,
            'event' => $event
        ]);
    }

    /**
     * List all the news
     * @Route("/news", name="admin.news")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @return Response
     */
    public function news()
    {
        $news = $this->em->getRepository(News::class)->findAll();

        return $this->render('admin/news/news.html.twig', ['listNews' => $news]);
    }

    /**
     * Create a news
     * @Route("/news/create", name="admin.news_create")
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @param Request $request
     * @return Response
     */
    public function createNews(Request $request)
    {
        return $this->setNews($request, new News());
    }

    /**
     * Read a news
     * @Route("/news/{id}", name="admin.news_read", requirements={"id" = "\d+"})
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @param News $news
     * @return Response
     */
    public function readNews(News $news)
    {
        return $this->render('admin/news/read.html.twig', ['news' => $news]);
    }

    /**
     * Update a news
     * @Route("/news/{id}/update", name="admin.news_update", requirements={"id" = "\d+"})
     *
     * @Security("is_granted('ROLE_MODERATEUR')")
     *
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function updateNews(Request $request, News $news)
    {
        return $this->setNews($request, $news);
    }

    /**
     * Delete a news
     * @Route("/news/{id}/delete", name="admin.news_delete", requirements={"id" = "\d+"})
     *
     * @Security("is_granted('ROLE_ADMINISTRATEUR')")
     *
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function deleteNews(Request $request, News $news)
    {
        $name = $news->getName();
        $form = $this->getDeleteForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('DELETE')) {
            $this->em->remove($news);
            $this->em->flush();
            $this->addFlash('notice', 'L\'actualité a bien été supprimée.');

            return $this->redirectToRoute('admin.news');
        }

        return $this->render('admin/news/delete.html.twig', [
            'form' => $form->createView(),
            'name' => $name
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
            if (!$isNewNews)
                $news->setUpdatedAt(new \DateTime());

            $this->em->persist($news);
            $this->em->flush();

            if ($isNewNews)
                $this->addFlash('notice', 'L\'actualité a été publiée.');
            else
                $this->addFlash('notice', 'L\'actualité a bien été modifiée.');

            return $this->redirectToRoute('admin.news');
        }

        return $this->render('admin/news/set.html.twig', [
            'form' => $form->createView(),
            'isNewNews' => $isNewNews,
            'news' => $news
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