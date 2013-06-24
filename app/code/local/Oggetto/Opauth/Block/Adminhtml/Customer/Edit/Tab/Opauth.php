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
 * Adminhtml customer opauth tab
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Block
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Block_Adminhtml_Customer_Edit_Tab_Opauth extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $customer = Mage::registry('current_customer');

        $fildset = $form->addFieldset('provider_ids', array(
            'legend' => $this->_getHelper()->__('Opauth Providers'),
        ));

        foreach ($strategies = Mage::getSingleton('oggetto_opauth/opauth')->getStrategies() as $strategy) {
            /* @var $strategy Oggetto_Opauth_Model_Strategy_Interface */
            $cnf = $strategy->getFormConfig();
            if (isset($cnf['used']) && $cnf['used']) {
                $data = array_merge(array(
                    'element_id' => null,
                    'name'       => null,
                    'label'      => null,
                ), isset($cnf['data'])? $cnf['data'] : array());
                $fildset->addField($data['element_id'], 'text', array(
                    'name'       => $data['name'],
                    'label'      => $data['label'],
                    'readonly'   => true,
                ));
            }
        }

        $form->setValues($customer->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('oggetto_opauth')->__('Opauth');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('oggetto_opauth')->__('Opauth');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return (bool) Mage::registry('current_customer')->getId();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Helper
     *
     * @return Oggetto_Opauth_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('oggetto_opauth');
    }
}
