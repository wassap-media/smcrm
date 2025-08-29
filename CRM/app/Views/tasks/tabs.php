<li class="js-cookie-tab" data-tab="tasks_list"><a class="<?php echo ($active_tab == 'tasks_list') ? 'active' : ''; ?>" href="<?php echo_uri('tasks/all_tasks/'); ?>"><?php echo app_lang("list"); ?></a></li>
<li class="js-cookie-tab" data-tab="tasks_kanban"><a class="<?php echo ($active_tab == 'tasks_kanban') ? 'active' : ''; ?>" href="<?php echo_uri('tasks/all_tasks_kanban/'); ?>"><?php echo app_lang('kanban'); ?></a></li>
<?php if (get_setting("module_gantt") && !$has_all_projects_restricted_role) { ?>
    <li><a class="<?php echo ($active_tab == 'gantt') ? 'active' : ''; ?>" href="<?php echo_uri('tasks/all_gantt/'); ?>"><?php echo app_lang('gantt'); ?></a></li>
<?php } ?>

<script>
    var tab = "<?php echo $selected_tab; ?>";
    if (!tab) {
        var activeTab = "<?php echo $active_tab; ?>";
        var selectedTab = getCookie("selected_tab_" + "<?php echo $login_user->id; ?>");

        if (activeTab != "gantt" && selectedTab && selectedTab !== "<?php echo $active_tab ?>" && selectedTab === "tasks_kanban") {
            window.location.href = "<?php echo_uri('tasks/all_tasks_kanban'); ?>";
        }
    }

    //save the selected tab in browser cookie
    $(document).ready(function() {
        $(".js-cookie-tab").click(function() {
            var tab = $(this).attr("data-tab");
            if (tab) {
                setCookie("selected_tab_" + "<?php echo $login_user->id; ?>", tab);
            }
        });
    });
</script>