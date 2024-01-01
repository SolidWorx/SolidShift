<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Validator;

use App\Validator\PhoneNumber as PhoneNumberConstraint;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use function assert;
use function is_string;

/**
 * @see \App\Tests\Validator\PhoneNumberValidatorTest
 */
final class PhoneNumberValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (! $constraint instanceof PhoneNumberConstraint) {
            throw new UnexpectedTypeException($constraint, PhoneNumberConstraint::class);
        }

        if (null === $value) {
            return;
        }

        if (! is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phone = $phoneUtil->parse($value);
            assert($phone instanceof PhoneNumber);

            if (! $phoneUtil->isValidNumber($phone)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $value)
                    ->setCode(PhoneNumberConstraint::INVALID_NUMBER_ERROR)
                    ->addViolation();

                return;
            }

            if ($phoneUtil->getNumberType($phone) !== PhoneNumberType::MOBILE && $phoneUtil->getNumberType($phone) !== PhoneNumberType::FIXED_LINE_OR_MOBILE) {
                $this->context->buildViolation($constraint->messageMobileOnly)
                    ->setParameter('{{ value }}', $value)
                    ->setCode(PhoneNumberConstraint::INVALID_MOBILE_NUMBER_ERROR)
                    ->addViolation();

                return;
            }
        } catch (NumberParseException $numberParseException) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setCode(PhoneNumberConstraint::INVALID_NUMBER_ERROR)
                ->setCause($numberParseException)
                ->addViolation();

            return;
        }
    }
}
