@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Add a new news</h3>
            <hr>
            <form method="POST" action="/news" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="form-group">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" value="{{ old('headline') }}" >
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
              </div>
              <div class="form-group">
                <label>Image</label>
                <input type="file" name="image_url" class="" value="{{ old('image_url') }}" >
              </div>
              <div class="form-group">
                <label>Url</label>
                <input type="text" name="url" class="form-control" value="{{ old('url') }}" >
              </div>
              <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="publish_date" class="datepicker form-control" value="{{ old('publish_date') }}" >
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
