(function ($)
{
    "use strict";

    $(document).ready(function ()
    {
        /**
         * Single Image Selection
         */
        $(document).on("click", ".cpcm-upload-image", function (e)
        {
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest(".cpcm-image-upload-wrapper");
            var $inputId = $wrapper.find(".cpcm-image-id");
            var $preview = $wrapper.find(".cpcm-image-preview");

            var frame = wp.media({
                title: "Select or Upload Image",
                button: { text: "Use this image" },
                multiple: false,
            });

            frame.on("select", function ()
            {
                var attachment = frame.state().get("selection").first().toJSON();
                $inputId.val(attachment.id);
                $preview.html(
                    '<img src="' + attachment.url + '" alt="">' +
                    '<button type="button" class="cpcm-remove-image" title="Remove image">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>'
                );
                window.cpcmSetHasChanges(true);
            });

            frame.open();
        });

        $(document).on("click", ".cpcm-remove-image", function (e)
        {
            e.preventDefault();
            var $wrapper = $(this).closest(".cpcm-image-upload-wrapper");
            $wrapper.find(".cpcm-image-id").val("");
            $wrapper.find(".cpcm-image-preview").html("");
            window.cpcmSetHasChanges(true);
        });

        /**
         * Multi Image Selection
         */
        $(document).on("click", ".cpcm-upload-multi-images", function (e)
        {
            e.preventDefault();
            var $button = $(this);
            var $wrapper = $button.closest(".cpcm-multi-image-wrapper");
            var $inputId = $wrapper.find(".cpcm-multi-image-ids");
            var $preview = $wrapper.find(".cpcm-multi-image-preview");

            var frame = wp.media({
                title: "Select or Upload Images",
                button: { text: "Add to gallery" },
                multiple: true,
            });

            frame.on("select", function ()
            {
                var selection = frame.state().get("selection");
                var ids = $inputId.val() ? $inputId.val().split(",") : [];

                selection.map(function (attachment)
                {
                    attachment = attachment.toJSON();
                    if (ids.indexOf(attachment.id.toString()) === -1)
                    {
                        ids.push(attachment.id);
                        $preview.append(
                            '<div class="cpcm-multi-image-item" data-id="' + attachment.id + '">' +
                            '<img src="' + attachment.url + '" alt="">' +
                            '<button type="button" class="cpcm-remove-multi-image">' +
                            '<span class="dashicons dashicons-no-alt"></span>' +
                            '</button>' +
                            '</div>'
                        );
                    }
                });

                $inputId.val(ids.join(","));
                window.cpcmSetHasChanges(true);
            });

            frame.open();
        });

        $(document).on("click", ".cpcm-remove-multi-image", function (e)
        {
            e.preventDefault();
            var $item = $(this).closest(".cpcm-multi-image-item");
            var $wrapper = $(this).closest(".cpcm-multi-image-wrapper");
            var $inputId = $wrapper.find(".cpcm-multi-image-ids");
            var idToRemove = $item.data("id").toString();

            var ids = $inputId.val().split(",");
            ids = ids.filter(function (id)
            {
                return id !== idToRemove;
            });

            $inputId.val(ids.join(","));
            $item.remove();
            window.cpcmSetHasChanges(true);
        });
    });
})(jQuery);
