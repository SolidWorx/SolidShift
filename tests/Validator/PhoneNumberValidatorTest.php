<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Validator;

use App\Validator\PhoneNumber;
use App\Validator\PhoneNumberValidator;
use libphonenumber\NumberParseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Throwable;

/**
 * @extends ConstraintValidatorTestCase<PhoneNumberValidator>
 */
#[CoversClass(PhoneNumberValidator::class)]
final class PhoneNumberValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PhoneNumberValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new PhoneNumber());

        $this->assertNoViolation();
    }

    public function testOnlyPhoneNumberConstraintIsValid(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "App\Validator\PhoneNumber", "Symfony\Component\Validator\Constraint@anonymous" given');

        $this->validator->validate(null, new class() extends Constraint {
        });
    }

    #[DataProvider('provideValidMobileNumbers')]
    public function testValidMobileNumber(string $number): void
    {
        $this->validator->validate($number, new PhoneNumber());

        try {
            $this->assertNoViolation();
        } catch (Throwable $e) {
            self::assertSame('a', $number);
        }
    }

    #[DataProvider('provideInvalidMobileNumbers')]
    public function testInvalidValues(string $value, string $code, ?NumberParseException $e): void
    {
        $this->validator->validate($value, new PhoneNumber());

        if ($code === PhoneNumber::INVALID_MOBILE_NUMBER_ERROR) {
            $violation = $this->buildViolation('The value "{{ value }}" should be a mobile phone number.');
        } else {
            $violation = $this->buildViolation('The value "{{ value }}" is not a valid phone number.');
        }

        $violation
            ->setParameter('{{ value }}', $value)
            ->setCode($code)
        ;

        if ($e instanceof NumberParseException) {
            $violation->setCause($e);
        }

        $violation->assertRaised();
    }

    #[DataProvider('provideInvalidTypes')]
    public function testNonStringValues(mixed $value): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessageMatches('/Expected argument of type "string", ".*" given/');

        $this->validator->validate($value, new PhoneNumber());
    }

    /**
     * @return iterable<array-key, array{0: string}>
     */
    public static function provideValidMobileNumbers(): iterable
    {
        yield ['+1 (240) 741-4741'];
        yield ['+1-252-865-8649'];
        yield ['+1-786-805-9810'];
        yield ['+1-262-815-4381'];
        yield ['+12344582668'];
        yield ['+1.843.610.6216'];
        yield ['+1-914-967-5639'];
    }

    /**
     * @return iterable<array-key, array{0: string, 1: string, 2: NumberParseException|null}>
     */
    public static function provideInvalidMobileNumbers(): iterable
    {
        yield [
            '+1 (240) 741-47',
            PhoneNumber::INVALID_NUMBER_ERROR,
            null,
        ];

        yield [
            '+1-252-868649',
            PhoneNumber::INVALID_NUMBER_ERROR,
            null,
        ];

        yield [
            '786-805-9810',
            PhoneNumber::INVALID_NUMBER_ERROR,
            new NumberParseException(NumberParseException::INVALID_COUNTRY_CODE, 'Missing or invalid default region.'),
        ];

        yield [
            '+1-815-4381',
            PhoneNumber::INVALID_NUMBER_ERROR,
            null,
        ];

        yield [
            '2344582668',
            PhoneNumber::INVALID_NUMBER_ERROR,
            new NumberParseException(NumberParseException::INVALID_COUNTRY_CODE, 'Missing or invalid default region.'),
        ];

        yield [
            '+1.843.610.6216.345234',
            PhoneNumber::INVALID_NUMBER_ERROR,
            null,
        ];

        yield [
            '+1-914-967-5639-234234',
            PhoneNumber::INVALID_NUMBER_ERROR,
            null,
        ];

        yield [
            '+1–533-555–1212',
            PhoneNumber::INVALID_MOBILE_NUMBER_ERROR,
            null,
        ];

        yield [
            'abcde',
            PhoneNumber::INVALID_NUMBER_ERROR,
            new NumberParseException(NumberParseException::NOT_A_NUMBER, 'The string supplied did not seem to be a phone number.'),
        ];
    }

    /**
     * @return iterable<array-key, array{0: mixed}>
     */
    public static function provideInvalidTypes(): iterable
    {
        yield [true];
        yield [false];
        yield [1];
        yield [1.1];
        yield [[]];
        yield [new stdClass()];
    }
}
