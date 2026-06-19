<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Model;

use Vendor\OtpLogin\Model\ResourceModel\MobileOtp as MobileOtpResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class MobileOtp
 * @package Vendor\OtpLogin\Model
 */
class MobileOtp extends AbstractModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(MobileOtpResourceModel::class);
    }
}
