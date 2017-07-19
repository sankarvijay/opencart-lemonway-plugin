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
	    `id` int(11) NOT NULL AUTO_INCREMENT,
		`customer_id` int(11) NOT NULL,
		`card_id` int(11) NOT NULL,
		`card_num` varchar(30) NOT NULL,
		`card_exp`  varchar(8) NOT NULL DEFAULT \'\',
		`card_type` varchar(20) NOT NULL DEFAULT \'\',
		`date_add` datetime NOT NULL,
	    `date_upd` datetime NOT NULL,
	    PRIMARY KEY  (`id`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'lemonway_wktoken` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `wktoken` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `wktoken` (`wktoken`),
        UNIQUE KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
    }

    public function uninstall()
    {

    }
}