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
class Oggetto_Opauth_Model_Strategy_Twitter implements Oggetto_Opauth_Model_Strategy_Interface
{
    const XML_CONFIG_KEY    = 'twitter';
    const ATTR_CODE         = 'opauth_twitter_id';

    /**
     * @var bool
     */
    protected $_isEnabled;


    /**
     * Check if service provider is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (null === $this->_isEnabled) {
            $this->_isEnabled =
                class_exists('TwitterStrategy')
                && Mage::getStoreConfigFlag('opauth/' . self::XML_CONFIG_KEY . '/enabled')
                && Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_id')
                && Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_secret');
        }
        return $this->_isEnabled;
    }

    /**
     * Service provide config
     *
     * @return array
     */
    public function getConfig()
    {
        return array(
            'key'    => Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_id'),
            'secret' => Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_secret'),
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

    public function prepareResponseData($data)
    {
        $default = array(
            'firstname' => null,
            'lastname'  => null,
            'email'     => null,
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
}
