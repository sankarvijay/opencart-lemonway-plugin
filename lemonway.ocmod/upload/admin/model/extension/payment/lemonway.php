<?php

/**
 * Created by PhpStorm.
 * User: Nabil CHARAF
 * Date: 17/05/2017
 * Time: 15:48
 */
class ModelExtensionPaymentLemonway extends Model
{
    public function install()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'lemonway_oneclic` (
	    `id_oneclic` int(11) NOT NULL AUTO_INCREMENT,
		`id_customer` int(11) NOT NULL,
		`id_card` int(11) NOT NULL,
		`card_num` varchar(30) NOT NULL,
		`card_exp`  varchar(8) NOT NULL DEFAULT \'\',
		`card_type` varchar(20) NOT NULL DEFAULT \'\',
		`date_add` datetime NOT NULL,
	    `date_upd` datetime NOT NULL,
	    PRIMARY KEY  (`id_oneclic`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');


        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'lemonway_moneyout` (
	    `id_moneyout` int(11) NOT NULL AUTO_INCREMENT,
		`id_lw_wallet` varchar(255) NOT NULL,
		`id_customer` int(11) NOT NULL DEFAULT 0,
		`id_employee` int(11) NOT NULL DEFAULT 0,
		`is_admin` tinyint(1) NOT NULL DEFAULT 0,
		`id_lw_iban` int(11) NOT NULL,
		`prev_bal` decimal(20,6) NOT NULL,
		`new_bal`  decimal(20,6) NOT NULL,
		`iban` varchar(34) NOT NULL,
		`amount_to_pay`  decimal(20,6) NOT NULL,
		`date_add` datetime NOT NULL,
	    `date_upd` datetime NOT NULL,
	    PRIMARY KEY  (`id_moneyout`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');


        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'lemonway_iban` (
	    `id_iban` int(11) NOT NULL AUTO_INCREMENT,
		`id_lw_iban` int(11) NOT NULL,
		`id_customer` int(11) NOT NULL,
		`id_wallet` varchar(255) NOT NULL,
		`holder` varchar(100) NOT NULL,
		`iban` varchar(34) NOT NULL,
		`bic` varchar(50) NOT NULL DEFAULT \'\',
		`dom1` text NOT NULL DEFAULT \'\',
		`dom2` text NOT NULL DEFAULT \'\',
		`comment` text NOT NULL DEFAULT \'\',
		`id_status` int(2) DEFAULT NULL,
		`date_add` datetime NOT NULL,
	    `date_upd` datetime NOT NULL,
	    PRIMARY KEY  (`id_iban`),
		UNIQUE KEY (`id_lw_iban`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

     $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'lemonway_wktoken` (
    `id_cart_wktoken` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` int(11) NOT NULL,
    `wktoken` varchar(255) NOT NULL,
    PRIMARY KEY (`id_cart_wktoken`),
    UNIQUE KEY `wktoken` (`wktoken`),
    UNIQUE KEY `id_cart` (`id_cart`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

     /*
      *@TODO
      *
      *
      * ADD SPLIT PAYMENT
      */
    }

    public function uninstall()
    {

    }
}