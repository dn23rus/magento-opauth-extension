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

$autoloader = Mage::getBaseDir() . DS .'vendor' . DS .'autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

/**
 * Opauth
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_Opauth2
{
    /**
     * @var array
     */
    protected $_config;

    /**
     * @var Opauth
     */
    protected $_opauthModel;

    /**
     * @var array
     */
    protected $_strategyMap = array();

    /**
     * @var array
     */
    protected $_strategies = array();

    /**
     * @var string
     */
    protected $_currentStrategy;

    /**
     * Constructor.
     * Init default configs.
     */
    public function __construct()
    {
        $this->_config = array(
            'callback_transport' => 'session',
            'path'               => '/opauth/login/',
            'callback_url'       => Mage::getUrl('oggetto_opauth/login/callback'),
            'security_iteration' => Mage::getStoreConfig('opauth/general/security_iteration'),
            'security_timeout'   => Mage::getStoreConfig('opauth/general/security_timeout'),
            'security_salt'      => Mage::getStoreConfig('opauth/general/security_salt'),
        );
        $this->_initStrategyMap();
    }

    /**
     * Read config and init strategy map
     *
     * @return $this
     */
    protected function _initStrategyMap()
    {
        $strategies = Mage::app()->getConfig()->getNode('opauth/strategies', 'global');
        if ($strategies) {
            foreach ($strategies->children() as $strategy => $config) {
                /* @var $config Mage_Core_Model_Config_Element */
                $result[strtolower($strategy)] = $config->asArray();
            }
        }
        return $this;
    }

    public function useStrategy($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->_strategies)) {
            $this->addStrategy($name);
        }
        $this->_currentStrategy = $name;
    }

    public function addStrategy()
    {

    }

    /**
     * Return Opauth
     *
     * @return Opauth
     * @throws Exception
     */
    public function getOpauthModel()
    {
        if (!class_exists('Opauth')) {
            throw new Exception('Require installed \'opauth\opauth\' module with composer');
        }
        if (null === $this->_opauthModel) {
            $this->_opauthModel = new Opauth($this->_config, false);
        }
        return $this->_opauthModel;
    }
}
