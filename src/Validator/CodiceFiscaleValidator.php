<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CodiceFiscaleValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CodiceFiscale) {
            throw new UnexpectedTypeException($constraint, CodiceFiscale::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        // Verifica lunghezza
        if (strlen($value) !== 16) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Verifica formato (lettere e numeri)
        if (!preg_match('/^[A-Z0-9]{16}$/', strtoupper($value))) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Verifica posizioni specifiche
        $cf = strtoupper($value);
        
        // Prime 6 posizioni: cognome (lettere)
        if (!preg_match('/^[A-Z]{6}/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizioni 7-8: nome (lettere)
        if (!preg_match('/^[A-Z]{6}[A-Z]{2}/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizioni 9-10: anno (numeri)
        if (!preg_match('/^[A-Z]{8}[0-9]{2}/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizione 11: mese (lettera)
        if (!preg_match('/^[A-Z]{8}[0-9]{2}[A-Z]/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizioni 12-13: giorno (numeri)
        if (!preg_match('/^[A-Z]{8}[0-9]{2}[A-Z][0-9]{2}/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizioni 14-15: comune (lettere)
        if (!preg_match('/^[A-Z]{8}[0-9]{2}[A-Z][0-9]{2}[A-Z]{2}/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        // Posizione 16: carattere di controllo (lettera)
        if (!preg_match('/^[A-Z]{8}[0-9]{2}[A-Z][0-9]{2}[A-Z]{2}[A-Z]$/', $cf)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }
    }
}
