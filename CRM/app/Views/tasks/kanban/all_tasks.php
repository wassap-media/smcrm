<div id="page-content" class="page-wrapper pb0 clearfix grid-button all-tasks-kanban-view">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab all-tasks-kanban">
            <h4 class="pl15 pt10 pr15"><?php echo app_lang("tasks"); ?></h4>
        </li>

        <?php echo view("tasks/tabs", array("active_tab" => "tasks_kanban", "selected_tab" => "")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php
                if ($can_create_tasks) {
                    echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_multiple_tasks'), array("class" => "btn btn-default", "title" => app_lang('add_multiple_tasks'), "data-post-add_type" => "multiple"));
                    echo modal_anchor(get_uri("tasks/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task'), array("class" => "btn btn-default", "title" => app_lang('add_task')));
                }
                ?>
            </div>
        </div>
    </ul>
    <div class="card border-top-0 rounded-top-0">
        <div class="bg-white" id="js-kanban-filter-container">
            <div id="kanban-filters"></div>
        </div>

        <div id="load-kanban"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        window.scrollToKanbanContent = true;
    });
</script>

<?php echo view("tasks/batch_update/batch_update_script"); ?>
<?php echo view("tasks/kanban/all_tasks_kanban_helper_js"); ?>
<?php echo view("tasks/quick_filters_helper_js"); ?>