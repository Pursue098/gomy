<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">Update Tier Information for {{$channel_type}}</h4>
        </div>
        @if(isset($tier))
            <form id="form" method="post" action="{{ route('tier.update', [$tier->id, $channel_type]) }}" class="wizard-big tierUpdateForm">
                <input name="_method" type="hidden" value="PUT">
                {!! csrf_field() !!}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-content">
                                    <h3>Tier name</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="text" placeholder="Enter tier name" value="{{ $tier->name }}" class="form-control" name="name" id="name">
                                        </div>
                                    </div>
                                    <h3>Tier complexity Min </h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Tier min value" value="{{ $tier->comp_start }}" class="form-control" disabled>
                                            <input type="hidden" value="{{ $tier->comp_start }}" class="form-control" name="comp_start" id="comp_start">
                                        </div>
                                    </div>
                                    <h3>Tier complexity Max </h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Enter tier max value" value="{{ $tier->comp_end }}" class="form-control" disabled>
                                            <input type="hidden" value="{{ $tier->comp_end }}" class="form-control" name="comp_end" id="comp_end">
                                        </div>
                                    </div>

                                    <h3>Plan Name </h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <select name="prod_plan_id" id="prod_plan_id" class="form-control" onchange="getPlanDetailsEdit()">
                                                @foreach($plans as $plan)
                                                    @if($tier->prod_plan_id == $plan->id)
                                                        <option value="{{$plan->id}}" data-plan-trial="{{$plan->trial_expiry}}" data-plan-price="{{$plan->net_price}}" data-plan-product-name="{{$plan->product_name}}" selected>{{$plan->nick_name}}</option>
                                                    @else
                                                        <option value="{{$plan->id}}" data-plan-trial="{{$plan->trial_expiry}}" data-plan-price="{{$plan->net_price}}" data-plan-product-name="{{$plan->product_name}}">{{$plan->nick_name}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    @if(isset($plans) && !empty($plans) && isset($tier->prod_plan_id))
                                        <h3>Plan Details </h3>
                                        <div class="row">
                                            @foreach($plans as $plan)
                                                @if($tier->prod_plan_id == $plan->id)
                                                    <div class="form-group col-lg-12">
                                                        <label>Plan Price</label>
                                                        <span id="plan_price_edit">{{$plan->net_price}}</span>
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        <label>Product Name</label>
                                                        <span id="plan_product_name_edit">{{$plan->product_name}}</span>
                                                    </div>
                                                    <div class="form-group col-lg-12">
                                                        <label>Plan Trail</label>
                                                        <span id="trial_expiry_edit">{{$plan->trial_expiry}}</span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"id="updateTier" >Update</button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </form>
        @endif
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function() {

            $('#updateTier').on('click', function(e){

                console.log('azam');
                e.preventDefault();
                swal({
                    title: 'Are you sure?',
                    height: '200px',
                    text: "You want to update!",
                    showCancelButton: true,
                    confirmButtonColor: '#1ab394',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!',
                    closeOnConfirm: false,
                    customClass: 'swal-height',
                }).then((result) => {

                    console.log('result: ', result);
                    $('form.tierUpdateForm').submit();
                });
                return false;
            });
        });
    </script>
@endsection
