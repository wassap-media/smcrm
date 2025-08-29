<?php
$points_dropdown_for_update = array();
foreach ($points_dropdown as $key => $value) {
    $points_dropdown_for_update[] = array("id" => $key, "text" => $value);
}
?>


<script type="text/javascript">
    $(document).ready(function () {
        $('body').on('click', '[data-act=update-task-info]', function (e) {
            var $instance = $(this),
                    type = $(this).attr('data-act-type'),
                    source = "",
                    select2Option = {},
                    showButtons = false,
                    placement = "bottom",
                    editableType = "select2",
                    datepicker = {};

            if (type === "status_id") {
                source = <?php echo json_encode($statuses_dropdown); ?>;
                select2Option = {data: source};
            } else if (type === "milestone_id") {
                source = <?php echo json_encode($milestones_dropdown); ?>;
                select2Option = {data: source};
            } else if (type === "assigned_to") {
                source = <?php echo json_encode($assign_to_dropdown); ?>;
                select2Option = {data: source};
            } else if (type === "points") {
                source = <?php echo json_encode($points_dropdown_for_update); ?>;
                select2Option = {data: source};
            } else if (type === "collaborators") {
                e.stopPropagation();
                e.preventDefault();

                showButtons = true;
                source = <?php echo json_encode($collaborators_dropdown); ?>;
                select2Option = {data: source, multiple: true};
            } else if (type === "labels") {
                e.stopPropagation();
                e.preventDefault();

                source = <?php echo json_encode($label_suggestions); ?>;
                showButtons = true;
                select2Option = {data: source, multiple: true};
                placement = "bottom";
            } else if (type === "start_date" || type === "deadline") {
                editableType = "date";

                if (type === "deadline") {
                    datepicker["endDate"] = "<?php echo $project_deadline; ?>";

                    //don't show dates before start date
<?php if (is_date_exists($model_info->start_date)) { ?>
                        datepicker["startDate"] = "<?php echo format_to_date($model_info->start_date); ?>";
<?php } ?>
                }
            } else if (type === "priority_id") {
                source = <?php echo json_encode($priorities_dropdown); ?>;
                select2Option = {data: source};
            } else if (type === "start_time" || type === "end_time") {
                e.stopPropagation();
                e.preventDefault();

                showButtons = true;
                placement = "bottom";
                editableType = "time";
            }

            $(this).appModifier({
                actionType: editableType,
                value: $(this).attr('data-value'),
                actionUrl: '<?php echo_uri("tasks/update_task_info") ?>/' + $(this).attr('data-id') + '/' + $(this).attr('data-act-type'),
                showButtons: showButtons,
                datepicker: datepicker,
                select2Option: select2Option,
                placement: placement,
                onSuccess: function (response, newValue) {
                    if (response.success) {
                        if (type === "assigned_to" && response.assigned_to_avatar) {
                            $("#task-assigned-to-avatar").attr("src", response.assigned_to_avatar);

                            if (response.assigned_to_id === "0") {
                                setTimeout(function () {
                                    $instance.html("<span class='text-off'><?php echo app_lang("add") . " " . app_lang("assignee"); ?><span>");
                                }, 50);
                            }
                        }

                        if (type === "status_id" && response.status_color) {
                            $instance.closest("span").css("background-color", response.status_color);
                        }

                        if (type === "milestone_id" && response.milestone_id === "0") {
                            setTimeout(function () {
                                $instance.html("<span class='text-off'><?php echo app_lang("add") . " " . app_lang("milestone"); ?><span>");
                            }, 50);
                        }

                        if (type === "points" && response.points) {
                            setTimeout(function () {
                                $instance.html(response.points);
                            }, 50);
                        }

                        if (type === "labels" && response.labels) {
                            setTimeout(function () {
                                $instance.html(response.labels);
                            }, 50);
                        }

                        if (type === "collaborators" && response.collaborators) {
                            setTimeout(function () {
                                $instance.html(response.collaborators);
                            }, 50);
                        }

                        if ((type === "start_date" || type === "deadline") && response.date) {
                            setTimeout(function () {
                                $instance.html(response.date);
                                $instance.append(response.time);

                                if (type === "deadline") {
                                    $(".task-deadline-milestone-tooltip").remove();
                                }
                            }, 50);
                        }

                        if (type === "priority_id" && response.priority_pill) {
                            setTimeout(function () {
                               
                                if (response.priority_id === "0") {
                                    $instance.html("<span class='text-off'><?php echo app_lang("add") . " " . app_lang("priority"); ?><span>");
                                }else{
                                    $instance.prepend(response.priority_pill);
                                    feather.replace();
                                }
                            }, 50);
                        }

                        if ((type === "start_time" || type === "end_time") && response.time) {
                            setTimeout(function () {
                                $instance.html(response.time);
                            }, 50);
                        }

                        $("#task-table").appTable({newData: response.data, dataId: response.id});

                        appLoader.hide();

                        window.reloadKanban = true;

                        //reload gantt
                        if (typeof window.reloadGantt === "function") {
                            window.reloadGantt(true);
                        }
                    }
                }
            });

            return false;
        });
    });
</script>