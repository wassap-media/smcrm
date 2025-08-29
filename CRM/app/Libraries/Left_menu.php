<?php

namespace App\Libraries;

use App\Controllers\Security_Controller;
use App\Libraries\Permission_manager;

class Left_menu {

    private $ci = null;

    public function __construct() {
        $this->ci = new Security_Controller(false);
    }

    private function _get_sidebar_menu_items($type = "") {
        $dashboard_menu = array("name" => "dashboard", "url" => "dashboard", "class" => "monitor");

        $selected_dashboard_id = get_setting("user_" . $this->ci->login_user->id . "_dashboard");
        if ($selected_dashboard_id) {
            $dashboard_menu = array("name" => "dashboard", "url" => "dashboard/view/" . $selected_dashboard_id, "class" => "monitor", "custom_class" => "dashboard-menu");
        }

        if ($this->ci->login_user->user_type == "staff" && $type !== "client_default") {

            $permission_manager = new Permission_manager($this->ci);
            $sidebar_menu = array("dashboard" => $dashboard_menu);

            $permissions = $this->ci->login_user->permissions;

            $access_expense = get_array_value($permissions, "expense");
            $access_invoice = get_array_value($permissions, "invoice");
            $access_ticket = get_array_value($permissions, "ticket");
            $access_client = get_array_value($permissions, "client");
            $access_lead = get_array_value($permissions, "lead");
            $access_timecard = get_array_value($permissions, "attendance");
            $access_leave = get_array_value($permissions, "leave");
            $access_estimate = get_array_value($permissions, "estimate");
            $access_contract = get_array_value($permissions, "contract");
            $access_subscription = get_array_value($permissions, "subscription");
            $access_proposal = get_array_value($permissions, "proposal");
            $access_order = get_array_value($permissions, "order");

            $client_message_users = get_setting("client_message_users");
            $client_message_users_array = explode(",", $client_message_users);
            $access_messages = ($this->ci->login_user->is_admin || get_array_value($permissions, "message_permission") !== "no" || in_array($this->ci->login_user->id, $client_message_users_array));
            $access_file_manager = get_array_value($permissions, "file_manager");
            $access_timeline = ($this->ci->login_user->is_admin || get_array_value($permissions, "timeline_permission") !== "no");

            if (get_setting("module_event") == "1") {
                $sidebar_menu["events"] = array("name" => "events", "url" => "events", "class" => "calendar");
            }


            if ($this->ci->login_user->is_admin || $access_client) {
                $sidebar_menu["clients"] = array("name" => "clients", "url" => "clients", "class" => "briefcase");
            }


            if ($this->ci->login_user->is_admin || !get_array_value($this->ci->login_user->permissions, "do_not_show_projects")) {
                $sidebar_menu["projects"] = array("name" => "projects", "url" => "projects/all_projects", "class" => "command");
            }

            $sidebar_menu["tasks"] = array("name" => "tasks", "url" => "tasks/all_tasks", "class" => "check-circle");

            if (get_setting("module_lead") == "1" && ($this->ci->login_user->is_admin || $access_lead)) {
                $sidebar_menu["leads"] = array("name" => "leads", "url" => "leads", "class" => "layers");
            }

            if (get_setting("module_subscription") && ($this->ci->login_user->is_admin || $access_subscription)) {
                $sidebar_menu["subscriptions"] = array("name" => "subscriptions", "url" => "subscriptions", "class" => "repeat");
            }

            $sales_submenu = array();

            if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {
                $sales_submenu[] = array("name" => "invoices", "url" => "invoices", "class" => "file-text");
            }

            if (get_setting("module_order") == "1" && ($this->ci->login_user->is_admin || $access_order)) {
                $sales_submenu[] = array("name" => "orders_list", "url" => "orders", "class" => "truck");
                $sales_submenu[] = array("name" => "store", "url" => "store", "class" => "list");
            }

            if (get_setting("module_invoice") == "1" && ($this->ci->login_user->is_admin || $access_invoice)) {
                $sales_submenu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "compass");
            }

            $access_items = $permission_manager->can_manage_items();

            if ($access_items) {
                $sales_submenu[] = array("name" => "items", "url" => "items", "class" => "list");
            }

            if (get_setting("module_contract") && ($this->ci->login_user->is_admin || $access_contract)) {
                $sales_submenu[] = array("name" => "contracts", "url" => "contracts", "class" => "book-open");
            }

            if (count($sales_submenu)) {
                $sidebar_menu["sales"] = array("name" => "sales", "class" => "shopping-cart", "submenu" => $sales_submenu);
            }


            $prospects_submenu = array();

            if (get_setting("module_estimate") && ($this->ci->login_user->is_admin || $access_estimate)) {

                $prospects_submenu["estimates"] = array("name" => "estimate_list", "url" => "estimates", "class" => "file");

                if (get_setting("module_estimate_request")) {
                    $prospects_submenu["estimate_requests"] = array("name" => "estimate_requests", "url" => "estimate_requests", "class" => "file");
                    $prospects_submenu["estimate_forms"] = array("name" => "estimate_forms", "url" => "estimate_requests/estimate_forms", "class" => "file");
                }
            }

            if (get_setting("module_proposal") && ($this->ci->login_user->is_admin || $access_proposal)) {
                $prospects_submenu["proposals"] = array("name" => "proposals", "url" => "proposals", "class" => "coffee");
            }

            if (count($prospects_submenu)) {
                $sidebar_menu["prospects"] = array("name" => "prospects", "url" => "estimates", "class" => "anchor", "submenu" => $prospects_submenu);
            }



            if (get_setting("module_note") == "1") {
                $sidebar_menu["notes"] = array("name" => "notes", "url" => "notes", "class" => "book");
            }

            if (get_setting("module_message") == "1" && $access_messages) {
                $sidebar_menu["messages"] = array("name" => "messages", "url" => "messages", "class" => "message-circle", "badge" => count_unread_message(), "badge_class" => "bg-primary");
            }



            $team_submenu = array();

            if (get_array_value($this->ci->login_user->permissions, "hide_team_members_list") != "1") {
                $team_submenu["team_members"] = array("name" => "team_members", "url" => "team_members", "class" => "users");
            }


            if (get_setting("module_attendance") == "1" && ($this->ci->login_user->is_admin || $access_timecard)) {
                $team_submenu["attendance"] = array("name" => "attendance", "url" => "attendance", "class" => "clock");
            } else if (get_setting("module_attendance") == "1") {
                $team_submenu["attendance"] = array("name" => "attendance", "url" => "attendance/attendance_info", "class" => "clock");
            }


            if (get_setting("module_leave") == "1" && ($this->ci->login_user->is_admin || $access_leave)) {
                $team_submenu["leaves"] = array("name" => "leaves", "url" => "leaves", "class" => "log-out");
            } else if (get_setting("module_leave") == "1") {
                $team_submenu["leaves"] = array("name" => "leaves", "url" => "leaves/leave_info", "class" => "log-out");
            }



            if (get_setting("module_timeline") == "1" && $access_timeline) {
                $team_submenu["timeline"] = array("name" => "timeline", "url" => "timeline", "class" => "send");
            }


            if (get_setting("module_announcement") == "1") {
                $team_submenu["announcements"] = array("name" => "announcements", "url" => "announcements", "class" => "bell");
            }

            if (get_setting("module_help")) {
                $team_submenu["help"] = array("name" => "help", "url" => "help", "class" => "help-circle");
            }

            if (count($team_submenu)) {
                $sidebar_menu["team"] = array("name" => "team", "url" => "team_members", "class" => "users", "submenu" => $team_submenu);
            }


            if (get_setting("module_ticket") == "1" && ($this->ci->login_user->is_admin || $access_ticket)) {

                $ticket_badge = 0;
                if ($this->ci->login_user->is_admin || $access_ticket === "all") {
                    $ticket_badge = count_new_tickets();
                } else if ($access_ticket === "specific") {
                    $specific_ticket_permission = get_array_value($permissions, "ticket_specific");
                    $ticket_badge = count_new_tickets($specific_ticket_permission);
                } else if ($access_ticket === "assigned_only") {
                    $ticket_badge = count_new_tickets("", $this->ci->login_user->id);
                }

                $sidebar_menu["tickets"] = array("name" => "tickets", "url" => "tickets", "class" => "life-buoy", "badge" => $ticket_badge, "badge_class" => "bg-primary");
            }

            $manage_help_and_knowledge_base = ($this->ci->login_user->is_admin || get_array_value($permissions, "help_and_knowledge_base"));

            if (get_setting("module_knowledge_base") == "1" && $manage_help_and_knowledge_base) {
                $sidebar_menu["knowledge_base"] = array(
                    "name" => "knowledge_base",
                    "url" => "knowledge_base",
                    "class" => "help-circle",
                    "sub_pages" => array(
                        "help/knowledge_base_articles",
                        "help/knowledge_base_categories"
                    )
                );
            }

            $access_file_manager = true;
            if (get_setting("module_file_manager") == "1" && ($this->ci->login_user->is_admin || $access_file_manager)) {
                $sidebar_menu["file_manager"] = array("name" => "files", "url" => "file_manager", "class" => "folder");
            }

            if (get_setting("module_expense") == "1" && ($this->ci->login_user->is_admin || $access_expense)) {
                $sidebar_menu["expenses"] = array("name" => "expenses", "url" => "expenses", "class" => "arrow-right-circle");
            }

            $sidebar_menu["reports"] = array(
                "name" => "reports",
                "url" => "reports/index",
                "class" => "pie-chart",
                "sub_pages" => array(
                    "invoices/invoices_summary",
                    "invoices/invoice_details",
                    "orders/orders_summary",
                    "projects/all_timesheets",
                    "expenses/income_vs_expenses",
                    "invoice_payments/payments_summary",
                    "expenses/summary",
                    "projects/team_members_summary",
                    "leads/converted_to_client_report",
                    "tickets/tickets_chart_report"
                )
            );

            if ($this->ci->login_user->is_admin || get_array_value($this->ci->login_user->permissions, "can_manage_all_kinds_of_settings")) {
                $sidebar_menu["settings"] = array(
                    "name" => "settings",
                    "url" => "settings/general",
                    "class" => "settings",
                    "sub_pages" => array(
                        "email_templates/index",
                        "left_menu/index",
                        "updates/index",
                        "roles/index",
                        "roles/user_roles",
                        "team/index",
                        "dashboard/client_default_dashboard",
                        "left_menus/index",
                        "company/index",
                        "item_categories/index",
                        "payment_methods/index",
                        "custom_fields/view",
                        "client_groups/index",
                        "expense_categories/index",
                        "leave_types/index",
                        "ticket_types/index",
                        "lead_status/index",
                        "pages/index",
                        "rise_plugins/index"
                    )
                );
            }

            $sidebar_menu = app_hooks()->apply_filters('app_filter_staff_left_menu', $sidebar_menu);
        } else {
            //client menu

            $sidebar_menu[] = $dashboard_menu;

            if ($this->ci->can_client_access("event")) {
                $sidebar_menu[] = array("name" => "events", "url" => "events", "class" => "calendar");
            }

            if ($this->ci->can_client_access("note") && get_setting("client_can_access_notes")) {
                $sidebar_menu[] = array("name" => "notes", "url" => "notes", "class" => "book");
            }

            //check message access settings for clients
            if ($this->ci->can_client_access("message") && get_setting("client_message_users")) {
                $sidebar_menu[] = array("name" => "messages", "url" => "messages", "class" => "message-circle", "badge" => count_unread_message());
            }

            if ($this->ci->can_client_access("project", false)) {
                $sidebar_menu[] = array("name" => "projects", "url" => "projects/all_projects", "class" => "command");
            }

            if ($this->ci->can_client_access("contract")) {
                $sidebar_menu[] = array("name" => "contracts", "url" => "contracts", "class" => "book-open");
            }

            if ($this->ci->can_client_access("proposal")) {
                $sidebar_menu[] = array("name" => "proposals", "url" => "proposals", "class" => "coffee");
            }

            if ($this->ci->can_client_access("estimate")) {
                $sidebar_menu[] = array("name" => "estimates", "url" => "estimates", "class" => "file");
            }

            if ($this->ci->can_client_access("subscription")) {
                $sidebar_menu["subscriptions"] = array("name" => "subscriptions", "url" => "subscriptions", "class" => "repeat");
            }

            if ($this->ci->can_client_access("invoice")) {
                if ($this->ci->can_client_access("invoice")) {
                    $sidebar_menu[] = array("name" => "invoices", "url" => "invoices", "class" => "file-text");
                }
                if ($this->ci->can_client_access("payment", false)) {
                    $sidebar_menu[] = array("name" => "invoice_payments", "url" => "invoice_payments", "class" => "compass");
                }
            }

            if ($this->ci->can_client_access("store", false) && get_setting("client_can_access_store")) {
                $sidebar_menu[] = array("name" => "store", "url" => "store", "class" => "truck");
                $sidebar_menu[] = array("name" => "orders", "url" => "orders", "class" => "shopping-cart");
            }

            if ($this->ci->can_client_access("ticket")) {
                $sidebar_menu[] = array("name" => "tickets", "url" => "tickets", "class" => "life-buoy");
            }

            if ($this->ci->can_client_access("announcement")) {
                $sidebar_menu[] = array("name" => "announcements", "url" => "announcements", "class" => "bell");
            }

            $sidebar_menu[] = array("name" => "users", "url" => "clients/users", "class" => "users");

            if (get_setting("client_can_view_files")) {
                $sidebar_menu[] = array("name" => "files", "url" => "clients/files/" . $this->ci->login_user->id . "/page_view", "class" => "image");
            }

            $sidebar_menu[] = array("name" => "my_profile", "url" => "clients/contact_profile/" . $this->ci->login_user->id, "class" => "settings");

            if ($this->ci->can_client_access("knowledge_base")) {
                $sidebar_menu[] = array("name" => "knowledge_base", "url" => "knowledge_base", "class" => "help-circle");
            }

            $sidebar_menu = app_hooks()->apply_filters('app_filter_client_left_menu', $sidebar_menu);
        }

        return $this->position_items_for_default_left_menu($sidebar_menu);
    }

    function _get_active_menu($sidebar_menu = array()) {
        $router = service('router');
        $controller_name = strtolower(get_actual_controller_name($router));
        $uri_string = uri_string();
        $current_url = get_uri($uri_string);
        $method_name = $router->methodName();

        $found_url_active_key = null;

        foreach ($sidebar_menu as $key => $menu) {
            if (isset($menu["name"])) {
                $menu_name = get_array_value($menu, "name");
                $menu_url = get_array_value($menu, "url");

                //compare with controller name
                if ($controller_name == $menu_url) {
                    $found_url_active_key = $key;
                }

                //compare with current url
                if ($menu_url && ($menu_url === $current_url || get_uri($menu_url) === $current_url)) {
                    $sidebar_menu[$key]["is_active_menu"] = 1;
                    return $sidebar_menu;
                }

                // check for controller match only if no active key is set
                if ($found_url_active_key === null && ($controller_name == $menu_url || $menu_name === $controller_name)) {
                    $found_url_active_key = $key;
                }

                //check in submenu values
                $submenu = get_array_value($menu, "submenu");
                if ($submenu && count($submenu)) {
                    foreach ($submenu as $sub_menu) {
                        if (isset($sub_menu['name'])) {

                            $sub_menu_url = get_array_value($sub_menu, "url");

                            if ($controller_name == $sub_menu_url) {
                                $found_url_active_key = $key;
                            }

                            //compare with current url
                            if ($sub_menu_url === $current_url || get_uri($sub_menu_url) === $current_url) {
                                $found_url_active_key = $key;
                            }

                            //compare with controller name
                            if (get_array_value($sub_menu, "name") === $controller_name) {
                                $found_url_active_key = $key;
                            } else if (get_array_value($sub_menu, "category") === $controller_name) {
                                $found_url_active_key = $key;
                            }
                        }
                    }
                }


                $sub_pages = get_array_value($menu, "sub_pages");
                if ($sub_pages) {
                    foreach ($sub_pages as $sub_page_ur) {
                        if ($sub_page_ur == $controller_name . "/" . $method_name) {
                            $found_url_active_key = $key;
                        }
                    }
                }
            }
        }

        if (!is_null($found_url_active_key)) {
            $sidebar_menu[$found_url_active_key]["is_active_menu"] = 1;
        }


        return $sidebar_menu;
    }

    function get_available_items($type = "default") {
        $items_array = $this->_prepare_sidebar_menu_items($type);

        $default_left_menu_items = $this->_get_left_menu_from_setting($type);

        if ($default_left_menu_items && is_array($default_left_menu_items) && count($default_left_menu_items)) {
            //remove used items
            foreach ($default_left_menu_items as $default_item) {
                unset($items_array[get_array_value($default_item, "name")]);
            }
        } else {
            //since all menu items will be added to the customization area when there is no item, don't show anything here
            $items_array = array();
        }

        $items = "";
        foreach ($items_array as $item) {
            $items .= $this->_get_item_data($item, true);
        }

        return $items ? $items : "<span class='text-off empty-area-text'>" . app_lang('no_more_items_available') . "</span>";
    }

    private function _prepare_sidebar_menu_items($type = "", $return_sub_menu_data = false) {
        $final_items_array = array();
        $items_array = $this->_get_sidebar_menu_items($type);

        foreach ($items_array as $item) {
            $main_menu_name = get_array_value($item, "name");

            if (isset($item["submenu"])) {
                //first add this menu removing the submenus
                $main_menu = $item;
                unset($main_menu["submenu"]);
                $final_items_array[$main_menu_name] = $main_menu;

                $submenu = get_array_value($item, "submenu");
                foreach ($submenu as $key => $s_menu) {

                    if ($return_sub_menu_data) {
                        $s_menu["is_sub_menu"] = true;
                    }

                    if (get_array_value($s_menu, "class")) {
                        $final_items_array[get_array_value($s_menu, "name")] = $s_menu;
                    }
                }
            } else {
                $final_items_array[$main_menu_name] = $item;
            }
        }

        //add todo
        $final_items_array["todo"] = array("name" => "todo", "url" => "todo", "class" => "check-square");

        return $final_items_array;
    }

    private function _get_left_menu_from_setting_for_rander($is_preview = false, $type = "default") {
        $user_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");
        $default_left_menu = ($type == "client_default" || $this->ci->login_user->user_type == "client") ? get_setting("default_client_left_menu") : get_setting("default_left_menu");
        $custom_left_menu = "";

        //for preview, show the edit type preview
        if ($is_preview) {
            $custom_left_menu = $default_left_menu; //default preview
            if ($type == "user") {
                $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //user level preview
            }
        } else {
            $custom_left_menu = $user_left_menu ? $user_left_menu : $default_left_menu; //page rander
        }

        return $custom_left_menu ? json_decode(json_encode(@unserialize($custom_left_menu)), true) : array();
    }

    private function _get_left_menu_from_setting($type) {
        if ($type == "client_default") {
            $default_left_menu = get_setting("default_client_left_menu");
        } else if ($type == "user") {
            $default_left_menu = get_setting("user_" . $this->ci->login_user->id . "_left_menu");
        } else {
            $default_left_menu = get_setting("default_left_menu");
        }

        $result = $default_left_menu ? json_decode(json_encode(@unserialize($default_left_menu)), true) : array();

        if (!is_array($result)) {
            $result = array();
        }

        return $result;
    }

    public function _get_item_data($item, $is_default_item = false) {
        $name = get_array_value($item, "name");
        $language_key = get_array_value($item, "language_key");
        $url = get_array_value($item, "url");
        $is_sub_menu = get_array_value($item, "is_sub_menu");
        $open_in_new_tab = get_array_value($item, "open_in_new_tab");
        $icon = get_array_value($item, "icon");

        if ($name) {
            $sub_menu_class = "";
            $clickable_menu_class = "make-sub-menu";
            $clickable_icon = "<i data-feather='corner-right-down' class='icon-14'></i>";
            if ($is_sub_menu) {
                $sub_menu_class = "ml20";
                $clickable_menu_class = "make-root-menu";
                $clickable_icon = "<i data-feather='corner-up-left' class='icon-14'></i>";
            }

            $extra_attr = "";
            $edit_button = "";
            $name_lang = "";
            if ($is_default_item || !$url) {
                $name_lang = app_lang($name);
            } else {
                if ($language_key) {
                    $name_lang = app_lang($language_key);
                } else {
                    $name_lang = $name;
                }

                //custom menu item
                $extra_attr = "data-url='$url' data-icon='$icon' data-custom_menu_item_id='" . rand(2000, 400000000) . "' data-open_in_new_tab='$open_in_new_tab' data-language_key='$language_key'";
                $edit_button = modal_anchor(get_uri("left_menus/add_menu_item_modal_form"), "<i data-feather='edit' class='icon-14'></i> ", array("title" => app_lang('edit'), "class" => "custom-menu-edit-button", "data-post-title" => $name, "data-post-url" => $url, "data-post-is_sub_menu" => $is_sub_menu, "data-post-icon" => $icon, "data-post-open_in_new_tab" => $open_in_new_tab, "data-post-language_key" => $language_key));
            }

            return "<div data-value='" . $name . "' $extra_attr class='left-menu-item mb5 widget clearfix p10 bg-white $sub_menu_class'>
                        <span class='float-start text-start'><i data-feather='move' class='icon-16 text-off mr5'></i> " . $name_lang . "</span>
                        <span class='float-end invisible'>
                            <span class='clickable $clickable_menu_class toggle-menu-icon' title='" . app_lang("make_previous_items_sub_menu") . "'>$clickable_icon</span>
                            $edit_button
                            <span class='clickable delete-left-menu-item' title=" . app_lang("delete") . "><i data-feather='x' class='icon-14 text-danger'></i></span>
                        </span>
                    </div>";
        }
    }

    function get_sortable_items($type = "default") {
        $items = "<div id='menu-item-list-2' class='js-left-menu-scrollbar add-column-drop text-center p15 menu-item-list sortable-items-container'>";

        $default_left_menu_items = $this->_get_left_menu_from_setting($type);
        if (count($default_left_menu_items)) {
            foreach ($default_left_menu_items as $item) {
                $items .= $this->_get_item_data($item);
            }
        } else {
            //if there has no item in the customization panel, show the default items
            $items_array = $this->_prepare_sidebar_menu_items($type, true);
            foreach ($items_array as $item) {
                $items .= $this->_get_item_data($item, true);
            }
        }

        $items .= "</div>";

        return $items;
    }

    function rander_left_menu($is_preview = false, $type = "default") {
        $final_left_menu_items = array();
        $custom_left_menu_items = $this->_get_left_menu_from_setting_for_rander($is_preview, $type);

        if ($custom_left_menu_items) {
            $left_menu_items = $this->_prepare_sidebar_menu_items($type);
            $last_final_menu_item = ""; //store the last menu item of final left menu to add submenu to this item

            foreach ($custom_left_menu_items as $custom_left_menu_item) {
                $item_value_array = $this->_get_item_array_value($custom_left_menu_item, $left_menu_items);
                $is_sub_menu = get_array_value($custom_left_menu_item, "is_sub_menu");

                if ($is_sub_menu) {
                    //this is a sub menu, move it to it's parent item
                    $final_left_menu_items[$last_final_menu_item]["submenu"][] = $item_value_array;
                } else {
                    $final_left_menu_items[] = $item_value_array;
                    $last_final_menu_item = end($final_left_menu_items);
                    $last_final_menu_item = key($final_left_menu_items);
                }
            }
        }

        if (count($final_left_menu_items)) {
            $view_data["sidebar_menu"] = $final_left_menu_items;
        } else {
            $view_data["sidebar_menu"] = $this->_get_sidebar_menu_items($type);
        }

        if (!$is_preview) {
            $view_data["sidebar_menu"] = $this->_get_active_menu($view_data["sidebar_menu"]);
        }

        $view_data["is_preview"] = $is_preview;
        $view_data["login_user"] = $this->ci->login_user;
        return view("includes/left_menu", $view_data);
    }

    private function _get_item_array_value($data_array, $left_menu_items) {
        $name = get_array_value($data_array, "name");
        $language_key = get_array_value($data_array, "language_key");
        $url = get_array_value($data_array, "url");
        $icon = get_array_value($data_array, "icon");
        $open_in_new_tab = get_array_value($data_array, "open_in_new_tab");
        $item_value_array = array();

        if ($url) { //custom menu item
            $item_value_array = array("name" => $name, "language_key" => $language_key, "url" => $url, "is_custom_menu_item" => true, "class" => "$icon", "open_in_new_tab" => $open_in_new_tab);
        } else if (array_key_exists($name, $left_menu_items)) { //default menu items
            $item_value_array = get_array_value($left_menu_items, $name);
        }

        return $item_value_array;
    }

    //position items for plugins
    private function position_items_for_default_left_menu($sidebar_menu = array()) {
        foreach ($sidebar_menu as $key => $menu) {
            $position = get_array_value($menu, "position");
            if ($position) {
                $position = $position - 1;
                $sidebar_menu = array_slice($sidebar_menu, 0, $position, true) +
                    array($key => $menu) +
                    array_slice($sidebar_menu, $position, NULL, true);
            }
        }

        return $sidebar_menu;
    }
}
