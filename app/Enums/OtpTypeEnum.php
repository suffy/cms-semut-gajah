<?php

namespace App\Enums;

use MyCLabs\Enum\Enum;

/**
 * Action enum
 */
final class OtpTypeEnum extends Enum
{
    const REGISTER = 'REGISTER';
    const FORGOT = 'FORGOT_PASSWORD';
    const PHONE = 'PHONE_NUMBER';
}