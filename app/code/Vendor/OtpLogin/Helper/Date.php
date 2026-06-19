<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context as Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
/**
 * Class Data
 * @package Vendor\OtpLogin\Helper
 */
class Date extends AbstractHelper
{
    /**
     * Undocumented function
     *
     * @param Context $context
     * @param TimezoneInterface $timezoneInterface
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezoneInterface,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_timezoneInterface = $timezoneInterface;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
    /**
     * Function to get particular date in UTC timezone
     *
     * @param $ref_date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getDateAsUTC($ref_date, $format = DateTime::DATETIME_PHP_FORMAT)
    {
        $value = $this->_scopeConfig->getValue(
            'general/locale/timezone',
            ScopeInterface::SCOPE_STORE
        );

        $date = (new \DateTime($ref_date))->format($format);
        $date = \DateTime::createFromFormat($format, $date, new \DateTimeZone($value));

        $date->setTimeZone(new \DateTimeZone('UTC'));

        return $date->format($format);
    }

    /**
     * Function to get particular date time as timezone
     *
     * @param $ref_date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getDateAsTimeZone($ref_date, $format = DateTime::DATETIME_PHP_FORMAT)
    {
        return $this->_timezoneInterface->date(new \DateTime($ref_date))->format($format);
    }

    /**
     * Function to get default store timezone.
     *
     * @return string
     */
    public function getCurrentStoreTimeZone()
    {
        return $this->_timezoneInterface->getConfigTimezone();
    }

    /**
     * Function to get Current Date and Time As UTC
     *
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getCurrentDateAsUTC($format = DateTime::DATETIME_PHP_FORMAT)
    {
        return (new \DateTime())->format($format);
    }

    /**
     * Function to get Current Date and Time of Store TimeZone
     *
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getCurrentDateAsTimeZone($format = DateTime::DATETIME_PHP_FORMAT)
    {
        return $this->_timezoneInterface
            ->date(new \DateTime())
            ->format($format);
    }

    /**
     * Function to get datetime in required format
     *
     * @param $date
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function getDateAsFormat($date, $format = 'M d,Y h:i A')
    {
        return $this->_timezoneInterface->date($this->getDateAsUTC($date))->format($format);
    }
}
