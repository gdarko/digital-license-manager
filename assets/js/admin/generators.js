jQuery(function($) {
    'use strict';

    let generateLicenseKeysProduct = $('select#generate__product');
    let generateLicenseKeysOrder   = $('select#generate__order');

    const productDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function(params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_generators_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'product'
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: dlm_generators_i18n.placeholderSearchProducts,
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
            data: function(params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_generators_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'shop_order'
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;

                return {
                    results: data.results,
                    pagination: {
                        more: data.pagination.more
                    }
                };
            }
        },
        placeholder: dlm_generators_i18n.placeholderSearchOrders,
        minimumInputLength: 1,
        allowClear: true
    };

    if (generateLicenseKeysProduct) {
        generateLicenseKeysProduct.select2(productDropdownSearchConfig);
    }

    if (generateLicenseKeysOrder) {
        generateLicenseKeysOrder.select2(orderDropdownSearchConfig);
    }
});