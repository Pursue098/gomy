const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.scripts([
    'public/js/jquery-2.1.1.js',
    'public/js/bootstrap.min.js',
    'public/js/plugins/metisMenu/jquery.metisMenu.js',
    'public/js/plugins/slimscroll/jquery.slimscroll.min.js',
    'public/js/plugins/select2/select2.full.min.js',
    'public/js/plugins/iCheck/icheck.min.js',
    'public/js/plugins/switchery/switchery.js',
    'public/js/inspinia.js',
    // 'public/js/plugins/pace/pace.min.js',
    // 'public/js/plugins/ladda/spin.min.js',
    // 'public/js/plugins/ladda/ladda.min.js',
    // 'public/js/plugins/ladda/ladda.jquery.min.js',
    'public/js/plugins/staps/jquery.steps.min.js'
], 'public/js/cyrano.min.js');


mix.styles([
    'public/css/bootstrap.min.css',
    'public/css/plugins/ladda/ladda-themeless.min.css',
    'public/css/plugins/iCheck/custom.css',
    'public/css/plugins/switchery/switchery.css',
    'public/css/plugins/steps/jquery.steps.css',
    'public/css/plugins/daterangepicker/daterangepicker-bs3.css',
    'public/css/plugins/select2/select2.min.css',
    'public/css/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css',
    'public/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css',
    'public/css/plugins/toastr/toastr.min.css',
    'public/css/intlTelInput.css',
    'public/css/plugins/blueimp/css/blueimp-gallery.min.css',
    'public/css/plugins/jQueryUI/jquery-ui.css',
    'public/css/plugins/dualListbox/bootstrap-duallistbox.min.css',
    'public/js/amcharts/plugins/export/export.css',
    'public/css/plugins/footable/footable.core.css',
    'public/css/animate.css',
    'public/css/style.css',
], 'public/css/cyrano.min.css');

mix.version();
