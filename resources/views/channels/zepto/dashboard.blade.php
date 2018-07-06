@extends('layouts.app')

@section('htmlheader_title')
    {{$channel->type}}
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>
            <a href="/">Home</a>
        </li>
        <li>
            <strong>{{ $channel->name }} {{ $channable->id }}</strong>
        </li>
    </ol>
@endsection

@section('main-content')


    @if(Session::has('success-message'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('success-message') }}
        </div>
    @endif
    @if(Session::has('errormessage'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('errormessage') }}
        </div>
    @endif
    @if(Session::has('errormessage1'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ Session::get('errormessage1') }}
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
    @if(Session::has('transcationMessage'))
        <?php $message = Session::get('transcationMessage'); ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ $message }}
        </div>
        @elseif(Session::has('transErrorMessage'))
        <?php $message = Session::get('transErrorMessage'); ?>
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ $message }}
        </div>
    @endif

    <div class="row">

        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="ibox float-e-margins">
                <label>
                    <h3 style="color: #1ab394">Subscribe As</h3>
                </label>
                <div class="ibox-content">
                    Complexity: <b>{{ $channable->complexity }} </b>
                    @if(isset($enterprise))
                        @if($enterprise->status == 0)
                            @if(isset($general))
                                @if($general->status == 0)
                                    @if($enterprise->transaction_id == 'Unapproved' || $enterprise->transaction_id == 'Approved')
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>Enterprise</label>
                                            <input type="radio" value="enterprise" name="subsType" checked>
                                        </div>
                                    @else
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>General</label>
                                            <input type="radio" value="general" name="subsType" checked>
                                        </div>
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>Enterprise</label>
                                            <input type="radio" value="enterprise" name="subsType" >
                                        </div>
                                    @endif
                                @elseif($general->status == 1)
                                    <div style="display: inline; margin-left: 30px;">
                                        <label>General</label>
                                        <input type="radio" value="general" name="subsType" checked>
                                    </div>
                                @endif
                            @else
                                <div style="display: inline; margin-left: 30px;">
                                    <label>General</label>
                                    <input type="radio" value="general" name="subsType" checked>
                                </div>
                                <div style="display: inline; margin-left: 30px;">
                                    <label>Enterprise</label>
                                    <input type="radio" value="enterprise" name="subsType" >
                                </div>
                            @endif
                        @else
                            <div style="display: inline; margin-left: 30px;">
                                <label>Enterprise</label>
                                <input type="radio" value="enterprise" name="subsType" checked>
                            </div>
                        @endif
                    @else
                        @if(isset($general))
                            @if($general->status == 0)
                                @if(isset($enterprise))
                                    @if($enterprise->status == 0)
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>General</label>
                                            <input type="radio" value="general" name="subsType" checked>
                                        </div>
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>Enterprise</label>
                                            <input type="radio" value="enterprise" name="subsType" >
                                        </div>
                                    @else
                                        <div style="display: inline; margin-left: 30px;">
                                            <label>Enterprise</label>
                                            <input type="radio" value="enterprise" name="subsType" checked>
                                        </div>
                                    @endif
                                @else
                                    <div style="display: inline; margin-left: 30px;">
                                        <label>General</label>
                                        <input type="radio" value="general" name="subsType" checked>
                                    </div>
                                    <div style="display: inline; margin-left: 30px;">
                                        <label>Enterprise</label>
                                        <input type="radio" value="enterprise" name="subsType" >
                                    </div>
                                @endif
                            @elseif($general->status == 1)
                                <div style="display: inline; margin-left: 30px;">
                                    <label>General</label>
                                    <input type="radio" value="general" name="subsType" checked>
                                </div>
                            @endif
                        @else
                            <div style="display: inline; margin-left: 30px;">
                                <label>General</label>
                                <input type="radio" value="general" name="subsType" checked>
                            </div>
                            <div style="display: inline; margin-left: 30px;">
                                <label>Enterprise</label>
                                <input type="radio" value="enterprise" name="subsType" >
                            </div>
                        @endif
                    @endif

                    <span style="float: right; padding-right: 20px;">
                        <div style="float: right; margin-left: 20px; margin-top: 0px">
                            @if(isset($enterprise) )
                                @if($enterprise->status == 0)
                                    @if(isset($general))
                                        @if($general->status == 0)
                                            @if($enterprise->transaction_id == 'Unapproved' || $enterprise->transaction_id == 'Approved')
                                                @if($enterprise->transaction_id == 'Unapproved')
                                                    <span id="enterprise_unapprovel_status" style="color: #1ab394; margin-left: 10px;"> Request is Pending </span>
                                                @elseif($enterprise->transaction_id == 'Approved')
                                                    <span id="enterprise_approval_status" style="color: #1ab394; margin-left: 10px;"> Request is Approved </span>
                                                @elseif($enterprise->transaction_id == 'Reject')
                                                    <a id="resumeEnterprise" href="{{ route('payment.resumeEnterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none"><i class="fa fa-money"></i> Enterprise </a>
                                                    <span id="Enterprise_cancel" style="color: #1ab394; margin-left: 10px; display: none"> Request is Rejected </span>
                                                @endif
                                            @else
                                                @if (isset($plan) && $user->subscription($plan->nick_name)->onGracePeriod())
                                                    <a id="resumeSubscription" href="{{ route('payment.resumeSubscription', [$project, $channel]) }}" class="btn btn-white btn-sm" style=""><i class="fa fa-money"></i> Resume General Subscription </a>
                                                    <span id="General_cancel" style="color: #1ab394; margin-left: 10px"> Cancelled </span>
                                                @else
                                                    <a id="general_mode" href="{{ route('payment.create', [$project, $channel]) }}" class="btn btn-white btn-sm" style="" ><i class="fa fa-money"></i> Subscribe </a>
                                                @endif
                                                @if($enterprise->transaction_id == 'Reject')
                                                    <a id="resumeEnterprise" href="{{ route('payment.resumeEnterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none"><i class="fa fa-money"></i> Enterprise </a>
                                                    <span id="Enterprise_cancel" style="color: #1ab394; margin-left: 10px; display: none"> Request is Rejected </span>
                                                @endif
                                            @endif
                                        @elseif($general->status == 1)
                                            <form class="form-horizontal" id="unsubscribe_form" role="form" method="POST" action="{{ route('payment.unsubscribe', [$project, $channel]) }}">
                                                {{ csrf_field() }}
                                                    <a class="btn btn-default " id="unsubscribe_button" >Un-Subscribe General</a>
                                                <span id="General_subscribed" style="color: #1ab394; margin-left: 10px"> Subscribed </span>
                                            </form>
                                        @endif
                                    @else
                                        <a id="general_mode" href="{{ route('payment.create', [$project, $channel]) }}" class="btn btn-white btn-sm" style="" ><i class="fa fa-money"></i> Subscribe </a>
                                        <a id="resumeEnterprise" href="{{ route('payment.resumeEnterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none"><i class="fa fa-money"></i> Enterprise </a>
                                        <span id="Enterprise_cancel" style="color: #1ab394; margin-left: 10px; display: none"> Rejected </span>
                                    @endif
                                @else
                                    @if(isset($general))
                                        @if($general->status == 0)
                                            @if (isset($plan) && $user->subscription($plan->nick_name)->onGracePeriod())
                                                <a id="resumeSubscription" href="{{ route('payment.resumeSubscription', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none"><i class="fa fa-money"></i> Resume General Subscription </a>
                                                <span id="General_cancel" style="color: #1ab394; margin-left: 10px; display: none"> Cancelled </span>
                                            @else
                                                <a id="general_mode" href="{{ route('payment.create', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none" ><i class="fa fa-money"></i> Subscribe </a>
                                            @endif

                                            @if($enterprise->transaction_id == 'Unapproved')
                                                <span id="enterprise_unapprovel_status" style="color: #1ab394; margin-left: 10px;"> Request is Pending </span>
                                            @elseif($enterprise->transaction_id == 'Approved')
                                                <span id="enterprise_approval_status" style="color: #1ab394; margin-left: 10px;"> Request is Approved </span>
                                            @elseif($enterprise->transaction_id == 'Reject')
                                                <a id="resumeEnterprise" href="{{ route('payment.resumeEnterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style=""><i class="fa fa-money"></i> Enterprise </a>
                                                <span id="Enterprise_cancel" style="color: #1ab394; margin-left: 10px;"> Request is Rejected </span>
                                            @endif
                                        @elseif($general->status == 1)
                                            <form class="form-horizontal" id="unsubscribe_enterprise_form" role="form" method="POST" action="{{ route('payment.unsubscribeEnterprise', [$project, $channel]) }}">
                                                {{ csrf_field() }}
                                                <a class="btn btn-default" id="unsubscribe_enterprise_button" >Un-Subscribe Enterprise</a>
                                                <span id="Enterprise_subscribed" style="color: #1ab394; margin-left: 10px"> Subscribed </span>
                                            </form>
                                        @endif
                                    @else
                                        @if($enterprise->transaction_id == 'Unapproved')
                                            <span id="enterprise_unapprovel_status" style="color: #1ab394; margin-left: 10px;"> Request is Pending </span>
                                        @elseif($enterprise->transaction_id == 'Approved')
                                            <span id="enterprise_approval_status" style="color: #1ab394; margin-left: 10px;"> Request is Approved </span>
                                        @elseif($enterprise->transaction_id == 'Reject')
                                            <a id="resumeEnterprise" href="{{ route('payment.resumeEnterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style=""><i class="fa fa-money"></i> Enterprise </a>
                                            <span id="Enterprise_cancel" style="color: #1ab394; margin-left: 10px;"> Request is Rejected </span>
                                        @endif
                                    @endif
                                @endif
                            @elseif(isset($general))
                                @if($general->status == 0)
                                    @if (isset($plan) && $user->subscription($plan->nick_name)->onGracePeriod())
                                        <a id="resumeSubscription" href="{{ route('payment.resumeSubscription', [$project, $channel]) }}" class="btn btn-white btn-sm" style=""><i class="fa fa-money"></i> Resume General Subscription </a>
                                        <span id="General_cancel" style="color: #1ab394; margin-left: 10px"> Cancelled </span>
                                    @else
                                        <a id="general_mode" href="{{ route('payment.create', [$project, $channel]) }}" class="btn btn-white btn-sm" style="" ><i class="fa fa-money"></i> Subscribe </a>
                                    @endif

                                    <a id="enterprise_mode" href="{{ route('payment.enterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none" ><i class="fa fa-money"></i> Enterprise </a>
                                @elseif($general->status == 1)
                                    <form class="form-horizontal" id="unsubscribe_form" role="form" method="POST" action="{{ route('payment.unsubscribe', [$project, $channel]) }}">
                                        {{ csrf_field() }}
                                        <a class="btn btn-default " id="unsubscribe_button" >Un-Subscribe General</a>
                                        <span id="General_subscribed" style="color: #1ab394; margin-left: 10px"> Subscribed </span>
                                    </form>

                                    <a id="enterprise_mode" href="{{ route('payment.enterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none" ><i class="fa fa-money"></i> Enterprise </a>
                                @endif
                            @elseif($tiers && count($tiers) > 0)
                                <a id="general_mode" href="{{ route('payment.create', [$project, $channel]) }}" class="btn btn-white btn-sm" style="" ><i class="fa fa-money"></i> Subscribe </a>
                                <a id="enterprise_mode" href="{{ route('payment.enterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none" ><i class="fa fa-money"></i> Enterprise </a>
                            @else
                                <span id="no_plan" style="color: #1ab394; margin-left: 10px; margin-top: 0"> No plan exist </span>
                                <a id="enterprise_mode" href="{{ route('payment.enterprise', [$project, $channel]) }}" class="btn btn-white btn-sm" style="display: none" ><i class="fa fa-money"></i> Enterprise </a>
                            @endif
                        </div>
                    </span>
                </div>
            </div>
        </div>
    </div>
        @if((!is_null($general) && is_null($enterprise)) || (!is_null($general) && $enterprise->status == 0))
            <div class="row" id="general_details">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <fieldset>
                            <h3><b>General Subscription details:</b></h3>
                            <table class="table table-hover">
                                <tbody>
                                <tr>
                                    <th>Subscription Status</th>
                                    <th>Subscription Interval</th>
                                    <th>Trial Status</th>
                                    <th>Trial Days</th>
                                    <th>Charged Amount</th>
                                </tr>
                                <tr>
                                    <td>
                                        @if(isset($general) && $general->status == 0)
                                            Un Subscribed
                                        @elseif(isset($general) && $general->status == 1)
                                            Subscribed
                                        @endif
                                    </td>
                                    <td>{{ucfirst($general->type)}}</td>
                                    <td>
                                        @if(isset($subscription->trial_ends_at))
                                            Trail is Expired
                                        @elseif($left_days == 0)
                                            No Trail Period
                                        @else
                                            Trail is Active
                                        @endif
                                    </td>
                                    <td>@if (isset($left_days)){{$left_days}} Days @endif</td>
                                    <td>€{{$plan->net_price}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="modal inmodal" id="payment_type" tabindex="-1" role="dialog" aria-hidden="true"></div>

@endsection


@section('scripts')
    <!-- FooTable -->
    <script src="{{ asset('/js/plugins/footable/footable.all.min.js') }}"></script>
    <script src="{{ asset('/js/plugins/dualListbox/jquery.bootstrap-duallistbox.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('input:radio[name="subsType"]').change(function(){
                if($(this).val() == 'general'){
                    console.log("general");
                    $("#Enterprise_cancel").hide();
                    $("#resumeEnterprise").hide();
                    $("#enterprise_mode").hide();
                    $("#unsubscribe_enterprise_form").hide();
                    $("#general_mode").show();
                    $("#General_cancel").show();
                    $("#resumeSubscription").show();
                    $("#unsubscribe_form").show();
                    $("#general_details").show();
                    $("#no_plan").show();

                }else if($(this).val() == 'enterprise'){
                    console.log("enterprise");
                    $("#Enterprise_cancel").show();
                    $("#resumeEnterprise").show();
                    $("#enterprise_mode").show();
                    $("#unsubscribe_enterprise_form").show();
                    $("#general_mode").hide();
                    $("#General_cancel").hide();
                    $("#resumeSubscription").hide();
                    $("#unsubscribe_form").hide();
                    $("#general_details").hide();
                    $("#no_plan").hide();
                }
            });

            $('#general_mode').click(function(e) {
                e.preventDefault();

                var modal = $('#payment_type');
                modal.load($(this).attr('href'), function() {
                    modal.modal();
                });
            });

            $('#unsubscribe_button').on('click', function(e){
                e.preventDefault();
                swal({
                    title: 'Are you sure?',
                    height: '200px',
                    text: "You want to Un subscribe!",
                    showCancelButton: true,
                    confirmButtonColor: '#1ab394',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Do it!',
                    closeOnConfirm: false,
                }).then((result) => {

                    console.log('result: ', result);
                    $('form#unsubscribe_form').submit();
                });
                return false;
            })
            $('#unsubscribe_enterprise_button').on('click', function(e){
                e.preventDefault();
                swal({
                    title: 'Are you sure?',
                    height: '200px',
                    text: "You want to Un subscribe!",
                    showCancelButton: true,
                    confirmButtonColor: '#1ab394',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Do it!',
                    closeOnConfirm: false,
                }).then((result) => {

                    console.log('result: ', result);
                    $('form#unsubscribe_enterprise_form').submit();
                });
                return false;
            })
        });
    </script>
@endsection
