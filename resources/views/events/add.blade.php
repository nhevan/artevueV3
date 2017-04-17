@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Add a new event</h3>
            <hr>
            <form method="POST" action="/events" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="form-group">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" value="{{ old('headline') }}" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" required>{{ old('description') }}</textarea>
              </div>
              <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" required>
              </div>
              <div class="form-group">
                <label>Image</label>
                <input type="file" name="image_url" class="" value="{{ old('image_url') }}" required>
              </div>
              <div class="form-group">
                <label>Url</label>
                <input type="text" name="url" class="form-control" value="{{ old('url') }}" required>
              </div>
              <div class="form-group">
                <label>Start Date</label>
                <input type="text" name="start_date" class="datepicker form-control" value="{{ old('start_date') }}" required>
              </div>
              <div class="form-group">
                <label>End Date</label>
                <input type="text" name="end_date" class="datepicker form-control" value="{{ old('end_date') }}" required>
              </div>
              <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="publish_date" class="datepicker form-control" value="{{ old('publish_date') }}" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Submit</button>
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
