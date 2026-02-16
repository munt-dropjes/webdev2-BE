<?php

namespace Config;

class NpcConfig
{
    private const ROLL_MAX = 100;
    private const THRESHOLD_IDLE = 70;
    private const THRESHOLD_SUBSIDY = 90;

    private const MULTIPLIER = 1000;
    private const MIN_BANKRUPTCY_SAFEGUARD = 1000;

    private const MIN_SUBSIDY_BASE = 5;
    private const MAX_SUBSIDY_BASE = 30;

    private const MIN_EXPENSE_BASE = 2;
    private const MAX_EXPENSE_BASE = 15;

    private const DESC_SUBSIDY = "Subsidie ontvangen door De Staf";
    private const DESC_EXPENSE = "Onverwachte materiaalkosten voor De Staf";

    // --- Standard Getters ---
    public static function getRollMax(): int { return self::ROLL_MAX; }
    public static function getThresholdIdle(): int { return self::THRESHOLD_IDLE; }
    public static function getThresholdSubsidy(): int { return self::THRESHOLD_SUBSIDY; }
    public static function getMinBankruptcySafeguard(): int { return self::MIN_BANKRUPTCY_SAFEGUARD; }
    public static function getDescSubsidy(): string { return self::DESC_SUBSIDY; }
    public static function getDescExpense(): string { return self::DESC_EXPENSE; }

    // --- Calculated Boundary Getters (As requested) ---
    public static function getSubsidyMin(): int {
        return self::MIN_SUBSIDY_BASE * self::MULTIPLIER;
    }

    public static function getSubsidyMax(): int {
        return self::MAX_SUBSIDY_BASE * self::MULTIPLIER;
    }

    public static function getExpenseMin(): int {
        return self::MIN_EXPENSE_BASE * self::MULTIPLIER;
    }

    public static function getExpenseMax(): int {
        return self::MAX_EXPENSE_BASE * self::MULTIPLIER;
    }

    public static function calculateRandomSubsidy(): int {
        return rand(self::MIN_SUBSIDY_BASE, self::MAX_SUBSIDY_BASE) * self::MULTIPLIER;
    }

    public static function calculateRandomExpense(): int {
        // Returns a negative number automatically
        return -(rand(self::MIN_EXPENSE_BASE, self::MAX_EXPENSE_BASE) * self::MULTIPLIER);
    }
}
