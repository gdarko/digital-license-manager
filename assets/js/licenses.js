jQuery(function ($) {
    'use strict';

    const importLicenseProduct = $('select#bulk__product');
    const importLicenseOrder = $('select#bulk__order');
    const addLicenseProduct = $('select#single__product');
    const addLicenseOrder = $('select#single__order');
    const addLicenseUser = $('select#single__user');
    const addValidFor = $('input#single__valid_for');
    const addExpiresAt = $('input#single__expires_at');
    const editLicenseProduct = $('select#edit__product');
    const editLicenseOrder = $('select#edit__order');
    const editValidFor = $('input#edit__valid_for');
    const editExpiresAt = $('input#edit__expires_at');
    const bulkAddSource = $('input[type="radio"].bulk__type');
    // Licenses table
    const dropdownOrders = $('select#filter-by-order-id');
    const dropdownProducts = $('select#filter-by-product-id');
    const dropdownUsers = $('select#filter-by-user-id');

    const productDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_licenses_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'product'
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: dlm_licenses_i18n.placeholderSearchProducts,
        minimumInputLength: 1,
        allowClear: true
    };
    const orderDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_licenses_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'shop_order'
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: dlm_licenses_i18n.placeholderSearchOrders,
        minimumInputLength: 1,
        allowClear: true
    };
    const userDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_licenses_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'user'
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: dlm_licenses_i18n.placeholderSearchUsers,
        minimumInputLength: 1,
        allowClear: true
    };

    if (importLicenseProduct.length > 0) {
        importLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (importLicenseOrder.length > 0) {
        importLicenseOrder.select2(orderDropdownSearchConfig);
    }

    if (addLicenseProduct.length > 0) {
        addLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (addLicenseOrder.length > 0) {
        addLicenseOrder.select2(orderDropdownSearchConfig);
    }

    if (addLicenseUser.length > 0) {
        addLicenseUser.select2(userDropdownSearchConfig);
    }

    if (addExpiresAt.length > 0) {
        addExpiresAt.datepicker({
            dateFormat: 'yy-mm-dd'
        });
    }

    if (editLicenseProduct.length > 0) {
        editLicenseProduct.select2(productDropdownSearchConfig);
    }

    if (editLicenseOrder.length > 0) {
        editLicenseOrder.select2(orderDropdownSearchConfig);
    }

    if (editExpiresAt.length > 0) {
        editExpiresAt.datepicker({
            dateFormat: 'yy-mm-dd'
        });

        onChangeValidity(editExpiresAt, editValidFor);
    }

    if (bulkAddSource.length > 0) {
        bulkAddSource.change(function () {
            const value = $('input[type="radio"].bulk__type:checked').val();

            if (value !== 'file' && value !== 'clipboard') {
                return;
            }

            // Hide the currently visible row
            $('tr.bulk__source_row:visible').addClass('hidden');

            // Display the selected row
            $('tr#bulk__source_' + value + '.bulk__source_row').removeClass('hidden');
        })
    }

    addExpiresAt.on('input', function () {
        onChangeValidity(addExpiresAt, addValidFor);
    });
    addExpiresAt.on('change', function () {
        onChangeValidity(addExpiresAt, addValidFor);
    });
    addValidFor.on('input', function () {
        onChangeValidity(addExpiresAt, addValidFor);
    });
    addValidFor.on('change', function () {
        onChangeValidity(addExpiresAt, addValidFor);
    });
    editExpiresAt.on('input', function () {
        onChangeValidity(editExpiresAt, editValidFor);
    });
    editExpiresAt.on('change', function () {
        onChangeValidity(editExpiresAt, editValidFor);
    });
    editValidFor.on('input', function () {
        onChangeValidity(editExpiresAt, editValidFor);
    });
    editValidFor.on('change', function () {
        onChangeValidity(editExpiresAt, editValidFor);
    });

    function onChangeValidity(expiresAt, validFor) {
        if (expiresAt.val() && !validFor.val()) {
            expiresAt.prop('disabled', false);
            validFor.prop('disabled', true);
            return;
        }

        if (!expiresAt.val() && validFor.val()) {
            expiresAt.prop('disabled', true);
            validFor.prop('disabled', false);
            return;
        }

        if (!expiresAt.val() && !validFor.val()) {
            expiresAt.prop('disabled', false);
            validFor.prop('disabled', false);
        }
    }

    if (dropdownOrders) {
        dropdownOrders.select2(orderDropdownSearchConfig);
    }

    if (dropdownProducts) {
        dropdownProducts.select2(productDropdownSearchConfig);
    }

    if (dropdownUsers) {
        dropdownUsers.select2(userDropdownSearchConfig);
    }

    $(document).on('change', '#dlm-license-table select[name=action]', function (e) {
        if ('export_csv' === $(this).val()) {
            e.preventDefault();
            MicroModal.show('dlm-license-export');
            var $exportForm = $('#dlm-license-export-form');
            var selected = [];
            var $checkboxes = $(this).closest('#dlm-license-table').find('.wp-list-table').find('[name^=id]:checked');
            $checkboxes.each(function (i, t) {
                selected.push($(t).val());
            })
            $exportForm.find('input[name^=dlm_export_licenses]').val(selected);
        }
    })

});