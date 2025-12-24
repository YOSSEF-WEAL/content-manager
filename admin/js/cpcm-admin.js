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
     * Confirm field deletion
     */
    $(".cpcm-btn-delete").on("click", function (e) {
      var fieldName = $(this).data("field-name");
      var confirmMessage =
        cpcmAdmin.confirmDelete ||
        "Are you sure you want to delete this field?";

      if (fieldName) {
        confirmMessage = confirmMessage.replace("%s", fieldName);
      }

      if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
      }
    });

    /**
     * Toast Notifications System
     */
    var $notificationsContainer;

    function showNotification(message, type) {
      type = type || "info";

      // Create container if it doesn't exist
      if (!$notificationsContainer || !$notificationsContainer.length) {
        $notificationsContainer = $(
          '<div class="cpcm-notifications-container"></div>'
        );
        $("body").append($notificationsContainer);
      }

      // Icon mapping
      var icons = {
        success: "yes-alt",
        error: "warning",
        info: "info",
      };
      var icon = icons[type] || "info";

      // Create toast HTML
      var $toast = $(
        '<div class="cpcm-toast cpcm-toast-' +
          type +
          '">' +
          '<div class="cpcm-toast-icon"><span class="dashicons dashicons-' +
          icon +
          '"></span></div>' +
          '<div class="cpcm-toast-content">' +
          '<p class="cpcm-toast-message">' +
          message +
          "</p>" +
          "</div>" +
          '<div class="cpcm-toast-close"><span class="dashicons dashicons-no-alt"></span></div>' +
          '<div class="cpcm-toast-progress"></div>' +
          "</div>"
      );

      $notificationsContainer.append($toast);

      // Trigger animation
      setTimeout(function () {
        $toast.addClass("active");
      }, 100);

      // Auto-remove after 5 seconds
      var timeout = setTimeout(function () {
        removeToast($toast);
      }, 5000);

      // Close button handler
      $toast.find(".cpcm-toast-close").on("click", function () {
        clearTimeout(timeout);
        removeToast($toast);
      });

      function removeToast($t) {
        $t.removeClass("active");
        setTimeout(function () {
          $t.remove();
        }, 500);
      }
    }

    /**
     * Handle initial notifications on page load
     */
    $(".cpcm-wrap .notice").each(function () {
      var $notice = $(this);
      var message = $notice.find("p").text();
      var type = "info";

      if ($notice.hasClass("notice-success")) type = "success";
      if ($notice.hasClass("notice-error")) type = "error";

      // Hide original and show toast
      $notice.hide();
      showNotification(message, type);
    });

    /**
     * Form validation
     */

    /**
     * Form validation
     */
    $("form.cpcm-form").on("submit", function (e) {
      var $form = $(this);
      var $fieldName = $form.find('input[name="field_name"]');

      if ($fieldName.length && !$fieldName.val().trim()) {
        e.preventDefault();
        $fieldName.focus();
        showNotification("Please enter a field name.", "error");
        return false;
      }
    });

    /**
     * WordPress Media Library - Single Image Upload
     */
    var mediaUploader;

    $(".cpcm-upload-image").on("click", function (e) {
      e.preventDefault();

      // Check if wp.media is available
      if (typeof wp === "undefined" || typeof wp.media === "undefined") {
        console.error("WordPress media library is not loaded");
        alert("Error: Media library not loaded. Please refresh the page.");
        return;
      }

      var $button = $(this);
      var $wrapper = $button.closest(".cpcm-image-upload-wrapper");
      var $input = $wrapper.find(".cpcm-image-id");
      var $preview = $wrapper.find(".cpcm-image-preview");

      // If the uploader object has already been created, reopen the dialog
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      // Extend the wp.media object
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Image",
        button: {
          text: "Choose Image",
        },
        multiple: false,
      });

      // When a file is selected, run a callback
      mediaUploader.on("select", function () {
        var attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();

        $input.val(attachment.id);

        var imageUrl = attachment.sizes.medium
          ? attachment.sizes.medium.url
          : attachment.url;

        $preview.html(
          '<img src="' +
            imageUrl +
            '" alt="">' +
            '<button type="button" class="cpcm-remove-image" title="Remove image">' +
            '<span class="dashicons dashicons-no-alt"></span>' +
            "</button>"
        );
      });

      // Open the uploader dialog
      mediaUploader.open();
    });

    // Remove single image
    $(document).on("click", ".cpcm-remove-image", function (e) {
      e.preventDefault();
      var $wrapper = $(this).closest(".cpcm-image-upload-wrapper");
      $wrapper.find(".cpcm-image-id").val("");
      $wrapper.find(".cpcm-image-preview").html("");
    });

    /**
     * WordPress Media Library - Multiple Images Upload
     */
    var multiMediaUploader;

    $(".cpcm-upload-multi-images").on("click", function (e) {
      e.preventDefault();

      var $button = $(this);
      var $wrapper = $button.closest(".cpcm-multi-image-wrapper");
      var $input = $wrapper.find(".cpcm-multi-image-ids");
      var $preview = $wrapper.find(".cpcm-multi-image-preview");

      // Create the media frame
      multiMediaUploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Images",
        button: {
          text: "Add Images",
        },
        multiple: true,
      });

      // When images are selected
      multiMediaUploader.on("select", function () {
        var attachments = multiMediaUploader.state().get("selection").toJSON();

        var currentIds = $input.val() ? $input.val().split(",") : [];

        attachments.forEach(function (attachment) {
          if (currentIds.indexOf(attachment.id.toString()) === -1) {
            currentIds.push(attachment.id);

            var imageUrl = attachment.sizes.thumbnail
              ? attachment.sizes.thumbnail.url
              : attachment.url;

            $preview.append(
              '<div class="cpcm-multi-image-item" data-id="' +
                attachment.id +
                '">' +
                '<img src="' +
                imageUrl +
                '" alt="">' +
                '<button type="button" class="cpcm-remove-multi-image">' +
                '<span class="dashicons dashicons-no-alt"></span>' +
                "</button>" +
                "</div>"
            );
          }
        });

        $input.val(currentIds.join(","));
      });

      multiMediaUploader.open();
    });

    // Remove image from multi-image field
    $(document).on("click", ".cpcm-remove-multi-image", function (e) {
      e.preventDefault();
      var $item = $(this).closest(".cpcm-multi-image-item");
      var $wrapper = $item.closest(".cpcm-multi-image-wrapper");
      var $input = $wrapper.find(".cpcm-multi-image-ids");
      var imageId = $item.data("id").toString();

      var currentIds = $input.val().split(",");
      currentIds = currentIds.filter(function (id) {
        return id !== imageId;
      });

      $input.val(currentIds.join(","));
      $item.remove();
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
        if (preview && Array.isArray(preview)) {
          var $galleryContainer = $(
            "#edit_field_content_container .cpcm-multi-image-preview"
          );
          preview.forEach(function (img) {
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
    });
  });
})(jQuery);
