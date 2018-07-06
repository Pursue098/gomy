<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-server modal-icon"></i>
            <h4 class="modal-title">Create a new project</h4>
        </div>
        <form id="form" method="post" action="{{ route('projects.create') }}" class="wizard-big">
        {!! csrf_field() !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">
                            <h3>Insert the project name</h3>
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <input type="text" placeholder="new project name" class="form-control" name="new-project" id="new-project">
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