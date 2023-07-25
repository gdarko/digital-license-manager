/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {
    /**
     * Admin Settings
     * @constructor
     */
    var AdminGeneral = function () {
        this.setupListeners();
    };
    /**
     * Initializes the event listeners
     */
    AdminGeneral.prototype.setupListeners = function () {

        // Dialog confirm
        var confirmDialogs = document.querySelectorAll('.dlm-confirm-dialog');
        if (confirmDialogs) {
            for (var i = 0; i < confirmDialogs.length; i++) {
                confirmDialogs[i].addEventListener('click', function (e) {
                    return confirm(DLM_MAIN.i18n.confirm_dialog);
                })
            }
        }

        // User select field
        var userField = document.getElementById('user')
        if(userField) {
            new window.DLM.Select(userField, {
                remote: {
                    url: ajaxurl,
                    action: 'dlm_dropdown_search',
                    type: 'user',
                    nonce: DLM_MAIN.security.dropdownSearch,
                    delay: 300
                },
                placeholder: DLM_MAIN.i18n.placeholderSearchUsers,
            });
        }
    }
    /**
     * Global.
     * @type {AdminTools}
     */
    window.DLM.AdminGeneral = AdminGeneral;
    /**
     * Init
     */
    new window.DLM.AdminGeneral()
});
