@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

          <h3>{{ $news->headline }}</h3><hr>
          <p>Url: <a href="{{ $news->url }}">{{ $news->url }}</a></p>
          <p>Publish Date: {{ $news->publish_date }}</p>
          <p><img src="http://dy01r176shqrv.cloudfront.net/{{$news->image}}" style="width: 70%;"/></p>
          <p>{{ $news->description }}</p>
        </div>
    </div>
</div>
@endsection
