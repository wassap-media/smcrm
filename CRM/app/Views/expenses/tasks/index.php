<div class="tab-title clearfix">
    <h4><?php echo app_lang('tasks'); ?></h4>
    <div class="title-button-group">
        <?php echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default mb0", "data-post-expense_id" => $expense_id, "title" => app_lang('add_task'))); ?>
    </div>
</div>

<div class="table-responsive">
    <table id="task-table" class="display" width="100%">
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var showResponsiveOption = true,
                idClass = "w10p",
                titleColumnClass = "",
                optionColumnClass = "w100";
        if (isMobile()) {
            showResponsiveOption = false;
            idClass = "w20p";
            titleColumnClass = "w60p";
            optionColumnClass = "w20p";
        }

        var idColumnClass = "";
        if ("<?php echo get_setting("show_the_status_checkbox_in_tasks_list"); ?>" === "1") {
            idColumnClass = idClass;
        }

        $("#task-table").appTable({
            source: '<?php echo_uri("tasks/list_data/expense/" . $expense_id) ?>',
            order: [[0, "desc"]],
            serverSide: true,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('id') ?>", "class": idColumnClass, order_by: "id"},
                {title: "<?php echo app_lang('title') ?>", "class": titleColumnClass, order_by: "title"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false, order_by: "start_date"},
                {title: "<?php echo app_lang('start_date') ?>", "iDataSort": 7, visible: showResponsiveOption, order_by: "start_date"},
                {visible: false, searchable: false, order_by: "deadline"},
                {title: "<?php echo app_lang('deadline') ?>", "iDataSort": 9, visible: showResponsiveOption, order_by: "deadline"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('assigned_to') ?>", "class": "min-w150", visible: showResponsiveOption, order_by: "assigned_to"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('status') ?>", visible: showResponsiveOption, order_by: "status"}
<?php echo $custom_field_headers_of_task; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option " + optionColumnClass}
            ],
            rowCallback: tasksTableRowCallback //load this function from the task_table_common_script.php 
        });
    });
</script>
<?php echo view("tasks/task_table_common_script"); ?>