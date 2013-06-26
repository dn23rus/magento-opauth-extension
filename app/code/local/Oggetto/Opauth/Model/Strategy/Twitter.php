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
    const ATTR_CODE = 'opauth_twitter_id';


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
        // TODO: Implement normalizeInfo() method.
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
}
