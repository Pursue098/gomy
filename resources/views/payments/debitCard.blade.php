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
                <form class="w3-container w3-display-middle w3-card-4 " method="POST" id="payment-form"  action="http://127.0.0.1:8000/paypal">
                    {{ csrf_field() }}
                    <h2 class="w3-text-blue">Payment Form</h2>
                    <p>
                        <label class="w3-text-blue"><b>Enter Amount</b></label>
                        <input class="w3-input w3-border" name="amount" type="text">
                    </p>
                        <button class="w3-btn w3-blue">Pay with PayPal</button></p>
                </form>
            </div>
        </div>
    </div>
@endsection