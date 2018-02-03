<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:58
 */

namespace App\Controller;

use App\Entity\News;
use App\Form\NewsType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->em = $entityManager;
    }

    /**
     * @Route("/news", name="news.index")
     * @return Response
     */
    public function index(): Response
    {
        $news = $this->em->getRepository(News::class)->findAll();

        return $this->render('news/index.html.twig', [
            'newsCollection' => $news
        ]);
    }

    /**
     * @Route("/news/create", name="news.create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        return $this->setNews($request, new News());
    }

    /**
     * @Route("/news/{id}", name="news.read", requirements={"id" = "\d+"})
     * @param News $news
     * @return Response
     */
    public function read(News $news): Response
    {
        return $this->render('news/read.html.twig', [
            'news' => $news
        ]);
    }

    /**
     * @Route("/news/{id}/update", name="news.update", requirements={"id" = "\d+"})
     * @param Request $request
     * @param News $news
     * @return Response
     */
    public function update(Request $request, News $news): Response
    {
        return $this->setNews($request, $news);
    }

    /**
     * @Route("/news/{id}/delete", name="news.delete", requirements={"id" = "\d+"})
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

            return $this->redirectToRoute('news.index');
        }

        return $this->render('news/delete.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
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
            $this->em->persist($news);
            $this->em->flush();

            return $this->redirectToRoute('news.read', [
                'id' => $news->getId(),
            ]);
        }

        return $this->render('news/set.html.twig', [
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
