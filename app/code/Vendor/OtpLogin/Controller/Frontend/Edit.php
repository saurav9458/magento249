<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Controller\Frontend;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Vendor\OtpLogin\Model\VendorOtpConstants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Vendor\OtpLogin\Helper\Datahelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Vendor\ShortMessageService\Helper\Data as OtpHelper;

/**
 * Class OtpSend
 * @package Vendor\OtpLogin\Controller\Frontend
 */
class Edit extends Action
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var float|int
     */
    public $timeLimit = 5 * 60;
    /**
     * @var DateTime
     */
    protected $DateTime;
    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeInterface;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;
    /**
     * @var Datahelper
     */
    protected $helper;
    /**
     * @var ObjectManagerInterface
     */
    protected $om;
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var UrlInterface
     */
    protected $urlFactory;
    /**
     * @var OtpHelper
     */
    protected $otpHelper;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var ResourceConnection
     */
    private $_resources;

    /**
     * OtpSend constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Session $_customerSession
     * @param ResourceConnection $resourceConnection
     * @param DateTime $DateTime
     * @param Datahelper $helper
     * @param StoreManagerInterface $storeInterface
     * @param CustomerFactory $customerFactory
     * @param UrlFactory $urlFactory
     * @param AccountManagementInterface $customerAccountManagement
     * @param OtpHelper $otpHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        CustomerExtractor $customerExtractor,
        Request $request,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Session $_customerSession,
        ResourceConnection $resourceConnection,
        DateTime $DateTime,
        Datahelper $helper,
        StoreManagerInterface $storeInterface,
        CustomerFactory $customerFactory,
        UrlFactory $urlFactory,
        AccountManagementInterface $customerAccountManagement,
        OtpHelper $otpHelper,
        CustomerRepositoryInterface $customerRepository,
        JsonHelper $jsonHelper
    ) {
        $this->context = $context;
        $this->customerExtractor = $customerExtractor;
        $this->_request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->om = $context->getObjectManager();
        $this->_customerSession = $_customerSession;
        $this->_resources = $resourceConnection;
        $this->DateTime = $DateTime;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->_storeInterface = $storeInterface;
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
        $this->customerFactory = $customerFactory;
        $this->urlFactory = $urlFactory->create();
        $this->customerAccountManagement = $customerAccountManagement;
        $this->otpHelper = $otpHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_eventManager = $eventManager;
        $this->websiteId = $this->_storeInterface->getWebsite()->getWebsiteId();
        parent::__construct($context);
    }
    public function execute()
    {
        try {
            $error = false;
            $customerId = $this->_customerSession->getCustomerId();
            // $customerDataObject = $this->customerRepository->getById($customerId);
            $customerDataObject = $this->customerFactory->create()->load($customerId);
                $email = $customerDataObject->getEmail();
                $mobile = $customerDataObject->getMobile();
            $customerData = $_POST;
            $otpvalue = $customerData['otpvalue-edit'];
            $currentTimestamp = $this->DateTime->timestamp();
            $contactnumber = $customerData['mobile-edit'];
            $return = $this->verifyOTP($otpvalue, $currentTimestamp, $contactnumber);
            if(!$return){
                $this->messageManager->addError(__('Verify Code is not Valid.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/edit');
                return $resultRedirect;
            } else {
            if (!empty($customerData['firstname'])) {
                $customerDataObject->setFirstname($customerData['firstname']);
            }else{
                $this->messageManager->addError(__('Firstname is required'));
                $error = true;
            }
            if (!empty($customerData['email'])) {
                if($email != $customerData['email']){
                    $message = $this->uniqueCheck('email' ,$customerData['email']);
                    if ($message) {
                        $this->messageManager->addError($message);
                        $error = true;
                    }else{
                        $customerDataObject->setEmail($customerData['email']);
                    }
                }
                
            }else{
                $this->messageManager->addError(__('Email is required.'));
                $error = true;
            }
            if (!empty($customerData['lastname'])) {
                $customerDataObject->setLastname($customerData['lastname']);
            }else{
                $this->messageManager->addError(__('Lastname is required.'));
                $error = true;
            }
            if (!empty($customerData['mobile'])) {
                if($mobile != $customerData['mobile']){
                    $message = $this->uniqueCheck('mobile' ,$customerData['mobile']);
                    if ($message) {
                        $this->messageManager->addError($message);
                        $error = true;
                    }else{
                        $customerDataObject->setMoile($customerData['mobile']);
                    }
                }
            }else{
                $this->messageManager->addError(__('Mobile is required.'));
                $error = true;
            }
            if (empty($customerData['zip_code'])) {
                $this->messageManager->addError(__('Zip Code is required,'));
                $error = true;
            }else{
                $customerDataObject->setZipCode($customerData['zip_code']);
            }
            if(!$error) {
                $this->messageManager->addSuccess(__('Profile Update sucessfully.'));
                $customerDataObject->save();
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account');
                return $resultRedirect;
                
            // $this->_eventManager->dispatch(
            //     'customer_register_success',
            //     ['account_controller' => $this, 'customer' => $customer]
            // );
        }else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/edit');
            return $resultRedirect;
        }
        }
        } catch (\RuntimeException $e) {
            $this->messageManager->addError(__('Problem while sending your requestion. %1', $e->getMessage()));
        }
    }

    public function uniqueCheck($attribute ,$value)
    {
        $currentCustomerId = $this->_customerSession->getCustomer()->getId();
        $websiteid = $this->_storeInterface->getStore()->getWebsiteId();
        $customerObj = $this->customerFactory->create()->getCollection()
                            ->addAttributeToFilter($attribute, trim($value));
        $message = __('Invalid value of '. $attribute. '');
        $return = 0;
        if ($customerObj->count()) {
            $return = $message;
        }
        return $return;
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

    public function verifyOTP($otpvalue, $currentTimestamp, $contactnumber)
    {
        $sessionmobilenumber = $this->_customerSession->getData('mobilenumber');
        $sessionTimestamp = $this->_customerSession->getData('timestamp') + $this->timeLimit;
        $sessionotpcode = $this->_customerSession->getData('otpcode');
        if (($sessionotpcode === $otpvalue) && ($currentTimestamp < $sessionTimestamp) && ($sessionmobilenumber == $contactnumber)) {
            $return = 1;
            $this->_customerSession->setData('mobile_verified', true);
        } else {
            $return = 0;
            $this->_customerSession->setData('mobile_verified', false);
        }
        return $return;
    }
}
