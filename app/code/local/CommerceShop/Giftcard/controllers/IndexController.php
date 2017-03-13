<?php
class CommerceShop_Giftcard_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		$post = $this->getRequest()->getPost();
		//$quote = Mage::getSingleton('checkout/cart')->getQuote();

		$quote = Mage::getSingleton('checkout/session')->getQuote();
		
		$discountAmount = 200;
		

        $shipp=$quote->getShippingAddress();
        $shipp->setBaseGrandTotal(300);
		$shipp->setGrandTotal(300);
		$shipp->setSubtotal(300);
		$shipp->setBaseSubtotal(300);
		$shipp->setSubtotalWithDiscount(300);
		$shipp->setBaseSubtotalWithDiscount(300);
		$shipp->save();

       

        $quote->setBaseGrandTotal(300);
		$quote->setGrandTotal(300);
		$quote->setSubtotal(300);
		$quote->setBaseSubtotal(300);
		$quote->setSubtotalWithDiscount(300);
		$quote->setBaseSubtotalWithDiscount(300);		
		$quote->save();



		echo '<pre>', print_r($quote->getShippingAddress()->getGrandTotal()) , '</pre>';	

        echo '<pre>', print_r($quote->getData()) , '</pre>';	
        die;		
		$this->_redirect('checkout/cart');
		}
	}