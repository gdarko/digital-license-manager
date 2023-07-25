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
    var AdminSettings = function () {
        this.setupListeners();
    };
    /**
     * Initializes the event listeners
     */
    AdminSettings.prototype.setupListeners = function () {
        var uploadButton = document.querySelectorAll('.dlm-field-upload-button');
        for (var i = 0; i < uploadButton.length; i++) {
            uploadButton[i].addEventListener('click', this.handleUpload.bind(uploadButton[i]));
            var removeBtn = uploadButton[i].closest('.dlm-field-upload').querySelector('.dlm-field-remove-button');
            removeBtn.addEventListener('click', this.handleRemove.bind(removeBtn));
        }
    }
    AdminSettings.prototype.handleUpload = function (e) {
        e.preventDefault();
        window.wpActiveEditor = null;
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var show_attachment_preview = this.closest('.dlm-field-upload').dataset.showAttachmentPreview;
        var self = this;
        wp.media.editor.send.attachment = function (props, attachment) {
            if (show_attachment_preview) {
                var prevParent = DLM.Utils.Prev(self.parentNode);
                if (prevParent) {
                    prevParent.setAttribute('src', attachment.url);
                }
            }
            var btnSibling = DLM.Utils.Prev(self);
            btnSibling.value = attachment.id;
            wp.media.editor.send.attachment = send_attachment_bkp;
        };
        wp.media.editor.open(null, {
            frame: 'post',
            state: 'insert',
            multiple: false
        });
    }
    /**
     * Handles remove action
     * @param e
     */
    AdminSettings.prototype.handleRemove = function (e) {
        e.preventDefault();
        var answer = confirm('Are you sure?');
        if (answer) {
            var show_attachment_preview = this.closest('.dlm-field-upload').dataset.showAttachmentPreview;
            if (show_attachment_preview) {
                var parentWrap = DLM.Utils.Prev(this.parentNode);
                var src = parentWrap.dataset.src;
                parentWrap.setAttribute('src', src);
            }
            var hiddenInputs = this.closest('.dlm-field-submit').querySelectorAll('input[type=hidden]');
            for (var i = 0; i < hiddenInputs.length; i++) {
                console.log(hiddenInputs.id);
                hiddenInputs[i].value = '';
            }
        }
    }
    /**
     * Global.
     * @type {AdminTools}
     */
    window.DLM.AdminSettings = AdminSettings;
    /**
     * Init
     */
    new window.DLM.AdminSettings()
});

// TODO: User search with Ajax.