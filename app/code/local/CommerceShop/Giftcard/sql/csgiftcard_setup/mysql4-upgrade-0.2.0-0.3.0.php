<?php
$installer = $this;
$installer->startSetup();

$csgiftCardOrderTable = $installer->getTable('csgiftcard/order');
$csgiftCardRecipientTable = $installer->getTable('csgiftcard/recipient');

$installer->run("
CREATE TABLE IF NOT EXISTS `{$csgiftCardOrderTable}` (
	`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NULL DEFAULT NULL,	
	PRIMARY KEY (`entity_id`),
	UNIQUE INDEX `order_id_gift_card_code` (`order_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;
");

$installer->run("
CREATE TABLE IF NOT EXISTS `{$csgiftCardRecipientTable}` (
	`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` INT(10) UNSIGNED NULL DEFAULT NULL,
	`sender_name` VARCHAR(50) NULL DEFAULT NULL,
	`sender_email` VARCHAR(100) NULL DEFAULT NULL,
	`recipient_name` VARCHAR(50) NULL DEFAULT NULL,
	`recipient_email` VARCHAR(100) NULL DEFAULT NULL,
	`gift_message` VARCHAR(255) NULL DEFAULT NULL,	
	PRIMARY KEY (`entity_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0;
");


$installer->endSetup();