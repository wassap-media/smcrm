<div id="page-content" class="<?php echo isset($is_editor_preview) ? "bg-all-white" : "page-wrapper"; ?> clearfix pb0">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));

    load_js(array(
        "assets/js/signature/signature_pad.min.js",
    ));
    ?>

    <div class="contract-preview">
        <?php
        if (!isset($is_editor_preview)) {

            $action_buttons = "<div class='clearfix float-end grid-button-group'>";

            if ($show_close_preview) {
                echo "<div class='text-center'>" . anchor("contracts/view/" . $contract_info->id, app_lang("close_preview"), array("class" => "btn btn-default round mb20 mr5")) . "</div>";
            }

            if ($has_pdf_access) {
                $action_buttons .= "<div class='float-start'>" . anchor(get_uri("contracts/download_pdf/" . $contract_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "btn btn-default round mr10")) . "</div>";
            }

            $action_buttons .= "<div class='float-start'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print'), array('id' => 'print-contract-btn', "class" => "btn btn-default round mr10")) . "</div>";

            if ($login_user->user_type === "staff") {
                $action_buttons .= "<div class='float-start'>" . anchor(get_uri("contract/preview/" . $contract_info->id . "/" . $contract_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('contract') . " " . app_lang("url"), array("class" => "btn btn-default round mr5")) . "</div>";
            }

            $action_buttons .= "</div>";

            if ($contract_info->status === "accepted" || $contract_info->status === "declined" || $contract_info->status === "rejected") {
        ?>
                <div class="card  p15 no-border grid-button">
                    <div class="clearfix contract-preview-button">
                        <div class="float-start mt5 grid-button-group">
                            <?php if ($contract_info->status === "accepted") { ?>
                                <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("contract_accepted"); ?>
                            <?php } else { ?>
                                <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("contract_rejected"); ?>
                            <?php } ?>
                        </div>

                        <?php echo $action_buttons; ?>
                    </div>
                </div>
                <?php
            } else {
                if ($login_user->user_type === "staff" || ($login_user->user_type === "client" && $contract_info->status == "new")) {
                ?>

                    <div class="card  p15 no-border grid-button">

                        <div class="clearfix contract-preview-button">
                            <div class="mr15 strong float-start grid-button-group">
                                <?php
                                if ($can_edit_contracts) {
                                    if ($login_user->user_type === "client" && get_setting("add_signature_option_on_accepting_contract")) {
                                        echo modal_anchor(get_uri("contract/accept_contract_modal_form/$contract_info->id"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept_contract'), array("class" => "btn btn-success mr15", "title" => app_lang('accept_contract')));
                                    } else {
                                        echo ajax_anchor(get_uri("contracts/update_contract_status/$contract_info->id/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => app_lang('mark_as_accepted'), "data-reload-on-success" => "1"));
                                    }

                                    echo ajax_anchor(get_uri("contracts/update_contract_status/$contract_info->id/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("class" => "btn btn-danger mr15", "title" => app_lang('mark_as_rejected'), "data-reload-on-success" => "1"));
                                }
                                ?>
                            </div>

                            <?php echo $action_buttons; ?>
                        </div>
                    </div>

        <?php
                }
            }
        }
        ?>

        <div id="contract-preview" class="invoice-preview-container contract-preview-container bg-white mt15 mb20">
            <?php
            echo $contract_preview;
            ?>

            <?php
            if ($login_user->user_type === "client") {
                if ($contract_info->files) {
                    $files = unserialize($contract_info->files);
                    if (count($files)) {
                        foreach ($files as $key => $value) {
                            $file_name = get_array_value($value, "file_name");
                            $link = get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                            echo js_anchor("<i data-feather='$link'></i>", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "float-start mr10 mt-4", "title" => remove_file_prefix($file_name), "data-url" => get_uri("contract/file_preview/" . $contract_info->id . "/" . $key . "/" . $contract_info->public_key)));
                        }
                    }
                }
            }
            ?>
        </div>

        <?php
        if (!isset($is_editor_preview)) {
            echo view("contracts/signer_info", array("contract_status" => $contract_info->status));
        }
        ?>

    </div>
</div>

<?php echo view("contracts/print_contract_helper_js"); ?>