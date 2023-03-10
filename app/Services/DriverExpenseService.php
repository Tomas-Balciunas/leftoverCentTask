<?php

namespace App\Services;

class DriverExpenseService
{
    public function calculateDriverExpenses(array $drivers, array $expenses)
    {
        $result = [];
        $driverTotals = array_fill_keys($drivers, 0);
        $numberOfDrivers = count($drivers);
        $totalAmount = 0;

        foreach ($expenses as $key => $expense) {
            $result[$key]['Total'] = $expense;
            $totalAmount = bcadd($totalAmount, $expense, 2);
            $leftoverCents = (int) (bcmul($expense, 100, 0) % $numberOfDrivers);
            $amountPerDriver = bcdiv(bcsub($expense, ($leftoverCents / 100), 2), $numberOfDrivers, 2);

            for ($i = 0; $i < $numberOfDrivers; $i++) {
                $result[$key][$drivers[$i]] = $amountPerDriver;
                $driverTotals[$drivers[$i]] = bcadd($driverTotals[$drivers[$i]], $amountPerDriver, 2);
            }

            while ($leftoverCents > 0) {
                $minDriver = $this->minDriverTotal($driverTotals);
                $result[$key][$minDriver] = bcadd($result[$key][$minDriver], 0.01, 2);
                $driverTotals[$minDriver] = bcadd($driverTotals[$minDriver], 0.01, 2);
                $leftoverCents--;
            }
        }

        $result['Data']['Expenses Total'] = $totalAmount;
        $result['Data'] += $driverTotals;

        return $result;
    }

    private function minDriverTotal(array $driverTotals)
    {
        $min = array_keys($driverTotals, min($driverTotals))[0];
        return $min;
    }
}
