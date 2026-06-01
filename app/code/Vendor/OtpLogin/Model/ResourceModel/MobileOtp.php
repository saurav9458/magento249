<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Model\ResourceModel;

use Origin\OtpLogin\Model\OriginOtpConstants;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class MobileOtp
 * @package Origin\OtpLogin\Model\ResourceModel
 */
class MobileOtp extends AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(OriginOtpConstants::ORIGIN_OTP_TABLE, OriginOtpConstants::OTP_TABLE_PRIMARY_KEY);
    }

    /**
     * Function to get otp table name
     * @return string
     */
    private function getMobileOtpTable()
    {
        try {
            return $this->getMainTable();
        } catch (\Exception $e) {
            return OriginOtpConstants::ORIGIN_OTP_TABLE;
        }
    }

    /**
     * Function to delete otp for mobile.
     * @param $mobileNumber
     * @return bool
     */
    public function deleteMobileOtp($mobileNumber)
    {
        try {
            if (filter_var($mobileNumber, FILTER_VALIDATE_INT)) {
                $otpTable = $this->getMobileOtpTable();
                $condition = ['mobile_number = ?' => (int)$mobileNumber];
                $connection= $this->getConnection();
                $connection->delete($otpTable, $condition);
                return true;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
