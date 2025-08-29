<?php

namespace App\Controllers;

class Labels extends Security_Controller {

    function __construct() {
        parent::__construct();
    }

    function index() {
        app_redirect("forbidden");
    }

    private function can_access_labels_of_this_context($context = "", $label_id = 0) {

        $access_info_role_key = $context;
        if ($context == "help" || $context == "knowledge_base") {
            $access_info_role_key = "help_and_knowledge_base";
        }

        $access_info = $this->get_access_info($access_info_role_key);

        if ($context == "project" && $this->can_edit_projects()) {
            return true;
        } else if ($context == "ticket" && $access_info->access_type) {
            return true;
        } else if ($context == "invoice" && $access_info->access_type) {
            return true;
        } else if ($context == "event" || $context == "note" || $context == "to_do") {

            if ($label_id) {
                //can access only own labels if there has any associated user id with this label
                $label_info = $this->Labels_model->get_one($label_id);
                if ($label_info->user_id && !$this->is_own_id($label_info->user_id)) {
                    return false;
                }
            }

            return true;
        } else if ($context == "task" && ($this->can_manage_all_projects() || get_array_value($this->login_user->permissions, "can_edit_tasks") == "1")) {
            return true;
        } else if ($context == "subscription" && $access_info->access_type) {
            return true;
        } else if ($context == "client" && $access_info->access_type) {
            return true;
        } else if ($context == "lead" && $access_info->access_type) {
            return true;
        } else if ($context == "client" && $this->get_access_info("lead")->access_type) {
            return true; //client and lead has same labels. allow access if there is any one. 
        } else if ($context == "lead" && $this->get_access_info("client")->access_type) {
            return true; //client and lead has same labels. allow access if there is any one. 
        } else if (($context == "help" || $context == "knowledge_base") && $access_info->access_type) {
            return true;
        }
    }

    function modal_form() {
        $type = $this->request->getPost("type");
        if (!$this->can_access_labels_of_this_context($type)) {
            app_redirect("forbidden");
        }

        if ($type) {
            $model_info = new \stdClass();
            $model_info->color = "";

            $view_data["type"] = clean_data($type);
            $view_data["model_info"] = $model_info;

            $view_data["existing_labels"] = $this->_make_existing_labels_data($type);

            return $this->template->view("labels/modal_form", $view_data);
        }
    }

    private function _make_existing_labels_data($type) {
        $labels_dom = "";
        $labels_where = array(
            "context" => $type
        );

        if ($type == "event" || $type == "note" || $type == "to_do") {
            $labels_where["user_id"] = $this->login_user->id;
        }

        $labels = $this->Labels_model->get_details($labels_where)->getResult();

        foreach ($labels as $label) {
            $labels_dom .= $this->_get_labels_row_data($label);
        }

        return $labels_dom;
    }

    private function _get_labels_row_data($data) {
        return "<span data-act='label-edit-delete' data-id='" . $data->id . "' data-color='" . $data->color . "' class='badge mr5 clickable' style='background-color: " . $data->color . "'>" . $data->title . "</span>";
    }

    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "type" => "required"
        ));

        $id = $this->request->getPost("id");
        $context = $this->request->getPost("type");

        if (!$this->can_access_labels_of_this_context($context, $id)) {
            app_redirect("forbidden");
        }

        $data = array(
            "context" => $context,
            "title" => $this->request->getPost("title"),
            "color" => $this->request->getPost("color")
        );

        //save user_id for only events and personal notes
        if ($context == "event" || $context == "to_do" || $context == "note") {
            $data["user_id"] = $this->login_user->id;
        }

        $data = clean_data($data);
        $save_id = $this->Labels_model->ci_save($data, $id);

        if ($save_id) {
            $label_info = $this->Labels_model->get_one($save_id);
            echo json_encode(array("success" => true, 'data' => $this->_get_labels_row_data($label_info), 'id' => $id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete() {
        $id = $this->request->getPost("id");
        $type = $this->request->getPost("type");

        validate_numeric_value($id);

        if (!$this->can_access_labels_of_this_context($type, $id)) {
            app_redirect("forbidden");
        }

        if (!$id || !$type) {
            show_404();
        }

        $final_type = ($type == "to_do") ? $type : ($type . "s");
        $existing_labels = $this->Labels_model->is_label_exists($id, $final_type);

        if ($existing_labels) {
            echo json_encode(array("label_exists" => true, 'message' => app_lang("label_existing_error_message")));
        } else {
            if ($this->Labels_model->delete($id)) {
                echo json_encode(array("success" => true, 'id' => $id, 'message' => app_lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            }
        }
    }
}

/* End of file Labels.php */
/* Location: ./app/controllers/Labels.php */