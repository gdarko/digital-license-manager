/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

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
                    if (response.hasOwnProperty('data') && response.data.hasOwnProperty('warning')) {
                        if (confirm(response.data.warning)) {
                            self.process(step, page);
                        }
                    } else {
                        self.process(step, page);
                    }
                } else {
                    alert(response.data.message)
                }
            },
            error: function (response, responseStatus, responseHeaders) {

            }
        });
    }
    /**
     * Set progress
     * @param message
     * @param percent
     */
    AdminTools.prototype.setProgress = function(message, percent) {
        var progressBarValue = this.form.querySelector('.dlm-tool-progress-bar-inner');
        var progressBarInfo = this.form.querySelector('.dlm-tool-progress-info');
        var progressBarRow = this.form.querySelector('.dlm-tool-form-row-progress');
        progressBarValue.style.width = percent + '%';
        progressBarInfo.innerHTML = (message + ' ' + '(' + percent + '%)');
        progressBarRow.style.display = 'block';
    };
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

        var submitButton = this.form.querySelector('button[type=submit]');
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
                self.setProgress(message, percent);
                if (next_step > 0 && next_page >= 0) {
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
    var forms = document.querySelectorAll('.dlm-tool-form');
    console.log(forms);
    if (forms && forms.length) {
        for (var i = 0; i < forms.length; i++) {
            forms[i].addEventListener('submit', function (e) {
                e.preventDefault();
                if (confirm(DLM_Tools.i18n.confirmation)) {
                    var tools = new window.DLM.AdminTools(this);
                    tools.init(1, 1);
                }
            })
        }
    }
});

