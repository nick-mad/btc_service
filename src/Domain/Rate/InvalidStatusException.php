<?php

declare(strict_types=1);

namespace App\Domain\Rate;

use App\Domain\DomainException\DomainException;

class InvalidStatusException extends DomainException
{
    public $message = 'Invalid status value';
    public $code = 400;
}
