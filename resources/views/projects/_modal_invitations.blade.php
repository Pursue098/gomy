<div class="modal-dialog modal-lg">
    <div class="modal-content animated bounceInRight">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h2>Invitations of <b>{{ $project['name'] }}</b> project</h2>
        </div>
        <div class="modal-body">

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">

                        <div class="ibox-content">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>e-mail</th>
                                            <th>role</th>
                                            <th>status</th>
                                            <th>expire</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    @foreach($project->invites as $invite)
                                        <?php $colors = [
                                            'successful' => 'text-success',
                                            'canceled'   => 'text-danger',
                                            'pending'    => '',
                                            'expired'    => 'text-muted',
                                        ]
                                        ?>
                                        <tr>
                                            <td style="{{ ($invite->status == 'canceled') ? 'text-decoration: line-through;' : '' }}">{{ $invite->email }}</td>
                                            <td><span class="label label-{{ $invite->role }}">{{ $invite->role }}</span></td>
                                            <td class="{{ $colors[$invite->status] }}">{{ $invite->status }}</td>
                                            <td>
                                                @if ($invite->status == 'successful' || $invite->status == 'canceled')
                                                    -
                                                @elseif ($invite->valid_till->isPast())
                                                    expired
                                                @else
                                                    {{ $invite->valid_till->diffForHumans() }}
                                                @endif
                                            </td>
                                            <td>
                                                @if ($invite->status == 'pending')
                                                    <a href="{{ route('project.invite.delete', [$project, $invite->code]) }}" class="btn btn-xs btn-danger"><i class="fa fa-remove"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>