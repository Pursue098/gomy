<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h2>Invite people to collaborate to <b>{{ $project['name'] }}</b> project</h2>
            <p>
                Invite people partecipate to your project
            </p>
        </div>
        <form id="form-invite" method="post" action="{{ route('project.invite', [$project]) }}" class="wizard-big">
        {!! csrf_field() !!}
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">

                        <div class="ibox-content">
                            <div id="wizard">
                                <h1>People</h1>
                                <div class="step-content" style="width: 100%">
                                    <h3>Insert new people to the list</h3>
                                    <div class="row">

                                        <div class="form-group col-lg-11">
                                            <input type="email" placeholder="email" class="form-control" name="top-search" id="new-email">
                                        </div>
                                        <div class="form-group col-lg-1">
                                            <a href="#" id="submit" class="btn btn-primary btn-sm">Add</a>
                                        </div>
                                    </div>
                                    <hr>

                                    <h3>Click on people to invite</h3>
                                    <select class="form-control dual_select" multiple style="width:100% !important;">
                                        @foreach ($teammates as $teammate)
                                            <option value="{{ $teammate['email'] }}">{{ $teammate['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <h1>Roles</h1>
                                <div class="step-content" style="width: 100%">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 80%;">e-mail</th>
                                                    <th>admin</th>
                                                    <th>user</th>
                                                </tr>
                                            </thead>
                                            <tbody id="user-table">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Invite</button>
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
        </div> --}}
        </form>
    </div>
</div>