<?php

namespace SlimGenerator;

use Exception;

class CliHelper
{
    protected static $current_tries = 0;

    public static function print($message) 
    {
        echo $message . PHP_EOL;
    }

    public static function getUserInput($question, $default, $allow_empty_value = false, $tries = 3)
    {
        if (self::$current_tries >= $tries) {
            self::$current_tries = 0;
            throw new Exception('Incorrect value.');
        }
        
        echo '>>> ' . trim($question) . ' ';
        $input = trim(fgets(STDIN, 1024));
        self::$current_tries++;

        if ($input !== '') {
            self::$current_tries = 0;
            return $input;
        }

        if ($allow_empty_value) {
            self::$current_tries = 0;
            return $input;
        }
        
        if ($default != '') {
            self::$current_tries = 0;
            return $default;
        }

        return self::getUserInput($question, $default,  $allow_empty_value, $tries);
    }

    public static function confirm($question)
    {
        $add = CliHelper::getUserInput($question . ' (y/n)', 'y');
        return strtolower($add) === 'y';
    }
}