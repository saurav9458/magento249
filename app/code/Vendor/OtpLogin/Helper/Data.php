<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Vendor\OtpLogin\Model\VendorOtpConstants;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Vendor\OtpLogin\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Function to OTP Lock Time.
     * @return int|mixed
     */
    public function getOTPLockTime()
    {
        $lockTimeInSeconds = $this->scopeConfig->getValue(
            VendorOtpConstants::OTP_LOCK_TIME_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($lockTimeInSeconds)) {
            $lockTimeInSeconds = VendorOtpConstants::SEND_OTP_LOCK_TIME_IN_SECONDS_DEFAULT;
        }
        return (int) $lockTimeInSeconds;
    }
}
