<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 18:05
 */

namespace App\Controller;

use App\Entity\Association;
use App\Entity\User;
use App\Form\AssociationType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/", name="admin.index")
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

    public function events()
    {
        return $this->render('admin/events.html.twig');
    }

    public function news()
    {
        return $this->render('admin/news.html.twig');
    }

    public function team()
    {
        return $this->render('admin/team.html.twig');
    }
}