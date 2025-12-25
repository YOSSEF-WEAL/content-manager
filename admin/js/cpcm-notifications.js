/**
 * Toast Notification System for CPCM
 */
function showNotification(message, type = "info")
{
    var $container = jQuery(".cpcm-notifications-container");
    if ($container.length === 0)
    {
        $container = jQuery('<div class="cpcm-notifications-container"></div>').appendTo("body");
    }

    var icons = {
        success: "yes",
        error: "warning",
        info: "info",
    };

    var icon = icons[type] || "info";

    var $toast = jQuery(
        '<div class="cpcm-toast cpcm-toast-' + type + '">' +
        '<div class="cpcm-toast-icon"><span class="dashicons dashicons-' + icon + '"></span></div>' +
        '<div class="cpcm-toast-content"><p class="cpcm-toast-message">' + message + "</p></div>" +
        '<div class="cpcm-toast-close"><span class="dashicons dashicons-no-alt"></span></div>' +
        '<div class="cpcm-toast-progress"></div>' +
        "</div>"
    );

    $container.append($toast);

    // Trigger animation
    setTimeout(function ()
    {
        $toast.addClass("active");
    }, 10);

    // Auto remove after 5 seconds
    var timeout = setTimeout(function ()
    {
        removeToast($toast);
    }, 5000);

    // Close button
    $toast.find(".cpcm-toast-close").on("click", function ()
    {
        clearTimeout(timeout);
        removeToast($toast);
    });

    function removeToast($t)
    {
        $t.removeClass("active");
        setTimeout(function ()
        {
            $t.remove();
        }, 500);
    }
}
