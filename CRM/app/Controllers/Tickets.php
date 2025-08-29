<?php

namespace App\Controllers;

use App\Libraries\Dropdown_list;
use App\Libraries\Clients;

class Tickets extends Security_Controller {

    protected $Ticket_templates_model;
    protected $Pin_comments_model;

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("ticket");

        $this->Ticket_templates_model = model('App\Models\Ticket_templates_model');
        $this->Pin_comments_model = model('App\Models\Pin_comments_model');
    }

    private function validate_ticket_access($ticket_id = 0) {
        if (!$this->can_access_tickets($ticket_id)) {
            app_redirect("forbidden");
        }
    }

    private function _validate_tickets_report_access() {
        if (!$this->login_user->is_admin && $this->access_type != "all") {
            app_redirect("forbidden");
        }
    }

    //only admin can delete tickets
    protected function can_delete_tickets() {
        return $this->login_user->is_admin;
    }

    // load ticket list view
    function index($status = "", $ticket_type_id = 0, $client_id = 0, $ticket_id = 0) {
        $this->check_module_availability("module_ticket");
        validate_numeric_value($client_id);
        validate_numeric_value($ticket_id);

        $view_data['show_project_reference'] = get_setting('project_reference_in_tickets');

        $view_data['status'] = clean_data($status);

        $view_data['ticket_id'] = $ticket_id;

        $custom_field_headers = $this->Custom_fields_model->get_custom_field_headers_for_table("tickets", $this->login_user->is_admin, $this->login_user->user_type);
        $custom_field_filters = $this->Custom_fields_model->get_custom_field_filters("tickets", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {

            //prepare ticket label filter list
            $view_data['ticket_labels_dropdown'] = json_encode($this->make_labels_dropdown("ticket", "", true));

            $view_data['show_options_column'] = true; //team members can view the options column

            $view_data['assigned_to_dropdown'] = json_encode($this->_get_assiged_to_dropdown());

            $view_data['ticket_types_dropdown'] = json_encode($this->_get_ticket_types_dropdown_list_for_filter($ticket_type_id));

            $dropdown_list = new Dropdown_list($this);
            $view_data['clients_dropdown'] = $dropdown_list->get_clients_id_and_text_dropdown(array("blank_option_text" => "- " . app_lang("client") . " -"));
            $view_data['selected_client_id'] = $client_id;

            $view_data["custom_field_headers"] = $custom_field_headers;
            $view_data["custom_field_filters"] = $custom_field_filters;

            return $this->template->rander("tickets/tickets_list", $view_data);
        } else {
            if (!$this->can_client_access("ticket")) {
                app_redirect("forbidden");
            }

            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            $view_data["custom_field_headers_of_tickets"] = $custom_field_headers;
            $view_data["custom_field_filters_of_tickets"] = $custom_field_filters;

            return $this->template->rander("clients/tickets/index", $view_data);
        }
    }

    function compact_view($ticket_id = 0, $client_id = 0) {
        validate_numeric_value($ticket_id);
        validate_numeric_value($client_id);

        if ($this->login_user->user_type === "client") {
            app_redirect("tickets/view/$ticket_id");
        }

        return $this->index("", "", $client_id, $ticket_id);
    }

    private function _get_assiged_to_dropdown() {
        $options = array("status" => "active", "user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $users_list = $this->Users_model->get_id_and_text_dropdown(
            array("first_name", "last_name"),
            $options
        );

        return array_merge(
            array(
                array("id" => "", "text" => "- " . app_lang("assigned_to") . " -"),
                array("id" => "unassigned", "text" => app_lang("unassigned"))
            ),
            $users_list
        );
    }

    //load new tickt modal 
    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "project_id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $this->validate_ticket_access($id);

        //client should not be able to edit ticket
        if ($this->login_user->user_type === "client" && $id) {
            app_redirect("forbidden");
        }

        $client_id = $this->request->getPost('client_id');
        validate_numeric_value($client_id);

        $where = array();
        if ($this->login_user->user_type === "staff" && $this->access_type !== "all" && $this->access_type !== "assigned_only") {
            $where = array("where_in" => array("id" => $this->allowed_ticket_types));
        }

        $ticket_info = $this->Tickets_model->get_one($this->request->getPost("id"));

        if ($ticket_info->client_id) {
            $client_id = $ticket_info->client_id;
        }

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $client_id, "project_type" => "client_project"));
        if ($this->login_user->user_type == "client") {
            $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $this->login_user->client_id, "project_type" => "client_project"));
            $ticket_info->client_id = $this->login_user->client_id;
        }
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }

        $model_info = $this->Tickets_model->get_one($id);
        $project_id = $this->request->getPost('project_id');

        //here has a project id. now set the client from the project
        if ($project_id || $client_id) {
            if ($project_id) {
                $client_id = $this->Projects_model->get_one($project_id)->client_id;
                $model_info->client_id = $client_id;
            }

            $view_data['requested_by_dropdown'] = array("" => "-") + $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", array("deleted" => 0, "client_id" => $client_id));
        } else {
            $requested_by_suggestion = array(array("id" => "", "text" => "-"));
            $view_data['requested_by_dropdown'] = $requested_by_suggestion;
        }

        $view_data['projects_suggestion'] = $suggestion;

        $view_data['ticket_types_dropdown'] = $this->Ticket_types_model->get_dropdown_list(array("title"), "id", $where);

        $view_data['model_info'] = $model_info;
        $view_data['client_id'] = $client_id;
        $dropdown_list = new Dropdown_list($this);
        $view_data['clients_dropdown'] = $dropdown_list->get_clients_id_and_text_dropdown(array("blank_option_text" => "-"));

        $view_data['show_project_reference'] = get_setting('project_reference_in_tickets');

        $view_data['project_id'] = $this->request->getPost('project_id');

        $view_data['requested_by_id'] = $ticket_info->requested_by;

        if ($this->login_user->user_type == "client") {
            $view_data['project_id'] = $this->request->getPost('project_id');
        } else {
            $view_data['projects_dropdown'] = $this->Projects_model->get_dropdown_list(array("title"), "id", array("project_type" => "client_project"));
        }

        //prepare assign to list
        $options = array("status" => "active", "user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $assigned_to_dropdown = array("" => "-") + $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", $options);
        $view_data['assigned_to_dropdown'] = $assigned_to_dropdown;

        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown("ticket", $view_data['model_info']->labels);

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("tickets", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('tickets/modal_form', $view_data);
    }

    //get project suggestion against client
    function get_project_suggestion($client_id = 0) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members();

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $client_id, "project_type" => "client_project"));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        echo json_encode($suggestion);
    }

    // add a new ticket
    function save() {
        $validation_array = array(
            "id" => "numeric",
            "client_id" => "numeric",
            "assigned_to" => "numeric",
            "requested_by_id" => "numeric",
            "ticket_type_id" => "required|numeric"
        );

        $id = $this->request->getPost('id');
        if (!$id) {
            $validation_array["client_id"] = "required|numeric";
        }

        $this->validate_submitted_data($validation_array);
        $this->validate_ticket_access($id);

        $client_id = $this->request->getPost('client_id');

        $this->access_only_allowed_members_or_client_contact($client_id);

        $ticket_type_id = $this->request->getPost('ticket_type_id');
        $assigned_to = $this->request->getPost('assigned_to');

        $requested_by = $this->request->getPost('requested_by_id');

        //if this logged in user is a client then overwrite the client id
        if ($this->login_user->user_type === "client") {
            $client_id = $this->login_user->client_id;
            $assigned_to = 0;
        }

        //if this logged in user is a team member and there has a requested_by client contact, change the created_by field also
        $created_by = $this->login_user->id;
        if ($this->login_user->user_type === "staff" && $requested_by) {
            $created_by = $requested_by;
        }

        $now = get_current_utc_time();

        $labels = $this->request->getPost('labels');
        validate_list_of_numbers($labels);

        $ticket_data = array(
            "title" => $this->request->getPost('title'),
            "client_id" => $client_id,
            "project_id" => $this->request->getPost('project_id') ? $this->request->getPost('project_id') : 0,
            "ticket_type_id" => $ticket_type_id,
            "created_by" => $created_by,
            "created_at" => $now,
            "last_activity_at" => $now,
            "labels" => $labels,
            "assigned_to" => $assigned_to ? $assigned_to : 0,
            "requested_by" => $requested_by ? $requested_by : 0
        );

        if (!$id) {
            $ticket_data["creator_name"] = "";
            $ticket_data["creator_email"] = "";
        }

        $ticket_data = clean_data($ticket_data);

        if ($id) {
            //client can't update ticket
            if ($this->login_user->user_type === "client") {
                app_redirect("forbidden");
            }

            //remove not updateable fields
            unset($ticket_data['client_id']);
            unset($ticket_data['created_by']);
            unset($ticket_data['created_at']);
            unset($ticket_data['last_activity_at']);
        }


        $ticket_id = $this->Tickets_model->ci_save($ticket_data, $id);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "ticket");

        if ($ticket_id) {

            save_custom_fields("tickets", $ticket_id, $this->login_user->is_admin, $this->login_user->user_type);

            //ticket added. now add a comment in this ticket
            if (!$id) {
                $description = decode_ajax_post_data($this->request->getPost('description'));

                $comment_data = array(
                    "description" => $description,
                    "ticket_id" => $ticket_id,
                    "created_by" => $this->login_user->id,
                    "created_at" => $now
                );

                $comment_data = clean_data($comment_data);

                $comment_data["files"] = $files_data; //don't clean serilized data

                $ticket_comment_id = $this->Ticket_comments_model->ci_save($comment_data);

                if ($ticket_comment_id) {
                    log_notification("ticket_created", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id));
                }

                if ($this->login_user->user_type !== "staff") {
                    //don't add auto reply if it's created by team members
                    add_auto_reply_to_ticket($ticket_id);
                }
            } else if ($assigned_to) {
                log_notification("ticket_assigned", array("ticket_id" => $ticket_id, "to_user_id" => $assigned_to));
            }

            echo json_encode(array("success" => true, "id" => $ticket_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }


    // list of tickets, prepared for datatable 
    function list_data($is_widget = 0, $is_mobile = 0) {
        $this->access_only_allowed_members();

        validate_numeric_value($is_widget);
        validate_numeric_value($is_mobile);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tickets", $this->login_user->is_admin, $this->login_user->user_type);

        $status = $this->request->getPost("status");
        if ($status && is_array($status)) {
            $status =  implode(",", $status);
        } else if (!$status) {
            $status =  "";
        }

        $ticket_label = $this->request->getPost("ticket_label");
        $assigned_to = $this->request->getPost("assigned_to");

        if ($assigned_to && $assigned_to != "unassigned") { //support only numeric value and "unassigned"
            $assigned_to = get_only_numeric_value($assigned_to);
        }

        $ticket_type_id = get_only_numeric_value($this->request->getPost('ticket_type_id'));
        $client_id = get_only_numeric_value($this->request->getPost('client_id'));
        $id = get_only_numeric_value($this->request->getPost('id'));

        $options = array(
            "id" => $id,
            "statuses" => $status,
            "ticket_types" => $this->allowed_ticket_types,
            "ticket_label" => $ticket_label,
            "assigned_to" => $assigned_to,
            "custom_fields" => $custom_fields,
            "created_at" => $this->request->getPost('created_at'),
            "ticket_type_id" => $ticket_type_id,
            "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id(),
            "client_id" => $client_id,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tickets", $this->login_user->is_admin, $this->login_user->user_type)
        );

        if ($is_widget) {
            $options = array(
                "status" => "open",
                "ticket_types" => $this->allowed_ticket_types,
                "custom_fields" => $custom_fields
            );
        }

        // if ($is_mobile) {
        //     $options["total_message_count"] = true;
        // }

        $all_options = append_server_side_filtering_commmon_params($options);

        $result = $this->Tickets_model->get_details($all_options);

        //by this, we can handel the server side or client side from the app table prams.
        if (get_array_value($all_options, "server_side")) {
            $list_data = get_array_value($result, "data");
        } else {
            $list_data = $result->getResult();
            $result = array();
        }

        $result_data = array();
        foreach ($list_data as $data) {
            $result_data[] = $this->_make_row($data, $custom_fields, $is_mobile);
        }

        $result["data"] = $result_data;

        echo json_encode($result);
    }

    // list of tickets of a specific client, prepared for datatable 
    function ticket_list_data_of_client($client_id, $is_widget = 0, $is_mobile = 0, $view_type = "") {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tickets", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "access_type" => $this->access_type,
            "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id(),
            "custom_fields" => $custom_fields,
            "status" => $this->request->getPost('status'),
            "custom_field_filter" => $this->prepare_custom_field_filter_values("tickets", $this->login_user->is_admin, $this->login_user->user_type)
        );

        if ($is_widget) {
            $options = array(
                "client_id" => $client_id,
                "access_type" => $this->access_type,
                "status" => "open",
                "custom_fields" => $custom_fields
            );
        }

        // if ($is_mobile) {
        //     $options["total_message_count"] = true;
        // }

        $list_data = $this->Tickets_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields, $is_mobile, $view_type);
        }
        echo json_encode(array("data" => $result));
    }

    // return a row of ticket list table 
    private function _row_data($id, $is_mobile = 0) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tickets", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "id" => $id,
            "access_type" => $this->access_type,
            "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id(),
            "custom_fields" => $custom_fields
        );

        $data = $this->Tickets_model->get_details($options)->getRow();

        if ($data) {
            return $this->_make_row($data, $custom_fields, $is_mobile);
        } else {
            return json_encode(array());
        }
    }

    //prepare a row of ticket list table
    private function _make_row($data, $custom_fields, $is_mobile = 0, $view_type = "") {
        $ticket_status_class = "bg-danger";
        if ($data->status === "new" || $data->status === "client_replied") {
            $ticket_status_class = "bg-warning";
        } else if ($data->status === "closed") {
            $ticket_status_class = "bg-success";
        }

        if ($data->status === "client_replied" && $this->login_user->user_type === "client") {
            $data->status = "open"; //don't show client_replied status to client
            $ticket_status_class = "bg-danger";
        }

        $ticket_status = "<span class='badge $ticket_status_class'>" . app_lang($data->status) . "</span> ";

        $company_name = $data->company_name ? $data->company_name : ($data->creator_name . " [" . app_lang("unknown_client") . "]");

        if ($is_mobile) {
            $avatar_url = get_avatar();
            $requested_by_name = $company_name;
            if ($data->requested_by) {
                $avatar_url = get_avatar($data->requested_by_avatar);
                $requested_by_name = $data->requested_by_name;
            }

            $avatar = "<span class='avatar avatar-xs'><img src='$avatar_url' alt='...'></span>";

            // $total_message_count_badge = "";
            // if (isset($data->total_message_count) && $data->total_message_count) {
            //     $total_message_count_badge = "<span class='badge badge-default'>" . $data->total_message_count . "</span>";
            // }

            $title_content = "<div class='text-default'><div class='clearfix'><span class='truncate-ellipsis w60p float-start'><span class='fw-bold'>" . $requested_by_name . "</span></span>
            <small class='text-off float-end'>" . format_to_relative_time($data->last_activity_at, true, true) . "</small></div>
            <div class='clearfix'><div class='float-start text-truncate max-w250'>" . $data->title . "</div><div class='float-end spinning-btn'></div></div>
            <div class='text-truncate'>" . "" . "</div>
            </div>";

            $link = js_anchor($title_content, array(
                "class" => "box-label",
                "data-action-url" => get_uri("tickets/view/" . $data->id),
                "data-action" => "load_compact_view",
                "data-compact_view_id" => $data->id
            ));

            $title = "<div class='box-wrapper mini-list-item'>
                <div class='box-avatar hover'>" . $avatar . "</div>" .
                $link .
                "</div>";

            if ($view_type === "widget") {
                $title = "
                    <div class='box-wrapper mini-list-item'>
                        <div class='box-avatar hover'>" . $avatar . "</div>" .
                    anchor(get_uri("tickets/view/" . $data->id), $title_content, array("class" => "box-label")) .
                    "</div>";
            }
        } else {
            $title = anchor(get_uri("tickets/view/" . $data->id), $data->title ? $data->title : "-");
        }

        //show labels field to team members only
        $labels = "";
        $ticket_labels = make_labels_view_data($data->labels_list, true);
        if ($ticket_labels) {
            $labels = "<span>" . $ticket_labels . "</span>";
        }

        //show assign to field to team members only
        $assigned_to = "-";
        if ($data->assigned_to && $this->login_user->user_type == "staff") {
            $image_url = get_avatar($data->assigned_to_avatar);
            $assigned_to_user = "<span class='avatar avatar-xs mr10'><img src='$image_url' alt='...'></span> $data->assigned_to_user";
            $assigned_to = get_team_member_profile_link($data->assigned_to, $assigned_to_user);
        }

        $status_color = "#FFC007";
        if ($data->status == "open") {
            $status_color = "#F4325B";
        } else if ($data->status == "closed") {
            $status_color = "#485ABD";
        }

        $row_data = array(
            $status_color,
            $data->id,
            anchor(get_uri("tickets/view/" . $data->id), get_ticket_id($data->id), array("class" => "js-selection-id", "data-id" => $data->id, "title" => "")),
            $title,
            $data->company_name ? anchor(get_uri("clients/view/" . $data->client_id), $data->company_name) : ($data->creator_name . " [" . app_lang("unknown_client") . "]"),
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $data->ticket_type ? $data->ticket_type : "-",
            $labels,
            $assigned_to,
            $data->last_activity_at,
            $data->client_last_activity_at,
            format_to_relative_time($data->last_activity_at, true, false, true),
            $ticket_status
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        if ($this->login_user->user_type == "staff") {
            $edit = '<li role="presentation">' . modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('edit'), "data-post-view" => "details", "data-post-id" => $data->id, "class" => "dropdown-item")) . '</li>';

            //show option to close/open the tickets
            $status = "";
            if ($data->status === "closed") {
                $status = '<li role="presentation">' . js_anchor("<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array('title' => app_lang('mark_as_open'), "class" => "dropdown-item", "data-action-url" => get_uri("tickets/save_ticket_status/$data->id/open"), "data-action" => "update")) . '</li>';
            } else {
                $status = '<li role="presentation">' . js_anchor("<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array('title' => app_lang('mark_as_closed'), "class" => "dropdown-item", "data-action-url" => get_uri("tickets/save_ticket_status/$data->id/closed"), "data-action" => "update")) . '</li>';
            }

            $assigned_to = "";
            if ($data->assigned_to === "0") {
                $assigned_to = '<li role="presentation">' . js_anchor("<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array('title' => app_lang('assign_myself_in_this_ticket'), "data-action-url" => get_uri("tickets/assign_to_me/$data->id"), "data-action" => "update", "class" => "dropdown-item")) . '</li>';
            }


            //show the delete menu if user has access to delete the tickets
            $delete_ticket = "";
            if ($this->can_delete_tickets()) {
                $delete_ticket = '<li role="presentation">' . js_anchor("<i data-feather='x' class='icon-16'></i>" . app_lang('delete'), array('title' => app_lang('delete'), "class" => "delete dropdown-item", "data-id" => $data->id, "data-action-url" => get_uri("tickets/delete"), "data-action" => "delete-confirmation")) . '</li>';
            }

            $actions = '
                        <span class="dropdown inline-block">
                            <button class="action-option dropdown-toggle mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true" data-bs-display="static">
                                <i data-feather="more-horizontal" class="icon-16"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" role="menu">' . $edit . $status . $assigned_to . $delete_ticket . '</ul>
                        </span>';

            $modal_view = "";
            if (!$is_mobile) {
                $modal_view =  anchor(get_uri("tickets/compact_view/" . $data->id), "<i data-feather='sidebar' class='icon-16'></i>", array("title" => "", "class" => "action-option",));
            }

            $row_data[] = $modal_view . $actions;
        }

        return $row_data;
    }

    // load ticket details view 
    function view($ticket_id = 0, $client_id = 0) {
        validate_numeric_value($ticket_id);
        validate_numeric_value($client_id);

        if (!$ticket_id) {
            $ticket_id = $this->request->getPost('id');
        }

        $view_type = $this->request->getPost('view_type');

        if ($ticket_id) {
            $this->validate_ticket_access($ticket_id);

            $sort_as_decending = get_setting("show_recent_ticket_comments_at_the_top");

            $options = array("id" => $ticket_id);
            $options["ticket_types"] = $this->allowed_ticket_types;

            $view_data["can_create_tasks"] = false;

            $ticket_info = $this->Tickets_model->get_details($options)->getRow();

            if ($ticket_info) {
                $this->access_only_allowed_members_or_client_contact($ticket_info->client_id);

                $view_data['ticket_info'] = $ticket_info;
                $view_data["view_type"] = $view_type;

                $view_data["show_project_reference"] = get_setting('project_reference_in_tickets');

                $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("tickets", $ticket_info->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

                $can_edit_ticket = false;
                if ($this->login_user->user_type != "client" && $this->can_access_tickets($ticket_id)) {
                    $can_edit_ticket = true;
                }
                $view_data['can_edit_ticket'] = $can_edit_ticket;

                $view_data['ticket_labels'] = make_labels_view_data($ticket_info->labels_list, false, true, "rounded-pill");

                //get labels suggestion
                $view_data['label_suggestions'] = $this->make_labels_dropdown("ticket", "");

                //get assign to dropdown
                $assign_to_options = array("status" => "active", "user_type" => "staff");
                if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
                    $assign_to_options["id"] = $this->login_user->id;
                }
                $view_data['assign_to_dropdown'] = $this->Users_model->get_id_and_text_dropdown(array("first_name", "last_name"), $assign_to_options, "-", "id");

                $contacts_options = array("user_type" => "client", "client_id" => $ticket_info->client_id);
                $contacts = $this->Users_model->get_details($contacts_options)->getResult();
                $cc_contacts_dropdown = array();
                foreach ($contacts as $contact) {
                    $cc_contacts_dropdown[] = array("id" => $contact->id, "text" => $contact->first_name . " " . $contact->last_name);
                }

                $view_data['cc_contacts_dropdown'] = $cc_contacts_dropdown;
                $cc_contacts_and_emails = $ticket_info->cc_contacts_and_emails;

                $cc_contacts_list = $ticket_info->cc_contacts_list;

                if ($cc_contacts_and_emails) {
                    $cc_items = array_map('trim', explode(',', $cc_contacts_and_emails));

                    // Filter only valid email addresses that are not numeric
                    $emails = array_filter($cc_items, function ($item) {
                        return !is_numeric($item) && filter_var($item, FILTER_VALIDATE_EMAIL);
                    });

                    if (!empty($emails)) {
                        $emails_string = implode(', ', $emails);
                        $cc_contacts_list = $cc_contacts_list ? $cc_contacts_list . ', ' . $emails_string : $emails_string;
                    }
                }

                $view_data['cc_contacts_list'] = $cc_contacts_list;

                $view_data["can_create_client"] = false;
                if ($this->login_user->is_admin || (get_array_value($this->login_user->permissions, "client") == "all")) {
                    $view_data["can_create_client"] = true;
                }

                $status_dropdown = array(
                    array("id" => "open", "text"  => app_lang("open")),
                    array("id" => "closed", "text"  => app_lang("closed"))
                );

                $view_data['status_dropdown'] = $status_dropdown;

                //Don't load all data if view type is ticket meta
                if ($view_type != "ticket_meta") {

                    //For project related tickets, check task cration permission for the project
                    if ($ticket_info->project_id) {
                        $this->init_project_permission_checker($ticket_info->project_id);
                        $view_data["can_create_tasks"] = true; //since the user has permission to manage the tickets.
                    }

                    $comments_options = array(
                        "ticket_id" => $ticket_id,
                        "sort_as_decending" => $sort_as_decending,
                        "login_user_id" => $this->login_user->id
                    );

                    if ($this->login_user->user_type === "client") {
                        $comments_options["is_note"] = 0;
                    }

                    $view_data["sort_as_decending"] = $sort_as_decending;

                    $view_data['comments'] = $this->Ticket_comments_model->get_details($comments_options)->getResult();
                    $view_data['pinned_comments'] = $this->Pin_comments_model->get_details(array("ticket_id" => $ticket_id, "pinned_by" => $this->login_user->id))->getResult();

                    $view_data["custom_field_headers_of_task"] = $this->Custom_fields_model->get_custom_field_headers_for_table("tasks", $this->login_user->is_admin, $this->login_user->user_type);
                }

                if ($view_type == "modal_view") {
                    return $this->template->view("tickets/view", $view_data);
                } else if ($view_type == "compact_view") {
                    $view_data['selected_client_id'] = $client_id;

                    echo json_encode(array(
                        "success" => true,
                        "content" => $this->template->view("tickets/view",  $view_data)
                    ));
                } else if ($view_type == "inline_view") {
                    return $this->template->view("tickets/view", $view_data);
                } else if ($view_type == "ticket_meta") {

                    echo json_encode(array(
                        "success" => true,
                        "top_bar" => $this->template->view("tickets/top_bar",  $view_data),
                        "ticket_info" => $this->template->view("tickets/ticket_info",  $view_data),
                        "client_info" => $this->template->view("tickets/ticket_client_info",  $view_data),
                    ));
                } else {
                    return $this->template->rander("tickets/view", $view_data);
                }
            } else {
                show_404();
            }
        }
    }

    //delete ticket and sub comments

    function delete() {

        if (!$this->can_delete_tickets()) {
            app_redirect("forbidden");
        }

        $id = $this->request->getPost('id');
        $this->validate_ticket_access($id);

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        if ($this->Tickets_model->delete_ticket_and_sub_items($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    function save_comment() {

        $this->validate_submitted_data(array(
            "ticket_id" => "required|numeric"
        ));

        $ticket_id = $this->request->getPost('ticket_id');
        $description = decode_ajax_post_data($this->request->getPost('description'));
        $now = get_current_utc_time();
        $this->validate_ticket_access($ticket_id);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "ticket");
        $is_note = $this->request->getPost('is_note');

        $comment_data = array(
            "description" => $description,
            "ticket_id" => $ticket_id,
            "created_by" => $this->login_user->id,
            "created_at" => $now,
            "files" => $files_data,
            "is_note" => $is_note ? $is_note : 0
        );


        $comment_data = clean_data($comment_data);
        $comment_data["files"] = $files_data; //don't clean serialized data

        if (!$description && $files_data == "a:0:{}") {
            echo json_encode(array("success" => true, 'validation_error' => true, 'message' => app_lang("empty_comment_cannot_be_saved")));
            exit();
        }

        $comment_id = $this->Ticket_comments_model->ci_save($comment_data);
        if ($comment_id) {

            //update ticket status and last activity if it's not a note
            if (!$is_note) {
                if ($this->login_user->user_type === "client") {
                    $ticket_data = array(
                        "status" => "client_replied",
                        "last_activity_at" => $now,
                        "client_last_activity_at" => $now
                    );
                } else {
                    $ticket_data = array(
                        "status" => "open",
                        "last_activity_at" => $now
                    );
                }

                $ticket_data = clean_data($ticket_data);

                $this->Tickets_model->ci_save($ticket_data, $ticket_id);
            }

            $comments_options = array("id" => $comment_id, "login_user_id" => $this->login_user->id);
            $view_data['comment'] = $this->Ticket_comments_model->get_details($comments_options)->getRow();
            $comment_view = $this->template->view("tickets/comment_row", $view_data);
            echo json_encode(array("success" => true, "data" => $comment_view, 'message' => app_lang('comment_submited')));

            if (!$is_note) {
                log_notification("ticket_commented", array("ticket_id" => $ticket_id, "ticket_comment_id" => $comment_id));
            }
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function save_ticket_status($ticket_id = 0, $status = "closed") {
        validate_numeric_value($ticket_id);
        if ($ticket_id) {
            $this->validate_ticket_access($ticket_id);

            $data = array(
                "status" => $status
            );

            $save_id = $this->Tickets_model->ci_save($data, $ticket_id);
            if ($save_id) {
                if ($status == "open") {
                    log_notification("ticket_reopened", array("ticket_id" => $ticket_id));
                } else if ($status == "closed") {
                    log_notification("ticket_closed", array("ticket_id" => $ticket_id));

                    //save closing time
                    $closed_data = array("closed_at" => get_current_utc_time());
                    $this->Tickets_model->ci_save($closed_data, $ticket_id);
                }

                echo json_encode(array("success" => true, "id" => $ticket_id, "message" => ($status == "closed") ? app_lang('ticket_closed') : app_lang('ticket_reopened')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        }
    }

    /* download files by zip */

    function download_comment_files($id) {
        validate_numeric_value($id);
        $files = $this->Ticket_comments_model->get_one($id)->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }

    function assign_to_me($ticket_id = 0) {
        if ($ticket_id) {
            validate_numeric_value($ticket_id);

            $this->validate_ticket_access($ticket_id);

            $data = array(
                "assigned_to" => $this->login_user->id
            );

            $save_id = $this->Tickets_model->ci_save($data, $ticket_id);
            if ($save_id) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($ticket_id), "id" => $ticket_id, "message" => app_lang("record_saved")));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        }
    }

    //load the ticket templates view of ticket template list
    function ticket_templates() {
        $this->access_only_team_members();
        return $this->template->rander("tickets/templates/index");
    }

    private function can_view_ticket_template($id = 0) {
        if ($id) {
            $template_info = $this->Ticket_templates_model->get_one($id);
            if ($template_info->private && $template_info->created_by !== $this->login_user->id) {
                app_redirect("forbidden");
            }
        }
    }

    private function can_edit_ticket_template($id = 0) {
        if ($id) {
            $template_info = $this->Ticket_templates_model->get_one($id);
            //admin could modify all public templates
            //team member could modify only own templates
            if ($this->login_user->is_admin && (!$template_info->private || $template_info->created_by !== $this->login_user->id)) {
                return true;
            } else if ($template_info->created_by == $this->login_user->id) {
                return true;
            } else {
                app_redirect("forbidden");
            }
        }
    }

    //add or edit form of ticket template form
    function ticket_template_modal_form() {
        $this->access_only_team_members();
        $where = array();
        if ($this->login_user->user_type === "staff" && $this->access_type !== "all" && $this->access_type !== "assigned_only") {
            $where = array("where_in" => array("id" => $this->allowed_ticket_types));
        }

        $view_data['ticket_types_dropdown'] = array("" => "-") + $this->Ticket_types_model->get_dropdown_list(array("title"), "id", $where);

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_edit_ticket_template($id);

        $view_data['model_info'] = $this->Ticket_templates_model->get_one($id);

        return $this->template->view('tickets/templates/modal_form', $view_data);
    }

    // add a new ticket template
    function save_ticket_template() {
        $this->access_only_team_members();
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required",
            "description" => "required"
        ));

        $id = $this->request->getPost('id');
        $this->can_edit_ticket_template($id);

        $private = $this->request->getPost('private');

        if (is_null($private)) {
            $private = "";
        }

        $now = get_current_utc_time();

        $ticket_template_data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "ticket_type_id" => $this->request->getPost('ticket_type_id') ? $this->request->getPost('ticket_type_id') : 0,
            "private" => $private
        );

        if (!$id) {
            $ticket_template_data["created_by"] = $this->login_user->id;
            $ticket_template_data["created_at"] = $now;
        }

        $save_id = $this->Ticket_templates_model->ci_save($ticket_template_data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, 'id' => $save_id, "data" => $this->_row_data_for_ticket_templates($save_id), 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete_ticket_template() {
        $id = $this->request->getPost('id');
        $this->can_edit_ticket_template($id);

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        if ($this->Ticket_templates_model->delete($id)) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    function ticket_template_list_data($view_type = "", $ticket_type_id = 0) {
        validate_numeric_value($ticket_type_id);
        $options = array("created_by" => $this->login_user->id, "ticket_type_id" => $ticket_type_id);
        if ($this->login_user->user_type === "staff" && $this->access_type !== "all" && $this->access_type !== "assigned_only") {
            $options["allowed_ticket_types"] = $this->allowed_ticket_types;
        }

        $list_data = $this->Ticket_templates_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row_for_ticket_templates($data, $view_type);
        }

        echo json_encode(array("data" => $result));
    }

    // return a row of ticket template table 
    private function _row_data_for_ticket_templates($id) {
        $options = array(
            "id" => $id
        );

        $data = $this->Ticket_templates_model->get_details($options)->getRow();
        return $this->_make_row_for_ticket_templates($data);
    }

    private function _make_row_for_ticket_templates($data, $view_type = "") {

        if ($view_type == "modal") {
            $title = $data->title;
            $ticket_type = "";
        } else {
            $title = modal_anchor(get_uri("tickets/ticket_template_view/" . $data->id), $data->title, array("class" => "edit", "title" => app_lang('ticket_template'), "data-post-id" => $data->id));
            $ticket_type = $data->ticket_type ? $data->ticket_type : "-";
        }

        $private = "";
        if ($data->private) {
            $private = app_lang("yes");
        } else {
            $private = app_lang("no");
        }

        //only creator and admin can edit/delete templates
        $actions = modal_anchor(get_uri("tickets/ticket_template_view/" . $data->id), "<i data-feather='cloud-lightning' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('template_details'), "data-modal-title" => app_lang('template'), "data-post-id" => $data->id));
        if ($data->created_by == $this->login_user->id || $this->login_user->is_admin) {
            $actions = modal_anchor(get_uri("tickets/ticket_template_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_template'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("tickets/delete_ticket_template"), "data-action" => "delete-confirmation"));
        }

        if ($view_type == "modal") {
            return array(
                "<div class='media-body template-row'>
                        <div class='truncate-ellipsis'>
                            <strong class='truncate-ellipsis'><span>$title</span></strong>
                        </div>
                        <div class='truncate-ellipsis js-description'><span>$data->description</span></div>
                    </div></div>"
            );
        } else {
            return array(
                "<div class='truncate-ellipsis'><span>$title</span></div>",
                "<div class='truncate-ellipsis js-description'><span>$data->description</span></div>",
                $ticket_type,
                $private,
                $actions
            );
        }
    }

    function ticket_template_view($id) {
        validate_numeric_value($id);
        $this->can_view_ticket_template($id);
        $view_data['model_info'] = $this->Ticket_templates_model->get_one($id);

        return $this->template->view('tickets/templates/view', $view_data);
    }

    //show a modal to choose a template for comment a ticket
    function insert_template_modal_form() {
        $this->access_only_team_members();
        $view_data['ticket_type_id'] = $this->request->getPost('ticket_type_id');

        return $this->template->view("tickets/templates/insert_template_modal_form", $view_data);
    }

    //add client when there has unknown client
    function add_client_modal_form($ticket_id = 0) {
        if ($ticket_id) {
            validate_numeric_value($ticket_id);
            $this->access_only_allowed_members();

            $dropdown_list = new Dropdown_list($this);
            $view_data['clients_dropdown'] = $dropdown_list->get_clients_id_and_text_dropdown(array("blank_option_text"));

            $view_data['ticket_id'] = $ticket_id;

            return $this->template->view("tickets/add_client_modal_form", $view_data);
        }
    }

    /* load tickets settings modal */

    function settings_modal_form() {
        return $this->template->view('tickets/settings/modal_form');
    }

    /* save tickets settings */

    function save_settings() {
        $settings = array("signature");

        foreach ($settings as $setting) {
            $value = $this->request->getPost($setting);
            if (is_null($value)) {
                $value = "";
            }

            $value = clean_data($value);

            $this->Settings_model->save_setting("user_" . $this->login_user->id . "_" . $setting, $value, "user");
        }

        echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
    }

    /* prepare client contact dropdown based on this suggestion */

    function get_client_contact_suggestion($client_id = 0) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members();

        $clients = $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", array("deleted" => 0, "client_id" => $client_id));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($clients as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        echo json_encode($suggestion);
    }

    private function _get_ticket_types_dropdown_list_for_filter($ticket_type_id = 0) {

        $where = array();
        if ($this->login_user->user_type === "staff" && $this->access_type !== "all" && $this->access_type !== "assigned_only") {
            $where = array("where_in" => array("id" => $this->allowed_ticket_types));
        }

        $ticket_type = $this->Ticket_types_model->get_dropdown_list(array("title"), "id", $where);
        $ticket_type_dropdown = array(array("id" => "", "text" => "- " . app_lang("ticket_type") . " -"));

        foreach ($ticket_type as $id => $name) {
            $selected_status = false;
            if (isset($ticket_type_id) && $ticket_type_id) {
                if ($id == $ticket_type_id) {
                    $selected_status = true;
                } else {
                    $selected_status = false;
                }
            }

            $ticket_type_dropdown[] = array("id" => $id, "text" => $name, "isSelected" => $selected_status);
        }
        return $ticket_type_dropdown;
    }

    /* list of ticket of a specific project, prepared for datatable  */

    function ticket_list_data_of_project($project_id) {
        validate_numeric_value($project_id);

        $this->validate_ticket_access();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("tickets", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "project_id" => $project_id,
            "status" => "open",
            "custom_fields" => $custom_fields
        );

        $list_data = $this->Tickets_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* batch update modal form */

    function batch_update_modal_form() {
        $this->access_only_allowed_members();
        $ticket_ids = $this->request->getPost("ids");
        $view_data["ticket_ids"] = clean_data($ticket_ids);

        $where = array();
        if ($this->login_user->user_type === "staff" && $this->access_type !== "all" && $this->access_type !== "assigned_only") {
            $where = array("where_in" => array("id" => $this->allowed_ticket_types));
        }
        $view_data['ticket_types_dropdown'] = array("" => "-") + $this->Ticket_types_model->get_dropdown_list(array("title"), "id", $where);

        //prepare assign to list
        $options = array("user_type" => "staff");
        if (get_array_value($this->login_user->permissions, "hide_team_members_list_from_dropdowns") == "1") {
            $options["id"] = $this->login_user->id;
        }

        $assigned_to_dropdown = array("" => "-") + $this->Users_model->get_dropdown_list(array("first_name", "last_name"), "id", $options);
        $view_data['assigned_to_dropdown'] = $assigned_to_dropdown;

        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown("ticket", "");

        return $this->template->view("tickets/batch_update/modal_form", $view_data);
    }

    /* save batch update */

    function save_batch_update() {
        $this->access_only_allowed_members();

        $batch_fields = $this->request->getPost("batch_fields");
        if ($batch_fields) {
            $allowed_fields = array("ticket_type_id", "assigned_to", "labels", "status");

            $fields_array = explode('-', $batch_fields);

            $data = array();
            foreach ($fields_array as $field) {
                if (in_array($field, $allowed_fields)) {

                    $value = $this->request->getPost($field);
                    $data[$field] = $value;

                    if ($field == "labels") {
                        validate_list_of_numbers($value);
                    }
                }
            }

            $data = clean_data($data);

            $ticket_ids = $this->request->getPost("ticket_ids");
            if ($ticket_ids) {
                $tickets_ids_array = explode('-', $ticket_ids);

                foreach ($tickets_ids_array as $id) {
                    validate_numeric_value($id);
                    $this->validate_ticket_access($id);
                    $this->Tickets_model->ci_save($data, $id);
                }

                echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('no_field_has_selected')));
            return false;
        }
    }

    function delete_comment($id = 0) {
        if (!$id) {
            exit();
        }

        validate_numeric_value($id);

        $comment_info = $this->Ticket_comments_model->get_one($id);

        //client can't delete the comment
        if ($this->login_user->user_type === "client") {
            app_redirect("forbidden");
        }

        //delete the comment and files
        if ($this->Ticket_comments_model->delete($id) && $comment_info->files) {

            //delete the files
            $file_path = get_setting("timeline_file_path");
            $files = unserialize($comment_info->files);

            foreach ($files as $file) {
                delete_app_files($file_path, array($file));
            }
        }
    }

    //load merge tickt modal 
    function merge_ticket_modal_form() {
        $this->validate_submitted_data(array(
            "ticket_id" => "numeric"
        ));

        $ticket_id = $this->request->getPost('ticket_id');
        $this->validate_ticket_access($ticket_id);

        $ticket_info = $this->Tickets_model->get_one($ticket_id);

        $options = $options = array(
            "status" => "open",
            "ticket_types" => $this->allowed_ticket_types,
            "exclude_ticket_id" => $ticket_id
        );

        if (!$ticket_info->client_id) {
            $options["creator_email"] = $ticket_info->creator_email;
        } else {
            $options["client_id"] = $ticket_info->client_id;
        }

        $tickets = $this->Tickets_model->get_details($options)->getResult();

        $ticket_dropdown = array();
        foreach ($tickets as $ticket) {
            $ticket_dropdown[] = array("id" => $ticket->id, "text" => $ticket->title);
        }

        $view_data['tickets_dropdown'] = $ticket_dropdown;
        $view_data['model_info'] = $ticket_info;

        return $this->template->view('tickets/merge_ticket_modal_form', $view_data);
    }

    function save_merge_ticket() {
        $ticket_id = $this->request->getPost('ticket_id');
        $this->validate_ticket_access($ticket_id);

        //client should not be able to merge ticket
        if ($this->login_user->user_type === "client") {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "ticket_id" => "required|numeric",
            "merge_with_ticket_id" => "required|numeric",
        ));

        $merge_with_ticket_id = $this->request->getPost('merge_with_ticket_id');
        $now = get_current_utc_time();
        $ticket_data = array(
            "merged_with_ticket_id" => $ticket_id,
            "last_activity_at" => $now
        );

        $save_id = $this->Tickets_model->ci_save($ticket_data, $merge_with_ticket_id);

        if ($save_id) {
            $comments = $this->Ticket_comments_model->get_all_where(array("ticket_id" => $merge_with_ticket_id))->getResult();
            foreach ($comments as $comment) {
                $comment_data["ticket_id"] = $ticket_id;

                $this->Ticket_comments_model->ci_save($comment_data, $comment->id);
            }

            $merged_ticket_data["status"] = "closed";
            $this->Tickets_model->ci_save($merged_ticket_data, $merge_with_ticket_id);

            echo json_encode(array("success" => true, 'id' => $ticket_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function tickets_chart_report() {
        $this->_validate_tickets_report_access();

        $view_data['ticket_labels_dropdown'] = json_encode($this->make_labels_dropdown("ticket", "", true));
        $view_data['assigned_to_dropdown'] = json_encode($this->_get_assiged_to_dropdown());
        $view_data['ticket_types_dropdown'] = json_encode($this->_get_ticket_types_dropdown_list_for_filter());

        return $this->template->rander("tickets/reports/chart_report_container", $view_data);
    }

    function tickets_chart_report_data() {
        $this->_validate_tickets_report_access();

        $start_date = $this->request->getPost("start_date");
        $options = array(
            "start_date" => $start_date,
            "end_date" => $this->request->getPost("end_date"),
            "ticket_type_id" => $this->request->getPost("ticket_type_id"),
            "assigned_to" => $this->request->getPost("assigned_to"),
            "ticket_label" => $this->request->getPost("ticket_label")
        );

        $view_data["month"] = strtolower(date("F", strtotime($start_date)));
        $days_of_month = date("t", strtotime($start_date));

        $open_data_array = array();
        $closed_data_array = array();
        $labels = array();
        $open_tickets_array = array();
        $closed_tickets_array = array();

        $options["group_by"] = "created_date";
        $options["status"] = "open";
        $open_tickets_list = $this->Tickets_model->get_ticket_statistics($options)->getResult();

        for ($i = 1; $i <= $days_of_month; $i++) {
            $open_tickets_array[$i] = 0;
            $closed_tickets_array[$i] = 0;
        }

        foreach ($open_tickets_list as $value) {
            $open_tickets_array[$value->day * 1] = $value->total ? $value->total : 0;
        }

        foreach ($open_tickets_array as $value) {
            $open_data_array[] = $value;
        }


        $options["group_by"] = "created_date";
        $options["status"] = "closed";
        $closed_tickets_list = $this->Tickets_model->get_ticket_statistics($options)->getResult();

        foreach ($closed_tickets_list as $value) {
            $closed_tickets_array[$value->day * 1] = $value->total ? $value->total : 0;
        }

        foreach ($closed_tickets_array as $value) {
            $closed_data_array[] = $value;
        }


        for ($i = 1; $i <= $days_of_month; $i++) {
            $labels[] = $i;
        }

        $view_data["labels"] = json_encode($labels);
        $view_data["open_data"] = json_encode($open_data_array);
        $view_data["closed_data"] = json_encode($closed_data_array);

        return $this->template->view("tickets/reports/chart_report_view", $view_data);
    }

    // pin/unpin comments
    function pin_comment($comment_id = 0, $ticket_id = 0) {
        if ($comment_id) {
            validate_numeric_value($comment_id);

            $data = array(
                "ticket_comment_id" => $comment_id,
                "pinned_by" => $this->login_user->id
            );

            $existing = $this->Pin_comments_model->get_one_where(array_merge($data, array("deleted" => 0)));

            $save_id = "";
            if ($existing->id) {
                //pinned already, unpin now
                $save_id = $this->Pin_comments_model->delete($existing->id);
            } else {
                //not pinned, pin now
                $data["created_at"] = get_current_utc_time();
                $save_id = $this->Pin_comments_model->ci_save($data);
            }

            if ($save_id) {
                $pinned_comments = $this->Pin_comments_model->get_details(array("id" => $save_id, "ticket_id" => $ticket_id, "pinned_by" => $this->login_user->id))->getResult();

                $save_data = $this->template->view("lib/pin_comments/comments_list", array("pinned_comments" => $pinned_comments));

                echo json_encode(array("success" => true, "data" => $save_data, "status" => "pinned"));
            } else {
                echo json_encode(array("success" => false));
            }
        }
    }

    function update_ticket_info($id = 0, $data_field = "") {
        if (!$id) {
            return false;
        }

        validate_numeric_value($id);
        $this->validate_ticket_access($id);

        //client should not be able to edit ticket
        if ($this->login_user->user_type === "client" && $id) {
            app_redirect("forbidden");
        }

        $value = $this->request->getPost('value');

        if ($data_field == "labels") {
            validate_list_of_numbers($value);
            $data = array(
                $data_field => $value
            );
        } else if ($data_field == "cc_contacts_and_emails") {
            $cc_contacts_and_emails = explode(',', $value);
            $valid_cc_values = array();

            foreach ($cc_contacts_and_emails as $cc_value) {
                $cc_value = trim($cc_value);
                if (empty($cc_value)) {
                    continue;
                }

                if (is_numeric($cc_value)) {
                    validate_numeric_value($cc_value);
                    $valid_cc_values[] = $cc_value;
                } else {
                    if (filter_var($cc_value, FILTER_VALIDATE_EMAIL)) {
                        $valid_cc_values[] = $cc_value;
                    }
                }
            }

            $data = array(
                $data_field => implode(',', $valid_cc_values)
            );
        } else {
            $data = array(
                $data_field => $value
            );
        }

        $data = clean_data($data);

        $save_id = $this->Tickets_model->ci_save($data, $id);
        if (!$save_id) {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
            return false;
        }

        if ($data_field == "assigned_to") {
            log_notification("ticket_assigned", array("ticket_id" => $id, "to_user_id" => $value));
        } else if ($data_field == "status") {
            if ($value == "open") {
                log_notification("ticket_reopened", array("ticket_id" => $id));
            } else if ($value == "closed") {
                log_notification("ticket_closed", array("ticket_id" => $id));

                //save closing time
                $closed_data = array("closed_at" => get_current_utc_time());
                $this->Tickets_model->ci_save($closed_data, $id);
            }
        }

        $success_array = array("success" => true, 'id' => $save_id, "message" => app_lang('record_saved'));

        echo json_encode($success_array);
    }

    function link_client_to_ticket() {

        $ticket_id = $this->request->getPost('ticket_id');
        $client_id = $this->request->getPost('client_id');
        $contact_id = $this->request->getPost('contact_id');

        $validation_array = array(
            "ticket_id" => "required|numeric",
            "client_id" => "required|numeric",
            "contact_id" => "numeric"
        );

        $this->validate_submitted_data($validation_array);

        $this->validate_ticket_access($ticket_id);

        $ticket_data = array(
            "client_id" => $client_id,
            "created_by" => $contact_id ? $contact_id : 0,
            "requested_by" => $contact_id ? $contact_id : 0
        );

        $ticket_data = clean_data($ticket_data);

        $save_id = $this->Tickets_model->ci_save($ticket_data, $ticket_id);

        if ($save_id) {
            echo json_encode(array('success' => true, 'message' => app_lang('record_saved')));

            if ($contact_id) {
                $ticket_comments = $this->Ticket_comments_model->get_details(array("ticket_id" => $ticket_id, "created_by" => 0))->getResult(); // get comments created by unknown client
                foreach ($ticket_comments as $comment) {
                    $comment_data["created_by"] = $contact_id;
                    $this->Ticket_comments_model->ci_save($comment_data, $comment->id);
                }
            }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }
}

/* End of file tickets.php */
/* Location: ./app/controllers/tickets.php */