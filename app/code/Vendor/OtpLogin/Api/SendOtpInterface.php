<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Api;

/**
 * Interface SendOtpInterface
 * @package Origin\OtpLogin\Api
 */
interface SendOtpInterface
{
    /**
     * Function to send OTP.
     * @api
     * @param string $mobileNumber
     * @param string $isd_code
     * @param string $isCustomer
     * @return string|void
     */
    public function send($mobileNumber, $isd_code, $isCustomer);

    /**
     * Function to verify otp and generate reset password token.
     * @api
     * @param string $mobile_number
     * @param string $isd_code
     * @param string $otp
     * @return mixed
     */
    public function verifyRPOtp($mobile_number, $isd_code, $otp);

    /**
     * Function to verify mobile OTP.
     * @api
     * @param string $mobile_number
     * @param string $otp
     * @param string $isd_code
     * @return void
     */
    public function verifyOtp($mobile_number, $otp, $isd_code);
}
