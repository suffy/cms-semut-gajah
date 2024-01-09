<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * Action enum
 */
final class CronJobEnums extends Enum
{
    const SUBSCRIBE = 'subscribe';
    const SITE = 'site';
    const SALESMAN = 'salesman';
    const CUSTOM_CUSTOMER = 'custom_customer';
    const DAILY_CUSTOMER = 'daily_customer';
    const CUSTOMER_BINAAN = 'customer_binaan';
    const PRODUCT = 'product';
    const STOCK = 'stock';
    const COMPLAINT = 'complaint';
    const COD = 'cod';
}