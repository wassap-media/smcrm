<?php

use App\Controllers\Security_Controller;
use App\Controllers\App_Controller;
use App\Libraries\Pdf;
use App\Libraries\Clean_data;
use App\Libraries\Outlook_smtp;
use App\Controllers\Notification_processor;
use App\Libraries\Pusher_connect;
use App\Libraries\Gmail_smtp;

/**
 * use this to print link location
 *
 * @param string $uri
 * @return print url
 */
if (!function_exists('echo_uri')) {

    function echo_uri($uri = "") {
        echo get_uri($uri);
    }
}

/**
 * prepare uri
 * 
 * @param string $uri
 * @return full url 
 */
if (!function_exists('get_uri')) {

    function get_uri($uri = "") {
        $index_page = config("App")->indexPage;
        $base_url = base_url($index_page);
        $last_chr_on_url = substr($base_url, -1);
        if ($last_chr_on_url == "/") {
            return $base_url . $uri;
        } else {
            return $base_url . '/' . $uri;
        }
    }
}

/**
 * use this to print file path
 * 
 * @param string $uri
 * @return full url of the given file path
 */
if (!function_exists('get_file_uri')) {

    function get_file_uri($uri = "") {
        return base_url($uri);
    }
}

/**
 * get the url of user avatar
 * 
 * @param string $image_name
 * @return url of the avatar of given image reference
 */
if (!function_exists('get_avatar')) {

    function get_avatar($image = "") {
        if ($image === "system_bot") {
            return base_url("assets/images/avatar-bot.jpg");
        } else if ($image === "bitbucket") {
            return base_url("assets/images/bitbucket_logo.png");
        } else if ($image === "github") {
            return base_url("assets/images/github_logo.png");
        } else if ($image) {
            $file = @unserialize($image);
            if (is_array($file)) {
                return get_source_url_of_file($file, get_setting("profile_image_path") . "/", "thumbnail");
            } else {
                return base_url(get_setting("profile_image_path")) . "/" . $image;
            }
        } else {
            return base_url("assets/images/avatar.jpg");
        }
    }
}

/**
 * link the css files 
 * 
 * @param array $array
 * @return print css links
 */
if (!function_exists('load_css')) {

    function load_css(array $array) {
        $version = get_setting("app_version");

        foreach ($array as $uri) {
            echo "<link rel='stylesheet' type='text/css' href='" . base_url($uri) . "?v=$version' />";
        }
    }
}


/**
 * link the javascript files 
 * 
 * @param array $array
 * @return print js links
 */
if (!function_exists('load_js')) {

    function load_js(array $array) {
        $version = get_setting("app_version");

        foreach ($array as $uri) {
            echo "<script type='text/javascript'  src='" . base_url($uri) . "?v=$version'></script>";
        }
    }
}

/**
 * check the array key and return the value 
 * 
 * @param array $array
 * @return extract array value safely
 */
if (!function_exists('get_array_value')) {

    function get_array_value($array, $key) {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
    }
}

/**
 * prepare a anchor tag for any js request
 * 
 * @param string $title
 * @param array $attributes
 * @return html link of anchor tag
 */
if (!function_exists('js_anchor')) {

    function js_anchor($title = '', $attributes = '') {
        $title = (string) $title;
        $html_attributes = "";

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $html_attributes .= ' ' . $key . '="' . $value . '"';
            }
        }

        return '<a href="#"' . $html_attributes . '>' . $title . '</a>';
    }
}


/**
 * prepare a anchor tag for modal 
 * 
 * @param string $url
 * @param string $title
 * @param array $attributes
 * @return html link of anchor tag
 */
if (!function_exists('modal_anchor')) {

    function modal_anchor($url, $title = '', $attributes = '') {
        $attributes["data-act"] = "ajax-modal";
        if (get_array_value($attributes, "data-modal-title")) {
            $attributes["data-title"] = get_array_value($attributes, "data-modal-title");
        } else {
            $attributes["data-title"] = get_array_value($attributes, "title");
        }
        $attributes["data-action-url"] = $url;

        return js_anchor($title, $attributes);
    }
}

/**
 * prepare a anchor tag for ajax request
 * 
 * @param string $url
 * @param string $title
 * @param array $attributes
 * @return html link of anchor tag
 */
if (!function_exists('ajax_anchor')) {

    function ajax_anchor($url, $title = '', $attributes = '') {
        $attributes["data-act"] = "ajax-request";
        $attributes["data-action-url"] = $url;
        return js_anchor($title, $attributes);
    }
}

if (!function_exists('get_actual_controller_name')) {

    function get_actual_controller_name($router) {
        $controller_name = $router->controllerName();
        $controller_name = explode("\\", $controller_name);
        return end($controller_name);
    }
}

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_setting')) {

    function get_setting($key = "") {
        $setting_value = get_array_value(config('Rise')->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            if (isset(config('Rise')->$key)) {
                return config('Rise')->$key;
            } else if (isset(config('App')->$key)) {
                return config('App')->$key;
            } else {
                return "";
            }
        }
    }
}



/**
 * check if a string starts with a specified sting
 * 
 * @param string $string
 * @param string $needle
 * @return true/false
 */
if (!function_exists('starts_with')) {

    function starts_with($string, $needle) {
        $string = $string;
        return $needle === "" || strrpos($string, $needle, -strlen($string)) !== false;
    }
}

/**
 * check if a string ends with a specified sting
 * 
 * @param string $string
 * @param string $needle
 * @return true/false
 */
if (!function_exists('ends_with')) {

    function ends_with($string, $needle) {
        return $needle === "" || (($temp = strlen($string) - strlen($string)) >= 0 && strpos($string, $needle, $temp) !== false);
    }
}

/**
 * create a encoded id for sequrity pupose 
 * 
 * @param string $id
 * @param string $salt
 * @return endoded value
 */
if (!function_exists('encode_id')) {

    function encode_id($id, $salt) {
        $encrypter = get_encrypter();
        $id = bin2hex($encrypter->encrypt($id . $salt));
        $id = str_replace("=", "~", $id);
        $id = str_replace("+", "_", $id);
        $id = str_replace("/", "-", $id);
        return $id;
    }
}

if (!function_exists('get_encrypter')) {

    function get_encrypter() {
        $config = new \Config\Encryption();
        $config->key = config('App')->encryption_key;
        $config->driver = 'OpenSSL';

        return \Config\Services::encrypter($config);
    }
}

/**
 * decode the id which made by encode_id()
 * 
 * @param string $id
 * @param string $salt
 * @return decoded value
 */
if (!function_exists('decode_id')) {

    function decode_id($id, $salt) {
        $encrypter = get_encrypter();
        if ($id) {
            $id = str_replace("_", "+", $id);
            $id = str_replace("~", "=", $id);
            $id = str_replace("-", "/", $id);
        }


        try {
            $id = $encrypter->decrypt(hex2bin($id));

            if ($id && strpos($id, $salt) != false) {
                return str_replace($salt, "", $id);
            } else {
                return "";
            }
        } catch (\Exception $ex) {
            return "";
        }
    }
}

/**
 * decode html data which submited using a encode method of encodeAjaxPostData() function
 * 
 * @param string $html
 * @return htmle
 */
if (!function_exists('decode_ajax_post_data')) {

    function decode_ajax_post_data($html) {
        $html = str_replace("00bg-img00", "background-image", $html);
        $html = str_replace("00quotation00", "&quot;", $html);
        $html = str_replace("00~00", "=", $html);
        $html = str_replace("00^00", "&", $html);
        return $html;
    }
}

/**
 * check if fields has any value or not. and generate a error message for null value
 * 
 * @param array $fields
 * @return throw error for bad value
 */
if (!function_exists('check_required_hidden_fields')) {

    function check_required_hidden_fields($fields = array()) {
        $has_error = false;
        foreach ($fields as $field) {
            if (!$field) {
                $has_error = true;
            }
        }
        if ($has_error) {
            echo json_encode(array("success" => false, 'message' => app_lang('something_went_wrong')));
            exit();
        }
    }
}

/**
 * convert simple link text to clickable link
 * @param string $text
 * @return html link
 */
if (!function_exists('link_it')) {

    function link_it($text) {
        if ($text != strip_tags($text)) {
            //contains HTML, return the actual text
            return $text;
        } else {
            return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s]?[^\s]+)?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
        }
    }
}

/**
 * convert mentions to link or link text
 * @param string $text containing text with mentioned brace
 * @param string $return_type indicates what to return (link or text)
 * @return text with link or link text
 */
if (!function_exists('convert_mentions')) {

    function convert_mentions($text, $convert_links = true) {

        preg_match_all('#@\[(.*?)\]#', $text, $matches);

        $members = array();

        $mentions = get_array_value($matches, 1);
        if ($mentions && count($mentions)) {
            foreach ($mentions as $mention) {
                $user = explode(":", $mention);
                if ($convert_links) {
                    $user_id = get_array_value($user, 1);
                    $members[] = get_team_member_profile_link($user_id, trim($user[0]));
                } else {
                    $members[] = $user[0];
                }
            }
        }

        if ($convert_links) {
            $text = custom_nl2br(link_it($text));
        } else {
            $text = custom_nl2br($text);
        }

        // Only replace @[...] with the appropriate converted value
        $text = preg_replace_callback('/@\[.*?\]/', function ($match) use (&$members) {
            return array_shift($members);
        }, $text);

        return $text;
    }
}

/**
 * get all the use_ids from comment mentions
 * @param string $text
 * @return array of user_ids
 */
if (!function_exists('get_members_from_mention')) {

    function get_members_from_mention($text) {

        preg_match_all('#\@\[(.*?)\]#', $text, $matchs);

        //find the user ids.
        $user_ids = array();
        $mentions = get_array_value($matchs, 1);

        if ($mentions && count($mentions)) {
            foreach ($mentions as $mention) {
                $user = explode(":", $mention);
                $user_id = get_array_value($user, 1);
                if ($user_id) {
                    array_push($user_ids, $user_id);
                }
            }
        }

        return $user_ids;
    }
}

/**
 * send mail
 * 
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param array $optoins
 * @return true/false
 */
if (!function_exists('send_app_mail')) {

    function send_app_mail($to, $subject, $message, $optoins = array(), $convert_message_to_html = true) {

        $emails_for_log = $to;
        if ($to && is_array($to)) {
            $emails_for_log = implode(',', $to);
        }
        log_message('notice', 'Email: ' . $emails_for_log . ' Subject: ' . $subject);

        $email_protocol = get_setting("email_protocol");
        $email_from_address = get_setting("email_sent_from_address");

        if ($email_protocol === "microsoft_outlook") {
            $Outlook_smtp = new Outlook_smtp();
            return $Outlook_smtp->send_app_mail($to, $subject, $message, $optoins, $convert_message_to_html);
        } else if ($email_protocol === "gmail_smtp") {
            $Gmail_smtp = new Gmail_smtp();
            return $Gmail_smtp->send_app_mail($to, $subject, $message, $optoins, $convert_message_to_html);
        } else {
            $email_config = array(
                'charset' => 'utf-8',
                'mailType' => 'html'
            );

            //check mail sending method from settings
            if ($email_protocol === "smtp") {
                $email_config["protocol"] = "smtp";
                $email_config["SMTPHost"] = get_setting("email_smtp_host");
                $email_config["SMTPPort"] = (int)get_setting("email_smtp_port");
                $email_config["SMTPUser"] = get_setting("email_smtp_user");
                $email_config["SMTPPass"] = decode_password(get_setting('email_smtp_pass'), "email_smtp_pass");
                $email_config["SMTPCrypto"] = get_setting("email_smtp_security_type");

                if (!$email_config["SMTPCrypto"]) {
                    $email_config["SMTPCrypto"] = "tls"; //for old clients, we have to set this by default
                }

                if ($email_config["SMTPCrypto"] === "none") {
                    $email_config["SMTPCrypto"] = "";
                }
            }

            $email = \CodeIgniter\Config\Services::email();
            $email->initialize($email_config);
            $email->clear(true); //clear previous message and attachment

            $email->setNewline("\r\n");
            $email->setCRLF("\r\n");
            $email->setFrom($email_from_address, get_setting("email_sent_from_name"));

            $email->setTo($to);
            $email->setSubject($subject);

            if ($convert_message_to_html) {
                $message = htmlspecialchars_decode($message);
            }

            $email->setMessage($message);

            //add attachment
            $attachments = get_array_value($optoins, "attachments");
            if (is_array($attachments)) {
                foreach ($attachments as $value) {
                    $file_path = get_array_value($value, "file_path");
                    $file_name = get_array_value($value, "file_name");
                    $email->attach(trim($file_path), "attachment", $file_name);
                }
            }

            //check reply-to
            $reply_to = get_array_value($optoins, "reply_to");
            if ($reply_to) {
                $email->setReplyTo($reply_to);
            }

            //check cc
            $cc = get_array_value($optoins, "cc");
            if ($cc) {
                $email->setCC($cc);
            }

            //check bcc
            $bcc = get_array_value($optoins, "bcc");
            if ($bcc) {
                $email->setBCC($bcc);
            }

            //send email
            if ($email->send()) {
                return true;
            } else {
                //show error message in none production version
                if (ENVIRONMENT !== 'production') {
                    throw new \Exception($email->printDebugger());
                }
                return false;
            }
        }
    }
}


/**
 * get users ip address
 * 
 * @return ip
 */
if (!function_exists('get_real_ip')) {

    function get_real_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}

/**
 * check if it's localhost
 * 
 * @return boolean
 */
if (!function_exists('is_localhost')) {

    function is_localhost() {
        $known_localhost_ip = array(
            '127.0.0.1',
            '::1'
        );
        if (in_array(get_real_ip(), $known_localhost_ip)) {
            return true;
        }
    }
}


/**
 * convert string to url
 * 
 * @param string $address
 * @return url
 */
if (!function_exists('to_url')) {

    function to_url($address = "") {
        if (strpos($address, 'http://') === false && strpos($address, 'https://') === false) {
            $address = "http://" . $address;
        }
        return $address;
    }
}

/**
 * validate post data using the codeigniter's form validation method
 * 
 * @param string $address
 * @return throw error if foind any inconsistancy
 */
if (!function_exists('validate_numeric_value')) {

    function validate_numeric_value($value = 0) {
        if ($value && !is_numeric($value)) {
            die("Invalid value");
        }
    }
}

/**
 * validate post data using the codeigniter's form validation method
 * 
 * @param string $address
 * @return throw error if foind any inconsistancy
 */
if (!function_exists('get_only_numeric_value')) {

    function get_only_numeric_value($value = 0) {
        if ($value && !is_numeric($value)) {
            die("Invalid value");
        }
        return $value;
    }
}

/**
 * team members profile anchor. only clickable to team members
 * client's will see a none clickable link
 * 
 * @param string $id
 * @param string $name
 * @param array $attributes
 * @return html link
 */
if (!function_exists('get_team_member_profile_link')) {

    function get_team_member_profile_link($id = 0, $name = "", $attributes = array()) {
        $ci = new Security_Controller(false);
        if ($ci->login_user->user_type === "staff") {
            return anchor("team_members/view/" . $id, $name ? $name : "", $attributes);
        } else {
            return js_anchor($name, $attributes);
        }
    }
}


/**
 * team members profile anchor. only clickable to team members
 * client's will see a none clickable link
 * 
 * @param string $id
 * @param string $name
 * @param array $attributes
 * @return html link
 */
if (!function_exists('get_client_contact_profile_link')) {

    function get_client_contact_profile_link($id = 0, $name = "", $attributes = array()) {
        return anchor("clients/contact_profile/" . $id, $name ? $name : "", $attributes);
    }
}


/**
 * return a colorful label according to invoice status
 * 
 * @param Object $invoice_info
 * @return html
 */
if (!function_exists('get_invoice_status_label')) {

    function get_invoice_status_label($invoice_info, $return_html = true, $extra_classes = "") {
        $invoice_status_class = "bg-secondary";
        $status = "not_paid";
        $now = get_my_local_time("Y-m-d");

        $tolarance = get_paid_status_tolarance();

        //ignore the hidden value. check only 2 decimal place.
        $invoice_value = floor($invoice_info->invoice_value * 100) / 100;

        if ($invoice_info->status == "cancelled") {
            $invoice_status_class = "bg-danger";
            $status = "cancelled";
        } else if ($invoice_info->status == "credited") {
            $invoice_status_class = "bg-danger";
            $status = "credited";
        } else if ($invoice_info->status != "draft" && $invoice_info->due_date < $now && $invoice_info->payment_received < $invoice_value - $tolarance) {
            $invoice_status_class = "bg-danger";
            $status = "overdue";
        } else if ($invoice_info->status !== "draft" && $invoice_info->payment_received <= 0) {
            $invoice_status_class = "bg-warning";
            $status = "not_paid";
        } else if ($invoice_info->payment_received * 1 && $invoice_info->payment_received >= $invoice_value - $tolarance) {
            $invoice_status_class = "bg-success";
            $status = "fully_paid";
        } else if ($invoice_info->payment_received > 0 && $invoice_info->payment_received < $invoice_value - $tolarance) {
            $invoice_status_class = "bg-primary";
            $status = "partially_paid";
        } else if ($invoice_info->status === "draft") {
            $invoice_status_class = "bg-secondary";
            $status = "draft";
        }

        $invoice_status = "<span class='mt0 badge $invoice_status_class $extra_classes'>" . app_lang($status) . "</span>";
        if ($return_html) {
            return $invoice_status;
        } else {
            return $status;
        }
    }
}


/**
 * get all data to make an invoice
 * 
 * @param Int $invoice_id
 * @return array
 */
if (!function_exists('get_invoice_making_data')) {

    function get_invoice_making_data($invoice_id) {
        $ci = new App_Controller();
        $invoice_info = $ci->Invoices_model->get_details(array("id" => $invoice_id))->getRow();
        if ($invoice_info) {
            $data['invoice_info'] = $invoice_info;
            $data['client_info'] = $ci->Clients_model->get_one($data['invoice_info']->client_id);
            $data['invoice_items'] = $ci->Invoice_items_model->get_details(array("invoice_id" => $invoice_id))->getResult();
            $data['invoice_status_label'] = get_invoice_status_label($invoice_info, true, "large rounded-pill");
            $data["invoice_total_summary"] = $ci->Invoices_model->get_invoice_total_summary($invoice_id);
            $data['invoice_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "invoices", "show_in_invoice" => true, "related_to_id" => $invoice_id))->getResult();
            $data['client_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_invoice" => true, "related_to_id" => $data['invoice_info']->client_id))->getResult();
            return $data;
        }
    }
}

/**
 * get all data to make an invoice
 * 
 * @param Invoice making data $invoice_data
 * @return array
 */
if (!function_exists('prepare_invoice_pdf')) {

    function prepare_invoice_pdf($invoice_data, $mode = "download", $is_mobiel_preview = false) {

        if ($invoice_data) {
            $invoice_data["mode"] = clean_data($mode);

            $html = view("invoices/invoice_pdf", $invoice_data);

            if ($mode === "html") {
                return $html;
            }

            $invoice_info = get_array_value($invoice_data, "invoice_info");

            $pdf = new Pdf("invoice");
            return $pdf->PreparePDF($html, $invoice_info->display_id, $mode, $is_mobiel_preview);
        }
    }
}

/**
 * get all data to make an estimate
 * 
 * @param emtimate making data $estimate_data
 * @return array
 */
if (!function_exists('prepare_estimate_pdf')) {

    function prepare_estimate_pdf($estimate_data, $mode = "download", $is_mobiel_preview = false) {

        if ($estimate_data) {
            $estimate_data["mode"] = clean_data($mode);

            $html = view("estimates/estimate_pdf", $estimate_data);

            if ($mode === "html") {
                return $html;
            }

            $estimate_info = get_array_value($estimate_data, "estimate_info");

            $pdf = new Pdf("estimate");
            return $pdf->PreparePDF($html, get_estimate_id($estimate_info->id), $mode, $is_mobiel_preview);
        }
    }
}

/**
 * get all data to make an order
 * 
 * @param emtimate making data $order_data
 * @return array
 */
if (!function_exists('prepare_order_pdf')) {

    function prepare_order_pdf($order_data, $mode = "download", $is_mobiel_preview = false) {

        if ($order_data) {
            $estimate_data["mode"] = clean_data($mode);
            $html = view("orders/order_pdf", $order_data);

            if ($mode === "html") {
                return $html;
            }

            $order_info = get_array_value($order_data, "order_info");

            $pdf = new Pdf("order");
            return $pdf->PreparePDF($html, get_order_id($order_info->id), $mode, $is_mobiel_preview);
        }
    }
}

/**
 * 
 * get invoice number
 * @param Int $invoice_id
 * @return string
 */
if (!function_exists('get_invoice_id')) {

    function get_invoice_id($invoice_id) {
        $prefix = get_setting("invoice_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("invoice")) . " #";
        return $prefix . $invoice_id;
    }
}

/**
 * 
 * get estimate number
 * @param Int $estimate_id
 * @return string
 */
if (!function_exists('get_estimate_id')) {

    function get_estimate_id($estimate_id) {
        $prefix = get_setting("estimate_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("estimate")) . " #";
        return $prefix . $estimate_id;
    }
}

/**
 * 
 * get proposal number
 * @param Int $proposal_id
 * @return string
 */
if (!function_exists('get_proposal_id')) {

    function get_proposal_id($proposal_id) {
        $prefix = get_setting("proposal_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("proposal")) . " #";
        return $prefix . $proposal_id;
    }
}

/**
 * 
 * get order number
 * @param Int $order_id
 * @return string
 */
if (!function_exists('get_order_id')) {

    function get_order_id($order_id) {
        $prefix = get_setting("order_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("order")) . " #";
        return $prefix . $order_id;
    }
}

/**
 * 
 * get ticket number
 * @param Int $ticket_id
 * @return string
 */
if (!function_exists('get_ticket_id')) {

    function get_ticket_id($ticket_id) {
        $prefix = get_setting("ticket_prefix");
        $prefix = $prefix ? $prefix : app_lang("ticket") . " #";
        return $prefix . $ticket_id;
    }
}


/**
 * get all data to make an estimate
 * 
 * @param Int $estimate_id
 * @return array
 */
if (!function_exists('get_estimate_making_data')) {

    function get_estimate_making_data($estimate_id) {
        validate_numeric_value($estimate_id);
        $ci = new App_Controller();
        $estimate_info = $ci->Estimates_model->get_details(array("id" => $estimate_id))->getRow();
        if ($estimate_info) {
            $data['estimate_info'] = $estimate_info;
            $data['client_info'] = $ci->Clients_model->get_one($data['estimate_info']->client_id);
            $data['estimate_items'] = $ci->Estimate_items_model->get_details(array("estimate_id" => $estimate_id))->getResult();
            $data["estimate_total_summary"] = $ci->Estimates_model->get_estimate_total_summary($estimate_id);
            $data['estimate_status_label'] = get_estimate_status_label($estimate_info);

            $data['estimate_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "estimates", "show_in_estimate" => true, "related_to_id" => $estimate_id))->getResult();
            return $data;
        }
    }
}

/**
 * get all data to make an contract
 * 
 * @param Int $contract_id
 * @return array
 */
if (!function_exists('get_contract_making_data')) {

    function get_contract_making_data($contract_id) {
        $ci = new App_Controller(false);
        $contract_info = $ci->Contracts_model->get_details(array("id" => $contract_id))->getRow();
        if ($contract_info) {
            $data['contract_info'] = $contract_info;
            $data['client_info'] = $ci->Clients_model->get_one($data['contract_info']->client_id);
            $data['contract_items'] = $ci->Contract_items_model->get_details(array("contract_id" => $contract_id))->getResult();
            $data["contract_total_summary"] = $ci->Contracts_model->get_contract_total_summary($contract_id);

            $data['contract_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "contracts", "show_in_contract" => true, "related_to_id" => $contract_id))->getResult();
            return $data;
        }
    }
}

/**
 * get all data to make an proposal
 * 
 * @param Int $proposal_id
 * @return array
 */
if (!function_exists('get_proposal_making_data')) {

    function get_proposal_making_data($proposal_id) {
        $ci = new App_Controller(false);
        $proposal_info = $ci->Proposals_model->get_details(array("id" => $proposal_id))->getRow();
        if ($proposal_info) {
            $data['proposal_info'] = $proposal_info;
            $data['client_info'] = $ci->Clients_model->get_one($data['proposal_info']->client_id);
            $data['proposal_items'] = $ci->Proposal_items_model->get_details(array("proposal_id" => $proposal_id))->getResult();
            $data["proposal_total_summary"] = $ci->Proposals_model->get_proposal_total_summary($proposal_id);

            $data['total_read_count'] = $ci->Event_tracker_model->total_read_count(array("context" => "proposal", "context_id" => $proposal_id));

            $data['proposal_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "proposals", "show_in_proposal" => true, "related_to_id" => $proposal_id))->getResult();
            return $data;
        }
    }
}

/**
 * get all data to make an order
 * 
 * @param Int $order_id
 * @return array
 */
if (!function_exists('get_order_making_data')) {

    function get_order_making_data($order_id = 0, $cookie_hash = "") {
        $ci = new Security_Controller(false);
        $data = array();
        if ($order_id) {
            $order_info = $ci->Orders_model->get_details(array("id" => $order_id))->getRow();
            if ($order_info) {
                $data['order_info'] = $order_info;
                $data['client_info'] = $ci->Clients_model->get_one($data['order_info']->client_id);
                $data['order_items'] = $ci->Order_items_model->get_details(array("order_id" => $order_id))->getResult();
                $data["order_total_summary"] = $ci->Orders_model->get_order_total_summary($order_id);

                $data['order_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "orders", "show_in_order" => true, "related_to_id" => $order_id))->getResult();
                return $data;
            }
        } else {
            //order total when it's in cart 
            //count all items of login user (client)
            $login_user_id = isset($ci->login_user->id) ? $ci->login_user->id : 0;
            $data["order_total_summary"] = $ci->Orders_model->get_processing_order_total_summary($login_user_id, $cookie_hash);
        }
        return $data;
    }
}


/**
 * get team members and teams select2 dropdown data list
 * 
 * @return array
 */
if (!function_exists('get_team_members_and_teams_select2_data_list')) {

    function get_team_members_and_teams_select2_data_list($exclude_inactive_users = false, $hide_team_members_list_user_id = 0) {
        $ci = new Security_Controller(false);

        $users_options = array("deleted" => 0, "user_type" => "staff");
        if ($exclude_inactive_users) {
            $users_options["status"] = "active";
        }

        if ($hide_team_members_list_user_id) {
            $users_options["id"] = $hide_team_members_list_user_id;
        }

        if ($ci->login_user->user_type === "client") {
            $client_message_users = get_setting("client_message_users");
            $client_message_users = explode(",", $client_message_users);
            if ($client_message_users) {
                $users_options["where_in"] = array("id" => $client_message_users);
            }
        }

        $team_members = $ci->Users_model->get_all_where($users_options)->getResult();
        $members_and_teams_dropdown = array();

        foreach ($team_members as $team_member) {
            $members_and_teams_dropdown[] = array("type" => "member", "id" => "member:" . $team_member->id, "text" => $team_member->first_name . " " . $team_member->last_name);
        }

        if ($ci->login_user->user_type === "staff") {
            $team = $ci->Team_model->get_all_where(array("deleted" => 0))->getResult();
            foreach ($team as $team) {
                $members_and_teams_dropdown[] = array("type" => "team", "id" => "team:" . $team->id, "text" => $team->title);
            }
        }

        return $members_and_teams_dropdown;
    }
}



/**
 * submit data for notification
 * 
 * @return array
 */
if (!function_exists('log_notification')) {

    function log_notification($event, $options = array(), $user_id = 0) {
        $ci = new Security_Controller(false);

        //send response to notification processor
        if (get_setting("log_direct_notifications")) {
            //send direct notification to the url
            $data = array(
                "event" => encode_id($event, "notification")
            );

            if ($user_id) {
                $data["user_id"] = $user_id;
            } else if ($user_id === "0") {
                $data["user_id"] = $user_id; //if user id is 0 (string) we'll assume that it's system bot 
            } else if (isset($ci->login_user->id)) {
                $data["user_id"] = $ci->login_user->id;
            }

            foreach ($options as $key => $value) {
                if ($value) {
                    $value = urlencode($value);
                }
                $data[$key] = $value;
            }

            $notification_processor = new Notification_processor();
            $notification_processor->create_notification($data);
        } else {
            //use curl to send request
            $url = get_uri("notification_processor/create_notification");

            $req = "event=" . encode_id($event, "notification");

            if ($user_id) {
                $req .= "&user_id=" . $user_id;
            } else if ($user_id === "0") {
                $req .= "&user_id=" . $user_id; //if user id is 0 (string) we'll assume that it's system bot 
            } else if (isset($ci->login_user->id)) {
                $req .= "&user_id=" . $ci->login_user->id;
            }


            foreach ($options as $key => $value) {
                if ($value) {
                    $value = urlencode($value);
                }

                $req .= "&$key=$value";
            }


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);

            if (get_setting("add_useragent_to_curl")) {
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:19.0) Gecko/20100101 Firefox/19.0");
            }

            curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                log_message('error', 'CURL error on log_notification: ' . $error_msg);
            }
            curl_close($ch);
        }
    }
}


/**
 * save custom fields for any context
 * 
 * @param Int $estimate_id
 * @return array
 */
if (!function_exists('save_custom_fields')) {

    function save_custom_fields($related_to_type, $related_to_id, $is_admin = 0, $user_type = "", $activity_log_id = 0, $save_to_related_type = "", $user_id = 0) {
        $ci = new App_Controller();
        $request = \Config\Services::request();

        $custom_fields = $ci->Custom_fields_model->get_combined_details($related_to_type, $related_to_id, $is_admin, $user_type)->getResult();

        // we have to update the activity logs table according to the changes of custom fields
        $changes = array();

        //for migration, we've to save on new related type
        if ($save_to_related_type) {
            $related_to_type = $save_to_related_type;
        }

        //save custom fields
        foreach ($custom_fields as $field) {
            $field_name = "custom_field_" . $field->id;

            //client can't edit the field value if the option is active
            if ($user_type == "client" && $field->disable_editing_by_clients) {
                continue; //skip to the next loop
            }

            //to get the custom field values for per users from the same page, we've to use the user id
            if ($user_id) {
                $field_name .= "_" . $user_id;
            }

            //save only submitted fields
            if (array_key_exists($field_name, $_POST)) {
                $value = $request->getPost($field_name);

                if ($field->field_type === "time" && get_setting("time_format") !== "24_hours") {
                    //convert to 24hrs time format
                    $value = convert_time_to_24hours_format($value);
                }

                $field_value_data = array(
                    "related_to_type" => $related_to_type,
                    "related_to_id" => $related_to_id,
                    "custom_field_id" => $field->id,
                    "value" => $value
                );

                $field_value_data = clean_data($field_value_data);

                $save_data = $ci->Custom_field_values_model->upsert($field_value_data, $save_to_related_type);

                if ($save_data) {
                    $changed_values = get_array_value($save_data, "changes");
                    $field_title = get_array_value($changed_values, "title");
                    $field_type = get_array_value($changed_values, "field_type");
                    $visible_to_admins_only = get_array_value($changed_values, "visible_to_admins_only");
                    $hide_from_clients = get_array_value($changed_values, "hide_from_clients");

                    //add changes of custom fields
                    if (get_array_value($save_data, "operation") == "update") {
                        //update
                        $changes[$field_title . "[:" . $field->id . "," . $field_type . "," . $visible_to_admins_only . "," . $hide_from_clients . ":]"] = array("from" => get_array_value($changed_values, "from"), "to" => get_array_value($changed_values, "to"));
                    } else if (get_array_value($save_data, "operation") == "insert") {
                        //insert
                        $changes[$field_title . "[:" . $field->id . "," . $field_type . "," . $visible_to_admins_only . "," . $hide_from_clients . ":]"] = array("from" => "", "to" => $value);
                    }
                }
            }
        }

        //finally save the changes to activity logs table
        return update_custom_fields_changes($related_to_type, $related_to_id, $changes, $activity_log_id);
    }
}

/**
 * update custom fields changes to activity logs table
 */
if (!function_exists('update_custom_fields_changes')) {

    function update_custom_fields_changes($related_to_type, $related_to_id, $changes, $activity_log_id = 0) {
        if ($changes && count($changes)) {
            $ci = new App_Controller();

            $related_to_data = new \stdClass();

            $log_type = "";
            $log_for = "";
            $log_type_title = "";
            $log_for_id = "";

            if ($related_to_type == "tasks") {
                $related_to_data = $ci->Tasks_model->get_one($related_to_id);
                $log_type = "task";
                $log_for = "project";
                $log_type_title = $related_to_data->title;
                $log_for_id = $related_to_data->project_id;
            }

            $log_data = array(
                "action" => "updated",
                "log_type" => $log_type,
                "log_type_title" => $log_type_title,
                "log_type_id" => $related_to_id,
                "log_for" => $log_for,
                "log_for_id" => $log_for_id
            );

            if ($activity_log_id) {
                $before_changes = array();

                //we have to combine with the existing changes of activity logs
                $activity_log = $ci->Activity_logs_model->get_one($activity_log_id);
                $activity_logs_changes = unserialize($activity_log->changes ? $activity_log->changes : "");
                if (is_array($activity_logs_changes)) {
                    foreach ($activity_logs_changes as $key => $value) {
                        $before_changes[$key] = array("from" => get_array_value($value, "from"), "to" => get_array_value($value, "to"));
                    }
                }

                $log_data["changes"] = serialize(array_merge($before_changes, $changes));

                if ($activity_log->action != "created") {
                    $ci->Activity_logs_model->update_where($log_data, array("id" => $activity_log_id));
                }
            } else {
                $log_data["changes"] = serialize($changes);
                return $ci->Activity_logs_model->ci_save($log_data);
            }
        }
    }
}


/**
 * use this to clean xss and html elements
 * the best practice is to use this before rendering 
 * but you can use this before saving for suitable cases
 *
 * @param string or array $data
 * @return clean $data
 */
if (!function_exists("clean_data")) {

    function clean_data($data) {
        $clean_data = new Clean_data();

        $data = $clean_data->xss_clean($data);
        $disable_html_input = get_setting("disable_html_input");

        if ($disable_html_input == "1") {
            $data = $clean_data->html_escape($data);
        }

        return $data;
    }
}


//return site logo
if (!function_exists("get_logo_url")) {

    function get_logo_url() {
        return get_file_from_setting("site_logo");
    }
}

//get logo from setting
if (!function_exists("get_file_from_setting")) {

    function get_file_from_setting($setting_name = "", $only_file_path_with_slash = false, $file_path = "") {

        if ($setting_name) {
            $setting_value = get_setting($setting_name);
            if ($setting_value) {
                $file_path = $file_path ? $file_path : get_setting("system_file_path");

                $file = @unserialize($setting_value);
                if (is_array($file)) {

                    //show full size thumbnail for signin page background
                    $show_full_size_thumbnail = false;
                    if ($setting_name == "signin_page_background" || $setting_name == "banner_image_on_public_store") {
                        $show_full_size_thumbnail = true;
                    }

                    return get_source_url_of_file($file, $file_path, "thumbnail", $only_file_path_with_slash, $only_file_path_with_slash, $show_full_size_thumbnail);
                } else {
                    if ($only_file_path_with_slash) {
                        return "/" . ($file_path . $setting_value);
                    } else {
                        return get_file_uri($file_path . $setting_value);
                    }
                }
            }
        }
    }
}

//get site favicon
if (!function_exists("get_favicon_url")) {

    function get_favicon_url() {
        $favicon_from_setting = get_file_from_setting('favicon');
        return $favicon_from_setting ? $favicon_from_setting : get_file_uri("assets/images/favicon.png");
    }
}


//get color plate
if (!function_exists("get_custom_theme_color_list")) {

    function get_custom_theme_color_list() {
        //scan the css files for theme color and show a list
        try {
            $dir = getcwd() . '/assets/css/color/';
            $files = scandir($dir);
            if ($files && is_array($files)) {

                echo "<span class='color-tag clickable mr15 change-theme' data-color='F2F2F2' style='background:#F2F2F2'> </span>"; //default color

                foreach ($files as $file) {
                    if ($file != "." && $file != ".." && $file != "index.html" && $file != ".DS_Store") {
                        $color_code = str_replace(".css", "", $file);
                        echo "<span class='color-tag clickable mr15 change-theme' style='background:#$color_code' data-color='$color_code'> </span>";
                    }
                }
            }
        } catch (\Exception $exc) {
        }
    }
}
//make random string
if (!function_exists("make_random_string")) {

    function make_random_string($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }

        return $random_string;
    }
}

//add custom variable data
if (!function_exists("get_custom_variables_data")) {

    function get_custom_variables_data($related_to_type = "", $related_to_id = 0, $is_admin = 0) {
        if ($related_to_type && $related_to_id) {
            $variables_array = array();
            $ci = new Security_Controller(false);

            $options = array("related_to_type" => $related_to_type, "related_to_id" => $related_to_id);

            if ($related_to_type == "leads" || $related_to_type == "tasks") {
                $options["is_admin"] = $is_admin;
                $options["check_admin_restriction"] = true;
            }

            $Custom_field_values_model = model("App\Models\Custom_field_values_model");
            $values = $Custom_field_values_model->get_details($options)->getResult();

            foreach ($values as $value) {
                if ($value->template_variable_name && $value->value) {
                    $variables_array[$value->template_variable_name] = $value->value;
                } else if (($related_to_type == "leads" || $related_to_type == "tasks") && ($value->show_on_kanban_card && !($ci->login_user->user_type === "client" && $value->hide_from_clients)) && $value->value) {
                    $variables_array[] = array(
                        "custom_field_type" => $value->custom_field_type,
                        "custom_field_title" => $value->custom_field_title,
                        "value" => $value->value
                    );
                }
            }

            return $variables_array;
        }
    }
}

//make labels view data for different contexts
if (!function_exists("make_labels_view_data")) {

    function make_labels_view_data($labels_list = "", $clickable = false, $large = false, $extra_class = "") {
        $labels = "";

        if ($labels_list) {
            $labels_array = explode(":--::--:", $labels_list);

            foreach ($labels_array as $label) {
                if (!$label) {
                    continue;
                }

                $label_parts = explode("--::--", $label);

                $label_id = get_array_value($label_parts, 0);
                $label_title = get_array_value($label_parts, 1);
                $label_color = get_array_value($label_parts, 2);

                $clickable_class = $clickable ? "clickable" : "";
                $large_class = $large ? "large" : "";
                $comman = "";
                if ($labels) {
                    $comman = "<span class='hide'>,</span>";
                }

                $labels .= $comman . " <span class='mt0 badge $large_class $clickable_class $extra_class' style='background-color:$label_color;' title=" . app_lang("label") . ">" . $label_title . "</span>";
            }
        }

        return $labels;
    }
}

//get update task info anchor data
if (!function_exists("get_update_task_info_anchor_data")) {

    function get_update_task_info_anchor_data($model_info, $type = "", $can_edit_tasks = false, $extra_data = "", $extra_condition = false) {
        if ($model_info && $type) {

            $start_date = "<span class='text-off'>" . app_lang("add") . " " . app_lang("start_date") . "<span>";
            if ($model_info->start_date) {
                $start_date = format_to_date($model_info->start_date, false);
            }

            $start_time = ""; //don't show start time if don't have the start date
            if ($model_info->start_date) {
                $start_time = "<span class='text-off'>" . app_lang("add") . " " . app_lang("start_time") . "<span>";
                if (date("H:i:s", strtotime($model_info->start_date)) != "00:00:00") {
                    $start_time = format_to_time($model_info->start_date, false, true);
                }
            }

            $deadline = "<span class='text-off'>" . app_lang("add") . " " . app_lang("deadline") . "<span>";
            if ($model_info->deadline) {
                $deadline = format_to_date($model_info->deadline, false);
            }

            $end_time = ""; //don't show end time if don't have the deadline
            if ($model_info->deadline && !$model_info->deadline_milestone_title) {
                $end_time = "<span class='text-off'>" . app_lang("add") . " " . app_lang("end_time") . "<span>";
                if (date("H:i:s", strtotime($model_info->deadline)) != "00:00:00") {
                    $end_time = format_to_time($model_info->deadline, false, true);
                }
            }

            $labels = "<span class='text-off'>" . app_lang("add") . " " . app_lang("label") . "<span>";
            if ($model_info->labels) {
                $labels = $extra_data;
            }

            $collaborators = "<span class='text-off'>" . app_lang("add") . " " . app_lang("collaborators") . "<span>";
            if ($model_info->collaborators) {
                $collaborators = $extra_data;
            }

            if ($type == "status") {

                return $can_edit_tasks ? js_anchor($model_info->status_key_name ? app_lang($model_info->status_key_name) : $model_info->status_title, array('title' => "", "class" => "white-link", "data-id" => $model_info->id, "data-value" => $model_info->status_id, "data-act" => "update-task-info", "data-act-type" => "status_id", "data-modifier-group" => "task_info")) : ($model_info->status_key_name ? app_lang($model_info->status_key_name) : $model_info->status_title);
            } else if ($type == "milestone") {

                return $can_edit_tasks ? js_anchor($model_info->milestone_title ? $model_info->milestone_title : "<span class='text-off'>" . app_lang("add") . " " . app_lang("milestone") . "<span>", array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->milestone_id, "data-act" => "update-task-info", "data-act-type" => "milestone_id", "data-modifier-group" => "task_info")) : $model_info->milestone_title;
            } else if ($type == "user") {

                return ($can_edit_tasks && $extra_condition) ? js_anchor($model_info->assigned_to_user ? $model_info->assigned_to_user : "<span class='text-off'>" . app_lang("add") . " " . app_lang("assignee") . "<span>", array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->assigned_to, "data-act" => "update-task-info", "data-act-type" => "assigned_to", "data-modifier-group" => "task_info")) : $model_info->assigned_to_user;
            } else if ($type == "labels") {

                return $can_edit_tasks ? js_anchor($labels, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->labels, "data-act" => "update-task-info", "data-act-type" => "labels", "data-modifier-group" => "task_info")) : $extra_data;
            } else if ($type == "points") {

                return $can_edit_tasks ? js_anchor($model_info->points, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->points, "data-act" => "update-task-info", "data-act-type" => "points", "data-modifier-group" => "task_info")) : $model_info->points;
            } else if ($type == "collaborators") {

                return $can_edit_tasks ? js_anchor($collaborators, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->collaborators, "data-act" => "update-task-info", "data-act-type" => "collaborators", "data-modifier-group" => "task_info")) : $extra_data;
            } else if ($type == "start_date") {

                return $can_edit_tasks ? js_anchor($start_date, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->start_date ? date("Y-m-d", strtotime($model_info->start_date)) : "", "data-act" => "update-task-info", "data-act-type" => "start_date", "data-modifier-group" => "task_info")) : format_to_date($model_info->start_date, false);
            } else if ($type == "start_time") {

                return $can_edit_tasks ? js_anchor($start_time, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => format_to_time($model_info->start_date, false), "data-act" => "update-task-info", "data-act-type" => "start_time", "data-modifier-group" => "task_info")) : format_to_time($model_info->start_date, false);
            } else if ($type == "deadline") {

                return $can_edit_tasks ? js_anchor($deadline, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->deadline ? date("Y-m-d", strtotime($model_info->deadline)) : "", "data-act" => "update-task-info", "data-act-type" => "deadline", "data-modifier-group" => "task_info")) : format_to_date($model_info->deadline, false);
            } else if ($type == "end_time") {

                return $can_edit_tasks ? js_anchor($end_time, array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => format_to_time($model_info->deadline, false), "data-act" => "update-task-info", "data-act-type" => "end_time", "data-modifier-group" => "task_info")) : format_to_time($model_info->deadline, false);
            } else if ($type == "priority") {

                $priority = "<span class='sub-task-icon priority-badge' style='background: $model_info->priority_color'><i data-feather='$model_info->priority_icon' class='icon-14'></i></span> $model_info->priority_title";
                return $can_edit_tasks ? js_anchor($model_info->priority_id ? $priority : "<span class='text-off'>" . app_lang("add") . " " . app_lang("priority") . "<span>", array('title' => "", "class" => "", "data-id" => $model_info->id, "data-value" => $model_info->priority_id, "data-act" => "update-task-info", "data-act-type" => "priority_id", "data-modifier-group" => "task_info")) : ($model_info->priority_id ? $priority : "");
            }
        }
    }
}

if (!function_exists('get_lead_contact_profile_link')) {

    function get_lead_contact_profile_link($id = 0, $name = "", $attributes = array()) {
        return anchor("leads/contact_profile/" . $id, $name, $attributes);
    }
}

if (!function_exists('decode_password')) {

    function decode_password($data = "", $salt = "") {
        if ($data && $salt) {
            if (strlen($data) > 100) {
                //encoded data with encode_id
                //return with decode
                return decode_id($data, $salt);
            } else {
                //old data, return as is
                return $data;
            }
        }
    }
}

if (!function_exists('validate_invoice_verification_code')) {

    function validate_invoice_verification_code($code = "", $given_invoice_data = array()) {
        if ($code) {
            if (strlen($code) !== 10) {
                return false;
            }

            $Verification_model = model("App\Models\Verification_model");
            $options = array("code" => $code, "type" => "invoice_payment");
            $verification_info = $Verification_model->get_details($options)->getRow();

            if ($verification_info && $verification_info->id) {
                $existing_invoice_data = unserialize($verification_info->params);

                //existing data
                $existing_invoice_id = get_array_value($existing_invoice_data, "invoice_id");
                $existing_client_id = get_array_value($existing_invoice_data, "client_id");
                $existing_contact_id = get_array_value($existing_invoice_data, "contact_id");

                //given data 
                $given_invoice_id = get_array_value($given_invoice_data, "invoice_id");
                $given_client_id = get_array_value($given_invoice_data, "client_id");
                $given_contact_id = get_array_value($given_invoice_data, "contact_id");

                if ($existing_invoice_id === $given_invoice_id && $existing_client_id === $given_client_id && $existing_contact_id === $given_contact_id) {
                    return true;
                }
            }
        }
    }
}

if (!function_exists('send_message_via_pusher')) {

    function send_message_via_pusher($to_user_id, $message_data, $message_id, $message_type = "message") {

        $pusher_connect = new Pusher_connect();
        if (!$pusher_connect->is_channel_ready()) {
            return false;
        }

        if ($message_type == "message") {
            //send message
            $data = array(
                "message" => $message_data
            );

            if ($pusher_connect->trigger_channel_event('user_' . $to_user_id . '_message_id_' . $message_id . '_channel', 'rise-chat-event', $data)) {
                return true;
            }
        } else {
            //send typing indicator
            $ci = new Security_Controller(false);
            $message_info = $ci->Messages_model->get_one($message_id);

            $user_info = $ci->Users_model->get_one($ci->login_user->id);
            $avatar = " <img alt='...' src='" . get_avatar($user_info->image) . "' class='dark strong' /> ";

            $message_data = array(
                "<div class='chat-other'>
                            <div class='row'>
                                <div class='col-md-12'>
                                    <div class='avatar-xs avatar mr10'>" . $avatar . "</div>
                                    <div class='chat-msg typing-indicator' data-message_id='$message_info->id'>" . app_lang("typing") . "<span></span><span></span><span></span></div>
                                </div>
                            </div>
                        </div>"
            );

            if ($pusher_connect->trigger_channel_event('user_' . $to_user_id . '_message_id_' . $message_id . '_channel', 'rise-chat-typing-event', $message_data)) {
                return true;
            }
        }
    }
}

if (!function_exists('can_access_messages_module')) {

    function can_access_messages_module() {
        $ci = new Security_Controller(false);

        $can_chat = false;

        $client_message_users = get_setting("client_message_users");
        $client_message_users_array = explode(",", $client_message_users);

        if (($ci->login_user->user_type === "staff" && ($ci->login_user->is_admin || get_array_value($ci->login_user->permissions, "message_permission") !== "no" || in_array($ci->login_user->id, $client_message_users_array))) || ($ci->login_user->user_type === "client" && $client_message_users)) {
            $can_chat = true;
        }

        return $can_chat;
    }
}

if (!function_exists('add_auto_reply_to_ticket')) {

    function add_auto_reply_to_ticket($ticket_id = 0) {
        $auto_reply_to_tickets = get_setting("auto_reply_to_tickets");
        $auto_reply_to_tickets_message = get_setting('auto_reply_to_tickets_message');

        if (!($ticket_id && $auto_reply_to_tickets && $auto_reply_to_tickets_message)) {
            return false;
        }

        $now = get_current_utc_time();
        $Ticket_comments_model = model("App\Models\Ticket_comments_model");

        $data = array(
            "description" => $auto_reply_to_tickets_message,
            "created_by" => 999999999, //because there will be 0 for imap ticket's comments too
            "created_at" => $now,
            "ticket_id" => $ticket_id,
            "files" => "a:0:{}"
        );

        $data = clean_data($data);
        $comment_id = $Ticket_comments_model->ci_save($data);

        //send notification
        if ($comment_id) {
            log_notification("ticket_commented", array("ticket_id" => $ticket_id, "ticket_comment_id" => $comment_id), "0");
        }
    }
}

/**
 * redirect to a location within the app
 * 
 * @param string $url
 * @return void
 */
if (!function_exists('app_redirect')) {

    function app_redirect($url, $global_link = false) {
        if ($global_link) {
            header("Location:$url");
        } else {
            header("Location:" . get_uri($url));
        }
        exit;
    }
}

if (!function_exists('app_lang')) {

    function app_lang($lang = "") {
        if (!$lang) {
            return false;
        }

        //first check if the key is exists in custom lang
        $language_result = lang("custom_lang.$lang");
        if ($language_result === "custom_lang.$lang") {
            //this key doesn't exists in custom language, get from default language
            $language_result = lang("default_lang.$lang");
        }

        return $language_result;
    }
}

/**
 * show 404 error page
 * 
 * @return void
 */
if (!function_exists('show_404')) {

    function show_404() {
        echo view("errors/html/error_404");
        exit();
    }
}

/**
 * get all data to make an contract
 * 
 * @param contract making data $contract_data
 * @return array
 */
if (!function_exists('prepare_contract_view')) {

    function prepare_contract_view($contract_data) {
        if ($contract_data) {
            $contract_info = get_array_value($contract_data, "contract_info");
            $contract_total_summary = get_array_value($contract_data, "contract_total_summary");

            $parser_data = array();

            $parser_data["CONTRACT_ID"] = get_contract_id($contract_info->id);
            $parser_data["CONTRACT_TITLE"] = $contract_info->title;
            $parser_data["CONTRACT_DATE"] = format_to_date($contract_info->contract_date, false);
            $parser_data["CONTRACT_EXPIRY_DATE"] = format_to_date($contract_info->valid_until, false);
            $parser_data["CONTRACT_ITEMS"] = view("contracts/contract_parts/contract_items_table", $contract_data);
            $parser_data["CONTRACT_SUBTOTAL"] = to_currency($contract_total_summary->contract_subtotal, $contract_total_summary->currency_symbol);
            $parser_data["CONTRACT_DISCOUNT"] = to_currency($contract_total_summary->discount_total, $contract_total_summary->currency_symbol);
            $parser_data["CONTRACT_TOTAL_AFTER_DISCOUNT"] = to_currency($contract_total_summary->contract_subtotal - $contract_total_summary->discount_total, $contract_total_summary->currency_symbol);
            $parser_data["CONTRACT_TOTAL"] = to_currency($contract_total_summary->contract_total, $contract_total_summary->currency_symbol);
            $parser_data["CONTRACT_NOTE"] = $contract_info->note;
            $parser_data["APP_TITLE"] = get_setting("app_title");
            $parser_data["PROJECT_TITLE"] = $contract_info->project_title;

            $signer_info = @unserialize($contract_info->meta_data);
            if (!($signer_info && is_array($signer_info))) {
                $signer_info = array();
            }

            if ($contract_info->status === "accepted" && ($signer_info || $contract_info->accepted_by)) {
                $parser_data["CLIENT_SIGNER_NAME"] = $contract_info->accepted_by ? $contract_info->signer_name : get_array_value($signer_info, "name");
                $parser_data["CLIENT_SIGNER_EMAIL"] = $contract_info->signer_email ? $contract_info->signer_email : get_array_value($signer_info, "email");

                if (get_array_value($signer_info, "signed_date")) {
                    $parser_data["CLIENT_SIGNING_DATE"] = format_to_relative_time(get_array_value($signer_info, "signed_date"));
                } else {
                    $parser_data["CLIENT_SIGNING_DATE"] = "";
                }

                if (get_array_value($signer_info, "signature")) {
                    $signature_file = @unserialize(get_array_value($signer_info, "signature"));
                    $signature_file_name = get_array_value($signature_file, "file_name");
                    $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
                    $parser_data["CLIENT_SIGNATURE"] = '<img class="signature-image" src="' . $signature_file . '" alt="' . $signature_file_name . '" />';
                } else {
                    $parser_data["CLIENT_SIGNATURE"] = "";
                }
            } else {
                $parser_data["CLIENT_SIGNER_NAME"] = "";
                $parser_data["CLIENT_SIGNER_EMAIL"] = "";
                $parser_data["CLIENT_SIGNING_DATE"] = "";
                $parser_data["CLIENT_SIGNATURE"] = "";
            }

            if ($contract_info->staff_signed_by) {
                $Users_model = model("App\Models\Users_model");
                $staff_signer_info = $Users_model->get_one($contract_info->staff_signed_by);
                $parser_data["STAFF_SIGNER_NAME"] = $staff_signer_info->first_name . " " . $staff_signer_info->last_name;
                $parser_data["STAFF_SIGNER_EMAIL"] = $staff_signer_info->email;
                $parser_data["STAFF_SIGNING_DATE"] = format_to_relative_time(get_array_value($signer_info, "staff_signed_date"));

                if (get_array_value($signer_info, "staff_signature")) {
                    $signature_file = @unserialize(get_array_value($signer_info, "staff_signature"));
                    $signature_file_name = get_array_value($signature_file, "file_name");
                    $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
                    $parser_data["STAFF_SIGNATURE"] = '<img class="signature-image" src="' . $signature_file . '" alt="' . $signature_file_name . '" />';
                } else {
                    $parser_data["STAFF_SIGNATURE"] = "";
                }
            } else {
                $parser_data["STAFF_SIGNER_NAME"] = "";
                $parser_data["STAFF_SIGNER_EMAIL"] = "";
                $parser_data["STAFF_SIGNING_DATE"] = "";
                $parser_data["STAFF_SIGNATURE"] = "";
            }

            $parser_data["COMPANY_INFO"] = view("contracts/contract_parts/contract_from");

            $options = array("is_default" => true);
            if ($contract_info->company_id) {
                $options = array("id" => $contract_info->company_id);
            }

            $options["deleted"] = 0;

            $Company_model = model('App\Models\Company_model');
            $company_info = $Company_model->get_one_where($options);

            //show default company when any specific company isn't exists
            if ($contract_info->company_id && !$company_info->id) {
                $options = array("is_default" => true);
                $company_info = $Company_model->get_one_where($options);
            }

            $parser_data["COMPANY_NAME"] = $company_info->name;
            $parser_data["COMPANY_ADDRESS"] = nl2br($company_info->address ? $company_info->address : "");
            $parser_data["COMPANY_PHONE"] = $company_info->phone;
            $parser_data["COMPANY_EMAIL"] = $company_info->email;
            $parser_data["COMPANY_WEBSITE"] = $company_info->website;

            $client_info = get_array_value($contract_data, "client_info");
            $view_data["client_info"] = $client_info;
            $view_data["is_preview"] = true;
            $parser_data["CONTRACT_TO_INFO"] = view("contracts/contract_parts/contract_to", $view_data);
            $parser_data["CONTRACT_TO_COMPANY_NAME"] = $client_info->company_name;
            $parser_data["CONTRACT_TO_ADDRESS"] = $client_info->address;
            $parser_data["CONTRACT_TO_CITY"] = $client_info->city;
            $parser_data["CONTRACT_TO_STATE"] = $client_info->state;
            $parser_data["CONTRACT_TO_ZIP"] = $client_info->zip;
            $parser_data["CONTRACT_TO_COUNTRY"] = $client_info->country;
            $parser_data["CONTRACT_TO_VAT_NUMBER"] = $client_info->vat_number;

            //perse custom fields
            if (isset($contract_info->custom_fields) && $contract_info->custom_fields) {
                foreach ($contract_info->custom_fields as $field) {
                    $variable = "CF_" . $field->custom_field_id;
                    if ($field->value) {
                        $parser_data[$variable] = view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value));
                    } else {
                        $parser_data[$variable] = "";
                    }

                    // Add template variable support
                    if ($field->template_variable_name && $field->value) {
                        $parser_data[$field->template_variable_name] = $field->value;
                    }
                }
            }

            $parser = \Config\Services::parser();
            $content = remove_custom_field_titles_from_variables($contract_info->content);
            $contract_view = $parser->setData($parser_data)->renderString($content);
            $contract_view = htmlspecialchars_decode($contract_view);
            $contract_view = process_images_from_content($contract_view);

            return $contract_view;
        }
    }
}

if (!function_exists('remove_custom_field_titles_from_variables')) {

    function remove_custom_field_titles_from_variables($content) {
        preg_match_all('#\{CF_(.*?)\}#', $content, $matches);
        $custom_fields = get_array_value($matches, 1); //["1_Custom_field_x", "2_Custom_field_y"]

        $custom_field_new_variables = array();
        if ($custom_fields && count($custom_fields)) {
            foreach ($custom_fields as $custom_field) {
                $explode_custom_field = explode("_", $custom_field);
                $custom_field_new_variables[] = "{CF_" . get_array_value($explode_custom_field, 0) . "}";
            }
        }

        if ($custom_field_new_variables) {
            $content = preg_replace_callback('#\{CF_(.*?)\}#', function ($custom_fields) use (&$custom_field_new_variables) {
                return array_shift($custom_field_new_variables);
            }, $content);
        }

        return $content;
    }
}

if (!function_exists('get_available_contract_variables')) {

    function get_available_contract_variables() {
        $variables = array(
            "CONTRACT_ID",
            "CONTRACT_TITLE",
            "CONTRACT_DATE",
            "CONTRACT_EXPIRY_DATE",
            "CONTRACT_ITEMS",
            "CONTRACT_NOTE",
            "CONTRACT_SUBTOTAL",
            "CONTRACT_DISCOUNT",
            "CONTRACT_TOTAL_AFTER_DISCOUNT",
            "CONTRACT_TOTAL",
            "APP_TITLE",
            "PROJECT_TITLE",
            /* signature info */
            "CLIENT_SIGNER_NAME",
            "CLIENT_SIGNER_EMAIL",
            "CLIENT_SIGNING_DATE",
            "CLIENT_SIGNATURE",
            "STAFF_SIGNER_NAME",
            "STAFF_SIGNER_EMAIL",
            "STAFF_SIGNING_DATE",
            "STAFF_SIGNATURE",
            /* company info */
            "COMPANY_INFO",
            "COMPANY_NAME",
            "COMPANY_ADDRESS",
            "COMPANY_PHONE",
            "COMPANY_EMAIL",
            "COMPANY_WEBSITE",
            /* contract to info */
            "CONTRACT_TO_INFO",
            "CONTRACT_TO_COMPANY_NAME",
            "CONTRACT_TO_ADDRESS",
            "CONTRACT_TO_CITY",
            "CONTRACT_TO_STATE",
            "CONTRACT_TO_ZIP",
            "CONTRACT_TO_COUNTRY",
            "CONTRACT_TO_VAT_NUMBER",
        );

        //prepare custom fields
        $ci = new Security_Controller(false);
        $custom_fields = $ci->Custom_fields_model->get_combined_details("contracts", 0, $ci->login_user->is_admin, $ci->login_user->user_type)->getResult();
        if ($custom_fields) {
            foreach ($custom_fields as $custom_field) {
                if ($custom_field->show_in_contract) {
                    if ($custom_field->template_variable_name) {
                        $variable = $custom_field->template_variable_name;
                    } else {
                        $variable = "CF_" . $custom_field->id . "_" . str_replace(' ', '_', strtolower(trim($custom_field->title)));
                    }
                    $variables[] = $variable;
                }
            }
        }

        return $variables;
    }
}

if (!function_exists('get_db_prefix')) {

    function get_db_prefix() {
        $db = db_connect('default');
        return $db->getPrefix();
    }
}

/**
 * convert copied comment code to link 
 * @param string $text containing text with copied comment id brace
 * @param string $return_type indicates what to return (link or text)
 * @return text with link or link text
 */
if (!function_exists('convert_comment_link')) {

    function convert_comment_link($text = "", $convert_links = true) {
        preg_match_all('#\#\[(.*?)\]#', $text, $matches);
        $link_codes = get_array_value($matches, 1); //["20-73", "20-72"]
        $link_code_removed_text = preg_replace('#\#\[(.*?)\] #', "#", $text);
        preg_match_all('#\#\((.*?)\)#', $link_code_removed_text, $matches);
        $link_texts = get_array_value($matches, 1); //["this comment", "another comment"]

        $links = array();
        if ($link_codes && count($link_codes)) {
            foreach ($link_codes as $key => $link_code) {
                $explode_link_code = explode("-", $link_code);
                $comment_text = get_array_value($link_texts, $key);

                if ($convert_links) {
                    $context = get_array_value($explode_link_code, 0);
                    $context_id = get_array_value($explode_link_code, 1);

                    if ($context === "recording") {
                        $links[] = anchor("/#recording-" . $context_id, $comment_text ? $comment_text : "", array('class' => 'file-highlight-link', 'data-file-id' => $context_id));
                    } else {
                        //it's a task commment. 
                        $links[] = anchor(get_uri("tasks/view/" . $context . "/#comment-" . $context_id), $comment_text ? $comment_text : "", array('class' => 'comment-highlight-link', 'data-comment-id' => $context_id, 'data-task-id' => $context));
                    }
                } else {
                    $links[] = $comment_text;
                }
            }
        }

        if ($links) {
            $text = $link_code_removed_text;
        }

        //don't apply nl2br function since it'll be added through convert_mentions function
        if ($convert_links) {
            $text = link_it($text);
        }

        if ($links) {
            $text = preg_replace_callback('#\#\((.*?)\)#', function ($link_texts) use (&$links) {
                return array_shift($links);
            }, $text);
        }

        return $text;
    }
}

/**
 * get all data to make an proposal
 * 
 * @param proposal making data $proposal_data
 * @return array
 */
if (!function_exists('prepare_proposal_view')) {

    function prepare_proposal_view($proposal_data) {
        if ($proposal_data) {
            $proposal_info = get_array_value($proposal_data, "proposal_info");
            $proposal_total_summary = get_array_value($proposal_data, "proposal_total_summary");

            $parser_data = array();

            $parser_data["PROPOSAL_ID"] = get_proposal_id($proposal_info->id);
            $parser_data["PROPOSAL_DATE"] = format_to_date($proposal_info->proposal_date, false);
            $parser_data["PROPOSAL_EXPIRY_DATE"] = format_to_date($proposal_info->valid_until, false);
            $parser_data["PROPOSAL_ITEMS"] = view("proposals/proposal_parts/proposal_items_table", $proposal_data);
            $parser_data["PROPOSAL_SUBTOTAL"] = to_currency($proposal_total_summary->proposal_subtotal, $proposal_total_summary->currency_symbol);
            $parser_data["PROPOSAL_DISCOUNT"] = to_currency($proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol);
            $parser_data["PROPOSAL_TOTAL_AFTER_DISCOUNT"] = to_currency($proposal_total_summary->proposal_subtotal - $proposal_total_summary->discount_total, $proposal_total_summary->currency_symbol);
            $parser_data["PROPOSAL_TOTAL"] = to_currency($proposal_total_summary->proposal_total, $proposal_total_summary->currency_symbol);
            $parser_data["PROPOSAL_NOTE"] = $proposal_info->note;
            $parser_data["APP_TITLE"] = get_setting("app_title");

            $parser_data["COMPANY_INFO"] = view("proposals/proposal_parts/proposal_from");

            $options = array("is_default" => true);
            if ($proposal_info->company_id) {
                $options = array("id" => $proposal_info->company_id);
            }

            $options["deleted"] = 0;

            $Company_model = model('App\Models\Company_model');
            $company_info = $Company_model->get_one_where($options);

            //show default company when any specific company isn't exists
            if ($proposal_info->company_id && !$company_info->id) {
                $options = array("is_default" => true);
                $company_info = $Company_model->get_one_where($options);
            }

            $parser_data["COMPANY_NAME"] = $company_info->name;
            $parser_data["COMPANY_ADDRESS"] = nl2br($company_info->address ? $company_info->address : "");
            $parser_data["COMPANY_PHONE"] = $company_info->phone;
            $parser_data["COMPANY_EMAIL"] = $company_info->email;
            $parser_data["COMPANY_WEBSITE"] = $company_info->website;

            $client_info = get_array_value($proposal_data, "client_info");
            $view_data["client_info"] = $client_info;
            $view_data["is_preview"] = true;
            $parser_data["PROPOSAL_TO_INFO"] = view("proposals/proposal_parts/proposal_to", $view_data);
            $parser_data["PROPOSAL_TO_COMPANY_NAME"] = $client_info->company_name;
            $parser_data["PROPOSAL_TO_ADDRESS"] = $client_info->address;
            $parser_data["PROPOSAL_TO_CITY"] = $client_info->city;
            $parser_data["PROPOSAL_TO_STATE"] = $client_info->state;
            $parser_data["PROPOSAL_TO_ZIP"] = $client_info->zip;
            $parser_data["PROPOSAL_TO_COUNTRY"] = $client_info->country;
            $parser_data["PROPOSAL_TO_VAT_NUMBER"] = $client_info->vat_number;

            //perse custom fields
            if (isset($proposal_info->custom_fields) && $proposal_info->custom_fields) {
                foreach ($proposal_info->custom_fields as $field) {
                    $variable = "CF_" . $field->custom_field_id;
                    if ($field->value) {
                        $parser_data[$variable] = view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value));
                    } else {
                        $parser_data[$variable] = "";
                    }

                    // Add template variable support
                    if ($field->template_variable_name && $field->value) {
                        $parser_data[$field->template_variable_name] = $field->value;
                    }
                }
            }

            $parser = \Config\Services::parser();
            $content = remove_custom_field_titles_from_variables($proposal_info->content);
            $proposal_view = $parser->setData($parser_data)->renderString($content);
            $proposal_view = htmlspecialchars_decode($proposal_view);
            $proposal_view = process_images_from_content($proposal_view);

            return $proposal_view;
        }
    }
}

if (!function_exists('get_available_proposal_variables')) {

    function get_available_proposal_variables() {
        $grouped_variables = array(
            "proposal_info" => array(
                "PROPOSAL_ID",
                "PROPOSAL_DATE",
                "PROPOSAL_EXPIRY_DATE",
                "PROPOSAL_ITEMS",
                "PROPOSAL_NOTE",
                "PROPOSAL_SUBTOTAL",
                "PROPOSAL_DISCOUNT",
                "PROPOSAL_TOTAL_AFTER_DISCOUNT",
                "PROPOSAL_TOTAL"
            ),
            "company_info" => array(
                "APP_TITLE",
                "COMPANY_INFO",
                "COMPANY_NAME",
                "COMPANY_ADDRESS",
                "COMPANY_PHONE",
                "COMPANY_EMAIL",
                "COMPANY_WEBSITE"
            ),
            "proposal_to_info" => array(
                "PROPOSAL_TO_INFO",
                "PROPOSAL_TO_COMPANY_NAME",
                "PROPOSAL_TO_ADDRESS",
                "PROPOSAL_TO_CITY",
                "PROPOSAL_TO_STATE",
                "PROPOSAL_TO_ZIP",
                "PROPOSAL_TO_COUNTRY",
                "PROPOSAL_TO_VAT_NUMBER"
            ),
            "custom_fields" => array()
        );

        // Prepare custom fields
        $ci = new Security_Controller(false);
        $custom_fields = $ci->Custom_fields_model->get_combined_details("proposals", 0, $ci->login_user->is_admin, $ci->login_user->user_type)->getResult();
        if ($custom_fields) {
            foreach ($custom_fields as $custom_field) {
                if ($custom_field->show_in_proposal) {
                    if ($custom_field->template_variable_name) {
                        $variable = $custom_field->template_variable_name;
                    } else {
                        $variable = "CF_" . $custom_field->id . "_" . str_replace(' ', '_', strtolower(trim($custom_field->title, " ")));
                    }
                    $grouped_variables["custom_fields"][] = $variable;
                }
            }
        }

        return $grouped_variables;
    }
}

if (!function_exists('prepare_allowed_members_array')) {

    function prepare_allowed_members_array($permissions, $user_id) {
        $allowed_members = array($user_id);
        $allowed_teams = array();
        foreach ($permissions as $vlaue) {
            $permission_on = explode(":", $vlaue);
            $type = get_array_value($permission_on, "0");
            $type_value = get_array_value($permission_on, "1");
            if ($type === "member") {
                array_push($allowed_members, $type_value);
            } else if ($type === "team") {
                array_push($allowed_teams, $type_value);
            }
        }


        if (count($allowed_teams)) {
            $team_model = model("App\Models\Team_model");
            $team = $team_model->get_members($allowed_teams)->getResult();

            foreach ($team as $value) {
                if ($value->members) {
                    $members_array = explode(",", $value->members);
                    $allowed_members = array_merge($allowed_members, $members_array);
                }
            }
        }

        return $allowed_members;
    }
}

/**
 * 
 * get contract number
 * @param Int $contract_id
 * @return string
 */
if (!function_exists('get_contract_id')) {

    function get_contract_id($contract_id) {
        $prefix = get_setting("contract_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("contract")) . " #";
        return $prefix . $contract_id;
    }
}

if (!function_exists('get_default_company_id')) {

    function get_default_company_id() {
        $Company_model = model('App\Models\Company_model');
        return $Company_model->get_details(array("is_default" => true))->getRow()->id;
    }
}

/**
 * 
 * get subscription number
 * @param Int $subscription_id
 * @return string
 */
if (!function_exists('get_subscription_id')) {

    function get_subscription_id($subscription_id) {
        $prefix = get_setting("subscription_prefix");
        $prefix = $prefix ? $prefix : strtoupper(app_lang("subscription")) . " #";
        return $prefix . $subscription_id;
    }
}

/**
 * get all data to make an subscription
 * 
 * @param Int $subscription_id
 * @return array
 */
if (!function_exists('get_subscription_making_data')) {

    function get_subscription_making_data($subscription_id) {
        $ci = new App_Controller();
        $subscription_info = $ci->Subscriptions_model->get_details(array("id" => $subscription_id))->getRow();
        if ($subscription_info) {
            $data['subscription_info'] = $subscription_info;
            $data['client_info'] = $ci->Clients_model->get_one($data['subscription_info']->client_id);
            $data['subscription_items'] = $ci->Subscription_items_model->get_details(array("subscription_id" => $subscription_id))->getResult();
            $data['subscription_status_label'] = get_subscription_status_label($subscription_info, true, "rounded-pill large");
            $data['subscription_type_label'] = get_subscription_type_label($subscription_info);
            $data["subscription_total_summary"] = $ci->Subscriptions_model->get_subscription_total_summary($subscription_id);

            $data['subscription_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "subscriptions", "show_in_subscription" => true, "related_to_id" => $subscription_id))->getResult();
            $data['client_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_subscription" => true, "related_to_id" => $data['subscription_info']->client_id))->getResult();
            return $data;
        }
    }
}


/**
 * return a colorful label according to subscription status
 * 
 * @param Object $subscription_info
 * @return html
 */
if (!function_exists('get_subscription_status_label')) {

    function get_subscription_status_label($subscription_info, $return_html = true, $extra_classes = "") {
        $ci = new Security_Controller(false);
        $subscription_status_class = "bg-secondary";
        $status = "draft";

        if ($subscription_info->status == "pending") {
            if ($ci->login_user->user_type == "client") {
                $subscription_status_class = "bg-warning";
                $status = "new";
            } else {
                $subscription_status_class = "bg-primary";
                $status = "pending";
            }
        } else if ($subscription_info->status == "active") {
            $subscription_status_class = "bg-success";
            $status = "active";
        } else if ($subscription_info->status == "cancelled") {
            $subscription_status_class = "bg-danger";
            $status = "cancelled";
        }

        $subscription_status = "<span class='mt0 badge $subscription_status_class $extra_classes'>" . app_lang($status) . "</span>";
        if ($return_html) {
            return $subscription_status;
        } else {
            return $status;
        }
    }
}

if (!function_exists('create_invoice_from_subscription')) {

    function create_invoice_from_subscription($subscription_id) {
        $ci = new App_Controller();
        $subscription_info = $ci->Subscriptions_model->get_one($subscription_id);

        $bill_date = get_my_local_time("Y-m-d");

        if ($subscription_info) {

            //update the subscription
            $no_of_cycles_completed = $subscription_info->no_of_cycles_completed + 1;
            if (!$no_of_cycles_completed) {
                $no_of_cycles_completed = 1;
            }

            $next_recurring_date = add_period_to_date($bill_date, $subscription_info->repeat_every, $subscription_info->repeat_type);

            $subscription_data = array(
                "next_recurring_date" => $next_recurring_date,
                "no_of_cycles_completed" => $no_of_cycles_completed
            );

            $ci->Subscriptions_model->ci_save($subscription_data, $subscription_info->id);
        }

        $due_date_days = get_setting("default_due_date_after_billing_date");
        if (!$due_date_days) {
            $due_date_days = 14;
        }

        $due_date = add_period_to_date($bill_date, $due_date_days);

        $_invoice_data = array(
            "client_id" => $subscription_info->client_id,
            "bill_date" => $bill_date,
            "due_date" => $due_date,
            "note" => $subscription_info->note,
            "status" => "not_paid",
            "tax_id" => $subscription_info->tax_id,
            "tax_id2" => $subscription_info->tax_id2,
            "subscription_id" => $subscription_info->id,
            "company_id" => $subscription_info->company_id
        );

        $invoice_data = array_merge($_invoice_data, prepare_invoice_display_id_data($due_date, $bill_date));

        //create new invoice
        $new_invoice_id = $ci->Invoices_model->ci_save($invoice_data);

        //create invoice items
        $items = $ci->Subscription_items_model->get_details(array("subscription_id" => $subscription_info->id))->getResult();
        foreach ($items as $item) {
            //create invoice items for new invoice
            $new_invoice_item_data = array(
                "title" => $item->title,
                "description" => $item->description,
                "quantity" => $item->quantity,
                "unit_type" => $item->unit_type,
                "rate" => $item->rate,
                "total" => $item->total,
                "invoice_id" => $new_invoice_id,
                "taxable" => 1,
                "item_id" => $item->item_id
            );
            $ci->Invoice_items_model->ci_save($new_invoice_item_data);
        }
        $ci->Invoices_model->update_invoice_total_meta($new_invoice_id);

        return $new_invoice_id;
    }
}

if (!function_exists('can_access_reminders_module')) {

    function can_access_reminders_module() {
        $ci = new Security_Controller();

        if (get_setting("module_reminder") && ($ci->login_user->user_type === "staff" || ($ci->login_user->user_type === "client" && get_setting("client_can_create_reminders")))) {
            return true;
        }
    }
}

if (!function_exists('show_clients_of_this_client_contact')) {

    function show_clients_of_this_client_contact($login_user, $show_icon = false) {
        $Users_model = model('App\Models\Users_model');
        $Clients_model = model('App\Models\Clients_model');
        $clients = $Users_model->get_other_clients_of_this_client_contact($login_user->email, $login_user->id)->getResult();

        if (count($clients)) {
            $view_data["clients"] = $clients;
            $view_data["login_user_company_name"] = $Clients_model->get_one($login_user->client_id)->company_name;
            $view_data["show_icon"] = $show_icon;
            echo view("clients/clients_dropdown_of_this_client_contact", $view_data);
        }
    }
}

if (!function_exists('append_server_side_filtering_commmon_params')) {

    function append_server_side_filtering_commmon_params($options = array()) {

        $request = \Config\Services::request();

        $server_side = $request->getPost("server_side");
        if ($server_side == "1") {
            $options["server_side"] = 1;
            $options["limit"] = $request->getPost("limit");

            if ($options["limit"] == -1) {
                $options["limit"] = 100; //max limit is 100 for serverside. 
            }

            $options["skip"] = $request->getPost("skip") ? $request->getPost("skip") : 0;
            $options["order_by"] = $request->getPost("order_by");

            $options["order_dir"] = $request->getPost("order_dir");

            // order by should be either ASC or DESC
            if ($options["order_by"] && $options["order_dir"] != "DESC") {
                $options["order_dir"] = "ASC";
            }

            $options["search_by"] = trim($request->getPost("search_by"));
        }

        return $options;
    }
}

if (!function_exists('get_reminder_context_info')) {

    function get_reminder_context_info($reminder_info) {
        $context_url = "";
        $context_icon = "";

        if ($reminder_info->lead_id) {
            $context_url = get_uri("leads/view/$reminder_info->lead_id");
            $context_icon = "layers";
        } else if ($reminder_info->client_id) {
            $context_url = get_uri("clients/view/$reminder_info->client_id");
            $context_icon = "briefcase";
        } else if ($reminder_info->task_id) {
            $context_url = get_uri("tasks/view/$reminder_info->task_id");
            $context_icon = "check-circle";
        } else if ($reminder_info->project_id) {
            $context_url = get_uri("projects/view/$reminder_info->project_id");
            $context_icon = "grid";
        } else if ($reminder_info->ticket_id) {
            $context_url = get_uri("tickets/view/$reminder_info->ticket_id");
            $context_icon = "life-buoy";
        } else if ($reminder_info->proposal_id) {
            $context_url = get_uri("proposals/view/$reminder_info->proposal_id");
            $context_icon = "file-text";
        } else if ($reminder_info->contract_id) {
            $context_url = get_uri("contracts/view/$reminder_info->contract_id");
            $context_icon = "file-plus";
        } else if ($reminder_info->subscription_id) {
            $context_url = get_uri("subscriptions/view/$reminder_info->subscription_id");
            $context_icon = "repeat";
        } else if ($reminder_info->invoice_id) {
            $context_url = get_uri("invoices/view/$reminder_info->invoice_id");
            $context_icon = "file-text";
        } else if ($reminder_info->order_id) {
            $context_url = get_uri("orders/view/$reminder_info->order_id");
            $context_icon = "shopping-cart";
        } else if ($reminder_info->estimate_id) {
            $context_url = get_uri("estimates/view/$reminder_info->estimate_id");
            $context_icon = "file";
        } else if ($reminder_info->related_user_id) {
            $context_url = get_uri("team_members/view/$reminder_info->related_user_id");
            $context_icon = "users";
        }

        return array(
            "context_url" => $context_url,
            "context_icon" => $context_icon
        );
    }
}

/**
 * return a colorful label according to estimate status
 * 
 * @param Object $estimate_info
 * @return html
 */
if (!function_exists('get_estimate_status_label')) {

    function get_estimate_status_label($estimate_info, $return_html = true, $extra_classes = "") {
        $ci = new Security_Controller(false);
        $estimate_status_class = "bg-secondary";

        //don't show sent status to client or public views, change the status to 'new' from 'sent'
        if ((isset($ci->login_user->id) && $ci->login_user->user_type == "client") || !isset($ci->login_user->id)) {
            if ($estimate_info->status == "sent") {
                $estimate_info->status = "new";
            } else if ($estimate_info->status == "declined") {
                $estimate_info->status = "rejected";
            }
        }

        if ($estimate_info->status == "draft") {
            $estimate_status_class = "bg-secondary";
        } else if ($estimate_info->status == "declined" || $estimate_info->status == "rejected") {
            $estimate_status_class = "bg-danger";
        } else if ($estimate_info->status == "accepted") {
            $estimate_status_class = "bg-success";
        } else if ($estimate_info->status == "sent") {
            $estimate_status_class = "bg-primary";
        } else if ($estimate_info->status == "new") {
            $estimate_status_class = "bg-warning";
        }

        $estimate_status = "<span class='mt0 badge $estimate_status_class $extra_classes'>" . app_lang($estimate_info->status) . "</span>";
        if ($return_html) {
            return $estimate_status;
        } else {
            return $estimate_info->status;
        }
    }
}

if (!function_exists('create_invoice_from_order')) {

    function create_invoice_from_order($order_id) {
        $ci = new Security_Controller(false);

        $order_info = $ci->Orders_model->get_one($order_id);
        $now = get_my_local_time("Y-m-d");

        $_invoice_data = array(
            "bill_date" => $now,
            "due_date" => $now,
            "client_id" => $order_info->client_id,
            "tax_id" => $order_info->tax_id,
            "tax_id2" => $order_info->tax_id2,
            "discount_amount" => $order_info->discount_amount,
            "discount_amount_type" => $order_info->discount_amount_type,
            "discount_type" => $order_info->discount_type,
            "company_id" => $order_info->company_id,
            "order_id" => $order_id, //save the order_id only to pay for the order, when we manually create invoice from order we don't save this
            "note" => $order_info->note,
            "status" => "not_paid"
        );

        $invoice_data = array_merge($_invoice_data, prepare_invoice_display_id_data($now, $now));

        $invoice_id = $ci->Invoices_model->ci_save($invoice_data);

        //copy items
        $order_items = $ci->Order_items_model->get_details(array("order_id" => $order_id))->getResult();
        foreach ($order_items as $data) {
            $invoice_item_data = array(
                "invoice_id" => $invoice_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
                "taxable" => 1,
                "item_id" => $data->item_id ? $data->item_id : 0
            );
            $ci->Invoice_items_model->ci_save($invoice_item_data);
        }
        $ci->Invoices_model->update_invoice_total_meta($invoice_id);
        return $invoice_id;
    }
}

/**
 * add preview on pasted images for rich text editor
 * @param string $text containing text with pasted images
 * @return text with clickable images
 */
if (!function_exists('process_images_from_content')) {

    function process_images_from_content($text = "", $add_preview = true) {
        if (!$text) {
            return "";
        }

        if (!$add_preview) {
            //send content to hook if there has any modification
            try {
                $text = app_hooks()->apply_filters('app_filter_process_images_from_content', $text);
            } catch (\Exception $ex) {
                log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            }

            return $text;
        }

        preg_match_all('/(<img[^>]+>)/i', $text, $matches);
        $image_tags = get_array_value($matches, 1); //image tags: <img href='' alt=''>

        $images = array();
        if ($image_tags && count($image_tags)) {
            foreach ($image_tags as $key => $image_tag) {

                //get image source url
                preg_match('/src="([^"]*)"/i', $image_tag, $matches);
                $source_url = get_array_value($matches, 1);

                //check if there has already an anchor tag surrounding this img tag
                //we also have to check the pasted-image class because there has static images somewhere like contract editor
                if (strpos($text, '<a href="' . $source_url . '" class="mfp-image"') === false && strpos($image_tag, 'class="pasted-image"') !== false) {
                    //anchor tag not exists and it's a pasted image
                    //get actual file name of image
                    preg_match('/alt="([^"]*)"/i', $image_tag, $matches);
                    $image_file_name = get_array_value($matches, 1);
                    $actual_file_name = remove_file_prefix($image_file_name);

                    //add mfp-image viewer anchor tag
                    $source_url = str_replace("=s700", "=s0", $source_url); //show full image on click;

                    $images[] = "<a href='$source_url' class='mfp-image' data-title='" . $actual_file_name . "'>$image_tag</a>";
                } else {
                    //anchor tag exists from before or anchor tag isn't necessary
                    $images[] = $image_tag;
                }
            }
        }

        if ($images) {
            $text = preg_replace_callback('/(<img[^>]+>)/i', function ($image_tags) use (&$images) {
                return array_shift($images);
            }, $text);
        }

        //send content to hook if there has any modification
        try {
            $text = app_hooks()->apply_filters('app_filter_process_images_from_content', $text);
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        return $text;
    }
}


/**
 * return a colorful label according to subscription type
 * 
 * @param Object $subscription_info
 * @return html
 */
if (!function_exists('get_subscription_type_label')) {

    function get_subscription_type_label($subscription_info, $return_html = true) {

        if ($subscription_info->type == "app") {
            $subscription_type_class = "bg-warning";
        } else {
            $subscription_type_class = "bg-primary";
        }

        $subscription_status = "<span class='mt0 badge $subscription_type_class'>" . app_lang($subscription_info->type) . "</span>";
        if ($return_html) {
            return $subscription_status;
        } else {
            return $subscription_info->type;
        }
    }
}


/**
 * 
 * get company logo
 * @param Int $company_id
 * @return string
 */
if (!function_exists('get_company_logo')) {

    function get_company_logo($company_id = 0, $type = "", $return_html = false) {

        $options = array("is_default" => true);
        if ($company_id) {
            $options = array("id" => $company_id);
        }

        $options["deleted"] = 0;

        $Company_model = model('App\Models\Company_model');
        $company_info = $Company_model->get_one_where($options);
        $only_file_path = get_setting('only_file_path');

        if (isset($company_info->logo) && $company_info->logo) {
            $file = unserialize($company_info->logo);
            if (is_array($file)) {
                $file = get_array_value($file, 0);

                if ($return_html) {
                    return "<img class='max-logo-size' src='" . get_source_url_of_file($file, get_setting('system_file_path'), 'thumbnail', $only_file_path, $only_file_path) . "' alt='...' />";
                } else {
?>
                    <img class="max-logo-size" src="<?php echo get_source_url_of_file($file, get_setting("system_file_path"), "thumbnail", $only_file_path, $only_file_path); ?>" alt="..." />
                <?php
                }
            }
        } else {
            $logo = $type . "_logo";
            if (!get_setting($logo)) {
                $logo = "invoice_logo";
            }

            if ($return_html) {
                return "<img class='max-logo-size' src='" . get_file_from_setting($logo, $only_file_path) . "' alt='...' />";
            } else {
                ?>
                <img class="max-logo-size" src="<?php echo get_file_from_setting($logo, $only_file_path); ?>" alt="..." />
<?php
            }
        }
    }
}



/**
 * get all project statuses text object
 * 
 * @return object
 */
if (!function_exists('get_project_status_text_info')) {

    function get_project_status_text_info() {
        $ci = new App_Controller();

        $info = new \stdClass();

        $info->open = 0;
        $info->completed = 0;
        $info->hold = 0;

        $project_statuses = $ci->Project_status_model->get_details()->getResult();
        foreach ($project_statuses as $status) {
            $text = $status->title;
            if ($status->title_language_key) {
                $text = app_lang($status->title_language_key);
            }

            if ($status->id == 1) {
                $info->open = $text;
            } else if ($status->id == 2) {
                $info->completed = $text;
            } else if ($status->id == 3) {
                $info->hold = $text;
            }
        }

        return $info;
    }
}


/**
 * check the client access permission
 * 
 * @return object
 */
if (!function_exists('can_client_access')) {

    function can_client_access($client_permissions, $menu_item, $check_module = true) {
        //get the array of hidden menu
        $hidden_client_menus = explode(",", get_setting("hidden_client_menus"));

        //get the client contact permissions
        $client_permissions_array = explode(",", $client_permissions);

        //check module availability 
        if ($check_module && get_setting("module_" . $menu_item) != "1") {
            return false;
        }

        if (!($menu_item == "store" || $menu_item == "knowledge_base")) {
            $menu_item = $menu_item . "s";
        }

        //check if the menu is hidden
        if ($hidden_client_menus && in_array($menu_item, $hidden_client_menus)) {
            return false;
        }

        //check contact permission
        if ($client_permissions_array && !(in_array("all", $client_permissions_array) || in_array($menu_item, $client_permissions_array))) {
            return false;
        }

        return true;
    }
}


if (!function_exists('get_first_letter')) {

    function get_first_letter($text = "") {
        if ($text) {
            return substr($text, 0, 1);
        }
    }
}


if (!function_exists('get_last_letter')) {

    function get_last_letter($text = "") {
        if ($text) {
            return substr($text, -1, 1);
        }
    }
}


// Preparing invoice display id based on settings
if (!function_exists('prepare_invoice_display_id_data')) {

    function prepare_invoice_display_id_data($invoice_due_date, $invoice_bill_date) {
        $ci = new App_Controller();

        $invoice_number_format = get_setting("invoice_number_format");

        $invoice_date = (get_setting("year_based_on") == "due_date") ? $invoice_due_date : $invoice_bill_date;
        $invoice_date_parts = explode('-', $invoice_date);
        $invoice_year = get_array_value($invoice_date_parts, 0);
        $invoice_month = get_array_value($invoice_date_parts, 1);

        // Define the variables used in the format
        $variables_map = [
            '{YEAR}' => $invoice_year,
            '{MONTH}' => $invoice_month,
            '{SERIAL}' => '',
            '{2_DIGIT_SERIAL}' => '',
            '{3_DIGIT_SERIAL}' => '',
            '{4_DIGIT_SERIAL}' => '',
            '{5_DIGIT_SERIAL}' => '',
            '{6_DIGIT_SERIAL}' => '',
        ];

        $last_sequence = $ci->Invoices_model->get_last_invoice_sequence();
        $invoice_number = (get_setting("initial_number_of_the_invoice") > ($last_sequence + 1)) ? get_setting("initial_number_of_the_invoice") : ($last_sequence + 1);

        if (get_setting("reset_invoice_number_every_year") && strpos($invoice_number_format, '{YEAR}') !== false) {
            $last_sequence = $ci->Invoices_model->get_last_invoice_sequence($invoice_year);
            $invoice_number = $last_sequence + 1;
        }

        $serial_variable_length = 0;
        foreach (['{SERIAL}', '{2_DIGIT_SERIAL}', '{3_DIGIT_SERIAL}', '{4_DIGIT_SERIAL}', '{5_DIGIT_SERIAL}', '{6_DIGIT_SERIAL}'] as $variable) {
            if ($variable == '{SERIAL}') {
                $variables_map[$variable] = $invoice_number;
            } else if (strpos($invoice_number_format, $variable) !== false) {
                $serial_variable_length = substr($variable, 1, 1); // Extract the number of digits from the variable

                $new_invoice_id = str_pad($invoice_number, $serial_variable_length, '0', STR_PAD_LEFT);
                $variables_map[$variable] = $new_invoice_id;
            }
        }

        $invoice_display_id = '';
        $remaining_format = $invoice_number_format;

        foreach ($variables_map as $variable => $value) {
            if (strpos($remaining_format, $variable) !== false) {
                // Replace the variable with its value in the remaining format
                $remaining_format = str_replace($variable, $value, $remaining_format);
            }
        }

        $invoice_display_id .= $remaining_format;

        $prefix = get_setting("invoice_prefix");
        $invoice_prefix = $prefix ? $prefix : strtoupper(app_lang("invoice")) . " #";

        $display_id = $invoice_prefix . $invoice_display_id;

        if (!(get_setting("reset_invoice_number_every_year") && strpos($invoice_number_format, '{YEAR}') !== false)) {
            $invoice_year = 0;
        }

        $invoice_data["display_id"] = $display_id;
        $invoice_data["number_year"] = $invoice_year;
        $invoice_data["number_sequence"] = $invoice_number;

        return $invoice_data;
    }
}


/**
 * get all data to make an proposal
 * 
 * @param Proposal making data $proposal_data
 * @return array
 */
if (!function_exists('prepare_proposal_pdf')) {

    function prepare_proposal_pdf($proposal_data, $mode = "download", $is_mobiel_preview = false) {
        if ($proposal_data) {
            $proposal_data["mode"] = clean_data($mode);

            $html = view("proposals/proposal_pdf", $proposal_data);

            if ($mode === "html") {
                return $html;
            }

            $proposal_info = get_array_value($proposal_data, "proposal_info");

            $pdf = new Pdf("proposal");
            return $pdf->PreparePDF($html, get_proposal_id($proposal_info->id), $mode, $is_mobiel_preview);
        }
    }
}


/**
 * get all data to make an contract
 * 
 * @param Contract making data $contract_data
 * @return array
 */
if (!function_exists('prepare_contract_pdf')) {

    function prepare_contract_pdf($contract_data, $mode = "download", $is_mobiel_preview = false) {

        if ($contract_data) {
            $contract_data["mode"] = clean_data($mode);

            $html = view("contracts/contract_pdf", $contract_data);

            if ($mode === "html") {
                return $html;
            }

            $contract_info = get_array_value($contract_data, "contract_info");

            $pdf = new Pdf("contract");
            return $pdf->PreparePDF($html, get_contract_id($contract_info->id), $mode, $is_mobiel_preview);
        }
    }
}


if (!function_exists('get_available_menus_for_clients_dropdown')) {
    function get_available_menus_for_clients_dropdown() {
        $menus = array(
            "announcements",
            "contracts",
            "events",
            "proposals",
            "estimates",
            "invoices",
            "subscriptions",
            "knowledge_base",
            "projects",
            "payments",
            "store",
            "tickets"
        );

        $hidden_menus = explode(",", get_setting("hidden_client_menus"));

        $available_menus = array_diff($menus, $hidden_menus);

        $available_client_menus_dropdown = array();
        foreach ($available_menus as $menu) {
            $available_client_menus_dropdown[] = array("id" => $menu, "text" => app_lang($menu));
        }

        return json_encode($available_client_menus_dropdown);
    }
}

if (!function_exists('get_escaped_value')) {
    function get_escaped_value($value) {
        if ($value) {
            return esc($value);
        } else {
            return $value;
        }
    }
}

if (!function_exists('echo_escaped_value')) {
    function echo_escaped_value($value) {
        echo get_escaped_value($value);
    }
}

if (!function_exists('custom_nl2br')) {
    function custom_nl2br($text, $fix_html_tags = false) {
        $pattern = '/<p\b[^>]*>(.*?)<\/p>/i';
        $result = "";
        // Check if the pattern matches any part of the text
        if (preg_match($pattern, $text)) {
            //$result = "<div class='rich-text-container'>" . $text . "</div>";
            $result =  $text;
        } else {
            $result = nl2br($text);
        }

        if ($fix_html_tags) {
            $result = fix_html_tags($result);
        }

        return $result;
    }
}

if (!function_exists('fix_html_tags')) {
    function fix_html_tags(string $html): string {
        if (!$html) {
            return '';
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // Extract and return only the <body> content
        $body = $dom->getElementsByTagName('body')->item(0);
        $html = '';
        foreach ($body->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return $html;
    }
}

if (!function_exists('validate_share_with_value')) {
    function validate_share_with_value($input) {
        if (!$input) {
            return true; // there is no value for only_me
        }

        // for new event sharing template: all|team|member|all_clients|client_group|contact|all_contacts
        // for announcements: all_members|cg
        // for app folder permission: all_team_members|authorized_team_members|project_members|client

        $pattern = '/^(all|team|member|all_clients|client_group|contact|all_contacts|all_members|cg|all_team_members|authorized_team_members|project_members|client|\d+|,|:|-)+$/';
        if (!preg_match($pattern, $input)) {
            die("Invalid value");
        }
    }
}

if (!function_exists('validate_list_of_numbers')) {
    function validate_list_of_numbers($input) {
        if (!$input) {
            return true;
        }

        // Regular expression to match numbers separated by , or -
        $pattern = '/^(\d+([,-]\d+)*)$/';
        if (!preg_match($pattern, $input)) {
            die("Invalid value");
        }
    }
}


/**
 * Remove quotation marks from a string
 * 
 * This function removes both single (') and double (") quotation marks from the given string.
 *
 * @param string $value The input string from which quotations need to be removed
 * @return string The cleaned string without any quotation marks
 */
if (!function_exists('remove_quotations')) {
    function remove_quotations($value) {
        return preg_replace('/["\']/', '', $value);
    }
}



if (!function_exists('generate_slug_from_title')) {
    function generate_slug_from_title($title) {
        if (get_setting("disable_auto_slagging") == "1" || !$title) {
            return "";
        }

        $slug = strtolower(get_hyphenated_string($title));

        // If the slug contains only hyphens, return an empty string
        return preg_match('/^-*$/', $slug) ? '' : "-" . $slug;
    }
}

if (!function_exists('get_hyphenated_string')) {
    function get_hyphenated_string($text) {
        if (!$text) {
            return "";
        }
        return trim(preg_replace('/-+/', '-', preg_replace('/[^A-Za-z0-9\-]/', '-', $text)), '-');
    }
}


if (!function_exists('prepare_statement_pdf')) {

    function prepare_statement_pdf($statement_data, $mode = "download", $is_mobile_preview = false) {

        if ($statement_data) {
            $statement_data["mode"] = clean_data($mode);

            $html = view("clients/statement/statement_pdf", $statement_data);

            if ($mode === "html") {
                return $html;
            }

            $pdf_title = app_lang("statement") . " -  " . get_array_value($statement_data, "client_info")->company_name;

            $pdf = new Pdf("statement");
            return $pdf->PreparePDF($html, $pdf_title, $mode, $is_mobile_preview);
        }
    }
}

if (!function_exists('get_statement_making_data')) {

    function get_statement_making_data($options = array()) {
        $ci = new App_Controller();

        $client_id = get_array_value($options, "client_id");
        $client_info = $ci->Clients_model->get_one($client_id);

        if ($client_info) {
            $data['client_statement'] = $ci->Invoice_payments_model->get_client_statement($options)->getResult();
            $opening_balance = 0;
            if (get_array_value($options, "start_date")) {
                $opening_balance = $ci->Invoice_payments_model->get_opening_balance_of_client($options);
            }

            $data["opening_balance"] = $opening_balance;

            $data['client_info'] = $client_info;
            $data["currency_symbol"] = $data['client_info']->currency_symbol ? $data['client_info']->currency_symbol : get_setting("currency_symbol");
            $data['client_info']->custom_fields = $ci->Custom_field_values_model->get_details(array("related_to_type" => "clients", "show_in_invoice" => true, "related_to_id" => $client_id))->getResult();

            $data = array_merge($data, $options); // add start date and end date to the view

            return $data;
        }
    }
}

if (!function_exists('render_user_list')) {
    function render_user_list($user_list, $clickable = true) {
        $result = "";

        if ($user_list) {
            $users_array = explode(",", $user_list);

            foreach ($users_array as $user) {
                $user_parts = explode("--::--", $user);

                $user_id = get_array_value($user_parts, 0);
                $user_name = get_array_value($user_parts, 1);
                $image_url = get_avatar(get_array_value($user_parts, 2));

                $_comma = $result ? ", " : "";

                $user_image = "<span class='avatar avatar-xxs mr10'><img src='$image_url' alt='...'></span><span class='hide'>$_comma $user_name</span>";

                if ($clickable) {
                    $result .= get_team_member_profile_link($user_id, $user_image, array("title" => $user_name));
                } else {
                    $result .= "<span title='$user_name'>$user_image</span>";
                }
            }
        }

        return $result;
    }
}
