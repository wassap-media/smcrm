<?php
$url = "projects/save_comment";
$comment_type = "";
if (isset($task_id)) {
    $comment_type = "task";
    $url = "tasks/save_comment";
} else if (isset($file_id)) {
    $comment_type = "file";
} else if (isset($customer_feedback_id)) {
    $comment_type = "customer_feedback";
} else {
    $comment_type = "project";
}

$mention_source = get_uri("projects/get_member_suggestion_to_mention");
if (isset($model_info->context) && $model_info->context != "project") {
    $mention_source = get_uri("tasks/get_member_suggestion_to_mention");
}
?>
<div id="<?php echo $comment_type . "-comment-form-container"; ?>">
    <?php echo form_open(get_uri($url), array("id" => $comment_type . "-comment-form", "class" => "general-form", "role" => "form")); ?>
    <div class="d-flex b-b comment-form-container">
        <div class="flex-shrink-0 d-none d-sm-block">
            <div class="avatar avatar-sm pr15 d-table-cell">
                <img src="<?php echo get_avatar($login_user->image); ?>" alt="..." />
            </div>
        </div>
        <div class="w-100">
            <div id="<?php echo $comment_type . "-dropzone"; ?>" class="post-dropzone mb-3 form-group">
                <input type="hidden" name="project_id" value="<?php echo isset($project_id) ? $project_id : 0; ?>">
                <input type="hidden" name="file_id" value="<?php echo isset($file_id) ? $file_id : 0; ?>">
                <input type="hidden" name="task_id" value="<?php echo isset($task_id) ? $task_id : 0; ?>">
                <input type="hidden" name="customer_feedback_id" value="<?php echo isset($customer_feedback_id) ? $customer_feedback_id : 0; ?>">
                <input type="hidden" name="reload_list" value="1">
                <input type="hidden" name="comment_type" value="<?php echo $comment_type; ?>">
                <?php
                echo form_textarea(array(
                    "id" => "comment_description",
                    "name" => "description",
                    "class" => "form-control comment_description",
                    "placeholder" => app_lang('write_a_comment'),
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                    "data-rich-text-editor" => true,
                    "data-mention" => true,
                    "data-mention_source" => $mention_source,
                    "data-mention_project_id" => $project_id
                ));
                ?>
                <?php echo view("includes/dropzone_preview"); ?>
                <footer class="card-footer b-a clearfix">
                    <div class="float-start">
                        <?php
                        if ($comment_type != "file") {
                            echo view("includes/upload_button");
                        }
                        ?>
                    </div>
                    <button class="btn btn-primary float-end" type="submit"><i data-feather="send" class='icon-16'></i> <?php echo app_lang("post_comment"); ?></button>
                </footer>
            </div>
        </div>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#comment_description').appMention({
            source: "<?php echo $mention_source; ?>",
            data: {project_id: <?php echo $project_id; ?>}
        });


        $("#<?php echo $comment_type; ?>-comment-form").appForm({
            isModal: false,
            onSuccess: function (result) {
<?php if ($comment_type === "task") { ?>
                    window.location.hash = ""; //prevent highlighting here
<?php } ?>

                $(".comment_description").val("");

                if ($("#file-preview-comment-container").length) {
                    $("#file-preview-comment-container").prepend(result.data);
                } else {
                    $(result.data).insertAfter("#<?php echo $comment_type; ?>-comment-form-container");
                }

                appAlert.success(result.message, {duration: 10000});

                var dropzoneId = "<?php echo $comment_type . '-dropzone'; ?>";

                if (window.formDropzone && window.formDropzone[dropzoneId]) {
                    window.formDropzone[dropzoneId].removeAllFiles();
                }
            }
        });
    });
</script>