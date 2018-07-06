@extends('layouts.app')

@section('htmlheader_title')
    Subscription
@endsection

@section('main-content')
<div class="container">
    <div class='row'>
        <div class='col-md-2'></div>

        <div class='col-md-8'>
            <div class="panel panel-default">
                <div class="panel-heading">
                    Octobat Stripe Payment
                </div>
                <div class="panel-body">

                    <form action="{{ route('payment.stripPayment', [$project, $channel]) }}" method="POST" id="octobat-payment-form" class="octobat-payment-form" data-octobat-pkey='{{ env('OCTOBAT_PKEY') }}' data-plan='{{$plan_id}}'>

                        {{ csrf_field() }}
                        <!-- Display errors -->
                        <div class='form-row'>
                            <div class='col-xs-12 form-group required'>
                                <span class="payment-errors"></span>
                            </div>
                        </div>
                        <!-- Required inputs -->
                        <div class='form-row'>
                            <div class='col-sm-12 form-group'>

                                <div class="col-sm-6">
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <h3>User information</h3>
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>E-mail address</label>
                                            <input class="form-control" type="email" value="{{$user->email}}" disabled>
                                            <input type="hidden" class="form-control" data-octobat="email" value="{{$user->email}}"/>
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Street Line 1</label>
                                            <input class="form-control" data-octobat="street-line-1"/>
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Street Line 2</label>
                                            <input class="form-control" data-octobat="street-line-2"/>
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>City</label>
                                            <input class="form-control" data-octobat="city"/>
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>state</label>
                                            <input class="form-control" data-octobat="state"/>
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Postal Code</label>
                                            <input class="form-control" data-octobat="zip-code" />
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Country</label>
                                            <select class="form-control country" data-octobat="country">
                                                <option value="US">United States</option>
                                                <option value="GB">United Kingdom</option>
                                                <option value="BE">Belgium</option>
                                                <option value="BG">Bulgaria</option>
                                                <option value="HR">Croatia</option>
                                                <option value="CY">Cyprus</option>
                                                <option value="CZ">Czech Republic</option>
                                                <option value="DK">Denmark</option>
                                                <option value="EE">Estonia</option>
                                                <option value="AT">Austria</option>
                                                <option value="FI">Finland</option>
                                                <option value="FR">France</option>
                                                <option value="DE">Germany</option>
                                                <option value="GR">Greece</option>
                                                <option value="HU">Hungary</option>
                                                <option value="IE">Ireland</option>
                                                <option value="IT">Italy</option>
                                                <option value="LV">Latvia</option>
                                                <option value="LT">Lithuania</option>
                                                <option value="LU">Luxembourg</option>
                                                <option value="MT">Malta</option>
                                                <option value="NL">Netherlands</option>
                                                <option value="PL">Poland</option>
                                                <option value="PT">Portugal</option>
                                                <option value="RO">Romania</option>
                                                <option value="SK">Slovakia</option>
                                                <option value="SI">Slovenia</option>
                                                <option value="EA">Spain</option>
                                                <option value="SE">Sweden</option>
                                                <option value="AU">Australia</option>

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            @if($card_type == 'credit_card')
                                                <h3>Credit Card Information</h3>
                                            @elseif($card_type == 'debit_card')
                                                <h3>Debit Card Information</h3>
                                            @endif
                                                <h3>Debit Card Information</h3>
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Card Number</label>
                                            <input class="form-control" id="cc-number" />
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Card expiry month</label>
                                            <input class="form-control" id="cc-exp-month" />
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Card expiry year</label>
                                            <input class="form-control" id="cc-exp-year" />
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Card CVC</label>
                                            <input class="form-control" id="cc-cvc" />
                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Subtotal</label>
                                            <span id="total" >{{$price}}</span>
                                        </div>
                                    </div>

                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>VAT</label>
                                            <span class="octobat-taxes" style="display: inline; margin-left: 20px;"></span>%

                                        </div>
                                    </div>
                                    <div class='form-row'>
                                        <div class='col-xs-12 form-group required'>
                                            <label class='control-label'>Total</label>
                                            <span id="total_octobat" style="display: inline; margin-left: 20px;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <input type="hidden" id="total_tax" name="total_tax">
                            <input type="hidden" id="net_total" name="net_total">
                            <input type="hidden" id="plan_id" name="plan_id" value="{{$plan_id}}">
                            <input type="hidden" id="plan_name" name="plan_name" value="{{$plan_name}}">

                            <!-- The submit button -->
                        <div class='form-row'>
                            <div class='col-xs-12 form-group required'>
                                <button type="submit" class="form-control btn btn-primary submit-button">Subscribe</button>
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
    <script src='https://js.stripe.com/v2/' type='text/javascript'></script>
    <script src="https://cdn.jsdelivr.net/gh/0ctobat/octobat.js@latest/dist/octobat-form.min.js"></script>
    <script>
        $('.country').on('change', function() {

            setTimeout(function(){

                var tax = $(".octobat-taxes").text();
                tax = tax.split('.');
                tax = Number(tax[0]);
                console.log( 'tax: ', tax );

                var total = $("span#total").text();
                console.log( 'total: ', total );

                var perc = (Number(total)/100)*Number(tax);
                // perc = Math.round(perc * 100) / 100;
                console.log( 'perc: ', perc );

                var net_total = Number(perc) + Number(total);
                // net_total = Math.round(net_total * 100) / 100;

                console.log( 'net_total: ', net_total );

                $('#tax_octobat').text(tax);
                $('#total_tax').val(tax);
                $('#total_octobat').text(net_total);
                $('#net_total').val(net_total);

            }, 2000);

        })


        var stripeResponseHandler = function(status, response) {
            var $form = $('#octobat-payment-form');
            if (response.error) {
                // Show the errors on the form
                $form.find('.payment-errors').text(response.error.message);
            } else {
                // token contains id, last4, and card type
                var token = response.id;
                console.log('token: ', token);
                // Insert the token into the form so it gets submitted to the server
                $form.append($('<input type="hidden" data-octobat="cardToken" />').val(token));
                $form.append($('<input type="hidden" name="token" />').val(token));
                // create the charge in Stripe via octobat and submit
                if (document.querySelector('#octobat-payment-form').getAttribute("data-plan") !== null) {
                    // Octobat.createSubscription({
                    //     success: function(status, response){
                    //         $form.append($('<input type="hidden" name="transactionDetails" data-octobat="transactionDetails" />').val(response.transaction));
                            $form.get(0).submit();
                    //     },
                    //     error: function(status, response) {
                    //         $form.find('.payment-errors').text(response.message);
                    //     }
                    // });
                }
            }
        };
        // When the DOM is ready
        jQuery(document).ready(function($) {
            // Submit the form

            $('.octobat-payment-form').submit(function(e) {
                e.preventDefault();
                // This identifies your website in the createToken call below
                Stripe.setPublishableKey($(this).data('gateway-pkey'));
                // Get the card token from Stripe
                Stripe.card.createToken({
                    number: $('#cc-number').val(),
                    cvc: $('#cc-cvc').val(),
                    exp_month: $('#cc-exp-month').val(),
                    exp_year: $('#cc-exp-year').val(),
                    address_country: document.querySelector("[data-octobat='country']").value,
                    address_line1: document.querySelector("[data-octobat='street-line-1']").value,
                    address_line2: document.querySelector("[data-octobat='street-line-2']").value,
                    address_city: document.querySelector("[data-octobat='city']").value,
                    address_state: document.querySelector("[data-octobat='state']").value, // State/County/Province/Region
                    address_zip: document.querySelector("[data-octobat='zip-code']").value, // billing ZIP code as a string (e.g., "94301")
                }, stripeResponseHandler);
            });


        });
    </script>
@endsection