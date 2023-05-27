<?php

declare(strict_types=1);

namespace App\Application\Actions\Rate;

use App\Application\Actions\Action;
use App\Domain\Rate\InvalidStatusException;
use App\Domain\Rate\Rate;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class RateAction extends Action
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        try {
            $rate = Rate::get();
        } catch (\Exception $e) {
            $this->response->getBody()->write($e->getMessage());
            return $this->response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($e->getCode());
        }

        $this->response->getBody()->write($rate);

        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
}
