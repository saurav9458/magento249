<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Controller\Frontend;

use Magento\Customer\Api\AccountManagementInterface;
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
class OtpSend extends Action
{
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
        parent::__construct($context);
    }
    public function execute()
    {
        try {
            $otpcode = $this->generateOtpCode();
            $timestamp = $this->DateTime->timestamp();
            $previousSentOtpTime = $this->_customerSession->getData('timestamp');
            if ($previousSentOtpTime && !empty($previousSentOtpTime)) {
                $otpLockInSeconds = $this->helper->getConfig('sms_setting/otp_general/otp_lock_time');
                $otpLockTimeStamp = strtotime('+' . $otpLockInSeconds . ' seconds', $previousSentOtpTime);
                if ($otpLockTimeStamp >= $timestamp) {
                    $response['messages'] = '<div class="message-error error message"><div>' .
                    __('OTP already sent. Please try again after some times') . '</div></div>';
                    $response['success'] = 0;
                    $this->getResponse()->representJson(
                        $this->_jsonHelper->jsonEncode($response)
                    );
                    return;
                }
            }
            $storeId = $this->_storeInterface->getStore()->getId();
            $mobilenumber = $this->request->getParam('mobilenumber');
            $this->_customerSession->setData('mobilenumber', $mobilenumber);
            $this->_customerSession->setData('timestamp', $timestamp);
            $this->_customerSession->setData('otpcode', $otpcode);
            $this->_customerSession->setData('mobile_verified', false);
            $message = str_replace("##OTP##", $otpcode,  OriginOtpConstants::OTP_DEFAULT_SMS_TEXT);
            if ($this->request->getParam('login') != null) {
                $otpMessage = $this->helper->getConfig('sms_setting/customer_message/login_otp_sms');
            } else {
                $otpMessage = $this->helper->getConfig('sms_setting/customer_message/otp_sms');
            };
            $message = str_replace("##OTP##", $otpcode, $otpMessage);
            $message = str_replace("##Otp##", $otpcode, $message);
            // die($message);
            if ($this->uniqueCheck()) {
                $response['messages'] = '<div class="message-error error message"><div>' .
                        $this->uniqueCheck() . '</div></div>';
            } elseif ($this->helper->lengthCheck($mobilenumber)) {
                $response['messages'] = '<div class="message-error error message"><div>' .
                        $this->helper->lengthCheck($mobilenumber) . '</div></div>';
                $response['success'] = 0;
            } else {
                $response['messages'] = '<div class="message-error error message"><div>' .
                        __('Mobile Number is not valid') . '</div></div>';
                if ($mobilenumber) {
                    $this->otpHelper->sendSms($message, $mobilenumber);
                    //$m_array = preg_grep('/^Success\s.*/', $msgResult);
                    /*if (!empty($m_array)) {
                        $response['messages'] = '<div class="message-success success message"><div>' .
                                                    $this->helper->sendSuccessMessage() .
                                                '</div></div>';
                        $response['success'] = 1;
                    } $this->helper->sendSuccessMessage() */
                    $response['messages'] = '<div class="message-success success message"><div>' . $this->helper->sendSuccessMessage() . '</div></div>';
                    $response['success'] = 1;
                } else {
                    $response['messages'] = '<div class="message-error error message"><div>' .
                            __('Mobile Number is not valid') .
                            '</div></div>';
                    $response['success'] = 1;
                }
            }
        } catch (\RuntimeException $e) {
            $this->messageManager->addError(__('Problem while sending your requestion. %1', $e->getMessage()));
        }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($response)
        );
        return;
    }

    public function generateOtpCode()
    {
        $string = '0123456789';
        $string_shuffled = str_shuffle($string);
        return substr($string_shuffled, 1, 4);
    }

    public function uniqueCheck()
    {
        $currentCustomerId = $this->_customerSession->getCustomer()->getId();
        $cn = $this->request->getParam('mobilenumber');
        $login = $this->request->getParam('login');
        $websiteid = $this->_storeInterface->getStore()->getWebsiteId();
        $customerObj = $this->customerFactory->create()->getCollection()
                            ->addAttributeToFilter('mobile', trim($cn));
        if ($currentCustomerId) {
            $message = __('There is already an account with this mobile number.');
        } else {
            $url = $this->urlFactory->getUrl('customer/account/forgotpassword');
            $message = __(
                'There is already an account with this mobile number. If you are sure that it is your mobile number, <a href="%1" >click here </a> to get your password and access your account.',
                $url
            );
        }
        $return = 0;
        
        if($login !=null){
            if ($customerObj->count() == 0) {
                $return = __("Customer account not found with Mobile Number: %1 Please create account for login. ", $cn);
                $this->_customerSession->setData('timestamp', '');
            } else {
                // Added by @sarita
                // checking value of customer approve attribute
                $customerObj->addAttributeToFilter('customer_approve', ['eq' => 'NULL']);
                if ($customerObj->count() == 0) {
                    $return = __("Customer is not approved yet. ", $cn);
                }

                $customerDisapproveObj = $this->customerFactory->create()->getCollection()
                ->addAttributeToFilter('mobile', trim($cn));
                $customerDisapproveObj->addAttributeToFilter('customer_approve', 0);
                if ($customerDisapproveObj->count() > 0) {
                    $return = __("Customer is disapproved by admin. ", $cn);
                }
            }

        }else{
            if ($customerObj->count()) {
                $return = $message;
                $this->_customerSession->setData('timestamp', '');
            }
        }
        return $return;
    }
}
