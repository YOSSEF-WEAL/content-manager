/**
 * Admin JavaScript for Custom Page Content Manager
 *
 * @package    CPCM
 * @subpackage CPCM/admin/js
 */

(function ($) {
  "use strict";

  $(document).ready(function () {
    /**
     * Copy shortcode to clipboard
     */
    $(".cpcm-btn-copy").on("click", function (e) {
      e.preventDefault();

      var shortcode = $(this).data("clipboard");
      var $button = $(this);

      // Create temporary textarea
      var $temp = $("<textarea>");
      $("body").append($temp);
      $temp.val(shortcode).select();

      try {
        // Copy to clipboard
        document.execCommand("copy");

        // Visual feedback
        var originalHTML = $button.html();
        $button.html('<span class="dashicons dashicons-yes"></span>');
        $button.css("background", "#10b981");

        // Show success message
        if (typeof cpcmAdmin !== "undefined" && cpcmAdmin.copiedToClipboard) {
          showNotification(cpcmAdmin.copiedToClipboard, "success");
        }

        // Reset button after 2 seconds
        setTimeout(function () {
          $button.html(originalHTML);
          $button.css("background", "");
        }, 2000);
      } catch (err) {
        console.error("Failed to copy:", err);
        showNotification("Failed to copy. Please copy manually.", "error");
      }

      $temp.remove();
    });

    /**
     * Local Field Deletion
     */
    $(document).on("click", ".cpcm-btn-delete-local", function (e) {
      e.preventDefault();
      var $row = $(this).closest("tr");
      var fieldName = $(this).data("field-name");
      var confirmMessage =
        cpcmAdmin.confirmDelete ||
        "Are you sure you want to delete this field?";

      if (fieldName) {
        confirmMessage = confirmMessage.replace("%s", fieldName);
      }

      if (confirm(confirmMessage)) {
        $row.fadeOut(300, function () {
          $(this).remove();
          updateEmptyState();
        });
        trackChanges();
        showNotification("Field removed from list (Save to persist)", "info");
      }
    });

    function updateEmptyState() {
      if ($("#cpcm-fields-tbody tr").length === 0) {
        location.reload(); // Simplest way to show empty state from PHP
      }
    }

    /**
     * Helper to generate row HTML
     */
    function generateRowHtml(key, name, type, value, preview) {
      var pageId = $('input[name="page_id"]').val();
      var typeIcons = {
        text: "editor-textcolor",
        longtext: "media-text",
        number: "calculator",
        single_image: "format-image",
        multi_images: "images-alt2",
      };
      var icon = typeIcons[type] || "admin-generic";
      var typeLabel =
        type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");

      var valueDisplay = value;
      var previewDataAttr = "";

      if (type === "single_image") {
        if (preview && typeof preview === "string") {
          valueDisplay =
            '<div class="cpcm-row-preview-container"><img src="' +
            preview +
            '" alt="" class="cpcm-table-row-preview"></div>';
          previewDataAttr = preview;
        } else {
          valueDisplay = '<span class="description">No image selected</span>';
        }
      } else if (type === "multi_images") {
        var images = [];
        if (typeof preview === "string" && preview.startsWith("[")) {
          try {
            images = JSON.parse(preview);
          } catch (e) {}
        } else if (Array.isArray(preview)) {
          images = preview;
        }

        if (images.length > 0) {
          valueDisplay = '<div class="cpcm-table-gallery-preview">';
          images.slice(0, 3).forEach(function (img) {
            valueDisplay += '<img src="' + img.url + '" alt="">';
          });
          if (images.length > 3) {
            valueDisplay +=
              '<span class="cpcm-gallery-more">+' +
              (images.length - 3) +
              "</span>";
          }
          valueDisplay += "</div>";
          previewDataAttr = JSON.stringify(images);
        } else {
          valueDisplay = '<span class="description">No images selected</span>';
        }
      } else if (type === "longtext") {
        valueDisplay =
          '<div class="cpcm-table-text-preview">' +
          (value.length > 100 ? value.substring(0, 100) + "..." : value) +
          "</div>";
      } else {
        valueDisplay =
          '<div class="cpcm-table-text-preview">' + value + "</div>";
      }

      var shortcode = '[cpcm_field id="' + pageId + '" field="' + key + '"]';

      return (
        '<tr data-field-key="' +
        key +
        '">' +
        '<td class="cpcm-td-name">' +
        "<strong>" +
        name +
        "</strong>" +
        '<input type="hidden" name="cpcm_field_registry[' +
        key +
        '][name]" value="' +
        name +
        '">' +
        '<input type="hidden" name="cpcm_field_registry[' +
        key +
        '][type]" value="' +
        type +
        '">' +
        "</td>" +
        '<td class="cpcm-td-type">' +
        '<span class="cpcm-type-badge cpcm-type-' +
        type +
        '">' +
        '<span class="dashicons dashicons-' +
        icon +
        '"></span> ' +
        typeLabel +
        "</span>" +
        "</td>" +
        '<td class="cpcm-td-value">' +
        valueDisplay +
        '<input type="hidden" name="cpcm_fields[' +
        key +
        ']" value="' +
        value +
        '" class="cpcm-row-value-input">' +
        "</td>" +
        '<td class="cpcm-td-shortcode">' +
        '<div class="cpcm-shortcode-wrapper">' +
        '<code class="cpcm-shortcode" data-shortcode=\'' +
        shortcode +
        "'>" +
        shortcode +
        "</code>" +
        '<button type="button" class="button button-small cpcm-btn-copy" data-clipboard=\'' +
        shortcode +
        "'>" +
        '<span class="dashicons dashicons-clipboard"></span>' +
        "</button>" +
        "</div>" +
        "</td>" +
        '<td class="cpcm-td-actions">' +
        '<button type="button" class="button button-small cpcm-btn-edit-field" ' +
        'data-field-key="' +
        key +
        '" ' +
        'data-field-name="' +
        name +
        '" ' +
        'data-field-type="' +
        type +
        '" ' +
        'data-field-value="' +
        value +
        '" ' +
        "data-preview='" +
        (typeof previewDataAttr === "string"
          ? previewDataAttr
          : JSON.stringify(previewDataAttr)) +
        "'>" +
        '<span class="dashicons dashicons-edit"></span> Edit' +
        "</button> " +
        '<button type="button" class="button button-small cpcm-btn-delete-local" data-field-name="' +
        name +
        '">' +
        '<span class="dashicons dashicons-trash"></span> Delete' +
        "</button>" +
        "</td>" +
        "</tr>"
      );
    }

    /**
     * Change Tracking System
     */
    var hasChanges = false;
    var $saveButton = $(".cpcm-btn-save-all");
    var $resetButton = $(".cpcm-btn-reset-fields");

    function trackChanges() {
      hasChanges = true;
      $saveButton.prop("disabled", false);
      $resetButton.prop("disabled", false);
    }

    /**
     * Reset Functionality
     */
    $(document).on("click", ".cpcm-btn-reset-fields", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to discard all unsaved changes and reload?"
        )
      ) {
        location.reload();
      }
    });

    /**
     * Apply Add Field
     */
    $(".cpcm-btn-apply-add").on("click", function () {
      var name = $("#add_field_name").val().trim();
      var type = $("#add_field_type").val();
      var value = "";
      var preview = "";

      if (!name) {
        showNotification("Please enter a field name.", "error");
        return;
      }

      var key = name
        .toLowerCase()
        .replace(/[^a-z0-9]/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-|-$/g, "");

      // Check if key already exists
      if ($('tr[data-field-key="' + key + '"]').length > 0) {
        showNotification(
          "A field with this name already exists locally.",
          "error"
        );
        return;
      }

      // Get value and preview based on type
      if (type === "text") {
        value = $('input[name="field_value_text"]').val();
      } else if (type === "longtext") {
        value = $('textarea[name="field_value_longtext"]').val();
      } else if (type === "number") {
        value = $('input[name="field_value_number"]').val();
      } else if (type === "single_image") {
        value = $("#add_modal .cpcm-image-id").val() || "";
        preview = $("#add_modal .cpcm-image-preview img").attr("src") || "";
      } else if (type === "multi_images") {
        value = $("#add_modal .cpcm-multi-image-ids").val() || "";
        var galleryItems = [];
        $("#add_modal .cpcm-multi-image-item").each(function () {
          galleryItems.push({
            id: $(this).data("id"),
            url: $(this).find("img").attr("src"),
          });
        });
        preview = galleryItems;
      }

      var rowHtml = generateRowHtml(key, name, type, value, preview);

      if ($("#cpcm-fields-tbody").length === 0) {
        // If empty state was showing, reload to get table structure or handle it
        location.reload();
        return;
      }

      $("#cpcm-fields-tbody").append(rowHtml);
      closeModal();
      trackChanges();
      showNotification("Field added to list (Save to persist)", "success");

      // Clear add form
      $("#add_field_name").val("");
      $('input[name="field_value_text"]').val("");
      $('textarea[name="field_value_longtext"]').val("");
      $('input[name="field_value_number"]').val("");
      $(".cpcm-image-id").val("");
      $(".cpcm-image-preview").html("");
      $(".cpcm-multi-image-ids").val("");
      $(".cpcm-multi-image-preview").html("");
    });

    /**
     * Apply Edit Field
     */
    $(".cpcm-btn-apply-edit").on("click", function () {
      var key = $("#edit_field_key").val();
      var name = $("#edit_field_name").val().trim();
      var type = $("#edit_field_type").val();
      var value = "";
      var preview = "";

      if (!name) {
        showNotification("Please enter a field name.", "error");
        return;
      }

      // Get value and preview based on type
      if (type === "text") {
        value = $("#edit_field_value_text").val();
      } else if (type === "longtext") {
        value = $("#edit_field_value_longtext").val();
      } else if (type === "number") {
        value = $("#edit_field_value_number").val();
      } else if (type === "single_image") {
        value = $("#edit_field_value_image").val();
        preview =
          $("#edit_field_content_container .cpcm-image-preview img").attr(
            "src"
          ) || "";
      } else if (type === "multi_images") {
        value = $("#edit_field_value_gallery").val();
        var galleryItems = [];
        $("#edit_field_content_container .cpcm-multi-image-item").each(
          function () {
            galleryItems.push({
              id: $(this).data("id"),
              url: $(this).find("img").attr("src"),
            });
          }
        );
        preview = galleryItems;
      }

      var rowHtml = generateRowHtml(key, name, type, value, preview);
      $('tr[data-field-key="' + key + '"]').replaceWith(rowHtml);

      closeModal();
      trackChanges();
      showNotification("Field updated in list (Save to persist)", "success");
    });

    /**
     * Modal Handling
     */
    var $editModal = $("#cpcm-edit-modal");
    var $addModal = $("#cpcm-add-modal");
    var $modals = $(".cpcm-modal");
    var $overlay = $(".cpcm-modal-overlay");
    var $closeBtn = $(".cpcm-modal-close");
    var $cancelBtn = $(".cpcm-modal-cancel");

    // Open Edit Modal
    $(document).on("click", ".cpcm-btn-edit-field", function (e) {
      e.preventDefault();

      var $btn = $(this);
      var fieldKey = $btn.data("field-key");
      var fieldName = $btn.data("field-name");
      var fieldType = $btn.data("field-type");
      var fieldValue = $btn.data("field-value");
      var preview = $btn.data("preview");

      // Populate form
      $("#edit_field_key").val(fieldKey);
      $("#edit_field_name").val(fieldName);
      $("#edit_field_type").val(fieldType);

      // Populate Content
      $("#edit_field_value_text").val("");
      $("#edit_field_value_longtext").val("");
      $("#edit_field_value_number").val("");
      $("#edit_field_value_image").val("");
      $("#edit_field_value_gallery").val("");
      $("#edit_field_content_container .cpcm-image-preview").html("");
      $("#edit_field_content_container .cpcm-multi-image-preview").html("");

      if (fieldType === "text") {
        $("#edit_field_value_text").val(fieldValue);
      } else if (fieldType === "longtext") {
        $("#edit_field_value_longtext").val(fieldValue);
      } else if (fieldType === "number") {
        $("#edit_field_value_number").val(fieldValue);
      } else if (fieldType === "single_image") {
        $("#edit_field_value_image").val(fieldValue);
        if (preview) {
          var $previewContainer = $(
            "#edit_field_content_container .cpcm-image-preview"
          );
          $previewContainer.html(
            '<img src="' +
              preview +
              '" alt="">' +
              '<button type="button" class="cpcm-remove-image" title="Remove image">' +
              '<span class="dashicons dashicons-no-alt"></span>' +
              "</button>"
          );
        }
      } else if (fieldType === "multi_images") {
        $("#edit_field_value_gallery").val(fieldValue);
        var images = [];
        if (typeof preview === "string" && preview.startsWith("[")) {
          try {
            images = JSON.parse(preview);
          } catch (e) {}
        } else if (Array.isArray(preview)) {
          images = preview;
        }

        if (images.length > 0) {
          var $galleryContainer = $(
            "#edit_field_content_container .cpcm-multi-image-preview"
          );
          images.forEach(function (img) {
            $galleryContainer.append(
              '<div class="cpcm-multi-image-item" data-id="' +
                img.id +
                '">' +
                '<img src="' +
                img.url +
                '" alt="">' +
                '<button type="button" class="cpcm-remove-multi-image">' +
                '<span class="dashicons dashicons-no-alt"></span>' +
                "</button>" +
                "</div>"
            );
          });
        }
      }

      // Store original type for comparison
      $("#edit_field_type").data("original-type", fieldType);

      // Hide warning initially
      $(".cpcm-warning").hide();

      // Update content inputs visibility
      updateContentInputs(fieldType);

      // Show modal
      $editModal.css("display", "flex");
    });

    // Open Add Modal
    $(document).on("click", ".cpcm-btn-add-modal-trigger", function (e) {
      e.preventDefault();
      $addModal.css("display", "flex");
      setTimeout(function () {
        $("#add_field_name").focus();
      }, 100);
    });

    // Close function
    function closeModal() {
      $modals.fadeOut(200);
      setTimeout(function () {
        $modals.css("display", "none");
      }, 200);
    }

    // Close events
    $(document).on(
      "click",
      ".cpcm-modal-overlay, .cpcm-modal-close, .cpcm-modal-cancel",
      closeModal
    );

    // Close on ESC key
    $(document).on("keydown", function (e) {
      if (e.key === "Escape" && $modals.is(":visible")) {
        closeModal();
      }
    });

    // Type change warning and Dynamic Content Display
    function updateContentInputs(type) {
      // Hide all inputs first
      $(".cpcm-input-wrapper").hide();

      // Show relevant input
      $(".cpcm-input-" + type).show();
    }

    // On Add Field Modal type change
    $("#add_field_type")
      .on("change", function () {
        var type = $(this).val();
        updateContentInputs(type);
      })
      .trigger("change"); // Trigger on init

    // On Edit Field Modal type change
    $("#edit_field_type").on("change", function () {
      var originalType = $(this).data("original-type");
      var newType = $(this).val();

      if (originalType !== newType) {
        $(".cpcm-warning").slideDown();
      } else {
        $(".cpcm-warning").slideUp();
      }

      updateContentInputs(newType);
    });
  });
})(jQuery);
