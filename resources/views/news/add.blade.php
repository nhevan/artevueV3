@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Add a new news</h3>
            <hr>
            <form method="POST" action="/news/store" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="form-group">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Image</label>
                <input type="file" name="image_url" class="" required>
              </div>
              <div class="form-group">
                <label>Url</label>
                <input type="text" name="url" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="publish_date" class="datepicker form-control" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Submit</button>
              </div>
            
            </form>
        </div>
    </div>
</div>

@endsection
