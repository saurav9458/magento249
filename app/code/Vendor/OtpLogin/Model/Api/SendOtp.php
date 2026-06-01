<?php
/**
 * Copyright © Origin, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Origin\OtpLogin\Model\Api;

use Origin\OtpLogin\Helper\Data as CoreDataHelper;
//use Origin\OtpLogin\Helper\ResponseMessage;
use Origin\OtpLogin\Api\SendOtpInterface;
use Origin\OtpLogin\Model\OriginOtpConstants;
use Origin\OtpLogin\Model\MobileOtpFactory as OriginOtpModel;
use Origin\OtpLogin\Model\ResourceModel\MobileOtpFactory as OriginOtpResourceModel;
use Origin\OtpLogin\Model\ResourceModel\MobileOtp\CollectionFactory as OriginOtpCollectionModel;
use Origin\OtpLogin\Helper\Data as SmsHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\Context;
use Magento\Store\Model\StoreManagerInterface;
//use Origin\OtpLogin\Model\AccountManagement as CustomerAccountManagement;

/**
 * Class SendOtp
 * @package Origin\OtpLogin\Model\Api
 */
class SendOtp implements SendOtpInterface
{
    /**
     * @var CoreDataHelper
     */
    protected $_coreDataHelper;

//    /**
//     * @var ResponseMessage
//     */
//    protected $_responseMessage;

    /**
     * @var OriginOtpModel
     */
    protected $_mobileOtpModel;

    /**
     * @var OriginOtpResourceModel
     */
    protected $_mobileOtpResourceModel;

    /**
     * @var OriginOtpCollectionModel
     */
    protected $_mobileOtpModelCollection;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var SmsHelper
     */
    protected $_smsHelper;

    /**
     * @var CustomerCollection
     */
    protected $_customerCollection;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var CustomerAccountManagement
     */
    protected $_customerAccountManagement;

    /**
     * @var AuthenticationInterface
     */
    protected $_authentication;

    /**
     * SendOtp constructor.
     * @param Context $context
     * @param CoreDataHelper $coreDataHelper
     * @param ResponseMessage $responseMessage
     * @param OriginOtpModel $mobileOtpModel
     * @param OriginOtpResourceModel $mobileOtpResourceModel
     * @param OriginOtpCollectionModel $mobileOtpModelCollection
     * @param StoreManagerInterface $storeManager
     * @param CustomerCollection $customerCollection
     * @param SmsHelper $smsHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param Random $mathRandom
     * @param CustomerAccountManagement $customerAccountManagement
     * @param AuthenticationInterface $authentication
     */
    public function __construct(
        Context $context,
        CoreDataHelper $coreDataHelper,
//        ResponseMessage $responseMessage,
        OriginOtpModel $mobileOtpModel,
        OriginOtpResourceModel $mobileOtpResourceModel,
        OriginOtpCollectionModel $mobileOtpModelCollection,
        StoreManagerInterface $storeManager,
        CustomerCollection $customerCollection,
        SmsHelper $smsHelper,
        CustomerRepositoryInterface $customerRepository,
        Random $mathRandom,
//        CustomerAccountManagement $customerAccountManagement,
        AuthenticationInterface $authentication
    ) {
        $this->_coreDataHelper = $coreDataHelper;
//        $this->_responseMessage = $responseMessage;
        $this->_mobileOtpModel = $mobileOtpModel;
        $this->_mobileOtpResourceModel = $mobileOtpResourceModel;
        $this->_mobileOtpModelCollection = $mobileOtpModelCollection;
        $this->_smsHelper = $smsHelper;
        $this->_storeManager = $storeManager;
        $this->_customerCollection = $customerCollection;
        $this->_customerRepository = $customerRepository;
        $this->mathRandom = $mathRandom;
//        $this->_customerAccountManagement = $customerAccountManagement;
        $this->_authentication = $authentication;
    }

    /**
     * Function to get current UTC datetime.
     * @return string
     * @throws \Exception
     */
    private function getCurrentUtcTime()
    {
        return $this->_coreDataHelper->getCurrentDateAsUTC();
    }

    /**
     * Function to send OTP.
     * @api
     * @param string $mobileNumber
     * @param string $isd_code
     * @param string $isCustomer
     * @return string|void
     */
    public function send($mobileNumber, $isd_code, $isCustomer)
    {
        try {
            if (empty($mobileNumber) ||
                ($isCustomer != OriginOtpConstants::SEND_OTP_CUSTOMER_REGISTRATION &&
                    $isCustomer != OriginOtpConstants::SEND_OTP_CUSTOMER_RESET) ||
                empty($this->validateIsdCode($isd_code))
            ) {
                return $this->_responseMessage->_sendResponse(
                        ResponseMessage::REQUEST_PARAMETERS_ERROR,
                        [],
                        417
                    );
            }
            if (empty($this->validateMobileNumber($mobileNumber))) {
                return $this->_responseMessage->_sendResponse(
                        ResponseMessage::MOBILE_NUMBER_INVALID,
                        [],
                        417
                    );
            }
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $mobileNumber = $isd_code . $mobileNumber;
            if ($this->checkIfOtpAlreadySent($mobileNumber) === true) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::OTP_ALREADY_SENT,
                    [],
                    417
                );
            }
            $customerCollection = $this->_customerCollection->create();
            $customerCollection->addAttributeToFilter("website_id", ['eq' => $websiteId]);
            $customerCollection->addAttributeToFilter('mobile', $mobileNumber);
            if ($isCustomer == OriginOtpConstants::SEND_OTP_CUSTOMER_REGISTRATION) {
                if ($customerCollection->getSize() > 0) {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::MOBILE_NUMBER_EXISTS,
                        [],
                        417
                    );
                }
            }
            if ($isCustomer == OriginOtpConstants::SEND_OTP_CUSTOMER_RESET) {
                if ($customerCollection->getSize() <= 0) {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::RECORDS_NOT_FOUND,
                        [],
                        417
                    );
                }
                $customerData = $customerCollection->getFirstItem();
                if (!empty($customerData->getConfirmation())) {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::EMAIL_NOT_VERIFIED,
                        null,
                        417
                    );
                }
                if (true === $this->_authentication->isLocked($customerData->getId())) {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::ACCOUNT_LOCKED,
                        null,
                        417
                    );
                }
            }
            $otpCode = $this->generateOtpCode();
            $message = $this->getCustomerOtpMessage($otpCode);
            $this->_smsHelper->sendSms($mobileNumber, $message, 'ALL');
            $otpId = $this->saveMobileOtp($mobileNumber, $otpCode);
            if (empty($otpId)) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::INTERNAL_ERROR,
                    [],
                    417
                );
            }
            return $this->_responseMessage->_sendResponse(
                ResponseMessage::OTP_SENT,
                [],
                200
            );
        } catch (\Exception $ex) {
            return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
        }
    }

    /**
     * Function to verify if otp is already sent or not.
     * @param string $mobileNumber
     * @return bool
     */
    private function checkIfOtpAlreadySent($mobileNumber)
    {
        try {
            return $this->_mobileOtpModelCollection->create()->isMobileInLockedPeriod($mobileNumber);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Function to verify isd code.
     * @param $isdCode
     * @return bool
     */
    private function validateIsdCode($isdCode)
    {
        if (filter_var($isdCode, FILTER_VALIDATE_INT) &&
            (strlen((string) $isdCode) > 0 && strlen((string) $isdCode) <= 4)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Function to validate mobile number
     * @param $mobileNumber
     * @return bool
     */
    private function validateMobileNumber($mobileNumber)
    {
        if (!filter_var($mobileNumber, FILTER_VALIDATE_INT) ||
            !preg_match("/\@/", $mobileNumber) && (preg_match("/^\+/", $mobileNumber) ||
                !ResponseMessage::validStrLen($mobileNumber, 10, 15))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Function to get OTP sms Text.
     * @param string $otpCode
     * @return string
     */
    private function getCustomerOtpMessage($otpCode)
    {
        $otpSms = OriginOtpConstants::OTP_DEFAULT_SMS_TEXT;
        if (strpos($otpSms, "##OTP##") >= 0) {
            $sms = str_replace("##OTP##", $otpCode, trim($otpSms));
        } else {
            $sms = $otpCode;
        }
        return $sms;
    }

    /**
     * Function to save otp data
     * @param $mobileNumber
     * @param $otpCode
     * @return mixed|null
     */
    private function saveMobileOtp($mobileNumber, $otpCode)
    {
        try {
            $currentDateTime = $this->getCurrentUtcTime();
            $expiryTime = date(
                'Y-m-d H:i:s',
                strtotime(
                    '+' . OriginOtpConstants::OTP_EXPIRY_TIME_IN_SECONDS . ' second',
                    strtotime(
                        $currentDateTime
                    )
                )
            );
            $mobileOtp = $this->_mobileOtpModelCollection->create();
            $mobileOtp->addFieldToFilter("mobile_number", ["eq" => $mobileNumber]);
            $mobileOtp->setOrder('created_at', 'DESC');
            if ($mobileOtp->getSize() > 0) {
                $mobileOtpItem = $mobileOtp->getFirstItem();
                $mobileOtpModel = $this->_mobileOtpModel->create()->load($mobileOtpItem->getId());
            } else {
                $mobileOtpModel = $this->_mobileOtpModel->create();
            }
            $mobileOtpModel->setMobileNumber($mobileNumber);
            $mobileOtpModel->setOtp($otpCode);
            $mobileOtpModel->setMobileVerified(OriginOtpConstants::NEW_OTP_STATUS);
            $mobileOtpModel->setExpiry($expiryTime);
            $mobileOtpModel->setCreatedAt($currentDateTime);
            $mobileOtpModel->setUpdatedAt(null);
            $mobileOtpModel->save();
            return $mobileOtpModel->getId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Function to verify mobile OTP on registration.
     * @api
     * @param string $mobile_number
     * @param string $otp
     * @param string $isd_code
     * @return void
     */
    public function verifyOtp($mobile_number, $otp, $isd_code)
    {
        try {
            if (empty($this->validateIsdCode($isd_code)) || empty($this->validateMobileNumber($mobile_number)) ||
                strlen((string) $otp) != OriginOtpConstants::OTP_LENGTH
            ) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::MOBILE_NUMBER_INVALID,
                    [],
                    417
                );
            }
            $currentUtcTime = $this->getCurrentUtcTime();
            $mobileNumber = $isd_code . $mobile_number;
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customerCollection = $this->_customerCollection->create();
            $customerCollection->addAttributeToFilter("website_id", ['eq' => $websiteId]);
            $customerCollection->addAttributeToFilter('mobile', $mobileNumber);
            if ($customerCollection->getSize() > 0) {
                return $this->_responseMessage->_sendResponse(ResponseMessage::MOBILE_NUMBER_EXISTS, [], 417);
            }
            $mobileOtp = $this->_mobileOtpModelCollection->create();
            $mobileOtp->addFieldToFilter("mobile_number", ["eq" => $mobileNumber]);
            $mobileOtp->addFieldToFilter("otp", ["eq"=>$otp]);
            $mobileOtp->addFieldToFilter("mobile_verified", ["eq"=>OriginOtpConstants::NEW_OTP_STATUS]);
            $mobileOtp->addFieldToFilter('expiry', ['gteq' => $currentUtcTime]);
            $mobileOtp->setOrder('created_at', 'DESC');
            if ($mobileOtp->getSize() > 0) {
                $mobileOtpRow = $mobileOtp->getFirstItem();
                $verifyOtpId = $this->verifyMobileOtp($mobileOtpRow->getId());
                if (empty($verifyOtpId)) {
                    return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
                }
                return $this->_responseMessage->_sendResponse(ResponseMessage::OTP_VERIFIED, [], 200);
            } else {
                return $this->_responseMessage->_sendResponse(ResponseMessage::OTP_INVALID, [], 417);
            }
        } catch (\Exception $ex) {
            return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
        }
    }

    /**
     * Function to update verified otp status.
     * @param int $otpId
     * @return mixed
     */
    private function verifyMobileOtp($otpId)
    {
        try {
            $currentUtcDateTime = $this->getCurrentUtcTime();
            $mobileOtpModel = $this->_mobileOtpModel->create()->load($otpId);
            $mobileOtpModel->setMobileVerified(OriginOtpConstants::OTP_VERIFIED_STATUS);
            $mobileOtpModel->setUpdatedAt($currentUtcDateTime);
            $mobileOtpModel->save();
            return $mobileOtpModel->getId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Function to verify otp and generate reset password token.
     * @api
     * @param string $mobile_number
     * @param string $isd_code
     * @param string $otp
     * @return mixed
     */
    public function verifyRPOtp($mobile_number, $isd_code, $otp)
    {
        try {
            if (empty($this->validateIsdCode($isd_code)) || empty($this->validateMobileNumber($mobile_number)) ||
                strlen((string) $otp) != OriginOtpConstants::OTP_LENGTH
            ) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::REQUEST_PARAMETERS_ERROR,
                    [],
                    417
                );
            }
            $mobileNumber = $isd_code . $mobile_number;
            //verify customer
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customerCollection = $this->_customerCollection->create();
            $customerCollection->addAttributeToFilter("website_id", ['eq' => $websiteId]);
            $customerCollection->addAttributeToFilter('mobile', $mobileNumber);
            if ($customerCollection->getSize() <= 0) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::RECORDS_NOT_FOUND,
                    [],
                    417
                );
            }
            $customerData = $customerCollection->getFirstItem();
            if (!empty($customerData->getConfirmation())) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::EMAIL_NOT_VERIFIED,
                    null,
                    417
                );
            }
            if (true === $this->_authentication->isLocked($customerData->getId())) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::ACCOUNT_LOCKED,
                    null,
                    417
                );
            }
            $currentUtcDateTime = $this->getCurrentUtcTime();
            $mobileOtpCollection = $this->_mobileOtpModelCollection->create();
            $mobileOtpCollection->addFieldToFilter('mobile_number', ['eq' => $mobileNumber]);
            $mobileOtpCollection->addFieldToFilter('otp', ['eq' => $otp]);
            $mobileOtpCollection->addFieldToFilter('mobile_verified', ['eq' => OriginOtpConstants::NEW_OTP_STATUS]);
            $mobileOtpCollection->addFieldToFilter('expiry', ['gteq' => $currentUtcDateTime]);
            if ($mobileOtpCollection->getSize() <= 0) {
                return $this->_responseMessage->_sendResponse(ResponseMessage::OTP_INVALID, [], 417);
            }
            $mobileOtpItem = $mobileOtpCollection->getFirstItem();
            $verifyOtpId = $this->verifyMobileOtp($mobileOtpItem->getId());
            if (empty($verifyOtpId)) {
                return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
            }
            try {
                $customer = $this->_customerRepository->getById($customerData->getId());
                $newPasswordToken = $this->mathRandom->getUniqueHash();
                $changeResetPassword = $this->_customerAccountManagement->changeResetPasswordLinkToken(
                    $customer,
                    $newPasswordToken
                );
                if (!empty($changeResetPassword)) {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::SUCCESS,
                        ["rp_token" => $newPasswordToken],
                        200
                    );
                } else {
                    return $this->_responseMessage->_sendResponse(
                        ResponseMessage::INTERNAL_ERROR,
                        null,
                        417
                    );
                }
            } catch (NoSuchEntityException $e) {
                return $this->_responseMessage->_sendResponse(
                    ResponseMessage::RECORDS_NOT_FOUND,
                    null,
                    417
                );
            } catch (InputException $e) {
                return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
            }
        } catch (\Exception $e) {
            return $this->_responseMessage->_sendResponse(ResponseMessage::INTERNAL_ERROR, [], 417);
        }
    }

    /**
     * Generate OTP Code
     * @return string
     */
    private function generateOtpCode()
    {
        $string = '0123456789';
        $string_shuffled = str_shuffle($string);
        return substr($string_shuffled, 1, OriginOtpConstants::OTP_LENGTH);
    }

    /**
     * Function to re-verify mobile verification.
     * @param $mobileNumber
     * @param $otp
     * @return bool
     * @throws InputException
     */
    public function validateMobileVerification($mobileNumber, $otp)
    {
        if (!filter_var($mobileNumber, FILTER_VALIDATE_INT)) {
            throw new InputException(
                __(
                    InputException::INVALID_FIELD_VALUE,
                    ['value' => $mobileNumber, 'fieldName' => 'mobileNumber']
                )
            );
        }
        if (!filter_var($otp, FILTER_VALIDATE_INT) || strlen((string) $otp) != OriginOtpConstants::OTP_LENGTH) {
            throw new InputException(
                __(
                    InputException::INVALID_FIELD_VALUE,
                    ['value' => $mobileNumber, 'fieldName' => 'otp']
                )
            );
        }
        $mobileOtpCollection = $this->_mobileOtpModelCollection->create();
        $mobileOtpCollection->addFieldToFilter('mobile_number', ['eq' => $mobileNumber]);
        $mobileOtpCollection->addFieldToFilter('otp', ['eq' => $otp]);
        $mobileOtpCollection->addFieldToFilter('mobile_verified', ['eq' => OriginOtpConstants::OTP_VERIFIED_STATUS]);
        if ($mobileOtpCollection->getSize() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Function to delete mobile number otp
     * @param $mobileNumber
     * @return bool
     */
    public function deleteMobileOtp($mobileNumber)
    {
        $this->_mobileOtpResourceModel->create()->deleteMobileOtp($mobileNumber);
        return true;
    }
}
