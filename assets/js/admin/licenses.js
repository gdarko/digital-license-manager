/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    var Licenses = function () {
        this.setupListeners();
    }

    /**
     * Set up all listeners
     */
    Licenses.prototype.setupListeners = function () {
        this.setupFormFields();
        this.setupLicenseKeyToggle();
        this.setupLicenseKeyClipboard();
    }

    /**
     * Set up the form fields listeners
     * - Tom-select
     * - Flatpickr
     */
    Licenses.prototype.setupFormFields = function () {

        var importLicenseProduct = document.querySelector('select#bulk__product');
        var importLicenseOrder = document.querySelector('select#bulk__order');
        var addLicenseProduct = document.querySelector('select#single__product');
        var addLicenseOrder = document.querySelector('select#single__order');
        var addLicenseUser = document.querySelector('select#single__user');
        var addExpiresAt = document.querySelector('input#single__expires_at');
        var editLicenseProduct = document.querySelector('select#edit__product');
        var editLicenseOrder = document.querySelector('select#edit__order');
        var editExpiresAt = document.querySelector('input#edit__expires_at');
        var editStatus = document.querySelector('select#edit__status');
        var bulkAddSource = document.querySelectorAll('input[type="radio"].bulk__type');
        // Licenses table
        var dropdownOrders = document.querySelector('select#filter-by-order-id');
        var dropdownProducts = document.querySelector('select#filter-by-product-id');
        var dropdownUsers = document.querySelector('select#filter-by-user-id');
        var selectActions = document.querySelectorAll('#dlm-license-table select[name=action]');

        // Search configurations
        var productDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'product',
                nonce: dlm_licenses_security.dropdownSearch,
                delay: 300
            },
            placeholder: dlm_licenses_i18n.placeholderSearchProducts,
        };
        var orderDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'shop_order',
                nonce: dlm_licenses_security.dropdownSearch,
            },
            placeholder: dlm_licenses_i18n.placeholderSearchOrders,
        };
        var userDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'user',
                nonce: dlm_licenses_security.dropdownSearch,

            },
            placeholder: dlm_licenses_i18n.placeholderSearchUsers,
        };

        var flatPickrConfig = {
            altInput: true,
            altFormat: dlm_licenses_i18n.dateTimeFormat,
            dateFormat: "Y-m-d H:i:S",
            enableTime: true,
        }

        if (importLicenseProduct) {
            new window.DLM.Select(importLicenseProduct, productDropdownSearchConfig);
        }

        if (importLicenseOrder) {
            new window.DLM.Select(importLicenseOrder, orderDropdownSearchConfig);
        }

        if (addLicenseProduct) {
            new window.DLM.Select(addLicenseProduct, productDropdownSearchConfig);
        }

        if (addLicenseOrder) {
            new window.DLM.Select(addLicenseOrder, orderDropdownSearchConfig);
        }

        if (addLicenseUser) {
            new window.DLM.Select(addLicenseUser, userDropdownSearchConfig);
        }

        if (addExpiresAt) {
            flatpickr(addExpiresAt, flatPickrConfig);
        }

        if (editLicenseProduct) {
            new window.DLM.Select(editLicenseProduct, productDropdownSearchConfig);
        }

        if (editLicenseOrder) {
            new window.DLM.Select(editLicenseOrder, orderDropdownSearchConfig);
        }

        if (editExpiresAt) {
            flatpickr(editExpiresAt, flatPickrConfig)
        }

        if (bulkAddSource && bulkAddSource.length > 0) {

            for (var i = 0; i < bulkAddSource.length; i++) {
                bulkAddSource[i].addEventListener('change', function () {
                    var bulkType = document.querySelector('input[type="radio"].bulk__type:checked');
                    var value = bulkType ? bulkType.value : '';

                    if (value !== 'file' && value !== 'clipboard') {
                        return;
                    }

                    // Hide the currently visible row
                    var bulkSourceRows = document.querySelectorAll('tr.bulk__source_row');
                    for (var j = 0; j < bulkSourceRows.length; j++) {
                        if (!bulkSourceRows[j].classList.contains('hidden')) {
                            bulkSourceRows[j].classList.add('hidden');
                        }
                    }
                    // Display the selected row
                    var bulkSourceRowCurrent = document.querySelector('tr#bulk__source_' + value + '.bulk__source_row');
                    if (bulkSourceRowCurrent) {
                        bulkSourceRowCurrent.classList.remove('hidden');
                    }


                })
            }
        }

        if (dropdownOrders) {
            new window.DLM.Select(dropdownOrders, orderDropdownSearchConfig);
        }
        if (dropdownProducts) {
            new window.DLM.Select(dropdownProducts, productDropdownSearchConfig);
        }
        if (dropdownUsers) {
            new window.DLM.Select(dropdownUsers, userDropdownSearchConfig);
        }

        if (editStatus) {
            new window.DLM.Select(editStatus, []);
        }

        for (var i = 0; i < selectActions.length; i++) {
            selectActions[i].addEventListener('change', function (e) {
                if ('export_csv' === this.value) {
                    e.preventDefault();
                    MicroModal.show('dlm-license-export');
                    var exportForm = document.querySelector('#dlm-license-export-form')
                    var checkboxes = this.closest('#dlm-license-table').querySelectorAll('.wp-list-table [name^=id]:checked');
                    var selected = [];
                    for (var j = 0; j < checkboxes.length; j++) {
                        selected.push(checkboxes[j].value);
                    }
                    exportForm.querySelector('input[name^=dlm_export_licenses]').value = selected;
                }
            }.bind(selectActions[i]))
        }
    }

    /**
     * Set up license key ajax based toggle
     */
    Licenses.prototype.setupLicenseKeyToggle = function () {
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

        // Toggle all
        var licensesToggleAllBtns = document.querySelectorAll('.dlm-license-keys-toggle-all');
        if (licensesToggleAllBtns) {
            for (var i = 0; i < licensesToggleAllBtns.length; i++) {
                licensesToggleAllBtns[i].addEventListener('click', function (e) {
                    var elements = this.closest('td').querySelectorAll('.dlm-license-list li');
                    var state, text;
                    if(this.dataset.toggleCurrent === 'hide') {
                        this.dataset.toggleCurrent = 'show';
                        state = 1;
                    } else {
                        this.dataset.toggleCurrent = 'hide';
                        state = 0;
                    }
                    var span = this.querySelector('span');
                    text = span.innerHTML;
                    span.innerText = this.dataset.toggleText;
                    this.dataset.toggleText = text;
                    self.handleLicenseKeysToggle(elements, state, this);
                }.bind(licensesToggleAllBtns[i]))
            }
        }

    }

    /**
     * Setup license key cliboard
     */
    Licenses.prototype.setupLicenseKeyClipboard = function () {
        var licenseKeys = document.querySelectorAll('.license_key .dlm-placeholder, .dlm-license-list .dlm-placeholder');
        if (licenseKeys) {
            for (var i = 0; i < licenseKeys.length; i++) {
                licenseKeys[i].addEventListener('click', this.copyLicenseToClipboard.bind(licenseKeys[i]));
            }
        }
    }

    /**
     * Handle license toggle
     * @param {Array} elements
     * @param {Boolean} isShow
     * @param spinner
     */
    Licenses.prototype.handleLicenseKeysToggle = function (elements, isShow, spinner) {
        var self = this
        spinner = spinner ? spinner : null;
        if (isShow) {

            var licenseIds = [];
            for (var i = 0; i < elements.length; i++) {
                var placeholder = elements[i].querySelector('.dlm-placeholder');
                if (placeholder) {
                    licenseIds.push(parseInt(placeholder.dataset.id));
                }
                if(null === spinner) {
                    self.toggleLicenseKeySpinner(elements[i], true);
                }
            }

            if(spinner) {
                self.toggleLicenseKeySpinner(spinner, true);
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
                    if(null === spinner) {
                        for (var i = 0; i < elements.length; i++) {
                            self.toggleLicenseKeySpinner(elements[i], false)
                        }
                    } else {
                        self.toggleLicenseKeySpinner(spinner, false)
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
     *
     * @param element
     * @param state
     */
    Licenses.prototype.toggleLicenseKeySpinner = function (element, state) {
        element.querySelector('.dlm-spinner').style.display = state ? 'inline-block' : 'none';
        element.querySelector('.dlm-spinner').style.opacity = state ? 1 : 0;
    }


    /**
     * Copy license to clipboard
     * @param e
     */
    Licenses.prototype.copyLicenseToClipboard = function (e) {

        var el = this;

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
        copied.innerText = dlm_licenses_i18n.copiedToClipboard
        document.body.appendChild(copied);

        setTimeout(function () {
            copied.style.opacity = '0';
        }, 700);
        setTimeout(function () {
            document.body.removeChild(copied);
        }, 1500);
    }


    /**
     * Initialize
     * @type {Licenses}
     */
    window.DLM.Licenses = Licenses;
    new window.DLM.Licenses();

});
