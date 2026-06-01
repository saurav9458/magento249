<?php

/**
 * Copyright © 2020 Origin. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Helper;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Datahelper
 * @package Origin\OtpLogin\Helper
 */
class Datahelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_OTP_MESSAGE = 'sms_setting/customer_message/otp_sms';
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var OrderFactory
     */
    private $_orderFactory;
    /**
     * @var OrderRepository
     */
    private $_orderRepository;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var AddressFactory
     */
    private $addressFactory;
    /**
     * @var Emulation
     */
    private $_appEmulation;
    /**
     * @var TransportBuilder
     */
    private $_transportBuilder;
    /**
     * @var StateInterface
     */
    private $inlineTranslation;
    /**
     * @var DateTime
     */
    private $_dateTime;
    /**
     * @var View
     */
    private $customerViewHelper;
    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;
    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * Datahelper constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param OrderFactory $orderFactory
     * @param OrderRepository $orderRepository
     * @param DateTime $dateTime
     * @param TransportBuilder $transportBuilder
     * @param View $customerViewHelper
     * @param CustomerRegistry $customerRegistry
     * @param CurrentCustomer $currentCustomer
     * @param Session $session
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param Emulation $appEmulation
     * @param StateInterface $inlineTranslation
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        OrderRepository $orderRepository,
        DateTime $dateTime,
        TransportBuilder $transportBuilder,
        View $customerViewHelper,
        CustomerRegistry $customerRegistry,
        CurrentCustomer $currentCustomer,
        Session $session,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        Emulation $appEmulation,
        StateInterface $inlineTranslation
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
        $this->_orderRepository = $orderRepository;
        $this->moduleManager = $context->getModuleManager();
        $this->session = $session;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->_appEmulation = $appEmulation;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_dateTime = $dateTime;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerRegistry = $customerRegistry;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context);
    }

    /**
     * @param $xml
     * @return mixed
     */
    public function getConfig($xml)
    {
        return $this->_scopeConfig->getValue($xml, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return true;
    }

    /**
     * @param null $storeId
     * @return Phrase
     */
    public function otpMessage($storeId = null)
    {
        return __($this->_scopeConfig->getValue(self::XML_PATH_OTP_MESSAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function sendSuccessMessage($storeId = null)
    {
        return "OTP Successfully Sent To Your Mobile Number";
    }

    /**
     * @param $message_log
     */
    public function recordLog($message_log)
    {
        // log exception to exceptions log
        $message = sprintf('Log message: %s', $message_log);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/OtpLogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }
    /**
     * function to check the length of mobile number
     * @param $mobile
     * @return int|Phrase
     */
    public function lengthCheck($mobile)
    {
        $message = __('Combination of ISD code and mobile number can not exceed 13 digits');
        $return = 0;
        if (!empty($mobile) && strlen($mobile)>13) {
            return $message;
        }
        if (empty($mobile) || $mobile == 0 || !filter_var($mobile, FILTER_VALIDATE_INT)) {
            $return = 0;
        }
        return $return;
    }
}
