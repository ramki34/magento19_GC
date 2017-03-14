<?php
$installer = Mage::getResourceModel('sales/setup', 'default_setup');
$installer->startSetup();

$installer->addAttribute('order','cs_gift_card',array(
    'label' => 'Gift Cards',
    'type'  => 'text',
));

$installer->addAttribute('quote','cs_gift_card',array(
    'label' => 'Gift Cards',
    'type'  => 'text',
));

$installer->addAttribute('invoice','cs_gift_card',array(
    'label' => 'Gift Cards',
    'type'  => 'text',
));

$installer->addAttribute('creditmemo','cs_gift_card',array(
    'label' => 'Gift Cards',
    'type'  => 'text',
));


$installer->endSetup();
