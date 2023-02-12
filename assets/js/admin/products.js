window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {
    var Products = function () {
        this.setupListeners();
    }
    Products.prototype.setupListeners = function () {
        let self = this;
        setTimeout(function () {
            self.handleAllConditionalFields();
        }, 50);
        jQuery(document).on('woocommerce_variations_loaded', function () {
            self.handleAllConditionalFields();
            self.attachConditionalFieldsListeners();
        });
        this.attachConditionalFieldsListeners();
    }

    Products.prototype.handleConditionalField = function (event, field) {
        let wrapper = field.closest('.woocommerce_variable_attributes') ? field.closest('div.data') : field.closest('div.woocommerce_options_panel')
        let sourceId = field.id;
        let sourceVal = field.value;
        let target = wrapper.querySelector('[data-conditional-source="' + sourceId + '"]');
        let targetW = target ? target.closest('.dlm-field-conditional-target') : null;
        if (targetW) {
            if (sourceVal === target.dataset.conditionalShowIf) {
                targetW.style.display = 'block';
            } else {
                targetW.style.display = 'none';
            }
        }
    }

    Products.prototype.handleAllConditionalFields = function () {
        let selectDropdowns = document.querySelectorAll('.dlm-field-conditional-src select');
        for (let i = 0; i < selectDropdowns.length; i++) {
            this.handleConditionalField(null, selectDropdowns[i]);
        }
    }

    /**
     * Attach the events
     */
    Products.prototype.attachConditionalFieldsListeners = function () {
        let self = this;
        let selectDropdowns = document.querySelectorAll('.dlm-field-conditional-src select');
        for (let i = 0; i < selectDropdowns.length; i++) {
            selectDropdowns[i].addEventListener('change', function (event) {
                self.handleConditionalField(event, selectDropdowns[i])
            })
        }
    }

    window.DLM.Products = Products;
    new window.DLM.Products();

});

