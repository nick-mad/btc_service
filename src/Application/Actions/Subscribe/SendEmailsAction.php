<?php

declare(strict_types=1);

namespace App\Application\Actions\Subscribe;

use App\Application\Actions\Action;
use App\Domain\Email\EmailRepository;
use App\Domain\Rate\InvalidStatusException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Domain\Rate\Rate;
use App\Application\Settings\SettingsInterface;
use Psr\Container\ContainerInterface;

class SendEmailsAction extends Action
{
    private EmailRepository $emailRepository;
    private MailerInterface $mailer;
    private $mailer_settings;

    public function __construct(
        LoggerInterface $logger,
        EmailRepository $emailRepository,
        MailerInterface $mailer,
        ContainerInterface $container
    ) {
        parent::__construct($logger);
        $this->emailRepository = $emailRepository;
        $this->mailer = $mailer;
        $settings = $container->get(SettingsInterface::class);
        $this->mailer_settings = $settings->get('mailer');
    }

    /**
     * @throws InvalidStatusException
     */
    protected function action(): Response
    {
        $rate = Rate::get();

        foreach ($this->emailRepository->findAll() as $email) {
            $this->sendEmail($email, $rate);
        }

        $this->response->getBody()->write('E-mail`и відправлено');

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    /**
     * Відправити Email на електронну адресу з актуальним курсом
     * @param $email
     * @param $rate
     * @return void
     */
    private function sendEmail($email, $rate): void
    {
        $fromEmail = $this->mailer_settings['email'] ?? '';
        if ($fromEmail) {
            $emailMailer = (new Email())
                ->from($fromEmail)
                ->to($email)
                ->subject('Курс BTC до UAH')
                ->text('Курс BTC до UAH становить: ' . $rate);

            try {
                $this->mailer->send($emailMailer);
            } catch (\Exception | TransportExceptionInterface $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
