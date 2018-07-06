<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-money modal-icon"></i>
            <h4 class="modal-title">Subscription Phase</h4>
        </div>

        <form id="form" method="post" action="{{ route('payment.store', [$project, $channel]) }}">
            {!! csrf_field() !!}
            <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <div class="form-group col-lg-4">
                                        <h4>Channel Name</h4>
                                        <div class="form-group col-lg-6">
                                            <h5 style="color: #1ab394"> {{ $channel->name }}</h5>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <h4>Channel complexity</h4>
                                        <div class="form-group col-lg-6">
                                            <h5 style="color: #1ab394"> {{$channable->complexity }}</h5>
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <h4>Price</h4>
                                        <div class="form-group col-lg-6">
                                            <h5 style="color: #1ab394"> {{$price }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-9" style="text-align: center">
                                    <h4>Please select your payment type</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">

                                    <div class="form-group col-lg-6">
                                        <h4>Paypal</h4>
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <input type="radio" name="subscription" value="paypal" class="form-control" style="height: 20px;" checked>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">

                                    <div class="form-group col-lg-6">
                                        <h4>Credit Card</h4>
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <input type="radio" name="subscription" value="credit_card" class="form-control"  style="height: 20px;" >
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">

                                    <div class="form-group col-lg-6">
                                        <h4>Debit Card</h4>
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <input type="radio" name="subscription" value="debit_card" class="form-control" style="height: 20px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-9" style="text-align: center">
                                    <h4>You want to use free trial</h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-12">

                                    <div class="form-group col-lg-6">
                                        <h4>Trial for 15 days</h4>
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <input type="radio" name="subscription" value="free_tiel" class="form-control" style="height: 20px;" >
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" value="{{$price}}" name="price">
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
    <script>

        console.log('dddddddddd');
        $( document ).ready(function() {
            $(".radioBtnClass").click(function(){
                console.log('here');
            });

        });
    </script>

@endsection