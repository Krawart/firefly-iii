@extends('layouts.guest')
@section('content')
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <h1 class="text-danger">Firefly<br/>
            <small>Error</small>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        {{$message or 'General unknown errror'}}
    </div>
</div>
@stop
