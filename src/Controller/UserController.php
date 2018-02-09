<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:21
 */

namespace App\Controller;

use App\Entity\Event;
use App\Form\UserType;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{
    private $imagesDir;

    public function __construct($images_dir)
    {
        $this->imagesDir = $images_dir;
    }

    /**
     * List website's all members
     * @Route("/user/list", name="user.list")
     *
     * Only authenticated members can see the members list.
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list()
    {
        return $this->render('user/list.html.twig');
    }

    /**
     * User profile
     * @Route("/profile/{username}", name="user.profile", defaults={"username" = null})
     *
     * Only authenticated members can visit users profile.
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @param $username
     * @param UserService $userService
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile($username, UserService $userService, Request $request)
    {
        # If the $username argument and the current user username are the same
        if ($username == $this->getUser()->getUsername()) {
            # Redirection to user.profile with $username argument set to null
            return $this->redirectToRoute('user.profile', ['username' => null]);
        }

        $em = $this->getDoctrine()->getManager();
        $self = false;

        # Fetching the user from the database with username
        # The UserService determines if $self is set to true or false
        $user = $userService->getUserByUsername($username, $self);

        $formView = null;

        # If current user
        if ($self) {
            # Create the upload file form
            $form = $this->createFormBuilder($user, [
                'validation_groups' => ['img_edition']
            ]);
            # Add a FileType field to it
            $form->add('uploadedFile', FileType::class, [
                'label' => false,
                'required' => true,
            ]);

            # Get the form and its view
            $form = $form->getForm();

            $form->handleRequest($request);
            $formView = $form->createView();

            if ($form->isSubmitted() && $form->isValid()) {
                # Get the file that was uploaded by the user
                $uploadedFile = $user->getUploadedFile();
                # If it's an instance of UploadedFile
                if ($uploadedFile instanceof UploadedFile) {
                    # And if there's already an image path defined for the user
                    if ($user->getImagePath()) {
                        # And if the file exists in the images directory
                        if (file_exists($this->imagesDir.DIRECTORY_SEPARATOR.$user->getImagePath())) {
                            # Delete it
                            @unlink($this->imagesDir.DIRECTORY_SEPARATOR.$user->getImagePath());
                        }
                    }

                    # Create a unique name for the file
                    $name = sha1(uniqid(true)).'.'.$uploadedFile->guessExtension();
                    # Move the file in the images directory with the new name
                    $uploadedFile->move($this->imagesDir, $name);
                    # Set the image path with the new name
                    $user->setImagePath($name);
                    # And set the uploadedFile attr to null
                    $user->setUploadedFile(null);

                    # Flush
                    $em->flush();
                }

                # Redirection to user.profile
                return $this->redirectToRoute('user.profile');
            }
        }

        # Fetching user's upcoming events
        $upcomingEvents = $this->getDoctrine()->getRepository(Event::class)->getUpcomingEvents($user);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'self' => $self,
            'upcomingEvents' => $upcomingEvents,
            'form' => $formView
        ]);
    }

    /**
     * Update user
     * @Route("/user/update", name="user.update")
     *
     * Only members can update their profile.
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request)
    {
        # Getting the current user
        $user = $this->getUser();

        # Form creation based on UserType
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Getting the entity manager
            $em = $this->getDoctrine()->getManager();
            # Flush
            $em->flush();

            # Redirection to user.profile
            return $this->redirectToRoute('user.profile', ['username' => $user->getUsername()]);
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
