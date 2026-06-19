<?php
/**
 * Copyright © Vendor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Vendor\OtpLogin\Controller\Frontend;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;

class OtpVerify extends Action
{
    /**
     * @var float|int
     */
    public $timeLimit = 1 * 60;
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
        JsonHelper $jsonHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $_customerSession;
        $this->_resources = $resourceConnection;
        $this->DateTime = $DateTime;
        $this->_jsonHelper = $jsonHelper;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }
    public function execute()
    {
        $otpvalue = $this->request->getParam('otpvalue');
        $currentTimestamp = $this->DateTime->timestamp();
        $contactnumber = $this->request->getParam('mobilenumber');
        $return = $this->verifyOTP($otpvalue, $currentTimestamp, $contactnumber);
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
