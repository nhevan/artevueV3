@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

          <h3>{{ $event->headline }}</h3><hr>
          <p>Location: {{ $event->location }}</p>
          <p>Url: <a href="{{ $event->url }}">{{ $event->url }}</a></p>
          <p>Start Date: {{ $event->start_date }}</p>
          <p>End Date: {{ $event->end_date }}</p>
          <p>Publish Date: {{ $event->publish_date }}</p>
          <p><img src="{{ URL::asset('images/events/al-marsa.jpg') }}" style="width: 70%;"/></p>
          <p>{{ $event->description }}</p>
        </div>
    </div>
</div>
@endsection
