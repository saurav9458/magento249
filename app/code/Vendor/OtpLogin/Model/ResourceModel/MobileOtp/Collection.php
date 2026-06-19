<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Model\ResourceModel\MobileOtp;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vendor\OtpLogin\Helper\Data as OtpHelper;
use Vendor\OtpLogin\Helper\Date as DateHelper;
use Vendor\OtpLogin\Model\MobileOtp as MobileOtpModel;
use Vendor\OtpLogin\ResourceModel\MobileOtp as MobileOtpResourceModel;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Vendor\OtpLogin\Model\ResourceModel\MobileOtp
 */
class Collection extends AbstractCollection
{
    /**
     * @var DateHelper
     */
    protected $_dateHelper;

    /**
     * @var OtpHelper
     */
    protected $otpHelper;

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param DateHelper $dateHelper
     * @param OtpHelper $otpHelper
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateHelper $dateHelper,
        OtpHelper $otpHelper,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_dateHelper = $dateHelper;
        $this->otpHelper = $otpHelper;
    }

    /**
     * Initialize resource collection
     * @return void
     */
    public function _construct()
    {
        $this->_init(MobileOtpModel::class, MobileOtpResourceModel::class);
    }

    /**
     * Function to check if otp is already sent within otp lock time.
     * @param $mobileNumber
     * @return bool
     * @throws \Exception
     */
    public function isMobileInLockedPeriod($mobileNumber)
    {
        $otpLockTime = $this->otpHelper->getOTPLockTime();
        $currentDateTime = $this->getCurrentDateTimeInUtc();
        $this->getSelect()->columns([
            "locked_time" => "ADDTIME(created_at, SEC_TO_TIME(" . $otpLockTime . "))"
        ]);
        $this->addFieldToFilter('mobile_number', ['eq' => $mobileNumber]);
        $this->getSelect()->where("ADDTIME(created_at, SEC_TO_TIME(" . $otpLockTime . ")) >= '" . $currentDateTime . "'");
        $this->setOrder('created_at', 'DESC');
        if ($this->getSize() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Function to get current date time in utc
     * @return string
     * @throws \Exception
     */
    private function getCurrentDateTimeInUtc()
    {
        return $this->_dateHelper->getCurrentDateAsUTC();
    }
}
