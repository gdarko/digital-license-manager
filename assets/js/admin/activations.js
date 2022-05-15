jQuery(function ($) {
    'use strict';

    const dropdownLicenses = $('select#filter-by-license-id');
    const dropdownSources = $('select#filter-by-source');

    const licenseDropdownSearchConfig = {
        ajax: {
            cache: true,
            delay: 500,
            url: ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    action: 'dlm_dropdown_search',
                    security: dlm_activations_security.dropdownSearch,
                    term: params.term,
                    page: params.page,
                    type: 'license'
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
        placeholder: dlm_activations_i18n.placeholderSearchLicenses,
        minimumInputLength: 1,
        allowClear: true
    };
    const sourceDropdownSearchConfig = {
        allowClear: true,
        placeholder: dlm_activations_i18n.placeholderSearchSources
    };

    if (dropdownLicenses) {
        dropdownLicenses.select2(licenseDropdownSearchConfig);
    }
    if (dropdownSources) {
        dropdownSources.select2(sourceDropdownSearchConfig);
    }

});