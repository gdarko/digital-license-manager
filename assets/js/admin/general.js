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

        var self = this;

        // Single key toggle
        var licenseToggles = document.querySelectorAll('.dlm-license-key-toggle');
        if (licenseToggles) {
            for (var i = 0; i < licenseToggles.length; i++) {
                licenseToggles[i].addEventListener('click', function (e) {
                    self.handleLicenseKeysToggle([this.closest('tr')], this.classList.contains('dlm-license-key-show'));
                }.bind(licenseToggles[i]));
            }
        }

        // Copy license key to clipboard
        var licenseKeys = document.querySelectorAll('.license_key .dlm-placeholder, .dlm-license-list .dlm-placeholder');
        if (licenseKeys) {
            for (var i = 0; i < licenseKeys.length; i++) {
                licenseKeys[i].addEventListener('click', function (e) {
                    self.copyLicenseToClipboard(licenseKeys[i], e);
                });
            }
        }

        // Toggle all
        var licensesToggleAllBtns = document.querySelectorAll('.dlm-license-keys-toggle-all');
        if (licensesToggleAllBtns) {
            for (var i = 0; i < licensesToggleAllBtns.length; i++) {
                licensesToggleAllBtns[i].addEventListener('click', function (e) {
                    var elements = this.closest('td').querySelectorAll('.dlm-license-list li');
                    self.handleLicenseKeysToggle(elements, this.classList.contains('dlm-license-keys-show-all'));
                }.bind(licensesToggleAllBtns[i]))
            }
        }

        // Dialog confirm
        var confirmDialogs = document.querySelectorAll('.dlm-confirm-dialog');
        if (confirmDialogs) {
            for (var i = 0; i < confirmDialogs.length; i++) {
                confirmDialogs[i].addEventListener('click', function (e) {
                    return confirm(DLM_MAIN.i18n.confirm_dialog);
                })
            }
        }
    }

    /**
     *
     * @param element
     * @param state
     */
    AdminGeneral.prototype.setLicenseKeySpinner = function (element, state) {
        element.querySelector('.dlm-spinner').style.opacity = state ? 1 : 0;
    }

    /**
     * Handle license toggle
     * @param {Array} elements
     * @param {Boolean} isShow
     */
    AdminGeneral.prototype.handleLicenseKeysToggle = function (elements, isShow) {
        var self = this
        if (isShow) {
            var licenseIds = [];
            for (var i = 0; i < elements.length; i++) {
                var placeholder = elements[i].querySelector('.dlm-placeholder');
                if (placeholder) {
                    licenseIds.push(parseInt(placeholder.dataset.id));
                }
                self.setLicenseKeySpinner(elements[i], true);
            }
            var http = new window.DLM.Http();
            http.post(ajaxurl, {
                data: {
                    action: 'dlm_show_all_license_keys',
                    show_all: DLM_MAIN.show_all,
                    ids: JSON.stringify(licenseIds)
                },
                success: function (response, responseStatus, responseHeaders) {
                    for (var id in response) {
                        var licenseKey = document.querySelector('.dlm-placeholder[data-id="' + id + '"]');
                        licenseKey.classList.remove('empty');
                        licenseKey.innerHTML = response[id];
                    }
                },
                error: function (response, responseStatus, responseHeaders) {
                    alert(response);
                },
                complete: function () {
                    for (var i = 0; i < elements.length; i++) {
                        self.setLicenseKeySpinner(elements[i], false)
                    }
                }
            });
        } else {
            for (var i = 0; i < elements.length; i++) {
                var licenseKey = elements[i].querySelector('.dlm-placeholder');
                if (licenseKey) {
                    licenseKey.innerHTML = '';
                    licenseKey.classList.add('empty');
                }
            }
        }

    }


    /**
     * Copy license to clipboard
     * @param el
     * @param e
     */
    AdminGeneral.prototype.copyLicenseToClipboard = function (el, e) {
        if (!el) {
            return;
        }
        var str = el.innerHTML;

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
        copied.innerText = document.querySelector('.dlm-txt-copied-to-clipboard').innerText.toString();
        document.body.appendChild(copied);

        setTimeout(function () {
            copied.style.opacity = '0';
        }, 700);
        setTimeout(function () {
            document.body.removeChild(copied);
        }, 1500);
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
