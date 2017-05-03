@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Edit event</h3>
            <hr>
            <form method="POST" action="/events/edit/{{ $event->id }}" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="form-group">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" value="{{ $event->headline }}" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required>{{ $event->description }}</textarea>
              </div>
              <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="{{ $event->location }}" required>
              </div>
              <div class="form-group">
                <label>Image</label>
                <p><img src="http://dy01r176shqrv.cloudfront.net/{{$event->image}}" style="width: 30%;margin-bottom: 20px;"/></p>
                <input type="file" name="image_url" class="" data-buttonText="Your label here.">
              </div>
              <div class="form-group">
                <label>Url</label>
                <input type="text" name="url" class="form-control" value="{{ $event->url }}" required>
              </div>
              <div class="form-group">
                <label>Start Date</label>
                <input type="text" name="start_date" class="datepicker form-control" value="{{ Carbon\Carbon::parse($event->start_date)->format('Y-m-d') }}" required>
              </div>
              <div class="form-group">
                <label>End Date</label>
                <input type="text" name="end_date" class="datepicker form-control" value="{{ Carbon\Carbon::parse($event->end_date)->format('Y-m-d') }}" required>
              </div>
              <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="publish_date" class="datepicker form-control" value="{{ $event->publish_date }}" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Update</button>
              </div>
            
            </form>
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
