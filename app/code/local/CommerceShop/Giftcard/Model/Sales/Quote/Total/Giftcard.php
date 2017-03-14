<?php
class CommerceShop_Giftcard_Model_Sales_Quote_Total_Giftcard extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('cs_gift_card_total');
    }
    
    public function getLabel()
    {
        return 'Gift Card';
    }
    
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if (($address->getAddressType() == 'billing')) {
            return $this;
        }
        
        $amount = Mage::helper('csgiftcard')->getGiftCardValue();
        $this->_addAmount($amount);
        $this->_addBaseAmount($amount);
        return $this;
    }
    
    
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (($address->getAddressType() == 'billing')) {
            $amount = Mage::helper('csgiftcard')->getGiftCardValue();
            
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amount
            ));
        }
        return $this;
    }
} 