<?php

namespace App\Exceptions;

use Exception;

class QuotaExceededException extends Exception
{
    protected $message = 'Vous avez atteint la limite de votre plan actuel.';
}

