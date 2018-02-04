<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:12
 */

namespace App\Controller;

use App\Entity\Association;
use App\Entity\Event;
use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
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
     * @Route("/", name="home.index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $association = $this->em->getRepository(Association::class)->find(1);
        $events = $this->em->getRepository(Event::class)->findAll();
        $news = $this->em->getRepository(News::class)->findAll();

        if (is_null($association)) {
            $association = new Association();
        }

        return $this->render('home/index.html.twig', [
            'association' => $association,
            'events' => $events,
            'newsList' => $news
        ]);
    }
}