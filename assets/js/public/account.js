/**
 * Copyright (C) 2020-2024 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.MyAccount = function () {
        this.setupListeners();
    }

    /**
     * Copy license to clipboard
     * @param e
     */
    window.DLM.MyAccount.prototype.copyLicenseToClipboard = function (e) {

        var el = this;

        if (!el) {
            return;
        }
        var str = el.innerHTML ? el.innerHTML : '';

        if (str.length === 0) {
            return;
        }

        var textArea = document.createElement('textarea');
        textArea.value = str;
        textArea.setAttribute('readonly', '');
        textArea.style.position = 'absolute';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        var selected = document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false;
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        if (selected) {
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(selected);
        }

        // Display info
        var copied = document.createElement('div');
        copied.classList.add('dlm-clipboard');
        copied.style.position = 'absolute';
        copied.style.left = e.clientX.toString() + 'px';
        copied.style.top = (window.pageYOffset + e.clientY).toString() + 'px';
        copied.innerText = DLM_MyAccount.i18n.copiedToClipboard
        document.body.appendChild(copied);

        setTimeout(function () {
            copied.style.opacity = '0';
        }, 700);
        setTimeout(function () {
            document.body.removeChild(copied);
        }, 1500);
    }

    /**
     * Initializes the "Add new" button in the license activations table.
     */
    window.DLM.MyAccount.prototype.setupListeners = function () {

        let self = this;

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

        // Copy to clipboard
        let keys = document.querySelectorAll('.dlm-myaccount-license-key');
        if (keys) {
            for (var i = 0; i < keys.length; i++) {
                keys[i].addEventListener('click', self.copyLicenseToClipboard.bind(keys[i]));
            }
        }

    }

    new window.DLM.MyAccount();

});