<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Model;

use Origin\OtpLogin\Model\ResourceModel\MobileOtp as MobileOtpResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class MobileOtp
 * @package Origin\OtpLogin\Model
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
