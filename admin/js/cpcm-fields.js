(function ($)
{
    "use strict";

    var messages = typeof window.cpcmAdmin === "object" ? window.cpcmAdmin : {};

    $(document).ready(function ()
    {
        // Initial state
        $(".cpcm-btn-save-all").addClass("disabled").prop("disabled", true);
        $(".cpcm-btn-reset-fields").prop("disabled", true);

        // Save form submission
        $('#cpcm-main-save-form').on('submit', function ()
        {
            var $btn = $('.cpcm-btn-save-all');
            $btn.prop('disabled', true).addClass('disabled is-loading')
                .html('<span class="dashicons dashicons-update cpcm-spin"></span> Saving...');
        });

        /**
         * Copy shortcode to clipboard
         */
        $(document).on("click", ".cpcm-btn-copy", function (e)
        {
            e.preventDefault();
            var shortcode = $(this).data("clipboard");
            var $button = $(this);
            var $temp = $("<textarea>");
            $("body").append($temp);
            $temp.val(shortcode).select();

            try
            {
                document.execCommand("copy");
                var originalHTML = $button.html();
                $button.html('<span class="dashicons dashicons-yes"></span>');
                $button.css("background", "#10b981");

                if (messages.copiedToClipboard)
                {
                    showNotification(messages.copiedToClipboard, "success");
                }

                setTimeout(function ()
                {
                    $button.html(originalHTML);
                    $button.css("background", "");
                }, 2000);
            } catch (err)
            {
                showNotification("Failed to copy.", "error");
            }
            $temp.remove();
        });

        /**
         * Local Field Deletion
         */
        $(document).on("click", ".cpcm-btn-delete-local", function (e)
        {
            e.preventDefault();
            var $row = $(this).closest("tr");
            var fieldKey = $row.data("field-key");
            var fieldName = $(this).data("field-name");
            var inUse = $row.data("in-use") == 1;

            if (inUse)
            {
                showNotification(messages.fieldCannotDelete || "Field in use.", "error");
                return;
            }

            var confirmMessage = (messages.confirmDelete || "Delete this field?").replace("%s", fieldName);

            if (confirm(confirmMessage))
            {
                $row.addClass("cpcm-marked-for-deletion");
                if ($('#cpcm_fields_to_delete').length === 0)
                {
                    $('form#cpcm-main-save-form').append('<input type="hidden" name="cpcm_fields_to_delete" id="cpcm_fields_to_delete" value="">');
                }
                var fieldsToDelete = $('#cpcm_fields_to_delete').val().split(',').filter(Boolean);
                if (!fieldsToDelete.includes(fieldKey))
                {
                    fieldsToDelete.push(fieldKey);
                    $('#cpcm_fields_to_delete').val(fieldsToDelete.join(','));
                }
                $row.find('td').css('opacity', '0.5');
                $(this).html('<span class="dashicons dashicons-undo"></span> Undo')
                    .removeClass('cpcm-btn-delete-local').addClass('cpcm-undo-delete');

                window.cpcmSetHasChanges(true);
                showNotification(messages.fieldMarkedForDeletion || "Marked for deletion", "warning");
            }
        });

        $(document).on('click', '.cpcm-undo-delete', function (e)
        {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var fieldKey = $row.data('field-key');
            var fieldsToDelete = $('#cpcm_fields_to_delete').val().split(',').filter(Boolean);
            fieldsToDelete = fieldsToDelete.filter(function (f) { return f !== fieldKey; });
            $('#cpcm_fields_to_delete').val(fieldsToDelete.join(','));

            $row.removeClass('cpcm-marked-for-deletion');
            $row.find('td').css('opacity', '1');
            $(this).html('<span class="dashicons dashicons-trash"></span>')
                .removeClass('cpcm-undo-delete').addClass('cpcm-btn-delete-local');

            window.cpcmSetHasChanges(true);
        });

        /**
         * Helper to generate row HTML
         */
        window.cpcmGenerateRowHtml = function (key, name, type, value, preview)
        {
            var pageId = $('input[name="page_id"]').val();
            var typeIcons = { text: "editor-textcolor", longtext: "media-text", number: "calculator", single_image: "format-image", multi_images: "images-alt2" };
            var icon = typeIcons[type] || "admin-generic";
            var typeLabel = type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");
            var valueDisplay = value;
            var previewDataAttr = "";

            if (type === "single_image")
            {
                if (preview && typeof preview === "string")
                {
                    valueDisplay = '<div class="cpcm-row-preview-container"><img src="' + preview + '" alt="" class="cpcm-table-row-preview"></div>';
                    previewDataAttr = preview;
                } else
                {
                    valueDisplay = '<span class="description">No image selected</span>';
                }
            } else if (type === "multi_images")
            {
                var images = [];
                try { images = (typeof preview === "string" ? JSON.parse(preview) : preview) || []; } catch (e) { }
                if (images.length > 0)
                {
                    valueDisplay = '<div class="cpcm-table-gallery-preview">';
                    images.slice(0, 3).forEach(function (img) { valueDisplay += '<img src="' + img.url + '" alt="">'; });
                    if (images.length > 3) valueDisplay += '<span class="cpcm-gallery-more">+' + (images.length - 3) + "</span>";
                    valueDisplay += "</div>";
                    previewDataAttr = JSON.stringify(images);
                } else
                {
                    valueDisplay = '<span class="description">No images selected</span>';
                }
            } else if (type === "longtext")
            {
                valueDisplay = '<div class="cpcm-table-text-preview">' + (value.length > 100 ? value.substring(0, 100) + "..." : value) + '</div>';
            } else
            {
                valueDisplay = '<div class="cpcm-table-text-preview">' + value + "</div>";
            }

            var shortcode = '[cpcm_field id="' + pageId + '" field="' + key + '"]';
            return '<tr data-field-key="' + key + '">' +
                '<td class="cpcm-td-name"><strong>' + name + '</strong>' +
                '<input type="hidden" name="cpcm_field_registry[' + key + '][name]" value="' + name + '">' +
                '<input type="hidden" name="cpcm_field_registry[' + key + '][type]" value="' + type + '"></td>' +
                '<td class="cpcm-td-type"><span class="cpcm-type-badge cpcm-type-' + type + '"><span class="dashicons dashicons-' + icon + '"></span> ' + typeLabel + '</span></td>' +
                '<td class="cpcm-td-value">' + valueDisplay + '<input type="hidden" name="cpcm_fields[' + key + ']" value="' + value + '" class="cpcm-row-value-input"></td>' +
                '<td class="cpcm-td-shortcode"><div class="cpcm-shortcode-wrapper"><code class="cpcm-shortcode" data-shortcode=\'' + shortcode + "'>" + shortcode + "</code>" +
                '<button type="button" class="button button-small cpcm-btn-copy" data-clipboard=\'' + shortcode + '\'><span class="dashicons dashicons-clipboard"></span></button></div></td>' +
                '<td class="cpcm-td-actions"><button type="button" class="button button-small cpcm-btn-edit-field" data-field-key="' + key + '" data-field-name="' + name + '" data-field-type="' + type + '" data-field-value="' + value + '" data-preview=\'' + (typeof previewDataAttr === "string" ? previewDataAttr : JSON.stringify(previewDataAttr)) + '\'><span class="dashicons dashicons-edit"></span> Edit</button> ' +
                '<button type="button" class="button button-small cpcm-btn-delete-local" data-field-name="' + name + '"><span class="dashicons dashicons-trash"></span> Delete</button></td></tr>';
        };

        /**
         * Change Tracking
         */
        window.cpcmSetHasChanges = function (state)
        {
            if (state)
            {
                $(".cpcm-btn-save-all").prop("disabled", false).removeClass("disabled");
                $(".cpcm-btn-reset-fields").prop("disabled", false);
            } else
            {
                $(".cpcm-btn-save-all").prop("disabled", true).addClass("disabled");
                $(".cpcm-btn-reset-fields").prop("disabled", true);
            }
        };

        $(document).on('input change', '#cpcm-fields-tbody input, #cpcm-fields-tbody select, #cpcm-fields-tbody textarea', function ()
        {
            window.cpcmSetHasChanges(true);
        });

        $(document).on("click", ".cpcm-btn-reset-fields", function (e)
        {
            e.preventDefault();
            if (confirm("Discard unsaved changes?")) location.reload();
        });

        /**
         * Apply Add Field
         */
        $(".cpcm-btn-apply-add").on("click", function ()
        {
            var name = $("#add_field_name").val().trim();
            var type = $("#add_field_type").val();
            var value = "", preview = "";

            if (!name) { showNotification(messages.fieldNameRequired || "Name required.", "error"); return; }
            var key = name.toLowerCase().replace(/[^a-z0-9]/g, "-").replace(/-+/g, "-").replace(/^-|-$/g, "");
            if ($('tr[data-field-key="' + key + '"]').length > 0) { showNotification(messages.fieldAlreadyExists || "Exists.", "error"); return; }

            if (type === "text") value = $('#cpcm-add-modal').find('input[name="field_value_text"]').val();
            else if (type === "longtext") value = $('#cpcm-add-modal').find('textarea[name="field_value_longtext"]').val();
            else if (type === "number") value = $('#cpcm-add-modal').find('input[name="field_value_number"]').val();
            else if (type === "single_image")
            {
                value = $('#cpcm-add-modal').find(".cpcm-image-id").val() || "";
                preview = $('#cpcm-add-modal').find(".cpcm-image-preview img").attr("src") || "";
            } else if (type === "multi_images")
            {
                value = $('#cpcm-add-modal').find(".cpcm-multi-image-ids").val() || "";
                var galleryItems = [];
                $('#cpcm-add-modal').find(".cpcm-multi-image-item").each(function ()
                {
                    galleryItems.push({ id: $(this).data("id"), url: $(this).find("img").attr("src") });
                });
                preview = galleryItems;
            }

            var rowHtml = window.cpcmGenerateRowHtml(key, name, type, value, preview);
            $("#cpcm-fields-tbody").append(rowHtml).find('tr[data-field-key="' + key + '"]').addClass('cpcm-new');

            // Update empty state and count (keeping it simple for now)
            $("#cpcm-empty-state-wrapper").hide();
            $("#cpcm-fields-container").show();
            $(".cpcm-fields-count-text").text("Existing Fields (" + $("#cpcm-fields-tbody tr").length + ")");

            window.cpcmCloseModal();
            window.cpcmSetHasChanges(true);
            showNotification(messages.fieldAddedTemp || "Added (Save to persist)", "success");

            // Reset form
            $("#add_field_name").val("");
            $('#cpcm-add-modal').find('input[name="field_value_text"], textarea, input[name="field_value_number"], .cpcm-image-id, .cpcm-multi-image-ids').val("");
            $('#cpcm-add-modal').find(".cpcm-image-preview, .cpcm-multi-image-preview").html("");
        });

        /**
         * Apply Edit Field
         */
        $(".cpcm-btn-apply-edit").on("click", function ()
        {
            var key = $("#edit_field_key").val();
            var name = $("#edit_field_name").val().trim();
            var type = $("#edit_field_type").val();
            var value = "", preview = "";

            if (!name) { showNotification("Name required.", "error"); return; }

            if (type === "text") value = $("#edit_field_value_text").val();
            else if (type === "longtext") value = $("#edit_field_value_longtext").val();
            else if (type === "number") value = $("#edit_field_value_number").val();
            else if (type === "single_image")
            {
                value = $("#edit_field_value_image").val();
                preview = $("#edit_field_content_container .cpcm-image-preview img").attr("src") || "";
            } else if (type === "multi_images")
            {
                value = $("#edit_field_value_gallery").val();
                var galleryItems = [];
                $("#edit_field_content_container .cpcm-multi-image-item").each(function ()
                {
                    galleryItems.push({ id: $(this).data("id"), url: $(this).find("img").attr("src") });
                });
                preview = galleryItems;
            }

            var rowHtml = window.cpcmGenerateRowHtml(key, name, type, value, preview);
            $('tr[data-field-key="' + key + '"]').replaceWith(rowHtml);

            window.cpcmCloseModal();
            window.cpcmSetHasChanges(true);
            showNotification(messages.fieldUpdatedTemp || "Updated (Save to persist)", "success");
        });

        // Open Edit Modal event
        $(document).on("click", ".cpcm-btn-edit-field", function (e)
        {
            e.preventDefault();
            var $btn = $(this), key = $btn.data("field-key"), name = $btn.data("field-name"), type = $btn.data("field-type"), val = $btn.data("field-value"), preview = $btn.data("preview");
            $("#edit_field_key").val(key);
            $("#edit_field_name").val(name);
            $("#edit_field_type").val(type).data("original-type", type);
            $("#edit_field_value_text, #edit_field_value_longtext, #edit_field_value_number, #edit_field_value_image, #edit_field_value_gallery").val("");
            $(".cpcm-image-preview, .cpcm-multi-image-preview").html("");

            if (type === "text") $("#edit_field_value_text").val(val);
            else if (type === "longtext") $("#edit_field_value_longtext").val(val);
            else if (type === "number") $("#edit_field_value_number").val(val);
            else if (type === "single_image")
            {
                $("#edit_field_value_image").val(val);
                if (preview) $("#edit_field_content_container .cpcm-image-preview").html('<img src="' + preview + '" alt=""><button type="button" class="cpcm-remove-image"><span class="dashicons dashicons-no-alt"></span></button>');
            } else if (type === "multi_images")
            {
                $("#edit_field_value_gallery").val(val);
                var images = (typeof preview === "string" && preview.startsWith("[")) ? JSON.parse(preview) : preview || [];
                images.forEach(function (img)
                {
                    $("#edit_field_content_container .cpcm-multi-image-preview").append('<div class="cpcm-multi-image-item" data-id="' + img.id + '"><img src="' + img.url + '" alt=""><button type="button" class="cpcm-remove-multi-image"><span class="dashicons dashicons-no-alt"></span></button></div>');
                });
            }
            $(".cpcm-warning").hide();
            window.cpcmUpdateContentInputs(type);
            $("#cpcm-edit-modal").css("display", "flex").hide().fadeIn(200);
        });
    });
})(jQuery);
