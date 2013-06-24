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
 * Time intervals
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_System_Config_Source_TimeInterval
{
    /**
     * Options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('oggetto_opauth')->__('0.5 minute'),
                'value' => '30 seconds',
            ),
            array(
                'label' => Mage::helper('oggetto_opauth')->__('1 minute'),
                'value' => '1 minute',
            ),
            array(
                'label' => Mage::helper('oggetto_opauth')->__('1.5 minutes'),
                'value' => '1 minute 30 seconds',
            ),
            array(
                'label' => Mage::helper('oggetto_opauth')->__('2 minutes'),
                'value' => '2 minutes',
            ),
            array(
                'label' => Mage::helper('oggetto_opauth')->__('2.5 minutes'),
                'value' => '2 minutes 30 seconds',
            ),
            array(
                'label' => Mage::helper('oggetto_opauth')->__('3 minutes'),
                'value' => '31 minutes',
            ),
        );
    }
}
