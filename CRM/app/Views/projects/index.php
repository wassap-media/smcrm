<div id="page-content" class="page-wrapper clearfix">
    <div class="card grid-button">
        <div class="page-title clearfix projects-page">
            <h1><?php echo app_lang('projects'); ?></h1>
            <div class="title-button-group">
                <?php
                if ($can_create_projects) {
                    if ($can_edit_projects) {
                        echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "project"));
                    }
                    
                    echo modal_anchor(get_uri("projects/import_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_projects'), array("class" => "btn btn-default", "title" => app_lang('import_projects')));

                    echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_project'), array("class" => "btn btn-default", "title" => app_lang('add_project')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="project-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo ($can_edit_projects || $can_delete_projects); ?>") {
            optionVisibility = true;
        }


        var ignoreSavedFilter = false;
<?php if (isset($selected_status_id) && $selected_status_id) { ?>
            ignoreSavedFilter = true;
<?php } ?>

        var dynamicDates = getDynamicDates();
        $("#project-table").appTable({
            source: '<?php echo_uri("projects/list_data") ?>',
            serverSide: true,
            smartFilterIdentity: "all_projects_list", //a to z and _ only. should be unique to avoid conflicts 
            ignoreSavedFilter: ignoreSavedFilter,
            multiSelect: [
                {
                    name: "status_id",
                    text: "<?php echo app_lang('status'); ?>",
                    options: <?php echo view("project_status/project_status_dropdown", array("project_statuses" => $project_statuses, "selected_status_id" => $selected_status_id)); ?>
                }
            ],
            filterDropdown: [{name: "project_label", class: "w200", options: <?php echo $project_labels_dropdown; ?>}, <?php echo $custom_field_filters; ?>],
            rangeDatepicker: [{startDate: {name: "start_date_from", value: ""}, endDate: {name: "start_date_to", value: ""}, showClearButton: true, label: "<?php echo app_lang('start_date'); ?>", ranges: ['this_month', 'last_month', 'this_year', 'last_year', 'next_7_days', 'next_month']}],
            singleDatepicker: [{name: "deadline", defaultText: "<?php echo app_lang('deadline') ?>",
                    options: [
                        {value: "expired", text: "<?php echo app_lang('expired') ?>"},
                        {value: dynamicDates.today, text: "<?php echo app_lang('today') ?>"},
                        {value: dynamicDates.tomorrow, text: "<?php echo app_lang('tomorrow') ?>"},
                        {value: dynamicDates.in_next_7_days, text: "<?php echo sprintf(app_lang('in_number_of_days'), 7); ?>"},
                        {value: dynamicDates.in_next_15_days, text: "<?php echo sprintf(app_lang('in_number_of_days'), 15); ?>"}
                    ]}],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "w50", order_by: "id"},
                {title: '<?php echo app_lang("title") ?>', "class": "all", order_by: "title"},
                {title: '<?php echo app_lang("client") ?>', "class": "w10p", order_by: "company_name"},
                {visible: optionVisibility, title: '<?php echo app_lang("price") ?>', "class": "w10p text-right", order_by: "price"},
                {visible: false, searchable: false, order_by: "start_date"},
                {title: '<?php echo app_lang("start_date") ?>', "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false, order_by: "deadline"},
                {title: '<?php echo app_lang("deadline") ?>', "class": "w10p", "iDataSort": 6},
                {title: '<?php echo app_lang("progress") ?>', "class": "w10p"},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p", order_by: "status"}
<?php echo $custom_field_headers; ?>,
                {visible: optionVisibility, title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>