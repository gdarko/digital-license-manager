window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.Licenses = function () {
        this.setupListeners();
    }

    /**
     * Set up the listeners
     */
    window.DLM.Licenses.prototype.setupListeners = function () {

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

    new window.DLM.Licenses();

});
