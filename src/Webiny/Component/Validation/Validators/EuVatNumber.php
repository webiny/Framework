<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class EuVatNumber implements ValidatorInterface
{
    public function getName()
    {
        return 'euVatNumber';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $message = 'Value must be a valid EU VAT number';
        $number = strtoupper($value);
        $number = preg_replace('/[ -,.]/', '', $number);
        if (strlen($number) < 8) {
            if ($throw) {
                throw new ValidationException($message);
            }

            return $message;
        }

        $country = substr($number, 0, 2);
        switch ($country) {
            case 'AT': // AUSTRIA
                $isValid = (bool)preg_match('/^(AT)U(\d{8})$/', $number);
                break;
            case 'BE': // BELGIUM
                $isValid = (bool)preg_match('/(BE)(0?\d{9})$/', $number);
                break;
            case 'BG': // BULGARIA
                $isValid = (bool)preg_match('/(BG)(\d{9,10})$/', $number);
                break;
            case 'CHE': // Switzerland
                $isValid = (bool)preg_match('/(CHE)(\d{9})(MWST)?$/', $number);
                break;
            case 'CY': // CYPRUS
                $isValid = (bool)preg_match('/^(CY)([0-5|9]\d{7}[A-Z])$/', $number);
                break;
            case 'CZ': // CZECH REPUBLIC
                $isValid = (bool)preg_match('/^(CZ)(\d{8,10})(\d{3})?$/', $number);
                break;
            case 'DE': // GERMANY
                $isValid = (bool)preg_match('/^(DE)([1-9]\d{8})/', $number);
                break;
            case 'DK': // DENMARK
                $isValid = (bool)preg_match('/^(DK)(\d{8})$/', $number);
                break;
            case 'EE': // ESTONIA
                $isValid = (bool)preg_match('/^(EE)(10\d{7})$/', $number);
                break;
            case 'EL': // GREECE
                $isValid = (bool)preg_match('/^(EL)(\d{9})$/', $number);
                break;
            case 'ES': // SPAIN
                $isValid = (bool)preg_match('/^(ES)([A-Z]\d{8})$/', $number) || preg_match('/^(ES)([A-H|N-S|W]\d{7}[A-J])$/',
                        $number) || preg_match('/^(ES)([0-9|Y|Z]\d{7}[A-Z])$/', $number) || preg_match('/^(ES)([K|L|M|X]\d{7}[A-Z])$/',
                        $number);
                break;
            case 'EU': // EU type
                $isValid = (bool)preg_match('/^(EU)(\d{9})$/', $number);
                break;
            case 'FI': // FINLAND
                $isValid = (bool)preg_match('/^(FI)(\d{8})$/', $number);
                break;
            case 'FR': // FRANCE
                $isValid = (bool)preg_match('/^(FR)(\d{11})$/', $number) || preg_match('/^(FR)([(A-H)|(J-N)|(P-Z)]\d{10})$/',
                        $number) || preg_match('/^(FR)(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/',
                        $number) || preg_match('/^(FR)([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $number);
                break;
            case 'GB': // GREAT BRITAIN
                $isValid = (bool)preg_match('/^(GB)?(\d{9})$/', $number) || preg_match('/^(GB)?(\d{12})$/',
                        $number) || preg_match('/^(GB)?(GD\d{3})$/', $number) || preg_match('/^(GB)?(HA\d{3})$/', $number);
                break;
            case 'GR': // GREECE
                $isValid = (bool)preg_match('/^(GR)(\d{8,9})$/', $number);
                break;
            case 'HR': // CROATIA
                $isValid = (bool)preg_match('/^(HR)(\d{11})$/', $number);
                break;
            case 'HU': // HUNGARY
                $isValid = (bool)preg_match('/^(HU)(\d{8})$/', $number);
                break;
            case 'IE': // IRELAND
                $isValid = (bool)preg_match('/^(IE)(\d{7}[A-W])$/', $number) || preg_match('/^(IE)([7-9][A-Z\*\+)]\d{5}[A-W])$/',
                        $number) || preg_match('/^(IE)(\d{7}[A-W][AH])$/', $number);
                break;
            case 'IT': // ITALY
                $isValid = (bool)preg_match('/^(IT)(\d{11})$/', $number);
                break;
            case 'LV': // LATVIA
                $isValid = (bool)preg_match('/^(LV)(\d{11})$/', $number);
                break;
            case 'LT': // LITHUNIA
                $isValid = (bool)preg_match('/^(LT)(\d{9}|\d{12})$/', $number);
                break;
            case 'LU': // LUXEMBOURG
                $isValid = (bool)preg_match('/^(LU)(\d{8})$/', $number);
                break;
            case 'MT': // MALTA
                $isValid = (bool)preg_match('/^(MT)([1-9]\d{7})$/', $number);
                break;
            case 'NL': // NETHERLAND
                $isValid = (bool)preg_match('/^(NL)(\d{9})B\d{2}$/', $number);
                break;
            case 'NO': // NORWAY
                $isValid = (bool)preg_match('/^(NO)(\d{9})$/', $number);
                break;
            case 'PL': // POLAND
                $isValid = (bool)preg_match('/^(PL)(\d{10})$/', $number);
                break;
            case 'PT': // PORTUGAL
                $isValid = (bool)preg_match('/^(PT)(\d{9})$/', $number);
                break;
            case 'RO': // ROMANIA
                $isValid = (bool)preg_match('/^(RO)([1-9]\d{1,9})$/', $number);
                break;
            case 'RS': // SERBIA
                $isValid = (bool)preg_match('/^(RS)(\d{9})$/', $number);
                break;
            case 'SI': // SLOVENIA
                $isValid = (bool)preg_match('/^(SI)([1-9]\d{7})$/', $number);
                break;
            case 'SK': // SLOVAK REPUBLIC
                $isValid = (bool)preg_match('/^(SK)([1-9]\d[(2-4)|(6-9)]\d{7})$/', $number);
                break;
            case 'SE': // SWEDEN
                $isValid = (bool)preg_match('/^(SE)(\d{10}01)$/', $number);
                break;
            default:
                $isValid = false;
        }

        // For development environment
        if (!$isValid) {
            $isValid = $number == '1234567890';
        }

        if ($isValid) {
            return true;
        }

        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}