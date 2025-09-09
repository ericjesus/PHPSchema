<?php

class validatorText
{
    public static function validadeName($value)
    {
      if (!is_string($value)) {
        return "Must be a valid name.";
      }

      if (!preg_match("/^[a-zA-ZÀ-ÖØ-öø-ÿ\s]+$/", $value)) {
        return "Name can only contain letters and spaces.";
      }

      $nameParts = explode(' ', $value);
      if (count($nameParts) < 2 || empty($nameParts[1])) {
        return "Name must include at least a first name and a surname.";
      }

      return true;
    }
}
