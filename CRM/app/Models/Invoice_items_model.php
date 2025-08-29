<?php

namespace App\Models;

use CodeIgniter\Model;

class Invoice_items_model extends Crud_model {

    protected $table = null;
    private $_Invoices_model = null;

    function __construct() {
        $this->table = 'invoice_items';
        parent::__construct($this->table);

        $this->_Invoices_model = model("App\Models\Invoices_model");
    }

    function get_details($options = array()) {
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $invoice_items_table.id=$id";
        }
        $invoice_id = $this->_get_clean_value($options, "invoice_id");
        if ($invoice_id) {
            $where .= " AND $invoice_items_table.invoice_id=$invoice_id";
        }

        $sql = "SELECT $invoice_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id limit 1) AS currency_symbol
        FROM $invoice_items_table
        LEFT JOIN $invoices_table ON $invoices_table.id=$invoice_items_table.invoice_id
        WHERE $invoice_items_table.deleted=0 $where
        ORDER BY $invoice_items_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_item_suggestion($keyword = "", $user_type = "") {
        $items_table = $this->db->prefixTable('items');

        $keyword = $this->_get_clean_value($keyword);
        $where = "";

        if ($keyword) {
            $keyword = $this->db->escapeLikeString($keyword);
            $where .= " AND $items_table.title LIKE '%$keyword%' ESCAPE '!' ";
        }

        if ($user_type && $user_type === "client") {
            $where .= " AND $items_table.show_in_client_portal=1";
        }

        $sql = "SELECT $items_table.id, $items_table.title
        FROM $items_table
        WHERE $items_table.deleted=0 $where
        LIMIT 10 
        ";
        return $this->db->query($sql)->getResult();
    }

    function get_item_info_suggestion($options = array()) {

        $items_table = $this->db->prefixTable('items');

        $where = "";
        $item_name = $this->_get_clean_value($options, "item_name");
        if ($item_name) {
            $item_name = $this->db->escapeLikeString($item_name);
            $where .= " AND $items_table.title LIKE '%$item_name%' ESCAPE '!' ";
        }

        $item_id = $this->_get_clean_value($options, "item_id");
        if ($item_id) {
            $where .= " AND $items_table.id=$item_id ";
        }

        $user_type = $this->_get_clean_value($options, "user_type");
        if ($user_type && $user_type === "client") {
            $where = " AND $items_table.show_in_client_portal=1 ";
        }

        $sql = "SELECT $items_table.*
        FROM $items_table
        WHERE $items_table.deleted=0 $where
        ORDER BY id DESC LIMIT 1
        ";

        $result = $this->db->query($sql);

        if ($result->resultID->num_rows) {
            return $result->getRow();
        }
    }

    function save_item_and_update_invoice($data, $id, $invoice_id) {
        $result = $this->ci_save($data, $id);

        $invoices_model = model("App\Models\Invoices_model");
        $invoices_model->update_invoice_total_meta($invoice_id);

        return $result;
    }

    function delete_item_and_update_invoice($id, $undo = false) {
        $item_info = $this->get_one($id);

        $result = $this->delete($id, $undo);

        $invoices_model = model("App\Models\Invoices_model");
        $invoices_model->update_invoice_total_meta($item_info->invoice_id);

        return $result;
    }
}
