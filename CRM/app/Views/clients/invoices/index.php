<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>

    <div class="card">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('invoices'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('invoices'); ?></h4>
                <div class="title-button-group">
                    <?php
                    if ($can_edit_invoices) {
                        echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_invoice'), array("class" => "btn btn-default mb0", "data-post-client_id" => $client_id, "title" => app_lang('add_invoice')));
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table id="invoice-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";

        var quick_filters_dropdown = <?php echo view("invoices/invoice_statuses_dropdown"); ?>;
        if (window.selectedInvoiceQuickFilter) {
            var filterIndex = quick_filters_dropdown.findIndex(x => x.id === window.selectedInvoiceQuickFilter);
            if ([filterIndex] > -1) {
                //match found
                quick_filters_dropdown[filterIndex].isSelected = true;
            }
        }

        $("#invoice-table").appTable({
            source: '<?php echo_uri("invoices/invoice_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [
                {name: "type", class: "w150", options: <?php echo $types_dropdown; ?>},
                {name: "status", class: "w150", options: quick_filters_dropdown},
                <?php echo $custom_field_filters; ?>],
            rangeDatepicker: [{startDate: {name: "start_date", value:""}, endDate: {name: "end_date",  value:""}, showClearButton: true}],
            columns: [
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("id") ?>', "class": "w10p all", "iDataSort": 0},
                {targets: [1], visible: false, searchable: false},
                {title: "<?php echo app_lang("project") ?>"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("bill_date") ?>", "class": "all w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
                {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
                {title: '<?php echo app_lang("status") ?>', "class": "w10p text-center"}
<?php echo $custom_field_headers; ?>
            ],
            printColumns: combineCustomFieldsColumns([1, 3, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 3, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            summation: [
                {column: 8, dataType: 'currency', currencySymbol: currencySymbol},
                {column: 9, dataType: 'currency', currencySymbol: currencySymbol},
                {column: 10, dataType: 'currency', currencySymbol: currencySymbol}
            ]
        });
    });
</script>