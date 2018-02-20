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
    /**
     * @var EntityManagerInterface
     */
    private $em;
    private $licensesDir;
    private $imagesDir;
    private $teamMemberThumbnailDir;
    /**
     * @var EmailManager
     */
    private $emailManager;

    /**
     * AdminController constructor.
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
        $userRepository = $this->em->getRepository(User::class);

        $activeUsers = $userRepository->findByStatus(true);
        $inactiveUsers = $userRepository->findByStatus(false);

        # Get the license repository
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

            $this->addFlash(
                'notice',
                'Les informations de '.ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()).' ont bien été mises à jour.'
            );

            # Redirection to admin.index
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
     * Members with ROLE_SUPER_ADMIN can validate a subscription.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
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
     * Members with ROLE_SUPER_ADMIN can reject a subscription.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function rejectSubscription(User $user)
    {
        # Remove the user from database
        $this->em->remove($user);
        # Send the user an email
        $this->emailManager->sendSubscriptionEmail($user);
        # Flush
        $this->em->flush();

        $this->addFlash(
            'notice',
            'L\'inscription de ' . ucfirst($user->getFirstname()) . ' ' . ucfirst($user->getLastname()) . ' a été rejetée.'
        );

        # Redirection to admin.index
        return $this->redirectToRoute('admin.index');
    }

    /**
     * Upload a license for a member
     * @Route("/member/{id}/new-license", name="admin.member.upload_license")
     *
     * Members with ROLE_SUPER_ADMIN can upload a license for a member.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function uploadLicense(Request $request, User $user)
    {
        # Create a new license
        $license = new License();
        # Gives the license a user_id
        $license->setUser($user);
        # Form creation based on LicenseType
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
            # Add the license to the user
            $user->addLicense($license);
            # Persist
            $this->em->persist($user);
            # Flush
            $this->em->flush();
            # Flash message
            $this->addFlash(
                'notice',
                'Une licence a bien été ajoutée pour '.ucfirst($user->getFirstname()).' '.ucfirst($user->getLastname()).'.'
            );

            # Redirection to admin.members
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
     * Members with ROLE_SUPER_ADMIN can delete a license.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param License $license
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function deleteLicense(License $license, Request $request)
    {
        # Confirmation form creation
        $confirmationForm = $this->createFormBuilder();
        # Get the form
        $form = $confirmationForm->getForm();

        $form->handleRequest($request);

        $user = $license->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            # Get the license filename
            $filename = $this->licensesDir.DIRECTORY_SEPARATOR.$license->getName();
            # Remove it
            $this->em->remove($license);
            # Flush
            $this->em->flush();

            # If the file exists
            if (file_exists($filename)) {
                # Delete it
                unlink($filename);
            }

            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();

            # Flash message
            $this->addFlash(
                'notice',
                'La licence '.$license->getYear().'/'.($license->getYear() + 1).' de '. ucfirst($firstname) .' '. ucfirst($lastname) .' a bien été supprimée.'
            );

            # Redirection to admin.members
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
            # Flash message
            $this->addFlash('notice', 'Le membre a bien été supprimé.');

            # Redirection to admin.index
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
        $teamMembers = $this->em->getRepository(TeamMember::class)->findAll();

        # If no Association entity were found, create one
        if (is_null($association)) {
            $association = new Association();
        }

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

        # Form creation, based on AssociationType
        $form = $this->createForm(AssociationType::class, $association);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Persist
            $this->em->persist($association);
            # Flush
            $this->em->flush();
            # Add a flash message
            $this->addFlash('notice', 'La mise à jour a bien été effectuée.');

            # Redirection to the same page
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
     * Members with ROLE_SUPER_ADMIN can delete a team member.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @param TeamMember $teamMember
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteTeamMember(TeamMember $teamMember)
    {
        # Get the team member original image and cached image (thumbnail, by LiipImagineBundle)
        $originalImage = $this->imagesDir . DIRECTORY_SEPARATOR . $teamMember->getImagePath();
        $cachedImage = $this->teamMemberThumbnailDir . DIRECTORY_SEPARATOR . $teamMember->getImagePath();

        # If the original image exists
        if (file_exists($originalImage)) {
            # Delete it
            @unlink($originalImage);
        }

        # If the cached image exists
        if(file_exists($cachedImage)) {
            # Delete it
            @unlink($cachedImage);
        }

        $this->em->remove($teamMember);
        $this->em->flush();
        $this->addFlash('notice', 'Le membre d\'équipe a bien été supprimé.');

        # Redirect to admin.content
        return $this->redirectToRoute('admin.content');
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
        $upcomingEvents = $eventRepository->getUpcomingEvents();
        # Fetching past events
        $pastEvents = $eventRepository->getPastEvents();
        # Fetching events in progress
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
        # If the targeted event is a past event
        if ($event->getEndingDate() < new \DateTime()) {
            # It's impossible to edit a past event
            $this->addFlash('warning', 'Impossible d\'éditer un évènement passé.');
            # Redirection to admin.event
            return $this->redirectToRoute('admin.event');
        }

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
        # If the targeted event is a past event
        if ($event->getEndingDate() < new \DateTime()) {
            # It's impossible to delete a past event
            $this->addFlash('warning', 'Impossible de supprimer un évènement passé.');
            # Redirection to admin.event
            return $this->redirectToRoute('admin.event');
        }

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
            # Flash message
            $this->addFlash('notice', 'L\'évènement a bien été supprimé.');

            # Redirection to the redirect route
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
        # Check if the event (in the method argument) is new
        $isNewEvent = $event->getId() === null;

        # Form creation based on EventType
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # When the event is updated, set its updatedAt attribute
            if (!$isNewEvent) {
                $event->setUpdatedAt(new \DateTime());
            }

            # Persist
            $this->em->persist($event);
            # Flush
            $this->em->flush();

            if ($isNewEvent) {
                $this->addFlash('notice', 'L\'évènement a été publié.');
            } else {
                $this->addFlash('notice', 'L\'évènement a bien été modifié.');
            }

            # Redirection to admin.event
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
    public function createNews(Request $request)
    {
        return $this->setNews($request, new News());
    }

    /**
     * Read a news
     * @Route("/news/{id}", name="admin.news_read", requirements={"id" = "\d+"})
     *
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can read a news.
     * @Security("is_granted('ROLE_ADMIN')")
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
     * Members with ROLE_SUPER_ADMIN and ROLE_ADMIN can update a news
     * @Security("is_granted('ROLE_ADMIN')")
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
     * Members with ROLE_SUPER_ADMIN can delete a news.
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Method({"GET", "DELETE"})
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function deleteNews(Request $request, News $news)
    {
        $name = $news->getName();

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

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getDeleteForm()
    {
        $form = $this->createFormBuilder()->setMethod('DELETE')->getForm();

        return $form;
    }
}