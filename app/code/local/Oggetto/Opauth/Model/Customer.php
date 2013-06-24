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
 * Customer
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_Customer
{
    const EXCEPTION_UNABLE_TO_LOGIN = 1;

    /**
     * @var Varien_Object
     */
    protected $userData;

    /**
     * @var array
     */
    protected $_responseData = array();

    protected $_currentProvider;

    /**
     * Set response data
     *
     * @param array $responseData response data
     * @return Oggetto_Opauth_Model_Customer
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
     */
    public function getResponseData()
    {
        return $this->_responseData;
    }



    /**
     * Set user data
     *
     * @param array|Varien_Object $data data
     * @return Oggetto_Opauth_Model_Customer
     * @throws Oggetto_Opauth_Exception
     */
    public function setUserData($data)
    {
        if (is_array($data)) {
            $this->userData = new Varien_Object($data);
        } elseif (is_object($data) && $data instanceof Varien_Object) {
            $this->userData = $data;
        } else {
            /* @var $exception Oggetto_Opauth_Exception */
            $exception = Mage::exception('Oggetto_Opauth', 'User data must be array or Varien_Object instance');
            throw $exception;
        }
        return $this;
    }

    /**
     * Get user data
     *
     * @return Varien_Object
     * @throws Oggetto_Opauth_Exception
     */
    public function getUserData()
    {
        if (null === $this->userData) {
            /* @var $exception Oggetto_Opauth_Exception */
            $exception = Mage::exception('Oggetto_Opauth', sprintf('Require set user data before call %s', __METHOD__));
            throw $exception;
        }
        return $this->userData;
    }

    /**
     * Try to login or create and login customer
     *
     * @param Mage_Core_Controller_Request_Http $request request instance
     * @param Mage_Customer_Model_Session|null  $session session instance
     */
    public function createAndLogin($request, $session = null)
    {
        try {
            $this->login($session);
        } catch (Oggetto_Opauth_Exception $e) {
            if ($e->getCode() === self::EXCEPTION_UNABLE_TO_LOGIN) {
                $session->setCustomerAsLoggedIn($this->create($request));
            }
        }
    }

    /**
     * Login customer
     *
     * @param Mage_Customer_Model_Session $session session instance
     * @return Oggetto_Opauth_Model_Customer
     * @throws Oggetto_Opauth_Exception
     */
    public function login($session = null)
    {
        $session = $session ? $session : Mage::getSingleton('customer/session');
        if (!$session->isLoggedIn()) {
            /* @var $resource Oggetto_Opauth_Model_Resource_Customer */
            $resource = Mage::getResourceSingleton('customer/customer');
            $customer = Mage::getModel('customer/customer');
            $customer->setData('website_id', (Mage::app()->getWebsite()->getId()));
            $resource->loadByOpauthProvider($customer, $this->_getCurrentProvider()->getAttributeCode(), '');
            if ($customerId = $customer->getEntityId()) {
                $session->loginById($customerId);
            } else {
                $customer->loadByEmail('email');
                if ($customerId = $customer->getEntityId()) {
                    $session->loginById($customerId);
                } else {
                    /* @var $exception Oggetto_Opauth_Exception */
                    $exception = Mage::exception(
                        'Oggetto_Opauth',
                        'Customer not exits.',
                        self::EXCEPTION_UNABLE_TO_LOGIN
                    );
                    throw $exception;
                }
            }
        }

        return $this;
    }

    /**
     * @return Oggetto_Opauth_Model_Strategy_Interface
     */
    protected function _getCurrentProvider()
    {
        if (null === $this->_currentProvider) {
            $data = $this->getResponseData();
            if (!isset($data['provider'])) {

            }
            $providers = Mage::getSingleton('oggetto_opauth/opauth')->getStrategies();
            if (!isset($providers[$data['provider']])) {

            }
            $this->_currentProvider = $providers[$data['provider']];
        }
        return $this->_currentProvider;
    }

    /**
     * Create customer with facebook data
     *
     * @param Mage_Core_Controller_Request_Http $request request instance
     * @return Mage_Customer_Model_Customer
     * @throws Mage_Core_Exception
     */
    public function create($request)
    {
        $data     = $this->getUserData();
        $customer = Mage::getModel('customer/customer');

        $customer->setEmail($data->getEmail())
            ->setFirstname($data->getFirstName())
            ->setLastname($data->getLastName())
            ->setAccountConfirmation(1)
            ->setPassword($customer->generatePassword())
            ->setConfirmation($customer->getPassword());

        $errors = $this->_validateCustomer($request, $customer);
        if ($errors !== true) {
            Mage::throwException(nl2br(implode(PHP_EOL, $errors)));
        }

        $customer->save();
        $customer->sendPasswordReminderEmail();

        return $customer;
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
