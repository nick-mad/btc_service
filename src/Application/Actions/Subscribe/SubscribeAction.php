<?php

declare(strict_types=1);

namespace App\Application\Actions\Subscribe;

use App\Application\Actions\Action;
use App\Domain\Email\EmailExistException;
use App\Domain\Email\EmailRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class SubscribeAction extends Action
{
    protected EmailRepository $emailRepository;

    public function __construct(LoggerInterface $logger, EmailRepository $emailRepository)
    {
        parent::__construct($logger);
        $this->emailRepository = $emailRepository;
    }

    protected function action(): Response
    {
        $post = $this->getFormData();
        $email = isset($post['email']) ? strtolower($post['email']) : null;

        try {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->emailRepository->storeEmail($email);
            }
        } catch (\Exception $e) {
            $this->response->getBody()->write($e->getMessage());
            return $this->response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($e->getCode());
        }

        $this->response->getBody()->write('E-mail додано');

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
