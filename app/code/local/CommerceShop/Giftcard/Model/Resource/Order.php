<?php
class CommerceShop_Giftcard_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('csgiftcard/order', 'entity_id');
    }
}