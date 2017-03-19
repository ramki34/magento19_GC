<?php
class CommerceShop_Giftcard_GiftController extends Mage_Core_Controller_Front_Action
{
    public function applyAction()
    {
        $post        = $this->getRequest()->getPost();
        $newgiftCode = $post['csgift_code'];      
        
        //call API for to get available balance.
        $giftBal=Mage::helper('csgiftcard')->getGiftCardBalance($newgiftCode);
        if(isset($giftBal['error'])){
            $this->_getSession()->addError($giftBal['error']);
            $this->_redirect('checkout/cart');
            return;
        }
        $newgiftBal  = $giftBal;        
        
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
    	$req=$this->getRequest()->getParams();
    	if(!$req){
    		$this->_getCoreSession()->addError("You didn't Purchased a Giftcard");
    		$this->_redirect('checkout/cart');
    		return;
    	}
    	$param=array_keys($req)[0];    	
    	$order_id = Mage::helper('core')->decrypt($param);    	
    	$isAvailable=Mage::getModel('sales/order')->load($order_id)->getEntityId();
    	if(!$isAvailable){
    		$this->_getCoreSession()->addError("Invalid Order");
    		$this->_redirect('checkout/cart');
    		return;
    	}
    	Mage::register('csgiftorder_no',$order_id);
        $this->loadLayout();
        $this->renderLayout();
    }
   
   //send gift to recipient.
    public function sendcardinfoAction()
    {       
        $post        = $this->getRequest()->getPost();
        if(!$post){
         $this->_getCoreSession()->addError('Please enter your Recipient informations');
         $this->loadLayout();
         $this->renderLayout();        
         return;
     }
               
        $sender=array('email'=>(string) $post['sender_email'],'name'=> (string) $post['sender_name']);
        $to['name']=$post['recipient_name']; 
        $to['email']=$post['recipient_email'];   

         $urlParam='000000'.$post['order_id'];
         $encryptedUrlParam = Mage::helper('core')->encrypt($urlParam);

        $redeemUrl = Mage::getBaseUrl() . 'csgiftcard/gift/csredeem?'.$encryptedUrlParam;
        $templateParams   = array(
                'gift_message' => trim($post['gift_message']),
                'card_redeem' => $redeemUrl
            );
    try
      {
       Mage::helper('csgiftcard')->sendGiftCardRecipientMail($to, $templateParams,$sender);
       $this->_getCoreSession()->addSuccess($this->__("You have successfully sent the Giftcard informations to Recipient account."));
       Mage::getModel('csgiftcard/recipient')->addData($post)->save();
      }
    catch(Exception $e){
     $this->_getCoreSession()->addError($e->getMessage());	 
     }
    $this->loadLayout();
    $this->renderLayout();
    }

   public function csredeemAction()
    {

     $req=$this->getRequest()->getParams();
    	if(!$req){
    		$this->_getCoreSession()->addError("Invalid Page");
    		$this->_redirect('checkout/cart');
    		return;
    	}
    	$param=array_keys($req)[0];
    	$order_id = Mage::helper('core')->decrypt($param);
    	$recipientModel=Mage::getModel('csgiftcard/recipient')->load($order_id,'order_id');
    	$isAvailable=$recipientModel->getEntityId();
    	if(!$isAvailable){
    		$this->_getCoreSession()->addError("Invalid Order");
    		$this->_redirect('checkout/cart');
    		return;
    	}
    	$gift['gift_message']=$recipientModel->getGiftMessage();
    	$gift['order_id']=$order_id;
    	Mage::register('giftinfo',$gift);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function csredeemsuccessAction()
    {
            $order_id=$this->getRequest()->getPost('order_id');
        	if(!$order_id){
    		$this->_getCoreSession()->addError("Invalid Page");
    		$this->_redirect('checkout/cart');
    		return;
    	}
     Mage::register('order_id',$order_id);
     $this->loadLayout();
     $this->renderLayout();
    }

    //Recipient send card informations self mail for later use
    public function cssendcardAction(){
    	$post        = $this->getRequest()->getPost(); 
        $to['name']=$post['recipient_name']; 
        $to['email']=$post['recipient_email']; 
        $sender=$to;
        
        $templateParams   = array(
                'giftcard_no' => $post['card_no'],
                'giftcard_barcode' => $post['barcode_img']
            );   
        
    try
      {
       Mage::helper('csgiftcard')->sendCardInfoRecipientMail($to, $templateParams,$sender);       
       $result['success']=1;
       $result['msg']='Card informations successfully sent. Please check your Mail';        
       }

    catch(Exception $e){
    	$result['success']=0;
    	$result['msg']=$e->getMessage();    
     }
   	 $result['url']=Mage::getBaseUrl().'csgiftcard/gift/csredeemsuccess';
   	 echo json_encode($result);
     exit;
    }
}