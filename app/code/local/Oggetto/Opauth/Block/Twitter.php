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
 * Twitter ask email form
 *
 * @category   Oggetto
 * @package    Oggetto_Opauth
 * @subpackage Block
 * @author     Dmitry Buryak <b.dmitry@oggettoweb.com>
 */
class Oggetto_Opauth_Block_Twitter extends Mage_Core_Block_Template
{
    /**
     * Form action url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('oggetto_opauth/twitter/askEmailPost');
    }
}
