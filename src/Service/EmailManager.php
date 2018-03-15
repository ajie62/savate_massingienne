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
    const EMAIL = "contact@savate-massingienne.fr";

    private $twig;
    private $mailer;

    /**
     * EmailManager constructor.
     *
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
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
        $title = 'Nouveau message de '.htmlspecialchars($contact->getFirstname());

        $message = (new \Swift_Message($title))
            ->setFrom(self::EMAIL)
            ->setTo(self::EMAIL)
            ->setReplyTo($contact->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Welcome message sent after subscription
     *
     * @param User $user
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendWelcomeEmail(User $user)
    {
        $body = $this->twig->render('emails/welcome.html.twig', ['user' => $user]);
        $title = "Inscription au site de l'association massingienne de savate";

        $message = (new \Swift_Message($title))
            ->setFrom(self::EMAIL)
            ->setTo($user->getEmail())
            ->setBody($body, 'text/html');

        $this->mailer->send($message);
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

        if (!$user->isActive()) {
            $body = $this->twig->render('emails/reject_subscription.html.twig', ['user' => $user]);
            $message = (new \Swift_Message("Association Massingienne de Savate : votre inscription a été refusée !"))
                ->setFrom(self::EMAIL)
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html');
        } else {
            $body = $this->twig->render('emails/validate_subscription.html.twig', ['user' => $user]);
            $message = (new \Swift_Message("Association Massingienne de Savate : votre inscription a été validée !"))
                ->setFrom(self::EMAIL)
                ->setTo($user->getEmail())
                ->setBody($body, 'text/html');
        }

        $this->mailer->send($message);
    }
}
