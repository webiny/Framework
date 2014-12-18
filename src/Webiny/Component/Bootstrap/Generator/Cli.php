<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap\Generator;

/**
 * Command line interface helper.
 *
 * @package         Webiny\Component\Bootstrap\Generator
 */

class Cli
{
    public static function printMessage($msg)
    {
        self::_print($msg);
    }

    public static function printErrorMessage($msg)
    {
        $str = "\033[41m" . $msg . "\033[0m";

        self::_print($str);
    }

    public static function printSuccessMessage($msg)
    {
        $str = "\033[32m" . $msg . "\033[0m";

        self::_print($str);
    }

    public static function printWarningMessage($msg)
    {
        $str = "\033[43m" . $msg . "\033[0m";

        self::_print($str);
    }

    public static function printOptions(array $options)
    {
        foreach ($options as $i => $o) {
            self::_print($i . ' - ' . $o);
        }

        self::_print('');
        self::_print('Please select an option: ', false);

        // wait for the option select
        $handle = fopen('php://stdin', 'r');
        $option = trim(fgets($handle));
        fclose($handle);

        // validate option
        if (!isset($options[$option])) {
            self::printErrorMessage('Invalid option selected, please try again.');
            self::printOptions($options);
        } else {
            return $option;
        }
    }

    public static function askQuestion($question)
    {
        self::_print($question, false);

        // wait for the answer
        $handle = fopen('php://stdin', 'r');
        $result = trim(fgets($handle));
        fclose($handle);

        return $result;
    }

    public static function acknowledgeMessage()
    {
        self::_print('Press any key to continue...', false);

        // wait for the answer
        $handle = fopen('php://stdin', 'r');
        trim(fgets($handle));
        fclose($handle);

        return true;
    }

    public static function printTitle($title)
    {
        $separator = str_repeat('=', strlen($title));
        self::printMessage($separator);
        self::printMessage($title);
        self::printMessage($separator);
    }

    public static function printSubTitle($subTitle)
    {
        $separator = str_repeat('-', strlen($subTitle));
        self::printMessage($separator);
        self::printSuccessMessage($subTitle);
        self::printMessage($separator);
    }

    public static function printSubSubTitle($subSubTitle)
    {
        $separator = str_repeat('-', strlen($subSubTitle));
        self::printMessage($separator);
        self::printSuccessMessage($subSubTitle);
    }

    private static function _print($str, $newLine = true)
    {
        fwrite(STDOUT, $str . ($newLine ? "\n" : ""));
    }
}