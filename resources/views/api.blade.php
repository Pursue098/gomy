@extends('layouts.app')

@section('htmlheader_title')
    Api
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>
            <a href="/">Home</a>
        </li>
        <li class="active">
            <strong>Api</strong>
        </li>
    </ol>
@endsection

@section('main-content')
    @if(Session::has('message'))
        <?php $message = Session::get('message'); ?>
        <div class="alert alert-{{ $message['type'] }} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fa fa-check"></i> {{ $message['text'] }}
        </div>
    @endif

    <div id="app">
        <!--
        <passport-clients></passport-clients>
        <passport-authorized-clients></passport-authorized-clients>
        -->
        <passport-personal-access-tokens></passport-personal-access-tokens>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div>
                <span>
                    Authentication and Headers
                </span>
            </div>
        </div>

        <div class="panel-body">
            All API call require a valid personal access token and correct headers configuration:
<pre>
Accept: application/json
Content-type: application/json
Authorization: Bearer your_personal_access_token
</pre>

        Here an example of request with PHP:
<pre><?php highlight_string('$token = \'your_personal_access_token\';

$body = json_encode([\'example\' => \'body\']);

$headers = [
    \'Accept: application/json\',
    \'Content-type: application/json\',
    \'Authorization: Bearer \' . $token
];

$opts = [
    \'http\' => [
        \'method\'        => \'POST\',
        \'ignore_errors\' => true,
        \'header\'        => implode("\r\n", $headers),
        \'content\'       => $body,
    ]
];

$context = stream_context_create($opts);

$json = file_get_contents(\'' . url('/') . '/api/v1/...\', false, $context);

$response = json_decode($json);

if (json_last_error() == JSON_ERROR_NONE) {
    print_r($response);
}') ?>
</pre>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.0.3/vue.js"></script>
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.0.3/vue.common.js"></script>-->
    <script src="{{ asset('/js/app.js') }}"></script>
@endsection