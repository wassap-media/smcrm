<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="expenses-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title scrollable-tabs" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("expenses"); ?></h4></li>
            <li><a id="monthly-expenses-button" role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#all-expenses"><?php echo app_lang("list"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("expenses/recurring/"); ?>" data-bs-target="#recurring-expenses"><?php echo app_lang('recurring'); ?></a></li>
            <div class="tab-title clearfix no-border expenses-page-title">
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("expenses/import_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_expense'), array("class" => "btn btn-default mb0", "title" => app_lang('import_expense'))); ?>
                    <?php echo modal_anchor(get_uri("expenses/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_expense'), array("class" => "btn btn-default mb0", "title" => app_lang('add_expense'))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="all-expenses">
                <div class="table-responsive">
                    <table id="expense-table" class="display" cellspacing="0" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-expenses"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadExpensesTable = function (selector, dateRange) {
    var smartFilterContext = "all-expenses";
    var customDatePicker = "", recurring = "0";
    var rangeRadioButtonOptions = [{name: "range_radio_button", selectedOption: 'monthly', options: ['monthly', 'yearly', 'custom', 'dynamic'], dynamicRanges:['this_month', 'last_month', 'next_month', 'this_year', 'last_year']}];
    if (dateRange === "recurring") {
        customDatePicker = [{startDate: {name: "start_date"}, endDate: {name: "end_date"}, showClearButton: true}];
        recurring = "1";
        smartFilterContext = "recurring-expenses";
        rangeRadioButtonOptions = "";
    }

    $(selector).appTable({
    source: '<?php echo_uri("expenses/list_data") ?>/' + recurring,
            smartFilterIdentity: smartFilterContext, //a to z and _ only. should be unique to avoid conflicts
            rangeRadioButtons: rangeRadioButtonOptions,
            filterDropdown: [
            {name: "category_id", class: "w200", options: <?php echo $categories_dropdown; ?>},
            {name: "user_id", class: "w200", options: <?php echo $members_dropdown; ?>}
<?php if ($projects_dropdown) { ?>
                , {name: "project_id", class: "w200", options: <?php echo $projects_dropdown; ?>}
<?php } ?>
            ,<?php echo $custom_field_filters; ?>
            ],
            order: [[0, "asc"]],
            rangeDatepicker: customDatePicker,
            columns: [
            {visible: false, searchable: false},
            {title: '<?php echo app_lang("date") ?>', "iDataSort": 0, "class": "all"},
            {title: '<?php echo app_lang("category") ?>'},
            {title: '<?php echo app_lang("title") ?>'},
            {title: '<?php echo app_lang("description") ?>'},
            {title: '<?php echo app_lang("files") ?>'},
            {title: '<?php echo app_lang("amount") ?>', "class": "text-right"},
            {title: '<?php echo app_lang("tax") ?>', "class": "text-right"},
            {title: '<?php echo app_lang("second_tax") ?>', "class": "text-right"},
            {title: '<?php echo app_lang("total") ?>', "class": "text-right all"}
<?php echo $custom_field_headers; ?>,
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 6, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 6, dataType: 'currency'}, {column: 7, dataType: 'currency'}, {column: 8, dataType: 'currency'}, {column: 9, dataType: 'currency'}]
    });
    };
    $(document).ready(function () {
    loadExpensesTable("#expense-table", "all");
    });
</script>
