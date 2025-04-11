<?php 

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TopImagesMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $params
    ) {}

    public function sendTopImagesEmail(array $images): void
    {
        $recipient = $this->params->get('top_images_recipient_email');
        $sender = $this->params->get('top_email_sender');

        $email = (new TemplatedEmail())
            ->from($sender)
            ->to($recipient)
            ->subject('Top 20 images les plus téléchargées')
            ->htmlTemplate('emails/top_images.html.twig')
            ->context([
                'images' => $images
            ]);

        $this->mailer->send($email);
    }
}

