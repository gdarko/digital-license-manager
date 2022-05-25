(function ($) {

    function tools_init($form, callback) {

        var data = $form.serializeArray();
        data.push({name: 'init', value: 1});

        $.ajax({
            type: 'POST',
            cache: false,
            url: DLM_Tools.ajax_url + '?action=dlm_handle_tool_process&_wpnonce=' + DLM_Tools.nonce,
            data: data,
            success: function (response) {
                if (response.success) {
                    callback();
                } else {
                    alert(response.data.message);
                }
            },
            error: function () {
                alert('HTTP Error');
            },
        });

    }

    function tools_process($form, step, page) {

        var data = $form.serializeArray();

        var $progressbarValue = $form.find('.dlm-tool-progress-bar-inner');
        var $progressInfoValue = $form.find('.dlm-tool-progress-info');
        var $submitButton = $form.find('button[type=submit]');
        $form.find('.dlm-tool-form-row-progress').show();

        $submitButton.addClass('disabled');

        // Enable navigation prompt
        window.onbeforeunload = function () {
            return true;
        };

        data.push({name: 'step', value: step});
        data.push({name: 'page', value: page});

        $.ajax({
            type: 'POST',
            cache: false,
            url: DLM_Tools.ajax_url + '?action=dlm_handle_tool_process&_wpnonce=' + DLM_Tools.nonce,
            data: data,
            success: function (response) {
                if (response.success) {
                    var next_step = response.data.next_step;
                    var next_page = response.data.next_page;
                    var message = response.data.message;
                    var percent = response.data.percent;

                    $progressbarValue.css('width', percent + '%');
                    $progressInfoValue.text(message + ' ' + '(' + percent + '%)');

                    if (next_step > 0 && next_page > 0) {
                        setTimeout(function () {
                            tools_process($form, next_step, next_page)
                        }, 2000);
                    } else {
                        // Remove navigation prompt
                        window.onbeforeunload = null;
                        $submitButton.removeClass('disabled');
                        $submitButton.hide();
                    }
                }
            },
            error: function () {
                alert('HTTP Error');
                // Remove navigation prompt
                window.onbeforeunload = null;
            },
        });
    }

    $(document).on('submit', '.dlm-tool-form', function () {

        var $self = $(this);

        tools_init($self, function () {
            tools_process($self, 1, 1);
        });

        return false;
    });

})(jQuery);
