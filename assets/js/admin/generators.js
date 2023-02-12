window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.Generators = function () {
        this.setupListeners();
    }

    /**
     * Set up the listeners
     */
    window.DLM.Generators.prototype.setupListeners = function () {

        const generateLicenseKeysProduct = document.querySelector('select#generate__product');
        const generateLicenseKeysOrder = document.querySelector('select#generate__order');

        // Search configurations
        const productDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'product',
                nonce: dlm_generators_security.dropdownSearch,
            },
            placeholder: dlm_generators_i18n.placeholderSearchProducts,
        };

        const orderDropdownSearchConfig = {
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
