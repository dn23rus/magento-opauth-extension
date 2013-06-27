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
 * Facebook strategy
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_Strategy_Twitter extends Oggetto_Opauth_Model_Strategy_Abstract
{
    const ATTR_CODE                 = 'opauth_twitter_id';
    const XML_PATH_CONFIRM_EMAIL    = 'opauth/twitter/confirm_email_template';
    const XML_PATH_EMAIL_SENDER     = 'opauth/twitter/confirm_email_identity';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_configKey = 'twitter';
    }

    /**
     * Service provide config
     *
     * @return array
     */
    public function getConfig()
    {
        return array(
            'key'    => Mage::getStoreConfig('opauth/' . $this->_getConfigKey() . '/app_id'),
            'secret' => Mage::getStoreConfig('opauth/' . $this->_getConfigKey() . '/app_secret'),
        );
    }

    /**
     * Config for Adminhtml Customer Form
     *
     * @return array
     */
    public function getFormConfig()
    {
        return array(
            'label' => Mage::helper('oggetto_opauth')->__('Twitter Id'),
        );
    }

    /**
     * Attribute code
     *
     * @return string
     */
    public function getAttributeCode()
    {
        return self::ATTR_CODE;
    }

    /**
     * Normalize response info
     *
     * @param array $data response data
     * @return array
     */
    public function normalizeInfo(array $data)
    {
        return array(

        );
    }

    /**
     * Redirect url for additional actions
     *
     * @return string
     */
    public function getRedirectRoute()
    {
        return 'oggetto_opauth/twitter/askEmail';
    }

    /**
     * Confirm email
     *
     * @param string $email   email
     * @param string $name    name
     * @param int    $storeId store id
     * @return Oggetto_Opauth_Model_Strategy_Twitter
     */
    public function sendConfirmationEmail($email, $name, $storeId)
    {
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($email, $name);

        $mailer = Mage::getModel('core/email_template_mailer');
        $mailer
            ->setStoreId($storeId)
            ->addEmailInfo($emailInfo)
            ->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER, $storeId))
            ->setTemplateId(Mage::getStoreConfig(self::XML_PATH_CONFIRM_EMAIL, $storeId))
            ->setTemplateParams(array(
                'name'          => $name,
                'confirm_url'   => Mage::getUrl('oggetto_opauth/twitter/login', array('token' => $this->getToken())),
                'reject_url'    => Mage::getUrl('oggetto_opauth/twitter/reject'),
            ));

        $mailer->send();
        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        $token = Mage::helper('core')->getRandomString(20, 'abcdefghijklmnopqrstuvwxyz1234567890');
        Mage::getSingleton('core/session')->setData('twitter_confirm_email_token', $token);
        return $token;
    }

    /**
     * Verify token
     *
     * @param string $token     token
     * @param bool   $autoClear clear after verifying
     * @return bool
     */
    public function verifyToken($token, $autoClear = true)
    {
        $storedToken = Mage::getSingleton('core/session')->getData('twitter_confirm_email_token');
        $result = (0 === strcasecmp($token, $storedToken));
        if ($autoClear) {
            $this->clearToken();
        }
        return $result;
    }

    /**
     * Clear token
     *
     * @return Oggetto_Opauth_Model_Strategy_Twitter
     */
    public function clearToken()
    {
        Mage::getSingleton('core/session')->unsetData('twitter_confirm_email_token');
        return $this;
    }
}
