<div class="table-responsive">
    <table id="timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {

        var filterDrpdown = [];

<?php if ($show_members_dropdown) { ?>
            filterDrpdown.push({name: "user_id", class: "w200", options: <?php echo $project_members_dropdown; ?>});
<?php } ?>
        filterDrpdown.push({name: "task_id", class: "w200", options: <?php echo $tasks_dropdown; ?>});
        filterDrpdown.push({name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>});
        filterDrpdown.push(<?php echo $custom_field_filters; ?>);

        $("#timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_summary_list_data/"); ?>',
            filterParams: {project_id: "<?php echo $project_id; ?>"},
            filterDropdown: filterDrpdown,
            rangeDatepicker: [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, showClearButton: true, showClearButton: true, label: "<?php echo app_lang('date'); ?>", ranges: ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year', 'last_year' ]}],
            columns: [
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang('member'); ?>"},
                {title: "<?php echo app_lang('task'); ?>"},
                {title: "<?php echo app_lang('duration'); ?>", "class": "w15p text-right"},
                {title: "<?php echo app_lang('hours'); ?>", "class": "w15p text-right"},
                {visible: false, title: "<?php echo app_lang('hours') ?>", "class": "text-right"}
            ],
            printColumns: [2, 3, 4, 5],
            xlsColumns: [2, 3, 4, 6],
            summation: [{column: 4, dataType: 'time'}, {column: 5, dataType: 'number'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status

                if (filterParams && filterParams.group_by === "member") {
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, false);
                } else if (filterParams && filterParams.group_by === "task") {
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, true);
                } else {
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>