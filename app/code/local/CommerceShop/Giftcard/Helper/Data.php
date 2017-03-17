<?php
class CommerceShop_Giftcard_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    CONST CS_GIFT_PRODUCT_TYPE = 'csgiftcard';
    CONST CS_GIFT_PURCHASE_EMAIL_TEMPLATE = 'csgiftcard_purchase_email_template';
    CONST CS_GIFT_RECIPIENT_EMAIL_TEMPLATE = 'csgiftcard_recipient_email_template';    
    CONST CS_GIFT_RECIPIENT_EMAIL_COPY_TEMPLATE = 'csgiftcard_recipient_email_copy_template';    

    CONST XML_PATH_UPDATE_EMAIL_IDENTITY = 'sales_email/order_comment/identity';
    
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
    
    public function isGiftCardPurchased($order)
    {
        $allVisibleItems = $order->getAllVisibleItems();
        $giftItems=0;
        foreach ($allVisibleItems as $item) {
            if ($item->getProductType() == self::CS_GIFT_PRODUCT_TYPE) {
                $giftItems[] = array(
                    'product_id' => $item->getData('product_id'),
                    'sku' => $item->getData('sku')
                );
            }
        }
        return $giftItems ? $giftItems : array();
    }
    
    
    public function sendGiftCardPurchaseMail($to, $templateParams, $sender = null)
    {
        $template = self::CS_GIFT_PURCHASE_EMAIL_TEMPLATE;
        $sender   = $sender ? $sender : Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId());
        
        try {
            $this->sendMail($to, $template, $templateParams, $sender);
        }
        catch (Exception $e) {
            Mage::helper('csgiftcard')->cslog($e->getMessage());
        }
        
    }


     public function sendGiftCardRecipientMail($to, $templateParams, $sender = null)
    {
        $template = self::CS_GIFT_RECIPIENT_EMAIL_TEMPLATE;
        $sender   = $sender ? $sender : Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId());       
        try {
            $this->sendMail($to, $template, $templateParams, $sender);
        }
        catch (Exception $e) {
            Mage::helper('csgiftcard')->cslog($e->getMessage());
            throw new Mage_Exception($e->getMessage());            
        }
        
    } 

    //Recipient send card informations self mail for later use    
     public function sendCardInfoRecipientMail($to, $templateParams, $sender = null)
    {
        $template = self::CS_GIFT_RECIPIENT_EMAIL_COPY_TEMPLATE;
        $sender   = $sender ? $sender : Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $this->getStoreId());       
        try {
            $this->sendMail($to, $template, $templateParams, $sender);
        }
        catch (Exception $e) {
            Mage::helper('csgiftcard')->cslog($e->getMessage());
            throw new Mage_Exception($e->getMessage());            
        }
        
    }
    public function sendMail($to, $template, $templateParams, $sender)
    {
        $mailer    = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($to['email'], $to['name']);
        $mailer->addEmailInfo($emailInfo);
        $mailer->setSender($sender);       
        $mailer->setStoreId($this->getStoreId());
        $mailer->setTemplateId((string) $template);
        $mailer->setTemplateParams($templateParams);
        $mailer->send();
    }
    
    public function getStoreId(){
    	return Mage::app()->getStore()->getId();
    }


    public function getBarCode128($barcodeString)
    {
    	$file = Zend_Barcode::draw('code128', 'image', array('text' => $barcodeString), array()); 
        $store_image = imagepng($file,Mage::getBaseDir('media')."/Cs_Barcode/$barcodeString.png");    
        if($store_image)  
        return Mage::getBaseUrl('media')."Cs_Barcode/$barcodeString.png";
    }


    public function getSenderInfo($orderId){
    $order=Mage::getModel('sales/order')->load($orderId);
    $info['sender_email']=$order->getData('customer_email');
    $info['sender_name']=$order->getData('customer_firstname').' '.$order->getData('customer_firstname');   
    return $info;
    }

    public function saveSenderInfo(){

    }
    
    public function cslog($data)
    {
        Mage::log($data, null, 'csgiftcard.log');
    }
    
}