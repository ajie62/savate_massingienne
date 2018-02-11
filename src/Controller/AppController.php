<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 03/02/2018
 * Time: 12:12
 */

namespace App\Controller;

use App\Entity\Association;
use App\Entity\Contact;
use App\Entity\Event;
use App\Entity\News;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * Website's homepage
     * @Route("/", name="app.index")
     *
     * Everyone can see this page.
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     *
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

        return $this->render('app/index.html.twig', [
            'association' => $association,
            'events' => $events,
            'newsList' => $news
        ]);
    }

    /**
     * @Route("/contact", name="app.contact")
     *
     * Everyone can see this page and send a message.
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     *
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function contact(Request $request, \Swift_Mailer $mailer)
    {
        # Create a contact object
        $contact = new Contact();
        # Getting the current user
        $user = $this->getUser();

        # If the person who wants to send an email is a member
        if ($user) {
            # Set the default values
            $contact->setFirstname($user->getFirstname());
            $contact->setLastname($user->getLastname());
            $contact->setEmail($user->getEmail());
        }

        # Create the contact form
        $contactForm = $this->createForm(ContactType::class, $contact);

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            # Create a new message to send with SwiftMailer
            $message = (new \Swift_Message($contact->getSubject()))
                ->setFrom($contact->getEmail())
                ->setTo('recipient@example.com')
                ->setBody(
                    $this->renderView(
                        // templates/emails/contact.html.twig
                        'emails/contact.html.twig', ['email' => $contact]
                    ),
                    'text/html'
                )
            ;

            $mailer->send($message);

            $this->addFlash('success', 'Votre message a bien été envoyé !');
                return $this->redirectToRoute('app.contact');
        }

        return $this->render('app/contact.html.twig', [
            'form' => $contactForm->createView(),
        ]);
    }
}