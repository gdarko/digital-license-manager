(function ($) {

    // Product conditionals.
    var handleProductConditionalField = function ($field) {
        var $wrapper = $field.closest('.woocommerce_variable_attributes').length > 0 ? $field.closest('div.data') : $field.closest('div.woocommerce_options_panel');
        var sourceId = $field.attr('id');
        var sourceVal = $field.val();
        var $target = $wrapper.find('[data-conditional-source="' + sourceId + '"]');
        console.log('here:');
        console.log($target.length);
        var $targetW = $target.closest('.dlm-field-conditional-target');
        if (sourceVal === $target.data('conditional-show-if')) {
            $targetW.show();
        } else {
            $targetW.hide();
        }
    };
    var handleProductConditionalFields = function () {
        $(document).find('.dlm-field-conditional-src select').each(function () {
            handleProductConditionalField($(this));
        });
    };
    $(document).on('change', '.dlm-field-conditional-src select', function () {
        handleProductConditionalField($(this));
    });
    $(document).on('woocommerce_variations_loaded', handleProductConditionalFields);
    setTimeout(handleProductConditionalFields, 50);

})(jQuery);
