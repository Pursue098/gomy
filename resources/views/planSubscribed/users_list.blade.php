@extends('layouts.app')

@section('htmlheader_title')
    Users List with Plans
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('success') }}
        </div>
    @endif
    @if(Session::has('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('error') }}
        </div>
    @endif

    <div class="ibox">
        <div class="ibox-title">
            <h5>Users With Plans</h5>
        </div><br>
        <ul class="nav nav-tabs">
            <li class="subscribedTabs active" id="Enterprize"><a href="#">Enterprize</a></li>
        </ul>
        <div class="table-responsive hidden datadiv" id="GeneralTab">

        </div>
        <div class="table-responsive  datadiv project-list" id="EnterprizeTab">
            <table class="table table-striped table-sm">
                <thead>
                <tr>
                    <th>Username </th>
                    <th>Project</th>
                    <th>Channels</th>
                    <th>Status</th>
                    <th style="padding-left: 5%;">Action</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($subscriptions_enterprize) && !empty($subscriptions_enterprize))
                    @foreach($subscriptions_enterprize as $v)
                        <tr>
                            <td>{{ $userslist[$v->user_id] }}
                                <div id="sub{{ $v->id }}win" class="win">
                                    <br>
                                    <hr>
                                    <table>
                                        @foreach($users as $user)
                                            @if($user->id == $v->user_id)
                                                @foreach($user->projects as $project)
                                                    @foreach($project->channels as $channel)
                                                        @if($channel->id == $v->channel_id)
                                                            <tr>
                                                                <td>
                                                                    @if ($channel->status == 'new')
                                                                        <a href="{{ route('channel.configure', [$project, $channel]) }}" title="{{ ucwords($channel->type) }}" class="btn btn-default btn-circle" type="button"><i class="fa fa-{{ $channel->icon() }}"></i></a>
                                                                    @else
                                                                        @if ($channel->channable->status == 'grabbing')
                                                                            <img alt="{{ $channel->channable->name }}" class="img-circle grabbing small" src="{{ $channel->channable->picture() }}" style="background-color: #000;">
                                                                        @else
                                                                            <a href="{{ route('channel.dashboard', [$project, $channel]) }}" title="{{ $channel->channable->name }}"><img alt="{{ $channel->channable->name }}" class="img-circle" src="{{ $channel->channable->picture() }}" style="background-color: #000;"></a>
                                                                        @endif
                                                                    @endif
                                                                </td>
                                                                <td></td>
                                                                <td>
                                                                    @if($v->transaction_id == 'Unapproved')
                                                                        <a href="{{ route('payment.subscriptionApproval', [$user, $project, $channel]) }}" class="btn btn-warning " id="sub{{ $v->id }}" title="Pending">
                                                                            Pending
                                                                        </a>
                                                                    @elseif($v->transaction_id == 'Approved')
                                                                        <a href="{{ route('payment.subscriptionUnapproved', [$user, $project, $channel]) }}" class="btn btn-warning " id="sub{{ $v->id }}" title="{{ $v->transaction_id }}">
                                                                            {{ $v->transaction_id }}
                                                                        </a>
                                                                    @endif

                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </table>
                                </div>
                            </td>
                            <td valign="top" style="vertical-align: text-top;">
                                @foreach($users as $user)
                                    @if($user->id == $v->user_id)
                                        @foreach($user->projects as $project)
                                            @foreach($project->channels as $channel)
                                                @if($channel->id == $v->channel_id)
                                                    {{$project->name }}
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endforeach
                            </td>
                            <td valign="top" style="vertical-align: text-top;">
                                @foreach($users as $user)
                                    @if($user->id == $v->user_id)
                                        @foreach($user->projects as $project)
                                            @foreach($project->channels as $channel)
                                                @if($channel->id == $v->channel_id)
                                                    @if ($channel->status == 'new')
                                                        <a href="{{ route('channel.configure', [$project, $channel]) }}" title="{{ ucwords($channel->type) }}" class="btn btn-default btn-circle" type="button"><i class="fa fa-{{ $channel->icon() }}"></i></a>
                                                    @else
                                                        @if ($channel->channable->status == 'grabbing')
                                                            <img alt="{{ $channel->channable->name }}" class="img-circle grabbing" src="{{ $channel->channable->picture() }}" style="background-color: #000;">
                                                        @else
                                                            <a href="{{ route('channel.dashboard', [$project, $channel]) }}" title="{{ $channel->channable->name }}"><img alt="{{ $channel->channable->name }}" class="img-circle" src="{{ $channel->channable->picture() }}" style="background-color: #000;"></a>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endforeach
                            </td>
                            <td>
                                @foreach($users as $user)
                                    @if($user->id == $v->user_id)
                                        @foreach($user->projects as $project)
                                            @foreach($project->channels as $channel)
                                                @if($channel->id == $v->channel_id)
                                                    @if($v->transaction_id == 'Unapproved')
                                                        Pending
                                                    @elseif($v->transaction_id == 'Approved')
                                                        Approved
                                                    @elseif($v->transaction_id == 'Reject')
                                                        Reject
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endforeach
                            </td>
                            <td valign="top" style="vertical-align: text-top;">
                                @foreach($users as $user)
                                    @if($user->id == $v->user_id)
                                        @foreach($user->projects as $project)
                                            @foreach($project->channels as $channel)
                                                @if($channel->id == $v->channel_id)

                                                    @if($v->transaction_id == 'Unapproved')
                                                        <form method="get" class="approveRequest" action="{{ route('payment.subscriptionApproval', [$user, $project, $channel]) }}" style="display: inline; margin: 10px;">
                                                            <input name="_method" type="hidden" value="GET">
                                                            {{csrf_field()}}
                                                            <a id="approveRequestButton" class="btn btn-primary approveRequestButton" class="btn btn-primary btn-xs" ><i class="fa fa-check" title="Pending"></i> Approve</a>
                                                        </form>
                                                        <form method="get" class="rejectEnterpriseFormRequest" action="{{ route('payment.subscriptionUnapproved', [$user, $project, $channel]) }}" style="display: inline; margin: 10px;">
                                                            <input name="_method" type="hidden" value="GET">
                                                            {{csrf_field()}}
                                                            <a id="rejectEnterpriseRequest" class="btn btn-danger rejectEnterpriseRequest" class="btn btn-danger btn-xs" ><i class="fa fa-times" title="Reject"></i> Reject It</a>
                                                        </form>
                                                    @elseif($v->transaction_id == 'Approved')
                                                        <form method="get" class="rejectEnterpriseFormRequest" action="{{ route('payment.subscriptionUnapproved', [$user, $project, $channel]) }}" style="display: inline; margin: 10px;">
                                                            <input name="_method" type="hidden" value="GET">
                                                            {{csrf_field()}}
                                                            <a id="rejectEnterpriseRequest" class="btn btn-danger rejectEnterpriseRequest" class="btn btn-danger btn-xs" ><i class="fa fa-times" title="Reject"></i> Reject It</a>
                                                        </form>
                                                    @elseif($v->transaction_id == 'Reject')
                                                        <form method="get" class="approveRequest" action="{{ route('payment.subscriptionApproval', [$user, $project, $channel]) }}" style="display: inline; margin: 10px;">
                                                            <input name="_method" type="hidden" value="GET">
                                                            {{csrf_field()}}
                                                            <a id="approveRequestButton" class="btn btn-primary approveRequestButton" class="btn btn-primary btn-xs" ><i class="fa fa-check" title="Pending"></i> Approve</a>
                                                        </form>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                @else
                    <span> No records found </span>
                @endif
                </tbody>
            </table>
            {{ $subscriptions_enterprize->links() }}
        </div>
    </div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        $('.win').hide();
        $('.approveRequestButton').on('click', function(e){
            e.preventDefault();
            swal({
                title: 'Are you sure?',
                height: '200px',
                text: "You want to reject!",
                showCancelButton: true,
                confirmButtonColor: '#1ab394',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Do it!',
                closeOnConfirm: false,
                customClass: 'swal-height',
            }).then((result) => {

                console.log('result: ', result);
                $(this ).closest('form.approveRequest').submit();
            });
            return false;
        });

        $('.rejectEnterpriseRequest').on('click', function(e){
            e.preventDefault();
            swal({
                title: 'Are you sure?',
                height: '200px',
                text: "You want to reject!",
                showCancelButton: true,
                confirmButtonColor: '#1ab394',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Do it!',
                closeOnConfirm: false,
                customClass: 'swal-height',
            }).then((result) => {

                console.log('result: ', result);
                $(this ).closest('form.rejectEnterpriseFormRequest').submit();
            });
            return false;
        });
    </script>
@endsection
