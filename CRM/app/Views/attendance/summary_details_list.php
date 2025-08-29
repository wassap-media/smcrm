<div class="table-responsive">
    <table id="attendance-summary-details-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var dynamicDates = getDynamicDates();
        $("#attendance-summary-details-table").appTable({
            source: '<?php echo_uri("attendance/summary_details_list_data/"); ?>',
            order: [[0, "asc"]],
            filterDropdown: [{name: "user_id", class: "w200", options: <?php echo $team_members_dropdown; ?>}],
            rangeDatepicker: [{startDate: {name: "start_date", value: dynamicDates.start_of_month}, endDate: {name: "end_date", value: dynamicDates.end_of_month}, label: "<?php echo app_lang('date'); ?>", ranges: ['this_month', 'last_month', 'this_year', 'last_year', 'last_30_days', 'last_7_days']}],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("team_member"); ?>", "iDataSort": 0},
                {title: "<?php echo app_lang("date"); ?>", sortable: false, "class": "w20p"},
                {title: "<?php echo app_lang("duration"); ?>", sortable: false, "class": "w20p text-right"},
                {title: "<?php echo app_lang("hours"); ?>", sortable: false, "class": "w20p text-right"}
            ],
            printColumns: [1, 2, 3, 4],
            xlsColumns: [1, 2, 3, 4],
            summation: [{column: 3, dataType: 'time'}, {column: 4, dataType: 'number'}]
        });
    });
</script>