<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CodiceFiscale extends Constraint
{
    public $message = 'Il codice fiscale "{{ value }}" non è valido.';
}
