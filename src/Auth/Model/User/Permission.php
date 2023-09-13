<?php

declare(strict_types=1);

namespace App\Auth\Model\User;

use App\Application\ValueObject\EnumValueObject;

final class Permission extends EnumValueObject
{
    public const SECTION_USERS = 100;
    public const SECTION_CATEGORIES = 200;
    public const SECTION_WAREHOUSES = 300;
    public const SECTION_POSITIONS = 400;
    public const SECTION_POSITION_BALANCES = 500;
    public const SECTION_POSITION_UNITS = 600;
    public const SECTION_FIXED_ASSETS = 700;
    public const SECTION_COUNTERPARTIES = 800;
    public const SECTION_ORDERS = 900;
    public const SECTION_PAYMENT_METHODS = 1000;
    public const SECTION_DELIVERY_TYPES = 1100;
    public const SECTION_EMPLOYEES = 1200;
    public const SECTION_VATS = 1300;
}
