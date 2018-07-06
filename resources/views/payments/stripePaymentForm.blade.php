@extends('layouts.app')

@section('htmlheader_title')
    Subscription
@endsection

@section('main-content')
<div class="container">
    <div class='row'>
        <div class='col-md-4'></div>

        <div class='col-md-4'>
            <div class="panel panel-default">
                <div class="panel-heading">
                    @if($card_type == 'credit_card')
                        Credit Card Payment
                    @elseif($card_type == 'debit_card')
                        Debit Card Payment
                    @endif
                </div>
                <div class="panel-body">
                    <script src='https://js.stripe.com/v2/' type='text/javascript'></script>

                    <form accept-charset="UTF-8" action="{{ route('payment.stripPayment', [$project, $channel]) }}" class="require-validation"
                                data-cc-on-file="false"
                                data-stripe-publishable-key="{{ env('STRIPE_KEY') }}"
                                id="payment-form"
                                method="post">
                        {{ csrf_field() }}
                        <div class='form-row'>
                            <div class='col-xs-12 form-group required'>
                                <label class='control-label'>Name on Card</label>
                                <input class='form-control name-on-card' size='4' type='text' name="card_name">
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='col-xs-12 form-group card required'>
                                <label class='control-label'>Card Number</label>
                                <input autocomplete='cc-number' name="card_no" class='form-control card-number' size='20' type='text'>
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='col-xs-4 form-group cvc required'>
                                <label class='control-label'>CVC</label>
                                <input autocomplete='off' name="card_cvc" class='form-control card-cvc' placeholder='ex. 311' size='4' type='text'>
                            </div>
                            <div class='col-xs-4 form-group expiration required'>
                                <label class='control-label'>Expiration</label>
                                <input class='form-control card-expiry-month' placeholder='MM' size='2' type='text' name="card_expiry">
                            </div>
                            <div class='col-xs-4 form-group expiration required'>
                                <label class='control-label'> </label>
                                <input class='form-control card-expiry-year' placeholder='YYYY' size='4' type='text'name="card_year">
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='col-md-12'>
                                <div class='form-control total btn btn-info'>
                                    Total:
                                    <span class='amount'>€{{$price}}</span>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" value="{{$price}}" name="net_total">
                        <br>
                        <div class='form-row'>
                            <div class='col-md-12 form-group'>
                                <button class='form-control btn btn-primary submit-button' type='submit'>Pay »</button>
                            </div>
                        </div>
                        <div class='form-row'>
                            <div class='col-md-12 error form-group hide'>
                                <div class='alert-danger alert'>
                                    Please correct the errors and try again.
                                </div>
                            </div>
                        </div>
                    </form>


                    @if((Session::has('success-message')))
                        <div class="alert alert-success col-md-12">
                            {{ Session::get('success-message') }}
                        </div>
                     @endif
                    @if((Session::has('fail-message')))
                        <div class="alert alert-danger col-md-12">
                               {{ Session::get('fail-message') }}
                        </div>
                     @endif
                </div>
            </div>
        </div>
        <div class='col-md-4'></div>
    </div>
</div>
@endsection
@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
        $(function() {
                $('form.require-validation').bind('submit', function(e) {

                    var $form = $(e.target).closest('form'),

                    inputSelector = [ 'input[type=email]',
                                      'input[type=password]',
                                      'input[type=text]',
                                      'input[type=file]',
                                      'textarea'
                                    ].join(', '),

                    $inputs       = $form.find('.required').find(inputSelector),
                    $errorMessage = $form.find('div.error'),
                    valid         = true;

                    $errorMessage.addClass('hide');
                    $('.has-error').removeClass('has-error');
                    $inputs.each(function(i, el) {
                            var $input = $(el);
                            if ($input.val() === '') {
                                 $input.parent().addClass('has-error');
                                 $errorMessage.removeClass('hide');
                                 e.preventDefault(); // cancel on first error
                            }
                    });
                });
        });

        $(function() {
            var $form = $("#payment-form");

            $form.on('submit', function(e) {
                if (!$form.data('cc-on-file')) {
                e.preventDefault();
                Stripe.setPublishableKey($form.data('stripe-publishable-key'));
                Stripe.createToken({
                    name : $('.name-on-card').val(),
                    number: $('.card-number').val(),
                    cvc: $('.card-cvc').val(),
                    exp_month: $('.card-expiry-month').val(),
                    exp_year: $('.card-expiry-year').val()
                    }, stripeResponseHandler);
                }
            });

            function stripeResponseHandler(status, response) {
                if (response.error) {
                    $('.error')
                    .removeClass('hide')
                    .find('.alert')
                    .text(response.error.message);
                }
                else {
                    // token contains id, last4, and card type
                    var token = response['id'];
                    // insert the token into the form so it gets submitted to the server
                    $form.find('input[type=text]').empty();
                    $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                    $form.get(0).submit();
                }
            }
        })
    </script>
@endsection