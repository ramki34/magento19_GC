<?php
class CommerceShop_Giftcard_GiftController extends Mage_Core_Controller_Front_Action
{
    public function applyAction()
    {
        $post        = $this->getRequest()->getPost();
        $newgiftCode = $post['csgift_code'];
        $newgiftBal  = 200;
        
        // need to call API for to get available balance.
        
        $quote                  = Mage::getSingleton('checkout/session')->getQuote();
        $grandTotal             = $quote->getData('grand_total');
        $appliedGiftcardBalance = Mage::helper('csgiftcard')->getGiftCardValue();
        
        if ($grandTotal == 0) {
            $this->_getSession()->addError('Grand Total must be grater then 0');
            $this->_redirect('checkout/cart');
            return;
        }
        
        if ($grandTotal < $newgiftBal) {
            $newgiftBal = $grandTotal;
        }
        
        
        
        $newgiftBal = -$newgiftBal;
        $giftInfo   = '';
        $error      = '';
        if ($isAlreadyAppliedCards = $quote->getData('cs_gift_card')) {
            $applied = unserialize($isAlreadyAppliedCards);
            foreach ($applied as $appl) {
                if ($appl['newgiftCode'] == $newgiftCode) {
                    $error = "Gift card '" . $appl['newgiftCode'] . "' was Already applied.";
                }
                
                $giftInfo[] = array(
                    'newgiftCode' => $appl['newgiftCode'],
                    'balance' => $appl['balance'],
                    'apply' => true
                );
            }
        }
        
        if (empty($error)) {
            $giftInfo[] = array(
                'newgiftCode' => $newgiftCode,
                'balance' => $newgiftBal,
                'apply' => true
            );
            $this->_getSession()->addSuccess($this->__("Gift card '$newgiftCode' was applied."));
        } else {
            $this->_getSession()->addError($error);
        }
        
        $quote->setData('cs_gift_card', serialize($giftInfo))->save();
        $this->_redirect('checkout/cart');
    }
    
    public function removeAction()
    {
        $giftCode            = $this->getRequest()->getPost('csgift_code_remove');
        $quote               = Mage::getSingleton('checkout/session')->getQuote();
        $alreadyAppliedCards = unserialize($quote->getData('cs_gift_card'));
        $id                  = $this->searchForCode($giftCode, $alreadyAppliedCards);
        unset($alreadyAppliedCards[$id]);
        $quote->setData('cs_gift_card', serialize($alreadyAppliedCards))->save();
        $this->_getSession()->addSuccess($this->__("Gift card '$giftCode' was removed successfully."));
        $this->_redirect('checkout/cart');
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getCoreSession(){
    	return Mage::getSingleton('core/session');
    }
    
    public function searchForCode($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['newgiftCode'] === $id) {
                return $key;
            }
        }
        
        return null;
    }
    
    public function carddeliveryAction()
    {       
        $this->loadLayout();
        $this->renderLayout();
    }
   
   //send gift to recipient.
    public function sendcardinfoAction()
    {       
        $post        = $this->getRequest()->getPost();
        $to['name']=$post['recipientname']; 
        $to['email']=$post['recipientemail']; 

        $sender=$post['sender_name'];
        $redeemUrl = Mage::getBaseUrl() . 'csgiftcard/gift/csredeem';
        $templateParams   = array(
                'gift_message' => $post['giftmessage'],
                'card_redeem' => $redeemUrl
            );
        
        $sender=array('email'=>(string) 'sample.dev@gmail.com','name'=> (string)'Ramki');

    try
      {
       Mage::helper('csgiftcard')->sendGiftCardRecipientMail($to, $templateParams,$sender);
       $this->_getCoreSession()->addSuccess($this->__("Recipient Mail Sent."));
      }
    catch(Exception $e){
     $this->_getCoreSession()->addError($e->getMessage());	 
     }
     $this->_redirect('csgiftcard/gift/carddelivery');
    }

   public function csredeemAction()
    {
     $this->loadLayout();
     $this->renderLayout();
    }

    public function csredeemsuccessAction()
    {   
     $this->loadLayout();
     $this->renderLayout();
    }

    //Recipient send card informations self mail for later use
    public function cssendcardAction(){
    	$post        = $this->getRequest()->getPost(); 
        // $to['name']=$post['recipientname']; 
        // $to['email']=$post['recipientemail']; 

        $to['name']='Sample'; 
        $to['email']='ramakrishnan.s@innoppl.com';
        
        $sender=$to;
        
        $templateParams   = array(
                'giftcard_no' => $post['card_no'],
                'giftcard_barcode' => $post['barcode_img']
            );   
        
    try
      {
       Mage::helper('csgiftcard')->sendCardInfoRecipientMail($to, $templateParams,$sender);
       $this->_getCoreSession()->addSuccess($this->__("Card informations successfully sent. Please check your Mail."));
       }

    catch(Exception $e){
     $this->_getCoreSession()->addError($e->getMessage());	 
     }
   	 $result['url']=Mage::getBaseUrl().'csgiftcard/gift/csredeemsuccess';
   	 echo json_encode($result);
     exit;
    }
}