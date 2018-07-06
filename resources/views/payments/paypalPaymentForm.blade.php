@extends('layouts.app')

@section('htmlheader_title')
    Payments
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="active">
            <strong>Paypal Payments</strong>
        </li>
    </ol>
@endsection

@section('main-content')
    <!doctype html>
    <html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Braintree-Demo</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

        <script src="https://js.braintreegateway.com/web/dropin/1.8.1/js/dropin.min.js"></script>
        {{--<script src="https://js.braintreegateway.com/js/braintree-2.30.0.min.js"></script>--}}
    </head>
    <body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div id="dropin-container"></div>
                <button id="submit-button" class='form-control btn btn-primary submit-button' >Perform payment »</button>
            </div>
        </div>
    </div>
    <script>
        var button = document.querySelector('#submit-button');

        braintree.dropin.create({
            authorization: "{{ Braintree_ClientToken::generate() }}",
            container: '#dropin-container'
        }, function (createErr, instance) {
            button.addEventListener('click', function () {
                instance.requestPaymentMethod(function (err, payload) {

                    $.post('{{ route("payment.paypalPayment", [$project, $channel]) }}', {payload}, function (response) {
                        if (response.success) {
                            alert('Payment successfull!');
                        } else {
                            alert('Payment failed');
                        }
                    }, 'json');
                });
            });
        });
    </script>
    </body>
    </html>
@endsection