@extends('layouts.app')

@section('htmlheader_title')
    Users List
@endsection

@section('breadcrumb')

@endsection

@section('main-content')
    <div class="ibox">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Users</h1>
        </div>
        <hr>
        <a class="btn btn-sm btn-primary" href="{{route('user.index')}}">Back</a>
        <br><br>
        <h2>{{$title}}</h2>
        <div class="clearfix"></div>
        <p>Are you sure you want to delete <strong>{{$user->name}}</strong></p>

        <form method="POST" action="{{ route('user.destroy', [$user]) }}">
           {{csrf_field()}}
            <input name="_method" type="hidden" value="DELETE">
            <button type="submit" class="btn btn-danger">Yes I'm sure. Delete</button>
        </form>
    </div>
@endsection