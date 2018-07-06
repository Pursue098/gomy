<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - @yield('htmlheader_title', 'Your title here')</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font Awesome Icons -->
    <script src="https://use.fontawesome.com/28d3b59d18.js"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>


    <!-- Production CSS -->
    <link href="{{ mix('/css/cyrano.min.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('/css/intlTelInput.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/6.10.1/sweetalert2.min.css">

    <!-- iCheck style -->
    <link href="{{ asset('/css/plugins/iCheck/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css') }}" rel="stylesheet">


    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Roboto:400,100,300,500">

    <!-- Favicon and touch icons -->
    <link rel="shortcut icon" href="{{ asset('assets/ico/favicon.png') }}">


 <!-- Bootstrap 3.3.4 -->
<link href="{{ asset('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

<!-- Ladda style -->
<link href="{{ asset('/css/plugins/ladda/ladda-themeless.min.css') }}" rel="stylesheet">



<!-- switchery style -->
<link href="{{ asset('/css/plugins/switchery/switchery.css') }}" rel="stylesheet">

<link href="{{ asset('/css/plugins/steps/jquery.steps.css') }}" rel="stylesheet">

<link href="{{ asset('/css/plugins/daterangepicker/daterangepicker-bs3.css') }}" rel="stylesheet">

<!-- Select2 -->
<link href="{{ asset('/css/plugins/select2/select2.min.css') }}" rel="stylesheet">

<!-- Bootstrap Tokenfield -->
<link href="{{ asset('/css/plugins/bootstrap-tokenfield/bootstrap-tokenfield.min.css') }}" rel="stylesheet">

<!-- Bootstrap Touchspin -->
<link href="{{ asset('/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet">

<!-- Toastr style -->
<link href="{{ asset('/css/plugins/toastr/toastr.min.css') }}" rel="stylesheet">

<!-- TelInput -->
<link href="{{ asset('/css/intlTelInput.css') }}" rel="stylesheet" >

<!-- Lightbox gallery -->
<link href="{{ asset('/css/plugins/blueimp/css/blueimp-gallery.min.css') }}" rel="stylesheet">

<!-- jQuery UI -->
<link href="{{ asset('/css/plugins/jQueryUI/jquery-ui.css') }}" rel="stylesheet">

<!-- duallistbox -->
<link href="{{ asset('/css/plugins/dualListbox/bootstrap-duallistbox.min.css') }}" rel="stylesheet">

<!-- AMCCHARTS EXPORT PLUGIN -->
<link href="{{ asset('/js/amcharts/plugins/export/export.css') }}" rel="stylesheet" type="text/css" media="screen">



<!-- FooTable -->
<link href="{{ asset('/css/plugins/footable/footable.core.css') }}" rel="stylesheet">

<link href="{{ asset('/css/animate.css') }}" rel="stylesheet">
<link href="{{ asset('/css/style.css') }}" rel="stylesheet">

    <!-- Sweet Alert -->
{{--    <link href="{{ asset('/css/plugins/sweetalert/sweetalert.css') }}" rel="stylesheet">--}}


    <link href="{{ asset('/css/plugins/datapicker/datepicker3.css') }}" rel="stylesheet">
    <link href="{{ asset('/css/custom.css') }}" rel="stylesheet">

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>--}}
    {{--<script src="https://js.braintreegateway.com/js/braintree-2.30.0.min.js"></script>--}}
    <script src="https://js.braintreegateway.com/web/dropin/1.8.1/js/dropin.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.19.3/sweetalert2.all.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
