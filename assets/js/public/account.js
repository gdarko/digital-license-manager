/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.MyAccount = function () {
        this.setupListeners();
    }

    /**
     * Initializes the "Add new" button in the license activations table.
     */
    window.DLM.MyAccount.prototype.setupListeners = function () {

        // Bind -> "Add new" in Activations table
        var button = document.getElementById('dlm-myaccount-license--new-activation');
        if (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                MicroModal.show('dlm-manual-activation-add');
            })
        }

        // Trigger activation modal if specific parameter exists.
        if (window.location.href.indexOf('new_activation') !== -1) {
            MicroModal.show('dlm-manual-activation-add');
        }

    }

    new window.DLM.MyAccount();

});