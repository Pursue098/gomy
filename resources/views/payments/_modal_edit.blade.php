<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">update this Tiers</h4>
        </div>
        @if(isset($tier))
            <form id="form" method="post" action="{{ route('tier.update', [ $project, $channel, $tier->id]) }}" class="wizard-big">
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
                                    <h3>Tier complexity Max </h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Enter tier max value" value="{{ $tier->comp_start }}" class="form-control" name="comp_start" id="comp_start">
                                        </div>
                                    </div>
                                    <h3>Tier complexity Min </h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Enter tier min value" value="{{ $tier->comp_end }}" class="form-control" name="comp_end" id="comp_end">
                                        </div>
                                    </div>
                                    <h3>Tier price</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Enter tier price" value="{{ $tier->price }}" class="form-control" name="price" id="price">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </form>
        @endif
    </div>
</div>