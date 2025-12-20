<?php

namespace App\Validation;

class RegistrationValidator
{
    public function validate(string $username, string $phone): void
    {
        $errors = [];

        $username = trim($username);
        $phone = trim($phone);

        if ($username === '') {
            $errors['username'] = 'Username is required';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            $errors['username'] = 'Username must be between 3 and 50 characters';
        }

        if ($phone === '') {
            $errors['phone'] = 'Phonenumber is required';
        } elseif (!preg_match('/^\+?[0-9]{8,20}$/', $phone)) {
            $errors['phone'] = 'Phonenumber must be 8-20 digits and may start with +';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
