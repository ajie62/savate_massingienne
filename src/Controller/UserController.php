<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:21
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/list", name="user.list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list()
    {
        return $this->render('user/list.html.twig');
    }

    /**
     * @Route("/profile/{username}", name="user.profile", defaults={"username" = null})
     * @param $username
     * @param UserService $userService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile($username, UserService $userService)
    {
        if ($username == $this->getUser()->getUsername()) {
            return $this->redirectToRoute('user.profile', ['username' => null]);
        }
        $self = false;
        $user = $userService->getUserByUsername($username, $self);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'self' => $self
        ]);
    }

    /**
     * @Route("/user/update", name="user.update")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('user.profile', ['username' => $user->getUsername()]);
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
