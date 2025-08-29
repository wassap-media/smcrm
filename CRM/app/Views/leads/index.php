<div id="page-content" class="page-wrapper clearfix grid-button leads-view">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab leads-title-section"><h4 class="pl15 pt10 pr15"><?php echo app_lang("leads"); ?></h4></li>

        <?php echo view("leads/tabs", array("active_tab" => "leads_list")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "client")); ?>
                <?php echo modal_anchor(get_uri("leads/import_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_leads'), array("class" => "btn btn-default", "title" => app_lang('import_leads'))); ?>
                <?php echo modal_anchor(get_uri("leads/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_lead'), array("class" => "btn btn-default", "title" => app_lang('add_lead'))); ?>
            </div>
        </div>
    </ul>

    <div class="card border-top-0 rounded-top-0">
        <div class="table-responsive">
            <table id="lead-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

    var ignoreSavedFilter = false;
    var hasString = window.location.hash.substring(1);
    var hasSelectedStatus = "<?php echo isset($selected_status_id) && $selected_status_id ?>";
    if (hasString || hasSelectedStatus) {
        ignoreSavedFilter = true;
    }

    var batchUpdateUrl = "<?php echo_uri('leads/batch_update_modal_form'); ?>";
    var batchDeleteUrl = "<?php echo_uri('leads/delete_selected_leads'); ?>";

    var mobileView = 0;

    if (isMobile()) {
        mobileView = 1;
    }

    $("#lead-table").appTable({
    source: '<?php echo_uri("leads/list_data/") ?>' + mobileView,
            serverSide: true,
            smartFilterIdentity: "all_leads_list", //a to z and _ only. should be unique to avoid conflicts
            selectionHandler: {batchUpdateUrl: batchUpdateUrl, batchDeleteUrl: batchDeleteUrl},
            ignoreSavedFilter: ignoreSavedFilter,
            order: [[5, "desc"]],
            columns: [
                {title: "<?php echo app_lang("name") ?>", "class": "all", order_by: "company_name"},
                {title: "<?php echo app_lang("primary_contact") ?>", order_by: "primary_contact"},
                {title: "<?php echo app_lang("phone") ?>"},
                {title: "<?php echo app_lang("owner") ?>", order_by: "owner_name"},
                {title: "<?php echo app_lang("labels") ?>"},
                {visible: false, searchable: false, order_by: "created_date"},
                {title: "<?php echo app_lang("created_at") ?>", "iDataSort": 5, order_by: "created_date"},
                {title: "<?php echo app_lang("status") ?>", order_by: "status"}
                <?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            filterDropdown: [
                <?php if (get_array_value($login_user->permissions, "lead") !== "own") { ?>
                 {name: "owner_id", class: "w200", options: <?php echo json_encode($owners_dropdown); ?>},
                <?php } ?>
                {name: "status", class: "w200", options: <?php echo view("leads/lead_statuses"); ?>},
            {name: "label_id", class: "w200", options: <?php echo $labels_dropdown; ?>},
            {name: "source", class: "w200", options: <?php echo view("leads/lead_sources"); ?>} ,
            <?php echo $custom_field_filters; ?>
            ],
            rangeDatepicker: [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, label: "<?php echo app_lang('created_date'); ?>", showClearButton: true}],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 6, 7], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 6, 7], '<?php echo $custom_field_headers; ?>')
    });
    }
    );
</script>

<?php echo view("leads/update_lead_status_script"); ?>