<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <i id='modal-icon' class="fa fa-pencil modal-icon"></i>
            <h4 class="modal-title">Modify team roles</h4>
        </div>
        <form id="form" method="post" action="{{ route('project.roles', [$project]) }}" class="wizard-big">
        {!! csrf_field() !!}
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-content">

                            <h3>Modify project stakeholder roles</h3>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>User</th>
                                                    <th class="text-center">Admin</th>
                                                    <th class="text-center">User</th>
                                                    <th class="text-center">Remove</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($teammates as $teammate)
                                                    @if ($teammate->pivot->role != 'owner')
                                                    <tr>
                                                        <td>{{ $teammate->name }} ({{ $teammate->pivot->role }})</td>
                                                        <td class="text-center">
                                                            <div class='i-checks center'>
                                                                <input type="radio" class="i-radio" name="mod_role[{{ $teammate->id }}]" value="admin" {{ ($teammate->pivot->role == 'admin') ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class='i-checks center'>
                                                                <input type="radio" class="i-radio" name="mod_role[{{ $teammate->id }}]" value="user" {{ ($teammate->pivot->role == 'user') ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class='i-checks center'>
                                                                <input type="radio" class="i-radio" name="mod_role[{{ $teammate->id }}]" value="delete">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
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
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
        </div>
        </form>
    </div>
</div>