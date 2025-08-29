<div id="page-content" class="page-wrapper clearfix">
    <div class="clearfix grid-button">
        <ul id="client-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#overview"><?php echo app_lang('overview'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/clients_list/"); ?>" data-bs-target="#clients_list"><?php echo app_lang('clients'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("clients/contacts/"); ?>" data-bs-target="#contacts"><?php echo app_lang('contacts'); ?></a></li>
            <div class="tab-title clearfix no-border">
                <div class="title-button-group">
                    <?php
                    if ($can_edit_clients) {
                        echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "client"));
                        echo modal_anchor(get_uri("clients/import_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_clients'), array("class" => "btn btn-default", "title" => app_lang('import_clients'), "id" => "import-btn"));
                        echo modal_anchor(get_uri("clients/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_client'), array("class" => "btn btn-default", "title" => app_lang('add_client')));
                    }
                    ?>
                </div>
            </div>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="overview">
                <?php echo view("clients/overview/index"); ?>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="clients_list"></div>
            <div role="tabpanel" class="tab-pane fade" id="contacts"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        setTimeout(function() {
            var tab = "<?php echo $tab; ?>";
            if (tab === "clients_list" || tab === "clients_list-has_open_projects") {
                $("[data-bs-target='#clients_list']").trigger("click");

                window.selectedClientQuickFilter = window.location.hash.substring(1);
            } else if (tab === "contacts") {
                $("[data-bs-target='#contacts']").trigger("click");

                window.selectedContactQuickFilter = window.location.hash.substring(1);
            }
        }, 210);

        //change the add button attributes on changing tab panel
        var importButton = $("#import-btn");
        $(".nav-tabs li").click(function() {
            var activeField = $(this).find("a").attr("data-bs-target");

            //lead status
            if (activeField === "#contacts") {
                importButton.attr("title", "<?php echo app_lang("import_contacts"); ?>");
                importButton.attr("data-title", "<?php echo app_lang("import_contacts"); ?>");
                importButton.attr("data-action-url", "<?php echo get_uri("contacts/import_modal_form"); ?>");

                importButton.html("<?php echo "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_contacts'); ?>");
                feather.replace();

            } else {
                importButton.attr("title", "<?php echo app_lang("import_clients"); ?>");
                importButton.attr("data-title", "<?php echo app_lang("import_clients"); ?>");
                importButton.attr("data-action-url", "<?php echo get_uri("clients/import_modal_form"); ?>");

                importButton.html("<?php echo "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_clients'); ?>");
                feather.replace();
            }
        });
    });
</script>