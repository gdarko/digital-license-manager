/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.Generators = function () {
        this.setupListeners();
    }

    /**
     * Set up the listeners
     */
    window.DLM.Generators.prototype.setupListeners = function () {

        var generateLicenseKeysProduct = document.querySelector('select#generate__product');
        var generateLicenseKeysOrder = document.querySelector('select#generate__order');

        // Search configurations
        var productDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'product',
                nonce: dlm_generators_security.dropdownSearch,
            },
            placeholder: dlm_generators_i18n.placeholderSearchProducts,
        };

        var orderDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'shop_order',
                nonce: dlm_generators_security.dropdownSearch,
            },
            placeholder: dlm_generators_i18n.placeholderSearchOrders,
        };

        if (generateLicenseKeysProduct) {
            new window.DLM.Select(generateLicenseKeysProduct, productDropdownSearchConfig);
        }
        if (generateLicenseKeysOrder) {
            new window.DLM.Select(generateLicenseKeysOrder, orderDropdownSearchConfig);
        }
    }

    new window.DLM.Generators();

});
