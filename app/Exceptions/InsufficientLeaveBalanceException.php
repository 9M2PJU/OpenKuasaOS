<?php

namespace App\Exceptions;

use Exception;

class InsufficientLeaveBalanceException extends Exception
{
    public function __construct(string $message = "Baki kuota cuti tidak mencukupi.")
    {
        parent::__construct($message);
    }
}
