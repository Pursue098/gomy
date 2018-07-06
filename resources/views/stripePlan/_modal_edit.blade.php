<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">Update Plan Information</h4>
        </div>
        @if(isset($plan))
            <form id="form" method="post" action="{{ route('plan.update', [$plan->id]) }}" class="wizard-big planUpdateForm">
                <input name="_method" type="hidden" value="PUT">
                {!! csrf_field() !!}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="ibox float-e-margins">
                                <div class="ibox-content">
                                    <h3>Product Name</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="text" placeholder="Enter product name" value="{{ $plan->product_name }}" class="form-control" name="product_name" id="product_name">
                                        </div>
                                    </div>
                                    <h3>Plan Nickname</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="text" placeholder="Enter plan nickname" value="{{ $plan->nick_name }}" class="form-control" name="nick_name" id="nick_name">
                                        </div>
                                    </div>
                                    <h3>Trial Expiry Days</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <input type="number" placeholder="Enter trial expiry" value="{{ $plan->trial_expiry }}" min="0" class="form-control" name="trial_expiry" id="trial_expiry">
                                        </div>
                                    </div>
                                    <h3>Plan Price</h3>
                                    <div class="row">
                                        <div class="form-group col-lg-12">
                                            <div class="input-group">
                                                <span class="input-group-addon">$</span>
                                                <input type="number" placeholder="Enter plan price" value="{{ $plan->price }}" class="form-control" name="price" id="price">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"id="updatePlan" >Update</button>
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </form>
        @endif
    </div>
</div>
@section('scripts')
    <script>
        $(document).ready(function() {

            $('#updatePlan').on('click', function(e){

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
                    $('form.planUpdateForm').submit();
                });
                return false;
            });
        });
    </script>
@endsection
