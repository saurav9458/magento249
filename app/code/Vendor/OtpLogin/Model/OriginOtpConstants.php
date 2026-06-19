<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Model;

/**
 * Class VendorOtpConstants
 * @package Vendor\OtpLogin\Model
 */
class VendorOtpConstants
{
    const Vendor_OTP_TABLE = 'Vendor_customer_mobile_otp';

    const OTP_TABLE_PRIMARY_KEY = 'id';

    const OTP_LENGTH = 4;

    const OTP_DEFAULT_SMS_TEXT = 'Your OTP code is ##OTP##.';

    const SEND_OTP_CUSTOMER_REGISTRATION = 'register';

    const SEND_OTP_CUSTOMER_RESET = 'resetpass';

    const OTP_EXPIRY_TIME_IN_SECONDS = 60;

    const NEW_OTP_STATUS = 0;

    const OTP_VERIFIED_STATUS = 1;

    const RESET_PASSWORD_OTP_SMS_CONFIG_PATH = '';

    const SEND_OTP_LOCK_TIME_IN_SECONDS_DEFAULT = 30;

    const OTP_LOCK_TIME_CONFIG_PATH = 'sms_setting/otp_general/otp_lock_time';

}
