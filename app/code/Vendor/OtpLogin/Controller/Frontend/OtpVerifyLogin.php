<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Controller\Frontend;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\ManagerInterface;

class OtpVerifyLogin extends Action
{
    /**
     * @var float|int
     */
    public $timeLimit = 5 * 60;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;
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
     * @var Session
     */
    private $_customerSession;
    /**
     * @var DateTime
     */
    private $DateTime;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * OtpVerify constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param Session $_customerSession
     * @param ResourceConnection $resourceConnection
     * @param DateTime $DateTime
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        Session $_customerSession,
        ResourceConnection $resourceConnection,
        DateTime $DateTime,
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        JsonHelper $jsonHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $_customerSession;
        $this->_resources = $resourceConnection;
        $this->DateTime = $DateTime;
        $this->_jsonHelper = $jsonHelper;
        $this->eventManager = $eventManager;
        $this->customerFactory = $customerFactory;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }
    public function execute()
    {
        $otpvalue = $this->request->getParam('otpvalue');
        $currentTimestamp = $this->DateTime->timestamp();
        $contactnumber = $this->request->getParam('mobilenumber');
        $return = $this->verifyOTP($otpvalue, $currentTimestamp, $contactnumber);
        if($return){
         // login
         $error = false;
         $customerObj = $this->customerFactory->create()->getCollection()
                                ->addAttributeToSelect("*")
                                ->addAttributeToFilter('mobile', trim($contactnumber))
                                ->load();
                $customerArray = $customerObj->getData();
                if (empty($customerArray)) {
                    $this->messageManager->addError(__('This account is not register with mobile number %1.', $mobileNumber));
                    $error = true;
                }
                if (!$error) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $CustomerModel = $objectManager->create('Magento\Customer\Model\Customer');
                    $CustomerModel->setWebsiteId(1);

                    $CustomerModel->loadByEmail($customerArray[0]['email']);
                    if (!$CustomerModel->getCustomerApprove()) {
                        $this->messageManager->addError(__('This account is under approvel process with mobile number %1, Please contact us for assistance.', $mobileNumber));
                        // $error = true;
                        // $response['success'] = $error;
                        // $this->getResponse()->representJson(
                        //     $this->_jsonHelper->jsonEncode($response)
                        // );
                        // return;
                    }
                    $this->_customerSession->setCustomerAsLoggedIn($CustomerModel);
                    $smsMessage = 'Login is successful';
                    $this->messageManager->addSuccess(__('Login is successful'));
                    $smsdata = [
                            'message'=> $smsMessage,
                            'mobile'=> $contactnumber
                        ];
                    $this->eventManager->dispatch(
                            'sent_sms_event',
                            ['data' => $smsdata]
                        );
                    $this->eventManager->dispatch(
                            'sent_whatsappsms_event',
                            ['data' => $smsdata]
                        );    
                }
                // login   
        }
        $response['response'] = $return;
        $response['contactnumber'] = $contactnumber;
        // if ($this->request->getParam('forgotpasswordverification') && $return) {
        //     $response['redirect'] = $this->_customerSession->getData('passwordResetLink');
        // }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($response)
        );
        return;
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
