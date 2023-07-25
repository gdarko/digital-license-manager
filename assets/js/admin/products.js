/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {
    var Products = function () {
        this.setupListeners();
    }
    Products.prototype.setupListeners = function () {
        var self = this;
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
        var wrapper = field.closest('.woocommerce_variable_attributes') ? field.closest('div.data') : field.closest('div.woocommerce_options_panel')
        var sourceId = field.id;
        var sourceVal = field.value;
        var target = wrapper.querySelector('[data-conditional-source="' + sourceId + '"]');
        var targetW = target ? target.closest('.dlm-field-conditional-target') : null;
        if (targetW) {
            if (sourceVal === target.dataset.conditionalShowIf) {
                targetW.style.display = 'block';
            } else {
                targetW.style.display = 'none';
            }
        }
    }

    Products.prototype.handleAllConditionalFields = function () {
        var selectDropdowns = document.querySelectorAll('.dlm-field-conditional-src select');
        for (var i = 0; i < selectDropdowns.length; i++) {
            this.handleConditionalField(null, selectDropdowns[i]);
        }
    }

    /**
     * Attach the events
     */
    Products.prototype.attachConditionalFieldsListeners = function () {
        var self = this;
        var selectDropdowns = document.querySelectorAll('.dlm-field-conditional-src select');
        for (var i = 0; i < selectDropdowns.length; i++) {
            selectDropdowns[i].addEventListener('change', function (event) {
                self.handleConditionalField(event, this)
            }.bind(selectDropdowns[i]))
        }
    }

    window.DLM.Products = Products;
    new window.DLM.Products();

});

