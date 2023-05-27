<?php

declare(strict_types=1);

namespace App\Domain\Email;

use App\Domain\DomainException\DomainException;

class EmailExistException extends DomainException
{
    public $code = 409;
}
