<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Origin\OtpLogin\Model\OriginOtpConstants;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Origin\OtpLogin\Helper
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
            OriginOtpConstants::OTP_LOCK_TIME_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($lockTimeInSeconds)) {
            $lockTimeInSeconds = OriginOtpConstants::SEND_OTP_LOCK_TIME_IN_SECONDS_DEFAULT;
        }
        return (int) $lockTimeInSeconds;
    }
}
