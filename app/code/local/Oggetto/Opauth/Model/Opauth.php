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
class Oggetto_Opauth_Model_Opauth
{
    /**
     * @var Opauth
     */
    protected $_opauthModel;

    /**
     * @var array
     */
    protected $_config;

    /**
     * @var array
     */
    protected $_providers = array();

    /**
     * @var array
     */
    protected $_strategyMap = array(
        'facebook'  => 'Facebook',
        'google'    => 'Google',
        'twitter'   => 'Twitter',
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_initDefaultConfigs();

        // add default strategies
//        $this->addStrategy('Facebook');
//        $this->addStrategy('Google');
//        $this->addStrategy('Twitter');
    }

    /**
     * Opauth model
     *
     * @param bool $run auto run after initialization
     * @return Opauth
     */
    public function getOpauthModel($run = false)
    {
        if (null === $this->_opauthModel) {
            $this->_opauthModel = new Opauth($this->_config, $run);
        }
        return $this->_opauthModel;
    }

    /**
     * Run authentication
     *
     * @param string|null $providerCode provide code
     * @return Oggetto_Opauth_Model_Opauth
     */
    public function run($providerCode = null)
    {
        if (null !== $providerCode) {
            $this->addStrategy($providerCode);
        }
        $opauth = $this->getOpauthModel();
        $opauth->env['request_uri'] = "/opauth/login/{$providerCode}/";
        $opauth->run();
        return $this;
    }

    /**
     * Add strategy
     *
     * @param string                                  $name  strategy name
     * @param Oggetto_Opauth_Model_Strategy_Interface $class strategy class
     * @return Oggetto_Opauth_Model_Opauth
     * @throws Oggetto_Opauth_Exception
     * @throws Exception
     */
    public function addStrategy($name, $class = null)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->_providers)) {
            return $this;
        }
        if (null === $class) {
            if (array_key_exists($name, $this->_strategyMap)) {
                $class = 'Oggetto_Opauth_Model_Strategy_' . $this->_strategyMap[$name];
            } else {
                /** @var $exception Oggetto_Opauth_Exception */
                $exception = Mage::exception(
                    'Oggetto_Opauth',
                    sprintf('Opauth \'%\' strategy is not supported', $name)
                );
                throw $exception;
            }
        }
        /** @var $instance Oggetto_Opauth_Model_Strategy_Interface */
        if (is_string($class)) {
            $instance = new $class;
        } elseif (is_object($class)) {
            $instance = $class;
        } else {
            throw new Exception(sprintf('%s require \'class\' as string or object', __METHOD__));
        }
        if (!$instance instanceof Oggetto_Opauth_Model_Strategy_Interface) {
            throw new Exception(
                sprintf('\'%s\' strategy must implement Oggetto_Opauth_Model_Strategy_Interface', $name)
            );
        }

        if ($instance->isEnabled()) {
            $this->_providers[$name] = $instance;
            $this->_config['Strategy'][$this->_strategyMap[$name]] = $instance->getConfig();
        }

        return $this;
    }

    /**
     * Get providers
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->_providers;
    }

    /**
     * Get provider
     *
     * @param string $code strategy code
     * @return null|Oggetto_Opauth_Model_Strategy_Interface
     */
    public function getProvider($code)
    {
        if ($this->hasProvider($code)) {
            return $this->_providers[strtolower($code)];
        }
        return null;
    }

    /**
     * Check if given provider exists
     *
     * @param string $code strategy code
     * @return bool
     */
    public function hasProvider($code)
    {
        $code = strtolower($code);
        return array_key_exists($code, $this->_providers);
    }

    /**
     * Init default configs
     *
     * @return Oggetto_Opauth_Model_Opauth
     */
    protected function _initDefaultConfigs()
    {
        $this->_config = array(
            'callback_transport' => 'session',
            'path'               => '/opauth/login/',
            'callback_url'       => Mage::getUrl('oggetto_opauth/login/callback'),
            'security_iteration' => Mage::getStoreConfig('opauth/general/security_iteration'),
            'security_timeout'   => Mage::getStoreConfig('opauth/general/security_timeout'),
            'security_salt'      => Mage::getStoreConfig('opauth/general/security_salt'),
        );
        return $this;
    }

    /**
     * Callback response data
     *
     * @return array
     */
    public function getResponseData()
    {
        $response  = array();
        $transport = $this->_config['callback_transport'];
        switch ($transport) {
            case 'session':
                if (!session_id()) {
                    session_start();
                }
                if (isset($_SESSION['opauth'])) {
                    $response = $_SESSION['opauth'];
                    unset($_SESSION['opauth']);
                }
                break;
            case 'post':
                if (isset($_POST['opauth'])) {
                    $response = unserialize(base64_decode($_POST['opauth']));
                }
                break;
            case 'get':
                if (isset($_GET['opauth'])) {
                    $response = unserialize(base64_decode($_GET['opauth']));
                }
                break;
            default:
                break;
        }
        if (isset($response['auth']['provider'])) {
            $this->addStrategy($response['auth']['provider']);
        }
        return $response;
    }

    /**
     * Call auth provider internal callback
     *
     * @param string $code strategy code
     * @return void
     */
    public function callInternalCallback($code)
    {
        $instance = $this->getStrategyInstance($code);
        if (0 === strcasecmp($code, 'twitter')) {
            $internalCallback = str_replace('{complete_url_to_strategy}', '', $instance->defaults['oauth_callback']);
        } else {
            $internalCallback = str_replace('{complete_url_to_strategy}', '', $instance->defaults['redirect_uri']);
        }
        $instance->{$internalCallback}();
    }

    /**
     * Opauth strategy instance
     *
     * @param string $code strategy code
     * @return OpauthStrategy
     * @throws Exception
     */
    public function getStrategyInstance($code)
    {
        $code = strtolower($code);
        if (!isset($this->_strategyMap[$code])) {
            throw new Exception(sprintf('Unable to retrieve \'%s\' strategy instance', $code));
        }
        $this->addStrategy($code);
        $class  = $this->_strategyMap[$code] . 'Strategy';
        $opauth = $this->getOpauthModel();
        $conf   = $opauth->env['Strategy'][$this->_strategyMap[$code]];

        return new $class($conf, $opauth->env);
    }

    /**
     * Validation
     *
     * @param array       $responseData response data
     * @param string|null &$reason      validation fail reason
     * @return bool
     */
    public function validate($responseData, &$reason = null)
    {
        if (empty($responseData['auth']) ||
            empty($responseData['timestamp']) ||
            empty($responseData['signature'])
        ) {
            $reason = 'Invalid Opauth response data';
            return false;
        }
        return $this->getOpauthModel()->validate(
            sha1(print_r($responseData['auth'], true)),
            $responseData['timestamp'],
            $responseData['signature'],
            $reason
        );
    }
}
