<?php

class validatorCommon
{
    public static function validateRequired($value, bool $required): string|bool
    {
        if (!isset($value) && $required) {
            return "Is required.";
        }

        return true;
    }

    public static function validateNotEmpty($value, bool $notEmpty): string|bool
    {
        if ($notEmpty && empty($value) && $value != 0) {
            return "Field cannot be empty";
        }

        return true;
    }
}