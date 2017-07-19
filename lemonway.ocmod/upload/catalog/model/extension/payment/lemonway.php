<?php
class ModelExtensionPaymentLemonway extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/lemonway');

        $method_data = array(
            'code'       => 'lemonway',
            'title'      => $this->language->get('text_card'),
            'terms'      => '',
            'sort_order' =>''
        );

        return $method_data;
    }

    public function getCustomerCard($customer_id)
    {
        $query = 'SELECT * FROM `' . DB_PREFIX . 'lemonway_oneclic` lo WHERE lo.`customer_id` = '
            . (int)$this->db->escape($customer_id) . '';
        $current_card = $this->db->query($query);

        return $current_card->row;
    }

    public function insertOrUpdateCard($customer_id, $data)
    {
        $oldCard = $this->getCustomerCard($customer_id);

        if (!empty($oldCard['card_num'])) {
            $oldCard['oneclic_id'] = (int)$oldCard['oneclic_id'];
            $data = array_merge($oldCard, $data);
            $data['date_upd'] = date('Y-m-d H:i:s');
        } else {
            $data['date_add'] = date('Y-m-d H:i:s');
        }

        // Escape data
        foreach ($data as $key => $value) {
            $data[$key] = $this->db->escape($value);
        }
        $data['customer_id'] = (int)$data['customer_id'];
        $data['card_id'] = (int)$data['card_id'];

        if (empty($data['card_num'])) {
            $query = 'INSERT  INTO  `' . DB_PREFIX . 'lemonway_oneclic`  (`customer_id`,`card_id`,`date_add`) values ( ' . $this->db->escape($data['customer_id']) . ', ' . $this->db->escape($data['card_id']) . ',\'' . $this->db->escape($data['date_add']) . '\')';
        } else {
            $query = 'REPLACE INTO  `' . DB_PREFIX . 'lemonway_oneclic`  (`id`,`customer_id`,`card_id`,`card_num`, `card_exp` ,`card_type`,`date_add`,`date_upd`) values (' . $data['oneclic_id'] . ',' . $data['customer_id'] . ',' . $data['card_id'] . ',\'' . $data['card_num'] . '\',\'' . $data['card_exp'] . '\',\'' . $data['card_type'] . '\',\'' . $data['date_add'] . '\',\'' . $data['date_upd'] . '\')';
        }

        $this->db->query($query);
    }

    private function generateUniqueToken($order_id)
    {
        return $order_id . "-" . time() . "-" . uniqid();
    }

    private function checkIfOrderHasWkToken($order_id)
    {   
        return (bool)$this->db->query(
            'SELECT `wktoken` FROM `' . DB_PREFIX . 'lemonway_wktoken` lw WHERE lw.`order_id` = ' . (int)$this->db->escape($order_id)
        )->num_rows;
    }

    public function saveWkToken($order_id)
    {
        $wkToken = $this->generateUniqueToken($order_id);

        //Default  update query
        $query = 'UPDATE `' . DB_PREFIX . 'lemonway_wktoken` SET `wktoken` = \'' . $this->db->escape($wkToken) .
            "' WHERE `order_id` = " . (int)$this->db->escape($order_id);

        // Whether the order has a wkToken
        if (!$this->checkIfOrderHasWkToken($order_id)) {
            $query = 'INSERT INTO `' . DB_PREFIX . 'lemonway_wktoken` (`order_id`, `wktoken`) VALUES (\''
                . (int)$this->db->escape($order_id) . '\',\'' . $this->db->escape($wkToken) . '\') ';
        }


        $this->db->query($query);

        return $wkToken;
    }

    public function getOrderIdFromToken($wkToken)
    {
        if ($order_id = $this->db->query(
            'SELECT `order_id` FROM `' . DB_PREFIX . 'lemonway_wktoken` lw WHERE lw.`wktoken` = \''
            . $this->db->escape($wkToken) . "'"
        )) {
            return $order_id;
        } else {
            $this->session->data['error'] = $this->language->get('error_order_not_found');
            return false;
        }
    }
}
