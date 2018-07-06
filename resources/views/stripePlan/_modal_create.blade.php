<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">Create a new Plan</h4>
        </div>
        <form id="form" method="post" action="{{ route('plan.store') }}" class="wizard-big">
        {!! csrf_field() !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h3>Product Name</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="text" placeholder="Enter product name" class="form-control" name="product_name" id="product_name">
                                </div>
                            </div>
                            <h3>Plan Nickname</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="text" placeholder="Enter plan nickname" class="form-control" name="nick_name" id="nick_name">
                                </div>
                            </div>
                            <h3>Trial Expiry Days</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="number" placeholder="Enter trial expiry" min="0" class="form-control" name="trial_expiry" id="trial_expiry">
                                </div>
                            </div>
                            <h3>Plan Price</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <div class="input-group">
                                        <span class="input-group-addon">€</span>
                                        <input type="number" name="price" id="price" onchange="euroToCent()" onkeyup="euroToCent()" placeholder="Plan Price in euro" class="form-control" >
                                        <input type="hidden" class="form-control" name="net_price" id="net_price">
                                        <input type="hidden" class="form-control" name="cent_price" id="cent_price">
                                        <input type="hidden" class="form-control" name="cent_net_price" id="cent_net_price">
                                    </div>
                                </div>
                            </div>
                            <h3>Plan Tax</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="text" placeholder="Tax on plan" value="{{env('APP_TAX_RATE','22%')}}" class="form-control" disabled="disabled">
                                    <input type="hidden" placeholder="Tax on plan" value="{{env('APP_TAX_RATE','22%')}}" class="form-control" name="tax" id="tax">
                                </div>
                            </div>

                            <h3>Net Plan Price</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <div class="input-group">
                                        <span class="input-group-addon">€</span>
                                        <input type="number" placeholder="Plan Price in euro" class="form-control" id="net_price_disabled" disabled="disabled">
                                    </div>
                                </div>
                            </div>
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