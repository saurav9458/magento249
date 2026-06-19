<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Model\ResourceModel;

use Vendor\OtpLogin\Model\VendorOtpConstants;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class MobileOtp
 * @package Vendor\OtpLogin\Model\ResourceModel
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
        $this->_init(VendorOtpConstants::Vendor_OTP_TABLE, VendorOtpConstants::OTP_TABLE_PRIMARY_KEY);
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
            return VendorOtpConstants::Vendor_OTP_TABLE;
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
