<?php

declare(strict_types=1);

use App\Application\Actions\Rate\RateAction;
use App\Application\Actions\Subscribe\SendEmailsAction;
use App\Application\Actions\Subscribe\SubscribeAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return static function (App $app) {
    $app->group('/api', function (Group $group) {
        $group->get('/rate', RateAction::class);
        $group->post('/subscribe', SubscribeAction::class);
        $group->post('/sendEmails', SendEmailsAction::class);
    });
};
