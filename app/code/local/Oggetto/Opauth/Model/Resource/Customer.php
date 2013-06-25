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
 * Customer resource
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Model
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Model_Resource_Customer extends Mage_Customer_Model_Resource_Customer
{
    /**
     * Load customer by provider attribute
     *
     * @param Mage_Customer_Model_Customer $customer     customer instance
     * @param string                       $providerAttr provider attribute name
     * @param string                       $id           provider attribute value
     * @return Oggetto_Opauth_Model_Resource_Customer
     */
    public function loadByOpauthProvider(Mage_Customer_Model_Customer $customer, $providerAttr, $id)
    {
        if ($attr = $this->getAttribute($providerAttr)) {
            $bind = array(
                'attribute_id' => (int) $attr->getId(),
                'opauth_provider_id' => $id,
            );
            $select = $this->_getReadAdapter()->select()
                ->from(array('e' => $this->getEntityTable()), array($this->getEntityIdField()))
                ->joinLeft(
                    array('attr_table' => $attr->getBackendTable()),
                    '`attr_table`.`entity_id`=`e`.`entity_id` AND `attr_table`.`entity_type_id`=`e`.`entity_type_id`',
                     array()
                )
                ->where('attr_table.value = :opauth_provider_id')
                ->where('attr_table.attribute_id = :attribute_id');

            if ($customer->getSharingConfig()->isWebsiteScope()) {
                if (!$customer->hasData('website_id')) {
                    Mage::throwException(Mage::helper('customer')->__(
                        'Customer website ID must be specified when using the website scope'
                    ));
                }
                $bind['website_id'] = (int) $customer->getData('website_id');
                $select->where('`e`.`website_id` = :website_id');
            }

            $customerId = $this->_getReadAdapter()->fetchOne($select, $bind);
            if ($customerId) {
                $this->load($customer, $customerId);
                return $this;
            }
        }
        $customer->setData(array());
        return $this;
    }
}
