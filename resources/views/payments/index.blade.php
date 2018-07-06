@extends('layouts.app')

@section('htmlheader_title')
    Payments
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="active">
            <strong>Payments</strong>
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
                        <h5>All the payments types</h5>
                        <div class="ibox-tools">
                            <a id="add_channel_tier" href="{{ route('tier.create', [$project, $channel]) }}" class="btn btn-primary btn-xs">Create new Payments type</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="project-list">
                            <table class="footable table toggle-arrow-tiny">
                                <thead>
                                <tr>
                                    <th >Tiers Name</th>
                                    <th>Channels name</th>
                                    <th>Complexity Min</th>
                                    <th>Complexity Max</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(isset($tiers) && count($tiers) > 0)
                                    @foreach($tiers as $tier)
                                        <tr>
                                            <td>
                                                <strong>{{ $tier->name }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $channel->name }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $tier->comp_start }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $tier->comp_end }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $tier->price }}</strong>
                                            </td>
                                            <td>
                                                @if ($project->canAdmin(Auth::user()))

                                                    <a id="edit_channel_tier" href="{{ route('tier.edit', [$project, $channel, $tier->id]) }}" class="btn btn-white btn-sm"><i class="fa fa-pencil"></i> Edit </a>

                                                    <form method="post" action="{{ route('tier.destroy', [ $project, $channel, $tier->id]) }}">
                                                        <input name="_method" type="hidden" value="DELETE">
                                                        {!! csrf_field() !!}
                                                        <button type="submit" class="btn btn-primary">Delete</button>
                                                    </form>

                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal inmodal" id="modal-create-tier" tabindex="-1" role="dialog" aria-hidden="true"></div>

    <div class="modal inmodal" id="modal-edit-tier" tabindex="-1" role="dialog" aria-hidden="true"></div>
@endsection

@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>

    <script>

        console.log('sddsadsadsa');
        $(document).ready(function() {

            $('input:radio[name="subscription"]').change(function(){
                alert("test");

                if($(this).val() == 'Yes'){
                    alert("test");
                }
            });
        });


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

            $('#add_channel_tier').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-create-tier');

                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('#edit_channel_tier').click(function(e) {
                e.preventDefault();

                var modal = $('#modal-edit-tier');

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
    </script>
@endsection
