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
 * @var $this Mage_Core_Model_Resource_Setup
 */

$installer = $this;

$setup = new Mage_Customer_Model_Resource_Setup('core_setup');

$installer->startSetup();
$installer->getConnection()->beginTransaction();
try {
    $setup->addAttribute('customer', Oggetto_Opauth_Model_Strategy_Facebook::ATTR_CODE, array(
        'label'             => 'Facebook Id',
        'required'          => 0,
        'visible'           => 1,
        'sort_order'        => 200,
        'input'             => 'text',
        'adminhtml_only'    => 1,
    ));
    $setup->addAttribute('customer', Oggetto_Opauth_Model_Strategy_Google::ATTR_CODE, array(
        'label'             => 'Google Id',
        'required'          => 0,
        'visible'           => 1,
        'sort_order'        => 200,
        'input'             => 'text',
        'adminhtml_only'    => 1,
    ));
    $setup->addAttribute('customer', Oggetto_Opauth_Model_Strategy_Twitter::ATTR_CODE, array(
        'label'             => 'Twitter Id',
        'required'          => 0,
        'visible'           => 1,
        'sort_order'        => 200,
        'input'             => 'text',
        'adminhtml_only'    => 1,
    ));
} catch (Exception $e) {
    $installer->getConnection()->rollBack();
    Mage::logException($e);
}
$installer->getConnection()->commit();
$installer->endSetup();
