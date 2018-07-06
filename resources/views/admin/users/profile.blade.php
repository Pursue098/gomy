@extends('layouts.app')

@section('htmlheader_title')
    User profile
@endsection

@section('main-content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            @if(Session::has('successMessage'))
                <?php $message = Session::get('successMessage'); ?>
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

            <div class="panel panel-default">
                <div class="panel-heading">
                    User's Profile Preview
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" id="profileUpdateForm" role="form" method="POST" action="{{ route('user.updateProfile', [$user]) }}">
                        <input name="_method" type="hidden" value="POST">
                        {{ csrf_field() }}

                        @if (isset($invite))
                            <input type="hidden" name="invite" value="{{ $invite->code }}" />
                        @endif

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Full Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{$user->name }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <span class="form-control">{{ $user->email }}</span>
                               {{--<input id="email" type="email" class="form-control" name="email" value="{{ $user->email }}" required>--}}

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('phone_number') ? ' has-error' : '' }}">
                            <label for="phone_number" class="col-md-4 control-label">Phone Number</label>

                            <div class="col-md-6">
                                <input id="phone_number" type="tel" class="form-control" name="phone_number" value="{{ $user->phone_number }}" required autofocus>

                                @if ($errors->has('phone_number'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('phone_number') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('company') ? ' has-error' : '' }}">
                            <label for="company" class="col-md-4 control-label">Company</label>

                            <div class="col-md-6">
                                <input id="company" type="text" class="form-control" name="company" value="{{  $user->company }}" required autofocus>

                                @if ($errors->has('company'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('company') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                {{--<button type="submit" id="updateProfile" class="btn btn-primary"> Update </button>--}}
                                <a class="btn btn-primary" id="updateProfile" >Update</a>
                                <a class="btn btn-default btn-close" href="{{ route('projects.index') }}">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

    <script>

        $('#updateProfile').on('click', function(e){
            e.preventDefault();
            swal({
                    title: 'Are you sure?',
                    height: '200px',
                    text: "You want to update!",
                    showCancelButton: true,
                    confirmButtonColor: '#1ab394',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!',
                    closeOnConfirm: false,
                    customClass: 'swal-height',
                }).then((result) => {

                    console.log('result: ', result);
                    $('form#profileUpdateForm').submit();
                });
            return false;
        })
    </script>

@endsection

