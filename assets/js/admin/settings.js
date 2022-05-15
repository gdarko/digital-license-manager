jQuery(function ($) {
    'use strict';

    var $selectUser = $('select#user');

    if ($selectUser.length) {
        $selectUser.select2();
    }
});


(function ($) {

    'use strict';
    // The "Upload" button
    $(document).on('click', '.dlm-field-upload-button', function () {
        window.wpActiveEditor = null;
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var show_attachment_preview = $(this).closest('.dlm-field-upload').data('show-attachment-preview')
        var button = $(this);
        wp.media.editor.send.attachment = function (props, attachment) {
            if (show_attachment_preview) {
                $(button).parent().prev().attr('src', attachment.url);
            }
            $(button).prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        };
        wp.media.editor.open(null, {
            frame: 'post',
            state: 'insert',
            multiple: false
        });
        return false;
    });

    // The "Remove" button (remove the value from input type='hidden')
    $(document).on('click', '.dlm-field-remove-button', function () {
        var answer = confirm('Are you sure?');
        if (answer) {
            var show_attachment_preview = $(this).closest('.dlm-field-upload').data('show-attachment-preview')
            if (show_attachment_preview) {
                var src = $(this).parent().prev().attr('data-src');
                $(this).parent().prev().attr('src', src);
            }
            $(this).prev().prev().val('');
        }
        return false;
    });
})(jQuery);
