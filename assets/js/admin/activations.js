window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    window.DLM.Activations = function () {
        this.setupListeners();
    }

    /**
     * Set up the listeners
     */
    window.DLM.Activations.prototype.setupListeners = function () {

        const dropdownLicenses = document.querySelector('select#filter-by-license-id');
        const dropdownSources = document.querySelector('select#filter-by-source');

        // Search configurations
        const licenseDropdownSearchConfig = {
            remote: {
                url: ajaxurl,
                action: 'dlm_dropdown_search',
                type: 'license',
                nonce: dlm_activations_security.dropdownSearch,
            },
            placeholder: dlm_activations_i18n.placeholderSearchLicenses,
        };

        const sourceDropdownSearchConfig = {
            placeholder: dlm_activations_i18n.placeholderSearchSources
        };

        if (dropdownLicenses) {
            new window.DLM.Select(dropdownLicenses, licenseDropdownSearchConfig);
        }

        if (dropdownSources) {
            new window.DLM.Select(dropdownSources, sourceDropdownSearchConfig);
        }
    }

    new window.DLM.Activations();

});
