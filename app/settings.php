<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true,
                'logError' => true,
                'logErrorDetails' => true,
                'logger' => [
                    'name' => 'app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'mailer' => [
                    'email' => $_ENV['MAIL_USERNAME'] ?? '',
                    'passwd' => $_ENV['MAIL_PASSWORD'] ?? '',
                    'host' => $_ENV['MAIL_SERVER'] ?? '',
                    'port' => $_ENV['MAIL_PORT'] ?? '',
                ],
            ]);
        }
    ]);
};
