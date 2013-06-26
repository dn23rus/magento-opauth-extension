<?php
/**
 * Oggetto Web extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Opauth module to newer versions in the future.
 * If you wish to customize the Oggetto Opauth module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @copyright  Copyright (C) 2013 Oggetto Web (http://oggettoweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Callback response handler class.
 * Require for login or create and login customer.
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_ResponseHandler
{
    const EXCEPTION_UNABLE_TO_LOGIN = 1;

    /**
     * @var Oggetto_Opauth_Model_Opauth
     */
    protected $_opauth;

    /**
     * @var array
     */
    protected $_responseData;

    /**
     * @var array
     */
    protected $_info;

    /**
     * @var Mage_Core_Model_Session_Abstract
     */
    protected $_session;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * @var Oggetto_Opauth_Model_Strategy_Interface
     */
    protected $_provider;

    /**
     * Set response data
     *
     * @param array $responseData response data
     * @return Oggetto_Opauth_Model_ResponseHandler
     */
    public function setResponseData(array $responseData)
    {
        $this->_responseData = $responseData;
        return $this;
    }

    /**
     * Get response data
     *
     * @return array
     * @throws Exception
     */
    public function getResponseData()
    {
        if (null === $this->_responseData) {
            throw new Exception(sprintf('Require set response data before call %s', __METHOD__));
        }
        return $this->_responseData;
    }

    /**
     * Set opauth instance
     *
     * @param Oggetto_Opauth_Model_Opauth $opauth opauth instance
     * @return Oggetto_Opauth_Model_ResponseHandler
     */
    public function setOpauth($opauth)
    {
        $this->_opauth = $opauth;
        return $this;
    }

    /**
     * Get opauth instance.
     * Lazy creation of oggetto_opauth/opauth singleton.
     *
     * @return Oggetto_Opauth_Model_Opauth
     */
    public function getOpauth()
    {
        if (null === $this->_opauth) {
            $this->_opauth = Mage::getSingleton('oggetto_opauth/opauth');
        }
        return $this->_opauth;
    }

    /**
     * Get normalized response data
     *
     * @return array
     */
    public function getResponseInfo()
    {
        if (null === $this->_info) {
            $this->_info = (array) $this->getProvider()->normalizeInfo($this->getResponseData());
        }
        return $this->_info;
    }

    /**
     * Check if requires get email
     *
     * @return bool
     */
    public function isRequireGetEmail()
    {
        $info = $this->getResponseInfo();
        return empty($info['email']);
    }

    /**
     * Set session instance
     *
     * @param Mage_Core_Model_Session_Abstract $session $session instance
     * @return Oggetto_Opauth_Model_Opauth
     */
    public function setSession(Mage_Core_Model_Session_Abstract $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Get session instance.
     * Lazy creation of Mage_Customer_Model_Session.
     *
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        if (null === $this->_session) {
            $this->_session = Mage::getSingleton('customer/session');
        }
        return $this->_session;
    }

    /**
     * Set customer instance
     *
     * @param Mage_Customer_Model_Customer $customer customer instance
     * @return Oggetto_Opauth_Model_Opauth
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Get customer instance.
     * Lazy creation of empty model.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if (null === $this->_customer) {
            $this->_customer = Mage::getModel('customer/customer');
        }
        return $this->_customer;
    }

    /**
     * Get provider instance
     *
     * @return null|Oggetto_Opauth_Model_Strategy_Interface|Oggetto_Opauth_Model_Strategy_Abstract
     * @throws Exception
     */
    public function getProvider()
    {
        if (null === $this->_provider) {
            $data = $this->getResponseData();
            if (empty($data['provider'])) {
                throw new Exception('Empty \'provider\' key in response info');
            }
            $this->_provider = $this->getOpauth()->getProvider($data['provider']);
        }
        return $this->_provider;
    }

    /**
     * Try to login or create and login customer
     *
     * @param Mage_Core_Controller_Request_Http $request request instance
     * @return Oggetto_Opauth_Model_ResponseHandler
     * @throws Oggetto_Opauth_Exception
     */
    public function createAndLogin($request)
    {
        try {
            $this->login();
        } catch (Oggetto_Opauth_Exception $e) {
            if ($e->getCode() === self::EXCEPTION_UNABLE_TO_LOGIN) {
                $this->create($request);
                $this->getSession()->setCustomerAsLoggedIn($this->getCustomer());
            } else {
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Login customer
     *
     * @return Oggetto_Opauth_Model_Opauth
     * @throws Oggetto_Opauth_Exception
     */
    public function login()
    {
        $session = $this->getSession();
        if (!$session->isLoggedIn()) {
            /* @var $resource Oggetto_Opauth_Model_Resource_Customer */
            $info     = $this->getResponseInfo();
            $customer = $this->getCustomer();
            $resource = $customer->getResource();
            $attrCode = $this->getProvider()->getAttributeCode();

            $customer->setData('website_id', Mage::app()->getWebsite()->getId());
            $resource->loadByOpauthProvider($customer, $attrCode, $info['uid']);
            if ($id = $customer->getEntityId()) {
                $session->loginById($id);
            } else {
                $customer->setData('website_id', Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($info['email']);
                if ($id = $customer->getEntityId()) {
                    $session->loginById($id);
                    $customer->setData($attrCode, $info['uid']);
                    $customer->save();
                } else {
                    /* @var $exception Oggetto_Opauth_Exception */
                    $exception = Mage::exception(
                        'Oggetto_Opauth',
                        'Can\'t identify customer',
                        self::EXCEPTION_UNABLE_TO_LOGIN
                    );
                    throw $exception;
                }
            }
        }
        return $this;
    }

    /**
     * Create customer with response data
     *
     * @param Mage_Core_Controller_Request_Http $request request instance
     * @return Oggetto_Opauth_Model_Opauth
     * @throws Mage_Core_Exception
     */
    public function create($request)
    {
        if ($this->isRequireGetEmail()) {
            Mage::throwException(sprintf('Require set email before call %s', __METHOD__));
        }
        $info     = $this->getResponseInfo();
        $customer = $this->getCustomer();

        $customer
            ->setData('email', $info['email'])
            ->setData('firstname', $info['first_name'])
            ->setData('lastname', $info['last_name'])
            ->setData('account_confirmation', 1)
            ->setData('password', $customer->generatePassword())
            ->setData('confirmation', $customer->getData('password'))
            ->setData($this->getProvider()->getAttributeCode(), $info['uid']);

        $errors = $this->_validateCustomer($request, $customer);
        if ($errors !== true) {
            Mage::throwException(nl2br(implode(PHP_EOL, $errors)));
        }

        $customer->save();
        $customer->sendPasswordReminderEmail();

        return $this;
    }

    /**
     * Validate customer
     *
     * @param Mage_Core_Controller_Request_Http $request  request instance
     * @param Mage_Customer_Model_Customer      $customer customer instance
     * @return bool|array
     */
    protected function _validateCustomer($request, $customer)
    {
        $customerForm = $this->_getCustomerForm($customer);
        $customerData = $customerForm->extractData($request);
        $errors       = $customerForm->validateData($customerData);
        if ($errors === true) {
            $customerForm->compactData($customerData);
            $errors = $customer->validate();
        }
        return $errors;
    }

    /**
     * Customer form initialized model
     *
     * @param Mage_Customer_Model_Customer $customer customer instance
     * @return Mage_Customer_Model_Form
     */
    protected function _getCustomerForm($customer)
    {
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setFormCode('customer_account_create');
        $customerForm->setEntity($customer);
        return $customerForm;
    }
}
