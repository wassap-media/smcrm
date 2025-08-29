<?php

namespace App\Models;

class Checklist_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'checklist_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $checklist_items_table = $this->db->prefixTable("checklist_items");
        $tasks_table = $this->db->prefixTable('tasks');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $checklist_items_table.id=$id";
        }

        $task_id = $this->_get_clean_value($options, "task_id");
        if ($task_id) {
            $where .= " AND $checklist_items_table.task_id=$task_id";
        }

        $sql = "SELECT $checklist_items_table.*, IF($checklist_items_table.sort!=0, $checklist_items_table.sort, $checklist_items_table.id) AS new_sort,
        $tasks_table.client_id
        FROM $checklist_items_table
        LEFT JOIN $tasks_table ON $tasks_table.id=$checklist_items_table.task_id
        WHERE $checklist_items_table.deleted=0 $where
        ORDER BY new_sort ASC";
        return $this->db->query($sql);
    }

    function get_all_checklist_of_project($project_id) {
        $project_id = $this->_get_clean_value($project_id);

        $checklist_items_table = $this->db->prefixTable('checklist_items');
        $tasks_table = $this->db->prefixTable('tasks');

        $sql = "SELECT $checklist_items_table.task_id, $checklist_items_table.title
        FROM $checklist_items_table
        WHERE $checklist_items_table.deleted=0 AND $checklist_items_table.task_id IN(SELECT $tasks_table.id FROM $tasks_table WHERE $tasks_table.deleted=0 AND $tasks_table.project_id=$project_id)";
        return $this->db->query($sql);
    }

    function get_next_sort_value($task_id) {
        $task_id = $this->_get_clean_value($task_id);

        $checklist_items_table = $this->db->prefixTable('checklist_items');

        $sql = "SELECT $checklist_items_table.sort
        FROM $checklist_items_table
        WHERE $checklist_items_table.deleted=0 AND $checklist_items_table.task_id = $task_id
        ORDER BY $checklist_items_table.sort DESC LIMIT 1";

        $row = $this->db->query($sql)->getRow();

        if ($row) {
            return $row->sort + 1;
        } else {
            return 1000; //could be any positive value
        }
    }
}
