<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Controller\Frontend;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Origin\OtpLogin\Model\OriginOtpConstants;
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
use Origin\OtpLogin\Helper\Datahelper;
use Origin\ShortMessageService\Helper\Data as OtpHelper;

/**
 * Class OtpSend
 * @package Origin\OtpLogin\Controller\Frontend
 */
class Register extends Action
{
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
            $customerData = $_POST;
            $otpvalue = $customerData['otpvalue'];
            $currentTimestamp = $this->DateTime->timestamp();
            $contactnumber = $customerData['mobile']; //$this->request->getParam('mobilenumber');
            $return = $this->verifyOTP($otpvalue, $currentTimestamp, $contactnumber);
            $response['response'] = $return;
            $response['contactnumber'] = $contactnumber;
            if(!$return){
                $this->getResponse()->representJson(
                    $this->_jsonHelper->jsonEncode($response)
                );
                return;
            } else {
            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            if (!empty($customerData['firstname'])) {
                $customer->setFirstname($customerData['firstname']);
            }else{
                $this->messageManager->addError(__('Firstname is required'));
                $error = true;
            }
            if (!empty($customerData['email'])) {
                $message = $this->uniqueCheck('email' ,$customerData['email']);
                if ($message) {
                    $this->messageManager->addError($message);
                    $error = true;
                }
                $customer->setEmail($customerData['email']);
            }else{
                $this->messageManager->addError(__('Email is required.'));
                $error = true;
            }
            if (!empty($customerData['lastname'])) {
                $customer->setLastname($customerData['lastname']);
            }else{
                $this->messageManager->addError(__('Lastname is required.'));
                $error = true;
            }
            if (!empty($customerData['mobile'])) {
                $message = $this->uniqueCheck('mobile' ,$customerData['mobile']);
                if ($message) {
                    $this->messageManager->addError($message);
                    $error = true;
                }
            }else{
                $this->messageManager->addError(__('Mobile is required.'));
                $error = true;
            }
            if (empty($customerData['isd_code'])) {
                $this->messageManager->addError(__('Isd Code is required.'));
                $error = true;
            }
            // if (!$customerData['mobile_verify']) {
            //     $this->messageManager->addError(__('Mobile number is not verify.'));
            //     $error = true;
            // }
            if (empty($customerData['zip_code'])) {
                $this->messageManager->addError(__('Zip Code is required,'));
                $error = true;
            }
            if(!$error) {
            $autoApprove = $this->helper->getConfig('sms_setting/otp_general/enable_auto_approve');
                if($autoApprove){
                    $customer->setCustomAttribute('customer_approve', 1);
                    
                }
            $customer->setCustomAttribute('mobile_verify', 1);    
            $password = $customerData['zip_code'] . $customerData['email'];
            $customer = $this->customerAccountManagement->createAccount($customer, $password, '');
            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );
            if($autoApprove){
                $login = $this->customerFactory->create()
                                                ->setWebsiteId(1)
                                                ->loadByEmail($customer->getEmail());
                $this->_customerSession->setCustomerAsLoggedIn($login);
                $response['islogin'] = true;
                $this->messageManager->addSuccess(__('Register sucessfully & login'));
            }else{
                $this->messageManager->addSuccess(__('Register sucessfully'));
                $response['islogin'] = false;
            }
            $response['success'] = $error;
        }
        $response['success'] = $error;
            $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($response)
            );
            return;
        }
        } catch (\RuntimeException $e) {
            $this->messageManager->addError(__('Problem while sending your requestion. %1', $e->getMessage()));
        }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($response)
        );
        return;
    }

    public function uniqueCheck($attribute ,$value)
    {
        $currentCustomerId = $this->_customerSession->getCustomer()->getId();
        $websiteid = $this->_storeInterface->getStore()->getWebsiteId();
        $customerObj = $this->customerFactory->create()->getCollection()
                            ->addAttributeToFilter($attribute, trim($value));
        if ($currentCustomerId) {
            $message = __('There is already an account with this mobile number.');
        } else {
            $url = $this->urlFactory->getUrl('customer/account/forgotpassword');
            $message = __('There is already an account with this ' . $attribute . '.');
        }
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
