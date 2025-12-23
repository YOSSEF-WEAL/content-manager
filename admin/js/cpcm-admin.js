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
     * Show notification
     */
    function showNotification(message, type) {
      type = type || "info";

      var $notice = $("<div>")
        .addClass("notice notice-" + type + " is-dismissible")
        .html("<p>" + message + "</p>");

      $(".cpcm-wrap").prepend($notice);

      // Auto-dismiss after 3 seconds
      setTimeout(function () {
        $notice.fadeOut(function () {
          $(this).remove();
        });
      }, 3000);
    }

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
     * Auto-dismiss WordPress notices
     */
    setTimeout(function () {
      $(".notice.is-dismissible").fadeOut();
    }, 5000);

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
  });
})(jQuery);
