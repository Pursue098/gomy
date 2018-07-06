@extends('layouts.app')

<!-- Main Content -->
@section('main-content')
<div class="container">

    @if(Session::has('message'))
        <?php $message = Session::get('message'); ?>
        <div class="alert alert-{{ $message['type'] }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ $message['text'] }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Account not verified</div>
                <div class="panel-body">
                    <span class="help-block">
                        <strong>We sent you a verification email. Click on the provided link to verify your account.</strong>
                        <br />
                        Haven't received the activation email? Please check your spam folder or <a href="{{ route('auth.resend') }}">click here to send it again.</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
