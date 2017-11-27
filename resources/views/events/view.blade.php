@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <h3>{{ $event->headline }}</h3><hr>
          <p>City: {{ $event->city or 'not mentioned' }}</p>
          <p>Location: {{ $event->location }}</p>
          <p>Url: <a href="{{ $event->url }}">{{ $event->url }}</a></p>
          <p>Start Date: {{ $event->start_date }}</p>
          <p>End Date: {{ $event->end_date }}</p>
          <p>Publish Date: {{ $event->publish_date }}</p>
          <p><img src="http://dy01r176shqrv.cloudfront.net/{{$event->image}}" style="width: 70%;"/></p>
          <p>{{ $event->description }}</p>
          
          <hr>
          @include('backbutton')
        </div>
    </div>
</div>
@endsection
