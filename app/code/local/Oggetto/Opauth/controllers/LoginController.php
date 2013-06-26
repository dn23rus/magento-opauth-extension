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
 * LoginController
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage controller
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_LoginController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account/');
            return;
        }

        $providerCode = $this->getRequest()->getParam('with');
        if (!$providerCode) {
            $this->_redirect('customer/account/login');
            return;
        }

        try {
            Mage::getSingleton('oggetto_opauth/opauth')->run($providerCode);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Opauth API error.'));
            $this->_redirect('customer/account/login');
            return;
        }
    }

    /**
     * Callback action
     *
     * @return void
     */
    public function callbackAction()
    {
        $auth = Mage::getSingleton('oggetto_opauth/opauth');
        $data = $auth->getResponseData();
        if (!$data) {
            $this->_getSession()->addError($this->__('Can\'t authenticate with empty Opauth response'));
            $this->_redirect('customer/account/login');
            return;
        }

        $reason = null;
        if (!$auth->validate($data, $reason)) {
            $this->_getSession()->addError($this->__($reason));
            $this->_redirect('customer/account/login');
            return;
        }

        $responseHandler = Mage::getModel('oggetto_opauth/responseHandler');
        try {
            $responseHandler->setResponseData($data['auth']);
            if (!$responseHandler->isRequireGetEmail()) {
                $responseHandler->createAndLogin($this->getRequest());
            } else {
                $this->_getSession()->setData('opauth_response_data_dump', $data);
                $this->_redirect($responseHandler->getProvider()->getRedirectRoute());
                return;
            }
            $this->_redirect('customer/account');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Something went wrong while login with Opauth'));
        }
        $this->_redirect('customer/account/login');
    }


    /**
     * Facebook action
     *
     * @return void
     */
    public function facebookAction()
    {
        Mage::getSingleton('oggetto_opauth/opauth')->addStrategy('Facebook')->callInternalCallback('Facebook');
    }

    /**
     * Google action
     *
     * @return void
     */
    public function googleAction()
    {
        Mage::getSingleton('oggetto_opauth/opauth')->callInternalCallback('Google');
    }

    /**
     * Twitter action
     *
     * @return void
     */
    public function twitterAction()
    {
        Mage::getSingleton('oggetto_opauth/opauth')->callInternalCallback('Twitter');
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
