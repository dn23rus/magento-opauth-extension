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
 * TwitterController
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage controller
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_TwitterController extends Mage_Core_Controller_Front_Action
{
    /**
     * Ask email action
     *
     * @return void
     */
    public function askEmailAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Ask email post action
     *
     * @return void
     */
    public function askEmailPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/noRoute');
        }
        $email = $this->getRequest()->getPost('email');
        if (!Zend_Validate::is($email, 'EmailAddress')) {
            $this->_getSession()->addError($this->__(
                'Please enter a valid email address. For example johndoe@domain.com.'
            ));
            $this->_redirect('*/*/askEmail');
            return;
        }
        $this->_getSession()->setData('opauth_email_dump', $email);
        $name = $this->_getSession()->getData('opauth_response_data_dump/auth/info/name');
        $name = empty($name) ? $name : 'confirm email';
        $this->_getProvider()->sendConfirmationEmail($email, $name, Mage::app()->getStore()->getId());
        $this->_getSession()->addSuccess($this->__(
            'Please, check your email. After confirmation you will automatically login.'
        ));
        $this->_redirect('customer/account/login');
    }

    /**
     * Login action
     *
     * @return void
     */
    public function loginAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/noRoute');
        }
        try {
            if ($this->_getProvider()->verifyToken($this->getRequest()->getParam('token'))) {
                $data = $this->_getSession()->getData('opauth_response_data_dump');
                $data['auth']['info']['email'] = $this->_getSession()->getData('opauth_email_dump');
                $this->_getSession()->unsetData('opauth_email_dump');
                $this->_getSession()->unsetData('opauth_response_data_dump');
                $responseHandler = Mage::getModel('oggetto_opauth/responseHandler');
                $responseHandler->setResponseData($data['auth']);
                $responseHandler->createAndLogin($this->getRequest());
                $this->_redirect('customer/account');
                return;
            } else {
                $this->_getSession()->addError($this->__('Email verification failed. Please, try again later.'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError('Something went wrong while login with opauth twitter service');
        }
        $this->_redirect('customer/account/login');
    }

    /**
     * Reject action
     *
     * @return void
     */
    public function rejectAction()
    {

    }

    /**
     * Twitter strategy provider
     *
     * @return Oggetto_Opauth_Model_Strategy_Twitter
     */
    protected function _getProvider()
    {
        Mage::getSingleton('oggetto_opauth/opauth')->addStrategy('Twitter')->getProvider('Twitter');
    }

    /**
     * Customer session
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}