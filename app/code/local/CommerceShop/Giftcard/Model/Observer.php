<?php
class CommerceShop_Giftcard_Model_Observer
{    
    public function sendGiftCardPurchaseMail(Varien_Event_Observer $observer)
    {
        $order                = $observer->getEvent()->getOrder();
        $isGiftcardsPurchased = Mage::helper('csgiftcard')->isGiftCardPurchased($order);
        if ($isGiftcardsPurchased) {
            $to['email']      = $order->getBillingAddress()->getEmail();
            $to['name']       = $order->getBillingAddress()->getFirstname();            
            $urlParam=$order->getId();
            $encryptedUrlParam = Mage::helper('core')->encrypt($urlParam);           
            $cardDeliveryPage = Mage::getBaseUrl() . 'csgiftcard/gift/carddelivery/?'.$encryptedUrlParam;
            $templateParams   = array(
                'gift_message' => 'Thank you for purchasing the Giftcard.',
                'card_deliver' => $cardDeliveryPage,
                'customer_name' => $to['name']
            );
            Mage::helper('csgiftcard')->sendGiftCardPurchaseMail($to, $templateParams);  
            Mage::getModel('csgiftcard/order')->setOrderId($order->getId())->save();          
        }
    }    
}