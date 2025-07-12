jQuery(document).ready(function ($) {
    // JS Optimizations
    const jsToggle = $('#rt_optimizer_disable_js_optimizations');
    const jsSubOptions = jsToggle.closest('tr').nextAll();

    function toggleJsOptions() {
        if (jsToggle.is(':checked')) {
            jsSubOptions.css('opacity', '0.5');
            jsSubOptions.find('input, textarea, select').prop('disabled', true);
        } else {
            jsSubOptions.css('opacity', '1');
            jsSubOptions
                .find('input, textarea, select')
                .prop('disabled', false);
        }
    }

    jsToggle.on('change', toggleJsOptions);
    toggleJsOptions();

    // CSS Optimizations
    const cssToggle = $('#rt_optimizer_disable_css_optimizations');
    const cssSubOptions = cssToggle.closest('tr').nextAll();

    function toggleCssOptions() {
        if (cssToggle.is(':checked')) {
            cssSubOptions.css('opacity', '0.5');
            cssSubOptions
                .find('input, textarea, select')
                .prop('disabled', true);
        } else {
            cssSubOptions.css('opacity', '1');
            cssSubOptions
                .find('input, textarea, select')
                .prop('disabled', false);
        }
    }
    cssToggle.on('change', toggleCssOptions);
    toggleCssOptions();
});
