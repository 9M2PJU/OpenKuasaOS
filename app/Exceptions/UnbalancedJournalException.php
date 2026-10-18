<?php

namespace App\Exceptions;

use Exception;

class UnbalancedJournalException extends Exception
{
    public function __construct(string $message = "Transaksi lejar am tidak seimbang (debits != credits).")
    {
        parent::__construct($message);
    }
}
