<?php

namespace App\Helpers;

class IdCardParser
{
    private static $arabicToEnglish = [
        '٠' => '0',
        '١' => '1',
        '٢' => '2',
        '٣' => '3',
        '٤' => '4',
        '٥' => '5',
        '٦' => '6',
        '٧' => '7',
        '٨' => '8',
        '٩' => '9'
    ];

    public static function parseIdCard($text)
    {
        // Remove any non-digit characters
        $text = preg_replace('/\D/', '', $text);

        // Convert Arabic numerals to English numerals
        $text = strtr($text, self::$arabicToEnglish);

        // Check if the text is a valid ID number (14 digits)
        if (preg_match('/^\d{14}$/', $text)) {
            return $text;
        }

        return null; // Return null if the ID number is not valid
    }


    public static function arabicToEnglish($text)
    {
        $result = [
            'national_id' => null,
            'company_number' => null,
            'name' => null,
        ];

        // Egyptian national ID pattern (14 Arabic digits)
        // Pattern for Arabic numerals: [\x{0660}-\x{0669}] matches Arabic digits ٠-٩
        if (preg_match('/[\x{0660}-\x{0669}]{14}/u', $text, $matches)) {
            $result['national_id'] = $matches[0];
        }

        // Company number pattern
        if (preg_match('/(?:رقم السجل التجاري|رقم الشركة)[\s:]*([A-Za-z0-9\x{0660}-\x{0669}]+)/u', $text, $matches)) {
            $result['company_number'] = $matches[1];
        }

        // Name pattern (Arabic names typically after "الاسم:" or similar)
        if (preg_match('/(?:اسم|الاسم)[\s:]*([^\n\r]+)/u', $text, $matches)) {
            $result['name'] = trim($matches[1]);
        }

        return $result;
    }
}
