@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Resources</div>

                <div class="panel-body text-center">
                    <a href="{{ url('/events') }}" class="btn btn-primary">Events</a>
                    <a href="{{ url('/news') }}" class="btn btn-primary">News</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
