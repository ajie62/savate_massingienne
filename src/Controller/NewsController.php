<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 11:58
 */

namespace App\Controller;

use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * List all the news
     * @Route("/news", name="news.index")
     * @return Response
     */
    public function index()
    {
        $news = $this->em->getRepository(News::class)->findAll();

        return $this->render('news/index.html.twig', [
            'newsCollection' => $news
        ]);
    }

    /**
     * Read an article thanks to its slug
     * @Route("/news/{slug}", name="news.read")
     *
     * Everyone can read a news.
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     *
     * @param News $news
     * @return Response
     */
    public function read(News $news)
    {
        return $this->render('news/read.html.twig', [
            'news' => $news
        ]);
    }
}
