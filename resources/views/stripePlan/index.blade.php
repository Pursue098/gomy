@extends('layouts.app')

@section('htmlheader_title')
    Product Plans
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="active">
            <strong>Product Plans</strong>
        </li>
    </ol>
@endsection

@section('main-content')
    <div class="spark-screen">
        <div class="row">
            <div class="col-md-12">

                @if(Session::has('success-message'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ Session::get('success-message') }}
                    </div>
                @endif
                @if(Session::has('fail-message'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ Session::get('fail-message') }}
                    </div>
                @endif

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Product plan management section</h5>
                        <div class="ibox-tools">
                            <a id="add_new_plan" href="{{ route('plan.create') }}" class="btn btn-primary btn-xs">Create new Plan</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="project-list">
                            <table class="footable table toggle-arrow-tiny">
                                <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Plan Nickname</th>
                                    <th>Price</th>
                                    <th>Tax</th>
                                    <th>Net Price</th>
                                    <th>Trail Expiry</th> 
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($plans) && count($plans) > 0)
                                    @foreach($plans as $plan)
                                        <tr>
                                            <td>
                                                <span>{{ $plan->product_name }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $plan->nick_name }}</span>
                                            </td>
                                            <td>
                                                <span>€ {{ $plan->price }}</span>
                                            </td>
                                            <td>
                                                <span>{{env('APP_TAX_RATE')}}</span>
                                            </td>
                                            <td>
                                                <span>€ {{ $plan->net_price }}</span>
                                            </td>

                                            <td>
                                                @if($plan->trial_expiry <= 1)
                                                    <span>{{ $plan->trial_expiry }} Day</span>
                                                @else
                                                    <span>{{ $plan->trial_expiry }} Days</span>
                                                @endif
                                            </td> 
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal" id="modal-create-plan" tabindex="-1" role="dialog" aria-hidden="true"></div>
    <div class="modal inmodal" id="modal-edit-plan" tabindex="-1" role="dialog" aria-hidden="true"></div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        function euroToCent() {

            var total = $('#price').val();
            console.log('total: ', total);
            var tax = $('#tax').val();
            tax = tax.split("%");
            console.log('tax[0]: ', tax[0]);

            var perc = (Number(total)/100)*Number(tax[0]);
            console.log('perc: ', perc);

            var net_total = Number(perc) + Number(total);
            console.log('net_total: ', net_total);

            $('#net_price').val(net_total);
            $('#net_price_disabled').val(net_total);

            var cent = total*100;
            $('#cent_price').val(cent);

            var cent_net = net_total*100;
            $('#cent_net_price').val(cent_net);

        }


        $('.deletePlan').on('click', function(e){
            e.preventDefault();
            swal({
                title: 'Are you sure?',
                height: '200px',
                text: "You want to Delete!",
                showCancelButton: true,
                confirmButtonColor: '#1ab394',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Do it!',
                closeOnConfirm: false,
                customClass: 'swal-height',
            }).then((result) => {

                console.log('result: ', result);
                $(this ).closest('form.planDeleteForm').submit();
            });
            return false;
        });

    </script>
    <script>
        $(document).ready(function() {

            $('#add_new_plan').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-create-plan');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('.edit_plan').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-edit-plan');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });
        });
    </script>
@endsection
