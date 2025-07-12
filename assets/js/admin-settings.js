jQuery(document).ready(function ($) {
    // Reusable function to toggle options
    function createToggleHandler(toggle, subOptions) {
        return function () {
            if (toggle.is(':checked')) {
                subOptions.css('opacity', '0.5');
                subOptions
                    .find('input, textarea, select')
                    .prop('disabled', true);
            } else {
                subOptions.css('opacity', '1');
                subOptions
                    .find('input, textarea, select')
                    .prop('disabled', false);
            }
        };
    }

    // JS Optimizations
    const jsToggle = $('#rt_optimizer_disable_js_optimizations');
    const jsSubOptions = $('.js-sub-option');
    const toggleJsOptions = createToggleHandler(jsToggle, jsSubOptions);

    jsToggle.on('change', toggleJsOptions);
    toggleJsOptions();

    // CSS Optimizations
    const cssToggle = $('#rt_optimizer_disable_css_optimizations');
    const cssSubOptions = $('.css-sub-option');
    const toggleCssOptions = createToggleHandler(cssToggle, cssSubOptions);

    cssToggle.on('change', toggleCssOptions);
    toggleCssOptions();
});
