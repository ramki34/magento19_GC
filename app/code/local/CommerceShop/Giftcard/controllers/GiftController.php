<?php
class CommerceShop_Giftcard_GiftController extends Mage_Core_Controller_Front_Action
{
    public function applyAction()
    {
        $post        = $this->getRequest()->getPost();
        $newgiftCode = $post['csgift_code'];
        $newgiftBal  = -200;
        
        // need to call API for to get available balance.
        
        $quote    = Mage::getSingleton('checkout/session')->getQuote();
        $giftInfo = '';
        $error    = '';
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
    
    public function searchForCode($id, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['newgiftCode'] === $id) {
                return $key;
            }
        }
        
        return null;
    }
}