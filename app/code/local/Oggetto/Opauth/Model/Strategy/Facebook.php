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
class Oggetto_Opauth_Model_Strategy_Facebook implements Oggetto_Opauth_Model_Strategy_Interface
{
    const XML_CONFIG_KEY    = 'facebook';
    const ATTR_CODE         = 'opauth_facebook_id';

    /**
     * @var bool
     */
    protected $_isEnabled;

    /**
     * Check if facebook service provider is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (null === $this->_isEnabled) {
            $this->_isEnabled =
                class_exists('FacebookStrategy')
                && Mage::getStoreConfigFlag('opauth/' . self::XML_CONFIG_KEY . '/enabled')
                && Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_id')
                && Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_secret');
        }
        return $this->_isEnabled;
    }

    /**
     * config
     *
     * @return array
     */
    public function getConfig()
    {
        return array(
            'app_id'        => Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_id'),
            'app_secret'    => Mage::getStoreConfig('opauth/' . self::XML_CONFIG_KEY . '/app_secret'),
            'redirect_uri'  => Mage::getUrl('oggetto_opauth/login/facebook'),
            'display'       => 'page',
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
            'used' => true,
            'data' => array(
                'element_id' => 'opauth_facebook_id',
                'name'       => 'opauth_facebook_id',
                'label'      => Mage::helper('oggetto_opauth')->__('Facebook Id'),
            ),
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
