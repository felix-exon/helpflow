<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

abstract class EmailCredentials implements Arrayable
{
    abstract public function validate(): bool;
}
