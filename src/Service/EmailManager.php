<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 11/02/2018
 * Time: 20:34
 */

namespace App\Service;

use App\Entity\Contact;

class EmailManager
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendEmail(Contact $contact)
    {
        $body = $this->renderTemplate($contact);

        # Create a new message to send with SwiftMailer
        $message = (new \Swift_Message($contact->getSubject()))
            ->setFrom($contact->getEmail())
            ->setTo('recipient@example.com')
            ->setBody($body, 'text/html')
        ;

        $this->mailer->send($message);
    }

    private function renderTemplate($contact)
    {
        return $this->twig->render('emails/contact.html.twig', [
            'email' => $contact,
        ]);
    }
}