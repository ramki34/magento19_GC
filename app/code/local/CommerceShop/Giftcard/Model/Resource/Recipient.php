<?php
class CommerceShop_Giftcard_Model_Resource_Recipient extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('csgiftcard/recipient', 'entity_id');
    }
}