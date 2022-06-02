<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Mailer {
 
    private $mailer;

    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer;
    }

    public function send($email, $token, $username){
        $email = (new TemplatedEmail())
            ->from('site@example.com')
            ->to(new Address($email))
            ->subject('Thanks for signing up!')

            // path of the Twig template to render
            ->htmlTemplate('security/email.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'username' => $username,
                'token' => $token,
            ])
        ;

        $this->mailer->send($email);
    }
}
