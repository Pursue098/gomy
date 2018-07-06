@extends('layouts.app')

@section('htmlheader_title')
    Projects
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="active">
            <strong>Projects</strong>
        </li>
    </ol>
@endsection

@section('main-content')
    <div class="spark-screen">
        <div class="row">
            <div class="col-md-12">

                @if(Session::has('message'))
                    <?php $message = Session::get('message'); ?>
                    <div class="alert alert-{{ $message['type'] }} alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fa fa-check"></i> {{ $message['text'] }}
                    </div>
                @endif
                
                 @if(Session::has('warning-message'))
                        <div class="alert alert-danger">
                            {{ Session::get('warning-message') }}
                        </div>
                    </div>
                @endif 

                @if(Session::has('successMessage'))
                    <div class="alert alert-success">
                        {{ Session::get('successMessage') }}
                    </div>
                @endif

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>All projects assigned to this account</h5>
                        <div class="ibox-tools">
                            <a id="btn-create-project" href="{{ route('projects.create') }}" class="btn btn-primary btn-xs">Create new project</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                       <div class="row m-b-sm m-t-sm">
                            <div class="col-md-12">
                                <div class="input-group">
                                 
                                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                    <input class="form-control" type="text" id="myInput" onkeyup="myFunction()" placeholder="Search For Project.."/>

                                </div>
                                <br>
                            </div>
                        </div>

                        <div class="project-list">

                            <table class="footable table toggle-arrow-tiny" id="myTable">
                                <thead>
                                <tr>
                                    <th data-toggle="true">Project</th>
                                    <th style="text-align: center;">Channels</th>
                                    <th style="text-align: center;" class="hidden-sm hidden-xs">Users</th>
                                    <th data-hide="all">Channels</th>
                                    <th class="project-actions">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($projects as $project)
                                    <tr>
                                        <td class="project-title">
                                            <strong>{{ $project->name }}</strong>
                                            <br/>
                                            <small style="padding-left: 14px;">Created {{ $project->created_at->format('Y-m-d') }}</small>
                                        </td>
                                        <td style="text-align: center;">
                                            @foreach($project->channels as $channel)
                                                @if ($channel->status == 'new')
                                                    <a href="{{ route('channel.configure', [$project, $channel]) }}" title="{{ ucwords($channel->type) }}" class="btn btn-default btn-circle" type="button"><i class="fa fa-{{ $channel->icon() }}"></i></a>
                                                @else
                                                    @if ($channel->channable->status == 'grabbing')
                                                        <img alt="{{ $channel->channable->name }}" class="img-circle grabbing" src="{{ $channel->channable->picture() }}" style="background-color: #000;">
                                                    @else
                                                        <a href="{{ route('channel.dashboard', [$project, $channel]) }}" title="{{ $channel->channable->name }}"><img alt="{{ $channel->channable->name }}" class="img-circle" src="{{ $channel->channable->picture() }}" style="background-color: #000;"></a>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </td>
                                        <td style="text-align: center;" class="hidden-sm hidden-xs">
                                            <table style="margin: 0 auto; text-align: center;">
                                                <tr>
                                                    @foreach($project->users as $user)
                                                        <td style="padding: 0 20px; border: none;">
                                                            <img data-id="{{ $user->id }}" alt="{{ $user->name }}" title="{{ $user->name }}" class="img-circle stakeholder" src="{{ $user->picture }}">
                                                        </td>
                                                    @endforeach
                                                </tr>
                                                <tr>
                                                    @foreach($project->users as $user)
                                                        <td style="padding: 0 20px 5px 20px; border: none;"><span class="label label-{{ $user->pivot->role }}" title="{{ $user->name }}">{{ $user->pivot->role }}</span></td>
                                                    @endforeach
                                                </tr>
                                            </table>
                                        </td>
                                        <td>
                                            <br />
                                            <table class="table table-hover ">
                                                @foreach($project->channels as $channel)
                                                    <tr class="sk-loading">
                                                        <td>
                                                            @if ($channel->status == 'new')
                                                                <a href="#" class="btn btn-default btn-circle" type="button"><i class="fa fa-{{ $channel->icon() }}"></i></a>
                                                            @else
                                                                <a href=""><img alt="image" class="img-circle" src="{{ $channel->channable->picture() }}" style="width: 32px;"></a>
                                                            @endif
                                                        </td>
                                                        <td>{!! ($channel->status == 'assigned') ? '<i class="fa fa-' . $channel->icon() . '"></i>' : '' !!} {{ $channel->name }}</td>
                                                        <td>
                                                            @if ($channel->status == 'new')
                                                                <a href="{{ route('channel.configure', [$project, $channel]) }}" class="btn btn-white btn-sm"><i class="fa fa-add"></i> Configure </a>
                                                            @endif

                                                            @if ($channel->channable['status'] == 'grabbing')
                                                                <div class="sk-spinner sk-spinner-wave">
                                                                    <div class="sk-rect1"></div>
                                                                    <div class="sk-rect2"></div>
                                                                    <div class="sk-rect3"></div>
                                                                    <div class="sk-rect4"></div>
                                                                    <div class="sk-rect5"></div>
                                                                </div>
                                                                {{-- <small>Grabbing history</small>
                                                                <div class="progress progress-small active" style="margin-bottom: 0px;">
                                                                    <div style="width: 100%;" class="progress-bar progress-bar-striped"></div>
                                                                </div> --}}
                                                            @endif

                                                            @if ($channel->channable['status'] == 'grabbed')
                                                                <a href="{{ route('channel.dashboard', [$project, $channel]) }}" class="btn btn-white btn-sm"><i class="fa fa-dashboard"></i> Dashboard </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                        <td class="project-actions">
                                            @if ($project->canAdmin(Auth::user()))

                                                <a href="#" class="btn btn-white btn-sm" data-toggle="modal" data-target="#modal_add_channel_{{ $project->id }}"><i class="fa fa-plus"></i> Channel </a>
                                                <a href="{{ route('project.roles', [$project]) }}" class="btn btn-white btn-sm btn-project-roles"><i class="fa fa-pencil"></i> Roles </a>
                                                <a href="{{ route('project.invite', [$project]) }}" class="btn btn-white btn-sm btn-project-invite"><i class="fa fa-user-plus"></i> Invite </a>
                                                <a href="{{ route('project.invitations', [$project]) }}" class="btn btn-white btn-sm btn-project-invitations"><i class="fa fa-address-book-o"></i> Invitations </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="modals">
        @foreach($projects as $project)
            <div class="modal inmodal modal_add_channel" id="modal_add_channel_{{ $project->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content animated bounceInRight">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title">{{ $project->name }}</h4>
                            <small class="font-bold">Add a new channel to project.</small>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label>Channel name</label>
                                    <input type="name" value="{{ $project->name }}" placeholder="Enter channel name" class="form-control channel_name">
                                </div>
                            </div>
                            @foreach(array_chunk(App\Channel::$supported, 4) as $chunk)
                                <div class="row">
                                    @foreach($chunk as $channel)
                                        <div class="col-md-3">
                                            <div class="widget navy-bg p-lg text-center">
                                                <i class="fa fa-{{ $channel['icon'] }} fa-3x"></i>
                                                <br /><br />
                                                <h3 class="font-bold no-margins">{{ ucwords($channel['name'])}}</h3>
                                                <br />
                                                <a style="color: #1ab394;" class="btn btn-default" data-method="post" href="{{ route('channel.add', [$project, $channel['name']]) }}">Add</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="modal inmodal" id="modal-roles" tabindex="-1" role="dialog" aria-hidden="true">
    </div>

    <div class="modal inmodal" id="modal-invite" tabindex="-1" role="dialog" aria-hidden="true">
    </div>

    <div class="modal inmodal" id="modal-invitations" tabindex="-1" role="dialog" aria-hidden="true">
    </div>

    <div class="modal inmodal" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true">
    </div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>

        function isValidEmailAddress(emailAddress) {
            var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,16}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
            return pattern.test(emailAddress);
        }

        function create_table(users) {
            $('#user-table').empty();

            if (users != null) {
                $.each(users, function(index,value){
                    $('#user-table').append(
                        $('<tr>').append(
                            $('<td>').text(value)
                        ).append(
                            $('<td>').append(
                                $('<div>').attr({
                                    'class': 'i-checks center'
                                }).append(
                                    $('<input>').attr({
                                        'type':  'radio',
                                        'class': 'i-radio',
                                        'name': 'invitations[' + value + ']',
                                        'value': 'admin'
                                    })
                                )
                            )
                        ).append(
                            $('<td>').append(
                                $('<div>').attr({
                                    'class': 'i-checks center'
                                }).append(
                                    $('<input>').attr({
                                        'type':  'radio',
                                        'class': 'i-radio',
                                        'name': 'invitations[' + value + ']',
                                        'checked': '',
                                        'value': 'user'
                                    })
                                )
                            )
                        )
                    );
                });

                $('.i-checks').iCheck({
                    checkboxClass: 'icheckbox_square-green',
                    radioClass: 'iradio_square-green',
                });

            } else {
                $('#user-table').empty();
            }
        }

    </script>
    <script>
        $(document).ready(function() {
            $('.footable').footable();

            $('.modal_add_channel').on('submit', function(e) {
                var name = $(this).find('.channel_name').val();

                $(e.target).append('<input type="hidden" name="name" value="' + name + '" />');

                return true;
            });

            $('.btn-project-roles').click(function(e) {

                e.preventDefault();

                var modal = $('#modal-roles');

                modal.on('shown.bs.modal', function() {
                    $('.i-radio').iCheck({
                        checkboxClass: 'icheckbox_square-green',
                        radioClass: 'iradio_square-green',
                    });
                });

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });



            });

            $('.btn-project-invite').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-invite');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('.btn-project-invitations').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-invitations');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('#btn-create-project').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-create');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('#modal-invite').on('show.bs.modal', function (e) {

                $('#wizard').steps({
                    showFinishButtonAlways: false,
                    onStepChanging: function() {
                        return true;
                    },
                    onFinishing: function() {
                        $('#form-invite').submit();
                        return true;
                    }
                });

                var dualList = $('.dual_select').bootstrapDualListbox({
                    selectorMinimalHeight: 200
                });

                dualList.on('change', function(){

                    create_table($('.dual_select').val());

                });

                $('#submit').on('click', function(){
                    var mail = $('#new-email');

                    if (! isValidEmailAddress(mail.val())) {
                        mail.parent().addClass('has-error');
                        return false;
                    }

                    if ($('.dual_select option').filter('[value="' + mail.val() + '"]').length > 0) {
                        mail.parent().addClass('has-error');
                        return false;
                    }

                    mail.parent().removeClass('has-error');

                    dualList.append(
                        $('<option>').attr({
                            'value': mail.val(),
                            'selected':''
                        }).text(mail.val())
                    )

                    create_table($('.dual_select').val());

                    mail.val('');

                    dualList.bootstrapDualListbox('refresh');
                });

            });

            $('.stakeholder').on('click', function(e) {

                console.log($(this).attr('data-id'));

            });


        });

        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('2a4877faf62f9cca50c2', {
            cluster: 'eu',
            encrypted: true
        });

        var projects =  $projects=>map(function($project, $key) { return $project=>getRouteKey(); }) ;

        $.each(projects, function(i, project) {
            var channel = pusher.subscribe('project.' + project);

            channel.bind('channel.grabbed', function(data) {
                console.log(data);

                //$('#page-' + data.page + ' .td-progress').html('');
                location.reload(true);
            });
        });


        function myFunction() {
            var input, filter, table, tr, td, i;
            input = document.getElementById("myInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("myTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        
    </script>
@endsection
