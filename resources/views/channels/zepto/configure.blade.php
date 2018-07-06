@extends('layouts.app')

@section('htmlheader_title')
    Configure {{ $channel->name }}
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>
            <a href="/">Home</a>
        </li>
        <li>
            <a href="{{ route('project.dashboard', [$project]) }}">{{ $project->name }}</a>
        </li>
        <li>
            <a href="{{ route('channel.configure', [$project, $channel]) }}">{{ $channel->name }}</a>
        </li>
        <li class="active">
            <strong>Configure {{ $channel->type }}</strong>
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
                        <i class="icon fa fa-check"></i> {!! $message['text'] !!}
                    </div>
                @endif

                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Configure Zepto Channel</h5>
                    </div>

                    <div class="ibox-content">
                        <form action="{{ route('channel.configure', [$project, $channel]) }}" method="post" class="form-horizontal">
                            {!! csrf_field() !!}
                            <div class="form-group {{ $errors->has('complexity') ? 'has-error' : '' }}">
                                <label class="col-sm-1 control-label">
                                    Complexity<br />
                                    <small class="text-navy">required</small>
                                </label>
                                <div class="col-sm-11">
                                    <input type="text" class="form-control" name="complexity" value="{{ old('complexity') }}">
                                    <span class="help-block m-b-none">{{ $errors->first('complexity') }}</span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-1">
                                    <button class="btn btn-primary" type="submit">Save Channel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection