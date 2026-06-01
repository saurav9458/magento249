<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Origin\OtpLogin\Controller\Frontend;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Phrase;

/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Login extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param CustomerFactory $customerFactory
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Customer $customer,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        CustomerFactory $customerFactory,
        Validator $formKeyValidator,
        ManagerInterface $eventManager,
        JsonHelper $jsonHelper,
        AccountRedirect $accountRedirect
    ) {
        $this->session = $customerSession;
        $this->_customer = $customer;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerFactory = $customerFactory;
        $this->accountRedirect = $accountRedirect;
        $this->eventManager = $eventManager;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $error = false;
        $mobileNumber = $this->getRequest()->getPost('mobile_login');
        $isdCode = $this->getRequest()->getPost('isd_code_login');
        $verify = $this->getRequest()->getPost('mobile_verify_login');
        $otp = $this->getRequest()->getPost('otpvalue_login');
        if ($verify) {
            try {
                if (empty($mobileNumber)) {
                    $this->messageManager->addError(__('Mobile Number is required'));
                    $error = true;
                }
                if (empty($isdCode)) {
                    $this->messageManager->addError(__('Isd Code is required'));
                    $error = true;
                }
                $customerObj = $this->customerFactory->create()->getCollection()
                                ->addAttributeToSelect("*")
                                ->addAttributeToFilter('mobile', trim($mobileNumber))
                                ->load();
                $customerArray = $customerObj->getData();
                if (empty($customerArray)) {
                    $this->messageManager->addError(__('This account is not register with mobile number %1.', $mobileNumber));
                    $error = true;
                }
                if (!$error) {
                    $CustomerModel = $this->customerFactory->create()
                                        ->setWebsiteId(1)
                                        ->loadByEmail($customerArray[0]['email']);
                    if (!$CustomerModel->getCustomerApprove()) {
                        $this->messageManager->addError(__('This account is under approvel process with mobile number %1, Please contact us for assistance.', $mobileNumber));
                        $error = true;
                        $response['success'] = $error;
                        $this->getResponse()->representJson(
                            $this->_jsonHelper->jsonEncode($response)
                        );
                        return;
                    }
                    $this->session->setCustomerAsLoggedIn($CustomerModel);
                    $smsMessage = 'Login sucessfully';
                    $this->messageManager->addSuccess(__('Login sucessfully'));
                    $smsdata = [
                            'message'=> $smsMessage,
                            'mobile'=> $mobileNumber
                        ];
                    $this->eventManager->dispatch(
                            'sent_sms_event',
                            ['data' => $smsdata]
                        );
                }
                $response['success'] = $error;
                $this->getResponse()->representJson(
                        $this->_jsonHelper->jsonEncode($response)
                    );
                return;
            } catch (EmailNotConfirmedException $e) {
                $value = "URl";//$this->customerUrl->getEmailConfirmationUrl($login['username']);
                $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
            } catch (UserLockedException $e) {
                $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
            } catch (AuthenticationException $e) {
                $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
            } catch (LocalizedException $e) {
                $message = $e->getMessage();
            } catch (\Exception $e) {
                // PA DSS violation: throwing or logging an exception here can disclose customer password
                $this->messageManager->addError(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
            } finally {
                if (isset($message)) {
                    $this->messageManager->addError($message);
                    // $this->session->setUsername($login['username']);
                }
            }
        } else {
            $this->messageManager->addError(__('Otp: %1 is not valid.', $otp));
            $response['success'] = $error;
            $this->getResponse()->representJson(
                        $this->_jsonHelper->jsonEncode($response)
                    );
            return;
        }
    }
}
