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
 * Abstract strategy
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
abstract class Oggetto_Opauth_Model_Strategy_Abstract implements Oggetto_Opauth_Model_Strategy_Interface
{
    /**
     * @var string
     */
    protected $_configKey;

    /**
     * @var bool
     */
    protected $_isEnabled;

    /**
     * Get config key value
     *
     * @return string
     * @throws Exception
     */
    protected function _getConfigKey()
    {
        if (null === $this->_configKey) {
            throw new Exception(sprintf('Require set config key before call %s', __METHOD__));
        }
        return $this->_configKey;
    }

    /**
     * Check if strategy enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (null === $this->_isEnabled) {
            $this->_isEnabled = Mage::getStoreConfigFlag('opauth/' . $this->_getConfigKey() . '/enabled')
                && Mage::getStoreConfig('opauth/' . $this->_getConfigKey() . '/app_id')
                && Mage::getStoreConfig('opauth/' . $this->_getConfigKey() . '/app_secret');
        }
        return $this->_isEnabled;
    }

    /**
     * Redirect url for additional actions
     *
     * @return string
     */
    public function getRedirectRoute()
    {
        return '';
    }
}
