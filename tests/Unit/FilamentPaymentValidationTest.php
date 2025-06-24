<?php

describe('Filament Payment Validation', function () {
    
    it('validates payment sum using closure rule as per Filament 3 docs', function () {
        // Симуляция данных формы (корректные)
        $netSum = 100.00;
        $payments = [
            [
                'payment_cash_amount' => 50.00,
                'payment_cashless_amount' => 50.00,
            ],
        ];
        
        // Симуляция closure валидации из Filament
        $validationClosure = function (string $attribute, $value, \Closure $fail) use ($netSum) {
            if (!is_array($value)) {
                return;
            }

            $totalPayments = 0;

            foreach ($value as $payment) {
                $cash = (float) ($payment['payment_cash_amount'] ?? 0);
                $cashless = (float) ($payment['payment_cashless_amount'] ?? 0);
                $totalPayments += $cash + $cashless;
            }

            if (round($totalPayments, 2) !== round($netSum, 2)) {
                $fail("Сумма всех платежей (" . number_format($totalPayments, 2) . "₽) должна быть равна сумме заказа (" . number_format($netSum, 2) . "₽)");
            }
        };
        
        $errors = [];
        $failFunction = function (string $message) use (&$errors) {
            $errors[] = $message;
        };
        
        // Выполняем валидацию
        $validationClosure('payments', $payments, $failFunction);
        
        // Проверяем результат
        expect($errors)->toBeEmpty();
    });
    
    it('fails validation when payment sum does not match order sum', function () {
        // Симуляция данных формы (некорректные)
        $netSum = 100.00;
        $payments = [
            [
                'payment_cash_amount' => 30.00,
                'payment_cashless_amount' => 20.00, // Итого 50, а не 100
            ],
        ];
        
        // Симуляция closure валидации из Filament
        $validationClosure = function (string $attribute, $value, \Closure $fail) use ($netSum) {
            if (!is_array($value)) {
                return;
            }

            $totalPayments = 0;

            foreach ($value as $payment) {
                $cash = (float) ($payment['payment_cash_amount'] ?? 0);
                $cashless = (float) ($payment['payment_cashless_amount'] ?? 0);
                $totalPayments += $cash + $cashless;
            }

            if (round($totalPayments, 2) !== round($netSum, 2)) {
                $fail("Сумма всех платежей (" . number_format($totalPayments, 2) . "₽) должна быть равна сумме заказа (" . number_format($netSum, 2) . "₽)");
            }
        };
        
        $errors = [];
        $failFunction = function (string $message) use (&$errors) {
            $errors[] = $message;
        };
        
        // Выполняем валидацию
        $validationClosure('payments', $payments, $failFunction);
        
        // Проверяем результат
        expect($errors)->toHaveCount(1);
        expect($errors[0])->toBe("Сумма всех платежей (50.00₽) должна быть равна сумме заказа (100.00₽)");
    });
    
    it('handles multiple payments correctly', function () {
        // Симуляция данных формы с несколькими платежами
        $netSum = 150.00;
        $payments = [
            [
                'payment_cash_amount' => 50.00,
                'payment_cashless_amount' => 0,
            ],
            [
                'payment_cash_amount' => 25.00,
                'payment_cashless_amount' => 75.00,
            ],
        ];
        
        // Симуляция closure валидации из Filament
        $validationClosure = function (string $attribute, $value, \Closure $fail) use ($netSum) {
            if (!is_array($value)) {
                return;
            }

            $totalPayments = 0;

            foreach ($value as $payment) {
                $cash = (float) ($payment['payment_cash_amount'] ?? 0);
                $cashless = (float) ($payment['payment_cashless_amount'] ?? 0);
                $totalPayments += $cash + $cashless;
            }

            if (round($totalPayments, 2) !== round($netSum, 2)) {
                $fail("Сумма всех платежей (" . number_format($totalPayments, 2) . "₽) должна быть равна сумме заказа (" . number_format($netSum, 2) . "₽)");
            }
        };
        
        $errors = [];
        $failFunction = function (string $message) use (&$errors) {
            $errors[] = $message;
        };
        
        // Выполняем валидацию
        $validationClosure('payments', $payments, $failFunction);
        
        // Проверяем результат
        expect($errors)->toBeEmpty();
    });
});
