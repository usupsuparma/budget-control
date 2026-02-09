<?php

if (! function_exists('numberToWordsEn')) {
    function numberToWordsEn($number)
    {
        $number = (int) $number;

        $units = [
            '', 'One', 'Two', 'Three', 'Four', 'Five',
            'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen',
            'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen',
            'Nineteen'
        ];

        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty',
            'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        if ($number < 20) {
            return $units[$number];
        } elseif ($number < 100) {
            return $tens[intval($number / 10)]
                . ($number % 10 ? ' ' . $units[$number % 10] : '');
        } elseif ($number < 1000) {
            return $units[intval($number / 100)] . ' Hundred'
                . ($number % 100 ? ' ' . numberToWordsEn($number % 100) : '');
        } elseif ($number < 1000000) {
            return numberToWordsEn(intval($number / 1000)) . ' Thousand'
                . ($number % 1000 ? ' ' . numberToWordsEn($number % 1000) : '');
        } elseif ($number < 1000000000) {
            return numberToWordsEn(intval($number / 1000000)) . ' Million'
                . ($number % 1000000 ? ' ' . numberToWordsEn($number % 1000000) : '');
        } elseif ($number < 1000000000000) {
            return numberToWordsEn(intval($number / 1000000000)) . ' Billion'
                . ($number % 1000000000 ? ' ' . numberToWordsEn($number % 1000000000) : '');
        } else {
            return numberToWordsEn(intval($number / 1000000000000)) . ' Trillion'
                . ($number % 1000000000000 ? ' ' . numberToWordsEn($number % 1000000000000) : '');
        }
    }
}
