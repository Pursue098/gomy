@extends('layouts.app')

@section('htmlheader_title')
    {{$channel_type}} Tiers
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="active">
            <strong>{{strtoupper($channel_type)}} Channel</strong>
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
                        <h5>Tier's management section</h5>
                        <div class="ibox-tools">
                            <a id="add_channel_tier" href="{{ route('tier.create', [$channel_type]) }}" class="btn btn-primary btn-xs">Create new Tier</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="project-list">
                            <table class="footable table toggle-arrow-tiny">
                                <thead>
                                <tr>
                                    <th>Tiers Name</th>
                                    <th>Channels type</th>
                                    <th>Complexity Min</th>
                                    <th>Complexity Max</th>
                                    <th>Plan Name</th>
                                    <th>Trial Expiry</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($tiers) && count($tiers) > 0)
                                    @foreach($tiers as $tier)
                                        <tr>
                                            <td>
                                                <span>{{ $tier->name }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $channel_type }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $tier->comp_start }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $tier->comp_end }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $tier->plan->nick_name }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $tier->plan->trial_expiry }}</span>
                                            </td>
                                            <td>
                                                <span>{{ $tier->plan->net_price }}</span>
                                            </td>
                                            <td>
                                                @if (Auth::user()->hasRole('superadministrator|administrator|project_role:admin'))

                                                    <a id="edit_channel_tier" href="{{ route('tier.edit', [$channel_type, $tier->id]) }}" class="btn btn-white btn-sm edit_channel_tier"><i class="fa fa-pencil"></i> Edit </a>

                                                    <form class="tierDeleteForm" method="post" action="{{ route('tier.destroy', [ $channel_type, $tier->id]) }}" style=" display: inline; margin: 2px;">
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        {!! csrf_field() !!}
                                                        <a class="btn btn-danger deleteTire" id="deleteTire">Delete</a>
                                                    </form>
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

    <div class="modal inmodal" id="modal-create-tier" tabindex="-1" role="dialog" aria-hidden="true"></div>

    <div class="modal inmodal" id="modal-edit-tier" tabindex="-1" role="dialog" aria-hidden="true"></div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $('.deleteTire').on('click', function(e){
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
                $(this ).closest('form.tierDeleteForm').submit();
            });
            return false;
        });

        function getPlanDetailsCreate() {

            console.log('getPlanDetailsCreate');
            var price = $('#prod_plan_id').find(':selected').attr('data-plan-price');
            var trial = $('#prod_plan_id').find(':selected').attr('data-plan-trial');
            var product_name = $('#prod_plan_id').find(':selected').attr('data-plan-product-name');

            console.log('price: ', price);
            console.log('trial: ', trial);
            console.log('product_name: ', product_name);

            $('#plan_price').text(price);
            $('#plan_product_name').text(product_name);
            $('#trial_expiry').text(trial);
        }

        function getPlanDetailsEdit() {

            console.log('getPlanDetailsEdit');
            var price = $('#prod_plan_id').find(':selected').attr('data-plan-price');
            var trial = $('#prod_plan_id').find(':selected').attr('data-plan-trial');
            var product_name = $('#prod_plan_id').find(':selected').attr('data-plan-product-name');

            console.log('price: ', price);
            console.log('trial: ', trial);
            console.log('product_name: ', product_name);

            $('#plan_price_edit').text(price);
            $('#plan_product_name_edit').text(product_name);
            $('#trial_expiry_edit').text(trial);
        }

    </script>
    <script>
        $(document).ready(function() {

            $('#add_channel_tier').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-create-tier');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('.edit_channel_tier').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-edit-tier');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });
        });
    </script>
@endsection
