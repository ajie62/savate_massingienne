<?php
/**
 * Created by PhpStorm.
 * User: jeromebutel
 * Date: 11/02/2018
 * Time: 20:34
 */

namespace App\Service;

use App\Entity\Contact;
use App\Entity\User;

/**
 * Class EmailManager
 *
 * @package App\Service
 */
class EmailManager
{
    private $twig;
    private $data;

    /**
     * EmailManager constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->data = require_once __DIR__ . './../../config/mailer.php';
    }

    /**
     * Contact message sent to the association team.
     *
     * @param Contact $contact
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendContactEmail(Contact $contact)
    {
        $body = $this->twig->render('emails/contact.html.twig', ['contact' => $contact]);

        $transport = (new \Swift_SmtpTransport($this->data['smtp'], 587))
            ->setUsername($this->data['username'])
            ->setPassword($this->data['password'])
        ;

        $mailer = new \Swift_Mailer($transport);

        $title = 'Nouveau message de '.htmlspecialchars($contact->getFirstname());

        # Create a new message to send with SwiftMailer
        $message = (new \Swift_Message($title))
            ->setFrom($this->data['from'])
            ->setTo($this->data['to'])
            ->setReplyTo($contact->getEmail())
            ->setBody($body, 'text/html');

        $mailer->send($message);
    }

    /**
     * Message sent after a subscription is either validated or rejected.
     *
     * @param User $user
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendSubscriptionEmail(User $user)
    {
        $body = null;
        $message = null;

        $transport = (new \Swift_SmtpTransport($this->data['smtp'], 587))
            ->setUsername($this->data['username'])
            ->setPassword($this->data['password'])
        ;

        $mailer = new \Swift_Mailer($transport);

        if (!$user->isActive()) {
            $body = $this->twig->render('emails/reject_subscription.html.twig', ['user' => $user]);
            $message = (new \Swift_Message("Association Massingienne de Savate : votre inscription a été refusée !"))
                ->setFrom($this->data['from'])
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html');
        } else {
            $body = $this->twig->render('emails/validate_subscription.html.twig', ['user' => $user]);
            $message = (new \Swift_Message("Association Massingienne de Savate : votre inscription a été validée !"))
                ->setFrom($this->data['from'])
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html');
        }

        $mailer->send($message);
    }
}
