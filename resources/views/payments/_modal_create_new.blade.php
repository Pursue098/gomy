<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-money modal-icon"></i>
            <h4 class="modal-title">Subscription</h4>
        </div>

        <form id="form" method="post" action="{{ route('payment.store', [$project, $channel]) }}">
            {!! csrf_field() !!}
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ibox float-e-margins">
                            <div class="ibox-content">
                                <fieldset>
                                    <h3><b>Invoice:</b></h3>
                                    <table class="table table-hover">
                                        <tbody>
                                            <tr>
                                                <th>Channel</th>
                                                <th>Complexity</th>
                                                <th>Subscription Type</th>
                                                <th>Total Price</th>
                                            </tr>
                                            <tr>
                                                <td>{{ $channel->type }}</td>
                                                <td>{{ $channable->complexity }} <a href="#"></a></td>
                                                <td>Monthly</td>
                                                <td>€{{$plan->price }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3"><span class="pull-right">Tax</span></th>
                                                <td>€{{ $tax_amount }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3"><span class="pull-right">Sub Total</span></th>
                                                <td>€{{$plan->net_price }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3"><span class="pull-right">Trail Period</span></th>
                                                <td>{{$trial_period }} Days</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3"><span class="pull-right">Total Interval to Charge</span></th>
                                                <td>Days {{$leftDays }} : Hours {{$leftHours}}  : Minutes {{$leftMinutes}}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="3"><span class="pull-right">Net Total</span></th>
                                                <td>€{{$price }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </fieldset>
                                <br><br>
                                <fieldset>
                                    <h3><b>Set your payment method:</b></h3>
                                    <hr>
                                        <div class="funkyradio">

                                            <div class="funkyradio-primary">
                                                <input type="radio" name="subscription" value="credit_card" id="subscription" checked/>
                                                <i class="fa fa-credit-card" style="font-size:24px;color:green; padding: 5px 5px; " ></i>
                                                <span style="display: inline">Credit Card</span>
                                            </div>

                                            <div class="funkyradio-primary">
                                                <input type="radio" name="subscription" value="debit_card" id="subscription" checked/>
                                                <i class="fa fa-credit-card" style="font-size:24px;color:green; padding: 5px 5px; " ></i>
                                                <span style="display: inline">Debit Card</span>

                                            </div>

                                            {{--<div class="funkyradio-default">--}}
                                                {{--<input type="radio" name="subscription" value="paypal" id="subscription" />--}}
                                                {{--<i class="fa fa-paypal" style="font-size:24px;color:green; padding: 5px 5px; "></i>--}}
                                            {{--</div>--}}

                                        </div>
                                    <input type="hidden" value="{{$price}}" name="price">
                                    <input type="hidden" value="{{$channable->complexity}}" name="complexity">
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Proceed</button>
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

@endsection