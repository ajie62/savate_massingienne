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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile($username, UserService $userService)
    {
        # If the $username argument and the current user username are the same
        if ($username == $this->getUser()->getUsername()) {
            # Redirection to user.profile with $username argument set to null
            return $this->redirectToRoute('user.profile', ['username' => null]);
        }

        $self = false;

        # Fetching the user from the database with username
        # The UserService determines if $self is set to true or false
        $user = $userService->getUserByUsername($username, $self);

        # Fetching user's upcoming events
        $upcomingEvents = $this->getDoctrine()->getRepository(Event::class)->getUpcomingEvents($user);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'self' => $self,
            'upcomingEvents' => $upcomingEvents
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
