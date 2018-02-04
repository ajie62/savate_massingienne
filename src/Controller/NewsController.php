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
}
