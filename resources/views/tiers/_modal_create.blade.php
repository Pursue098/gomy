<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">Create a new Tier for {{$channel_type}}</h4>
        </div>
        <form id="form" method="post" action="{{ route('tier.store', [$channel_type]) }}" class="wizard-big">
        {!! csrf_field() !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h3>Tier name</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="text" placeholder="Enter tier name" class="form-control" name="name" id="name">
                                </div>
                            </div>
                            <h3>Tier complexity Min </h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="number" placeholder="Tier min value" value="{{$max}}" class="form-control" disabled>
                                    <input type="hidden" value="{{$max}}" class="form-control" name="comp_start" id="comp_start" >
                                </div>
                            </div>
                            <h3>Tier complexity Max </h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="number" placeholder="Enter tier max value" min="{{$max+1}}" class="form-control" name="comp_end" id="comp_end">
                                </div>
                            </div>
                            <h3>Plan Nickname </h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <select name="prod_plan_id" id="prod_plan_id" class="form-control" onchange="getPlanDetailsCreate()">
                                        @foreach($plans as $plan)
                                            <option value="{{$plan->id}}" data-plan-trial="{{$plan->trial_expiry}}" data-plan-price="{{$plan->net_price}}" data-plan-product-name="{{$plan->product_name}}">{{$plan->nick_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if(isset($plans) && !empty($plans))
                                <h3>Plan Details </h3>
                                <div class="row">
                                    <div class="form-group col-lg-12">
                                        <label>Plan Price</label>
                                        <span id="plan_price">{{$plans[0]->net_price}}</span>
                                    </div>
                                    <div class="form-group col-lg-12">
                                        <label>Product Name</label>
                                        <span id="plan_product_name">{{$plans[0]->product_name}}</span>
                                    </div>
                                    <div class="form-group col-lg-12">
                                        <label>Plan Trail</label>
                                        <span id="trial_expiry">{{$plans[0]->trial_expiry}}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Create</button>
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
        </div>
        </form>
    </div>
</div>