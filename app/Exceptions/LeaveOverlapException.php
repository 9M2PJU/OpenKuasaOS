<?php

namespace App\Exceptions;

use Exception;

class LeaveOverlapException extends Exception
{
    public function __construct(string $message = "Tarikh permohonan cuti bertindih dengan cuti sedia ada.")
    {
        parent::__construct($message);
    }
}
