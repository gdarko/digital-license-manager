window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {
    /**
     * Admin tools
     * @param form
     * @constructor
     */
    var AdminTools = function (form) {
        this.form = form;
    };
    /**
     * Initializes the tool
     * @param step
     * @param page
     */
    AdminTools.prototype.init = function (step, page) {
        var data = new FormData(this.form);
        var http = new window.DLM.Http();
        var url = DLM_Tools.ajax_url + '?action=dlm_handle_tool_process&_wpnonce=' + DLM_Tools.nonce;
        var self = this;
        data.append('init', 1)
        http.post(url, {
            data: data,
            success: function (response, responseStatus, responseHeaders) {
                if (response.success) {
                    self.process(step, page);
                } else {
                    alert(response.data.message)
                }
            },
            error: function (response, responseStatus, responseHeaders) {

            }
        });
    }
    /**
     * Processes single step
     * @param step
     * @param page
     */
    AdminTools.prototype.process = function (step, page) {
        var self = this;
        var data = new FormData(this.form);
        var http = new window.DLM.Http();
        var url = DLM_Tools.ajax_url + '?action=dlm_handle_tool_process&_wpnonce=' + DLM_Tools.nonce;
        var progressBarValue = this.form.querySelector('.dlm-tool-progress-bar-inner');
        var progressBarInfo = this.form.querySelector('.dlm-tool-progress-info');
        var progressBarRow = this.form.querySelector('.dlm-tool-form-row-progress');
        var submitButton = this.form.querySelector('button[type=submit]');
        progressBarRow.style.display = 'block';
        submitButton.classList.add('disabled');
        window.onbeforeunload = function () {
            return true;
        }
        data.append('step', step);
        data.append('page', page);
        http.post(url, {
            data: data,
            success: function (response, responseStatus, responseHeaders) {
                var next_step = response.data.next_step;
                var next_page = response.data.next_page;
                var message = response.data.message;
                var percent = response.data.percent;
                progressBarValue.style.width = percent + '%';
                progressBarInfo.innerHTML = (message + ' ' + '(' + percent + '%)');
                if (next_step > 0 && next_page > 0) {
                    setTimeout(function () {
                        self.process(next_step, next_page)
                    }, 2000);
                } else {
                    // Remove navigation prompt
                    window.onbeforeunload = null;
                    submitButton.classList.remove('disabled');
                    submitButton.style.display = 'none';
                }
            },
            error: function (response, responseStatus, responseHeaders) {
                alert('HTTP Error');
                // Remove navigation prompt
                window.onbeforeunload = null;
            }
        });
    }
    /**
     * Global.
     * @type {AdminTools}
     */
    window.DLM.AdminTools = AdminTools;
    /**
     * Initalize
     */
    var form = document.querySelector('.dlm-tool-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var tools = new window.DLM.AdminTools(form);
            tools.init(1, 1);
        })
    }
});

