<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:42
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 *
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * Register
     * @Route("/register", name="security.register")
     *
     * Only anonymous users can register.
     * @Security("not has_role('ROLE_USER') and not is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();

        # Form creation, based on RegistrationType
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # UserPasswordEncoderInterface used to encode the password submitted with the form (bcrypt)
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            # Set the user password with the encoded one
            $user->setPassword($password);

            # Getting the entity manager
            $em = $this->getDoctrine()->getManager();
            # Persist
            $em->persist($user);
            # Flush
            $em->flush();

            # Redirection to home.index
            return $this->redirectToRoute('home.index');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Login
     * @Route("/login", name="security.login")
     *
     * Only anonymous users can log in.
     * @Security("not has_role('ROLE_USER') and not is_granted('IS_AUTHENTICATED_REMEMBERED')")
     *
     * @param AuthenticationUtils $authenticationUtils
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        # Last email entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        # Last error if there is one
        $lastError = $authenticationUtils->getLastAuthenticationError();

        # Create the login form
        $form = $this->createForm(LoginType::class, ['username' => $lastUsername]);

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $lastError,
        ]);
    }

    /**
     * Logout
     * @Route("/logout", name="security.logout")
     */
    public function logout()
    {
    }
}