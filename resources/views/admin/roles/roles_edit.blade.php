@extends('layouts.app')

@section('htmlheader_title')
    Roles User
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    <div class="ibox">
        <div class="ibox-title">
            <h5>Edit Roles</h5>
        </div>
        <hr>
        <a class="btn btn-sm btn-primary" href="{{route('roles.index')}}">Back</a><br><br>
        <form method="post" action="{{ route('roles.update', ['id' => $role->id]) }}" data-parsley-validate class="form-horizontal form-label-left">

            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} row">
                <label for="name" class="col-sm-2 col-form-label">Name</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" value="{{$role->name}}" id="name" name="name" class="form-control col-md-7 col-xs-12"> @if ($errors->has('name'))
                    <span class="help-block">{{ $errors->first('name') }}</span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('display_name') ? ' has-error' : '' }} row">
                <label for="display_name" class="col-sm-2 col-form-label">Display Name</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" value="{{$role->display_name}}" id="display_name" name="display_name" class="form-control col-md-7 col-xs-12"> @if ($errors->has('display_name'))
                    <span class="help-block">{{ $errors->first('display_name') }}</span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }} row">
                <label for="description" class="col-sm-2 col-form-label">Description</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <input type="text" value="{{$role->description}}" id="description" name="description" class="form-control col-md-7 col-xs-12"> @if ($errors->has('description'))
                    <span class="help-block">{{ $errors->first('description') }}</span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('permission_id') ? ' has-error' : '' }} row">
                <label for="permission_id" class="col-sm-2 col-form-label">Permission</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    @if(count($permissions))
                        @foreach($permissions as $row)
                                <label>
                                    {{ Form::checkbox('permission_id[]',
                                       $row->id,
                                       in_array($row->id,$role_permissions) ? true : false, array('class' => 'name'))
                                    }}

                                {{ $row->display_name }}</label><br>
                        @endforeach
                    @endif
                    @if ($errors->has('permission_id'))
                    <span class="help-block">{{ $errors->first('permission_id') }}</span>
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
                    <button type="submit" class="btn btn-primary">Save Role Changes</button>
                </div>
            </div>
        </form>
    </div>
@endsection