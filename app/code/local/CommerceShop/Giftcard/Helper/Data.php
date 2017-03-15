<?php
class CommerceShop_Giftcard_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGiftCardValue()
    {
        $giftInfo = Mage::getSingleton('checkout/session')->getQuote()->getData('cs_gift_card');
        $amount   = 0;
        if ($giftInfo) {
            $info = unserialize($giftInfo);
            foreach ($info as $gift) {
                $amount += $gift['balance'];
            }
        }
        return $amount;
    }
    
    
    public function getAppliedGiftCards()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($isAlreadyAppliedCards = $quote->getData('cs_gift_card'))
            return unserialize($isAlreadyAppliedCards);
    }
    
    public function cslog($data)
    {
    	Mage::log($data, null, 'csgiftcard.log');
    }
    
} 