@extends('layouts.app')

@section('htmlheader_title')
    Edit User
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    <div class="ibox" xmlns="http://www.w3.org/1999/html">
        <div class="ibox-title">
            <h5>Edit User</h5>
        </div>
            <hr>
        <a class="btn btn-sm btn-primary " href="{{route('user.index')}}">Back</a>
        <br><br>
            <form method="post" action="{{ route('user.update', [$user]) }}" data-parsley-validate class="form-horizontal form-label-left">
                {{csrf_field()}}
                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} row">
                    <label for="name" class="col-sm-2 col-form-label">Name</label>
                    <div class="col-sm-6 col-sm-6 col-xs-12">
                        <input type="text" value="{{$user->name}}" id="name" name="name" class="form-control col-md-7 col-xs-12">
                            @if ($errors->has('name'))
                                <span class="help-block">{{ $errors->first('name') }}</span>
                            @endif
                    </div>
                </div>

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} row">
                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-6 col-sm-6 col-xs-12">
                       <span class="form-control col-md-7 col-xs-12"> {{$user->email}} </span>
                    </div>
                </div>

                <div class="form-group{{ $errors->has('role_id') ? ' has-error' : '' }} row">
                    <label class="col-sm-2 col-form-label" for="category_id">Role
                        <span class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <select class="form-control" id="role_id" name="role_id">
                            @if(count($roles))
                                @if(isset( $user->roles) && count($user->roles) > 0)
                                    @foreach($roles as $row)
                                        <option value="{{$row->id}}" {{$row->id == $user->roles[0]->id ? 'selected="selected"' : ''}}>{{$row->name}}</option>
                                    @endforeach
                                @else
                                    @foreach($roles as $row)
                                        <option value="{{$row->id}}" >{{$row->name}}</option>
                                    @endforeach
                                @endif
                            @endif
                        </select>
                        @if ($errors->has('role_id'))
                            <span class="help-block">{{ $errors->first('role_id') }}</span>
                        @endif
                    </div>
                </div>

                <div class="ln_solid"></div>

                <div class="form-group">
                    <label class="col-sm-2 col-form-label" for="category_id">
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <input type="hidden" name="_token" value="{{ Session::token() }}">
                        <input name="_method" type="hidden" value="PUT">
                        <button type="submit" class="btn btn-primary" style="">Save User Changes</button>
                    </div>
                </div>
            </form>
        </div>
        <hr>
    </div>
@endsection