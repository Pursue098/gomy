@extends('layouts.app')

@section('htmlheader_title')
    Confirmation
@endsection

@section('main-content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Confirmation</div>
                <div class="panel-body">

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <h3>User successfully verified !</h3>

                        <a class="btn btn-link" href="{{ route('login') }}">
                           Login
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
