(function ($)
{
    "use strict";

    $(document).ready(function ()
    {
        var $modals = $(".cpcm-modal");

        // Close function
        window.cpcmCloseModal = function ()
        {
            $modals.fadeOut(200);
            setTimeout(function ()
            {
                $modals.css("display", "none");
            }, 200);
        };

        // Open Add Modal
        $(document).on("click", ".cpcm-btn-add-modal-trigger", function (e)
        {
            e.preventDefault();
            $("#cpcm-add-modal").css("display", "flex").hide().fadeIn(200);
            setTimeout(function ()
            {
                $("#add_field_name").focus();
            }, 100);
        });

        // Close events
        $(document).on(
            "click",
            ".cpcm-modal-overlay, .cpcm-modal-close, .cpcm-modal-cancel",
            window.cpcmCloseModal
        );

        // Close on ESC key
        $(document).on("keydown", function (e)
        {
            if (e.key === "Escape" && $modals.is(":visible"))
            {
                window.cpcmCloseModal();
            }
        });

        // Type change warning and Dynamic Content Display
        window.cpcmUpdateContentInputs = function (type)
        {
            // Hide all inputs first
            $(".cpcm-input-wrapper").hide();
            // Show relevant input
            $(".cpcm-input-" + type).show();
        };

        // On Add Field Modal type change
        $("#add_field_type")
            .on("change", function ()
            {
                var type = $(this).val();
                window.cpcmUpdateContentInputs(type);
            })
            .trigger("change"); // Trigger on init

        // On Edit Field Modal type change
        $("#edit_field_type").on("change", function ()
        {
            var originalType = $(this).data("original-type");
            var newType = $(this).val();

            if (originalType !== newType)
            {
                $(".cpcm-warning").slideDown();
            } else
            {
                $(".cpcm-warning").slideUp();
            }

            window.cpcmUpdateContentInputs(newType);
        });
    });
})(jQuery);
