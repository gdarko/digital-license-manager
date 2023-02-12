window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.Licenses = function () {
        this.setupListeners();
    }

    /**
     * Set up the listeners
     */
    window.DLM.Licenses.prototype.setupListeners = function () {

        const importLicenseProduct = document.querySelector('select#bulk__product');
        const importLicenseOrder = document.querySelector('select#bulk__order');
        const addLicenseProduct = document.querySelector('select#single__product');
        const addLicenseOrder = document.querySelector('select#single__order');
        const addLicenseUser = document.querySelector('select#single__user');
        const addExpiresAt = document.querySelector('input#single__expires_at');
        const editLicenseProduct = document.querySelector('select#edit__product');
        const editLicenseOrder = document.querySelector('select#edit__order');
        const editExpiresAt = document.querySelector('input#edit__expires_at');
        const editStatus = document.querySelector('select#edit__status');
        const bulkAddSource = document.querySelectorAll('input[type="radio"].bulk__type');
        // Licenses table
        const dropdownOrders = document.querySelector('select#filter-by-order-id');
        const dropdownProducts = document.querySelector('select#filter-by-product-id');
        const dropdownUsers = document.querySelector('select#filter-by-user-id');
        const selectActions = document.querySelectorAll('#dlm-license-table select[name=action]');

        // Search configurations
        const productDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'product',
                nonce: dlm_licenses_security.dropdownSearch,
                delay: 300
            },
            placeholder: dlm_licenses_i18n.placeholderSearchProducts,
        };
        const orderDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'shop_order',
                nonce: dlm_licenses_security.dropdownSearch,
            },
            placeholder: dlm_licenses_i18n.placeholderSearchOrders,
        };
        const userDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'user',
                nonce: dlm_licenses_security.dropdownSearch,

            },
            placeholder: dlm_licenses_i18n.placeholderSearchUsers,
        };

        const flatPickrConfig = {
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
                    const bulkType = document.querySelector('input[type="radio"].bulk__type:checked');
                    const value = bulkType ? bulkType.value : '';

                    if (value !== 'file' && value !== 'clipboard') {
                        return;
                    }

                    // Hide the currently visible row
                    const bulkSourceRows = document.querySelectorAll('tr.bulk__source_row');
                    for (var j = 0; j < bulkSourceRows.length; j++) {
                        if (!bulkSourceRows[j].classList.contains('hidden')) {
                            bulkSourceRows[j].classList.add('hidden');
                        }
                    }
                    // Display the selected row
                    const bulkSourceRowCurrent = document.querySelector('tr#bulk__source_' + value + '.bulk__source_row');
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
