@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <h3 class="text-center">Edit news</h3>
            <hr>
            <form method="POST" action="/news/edit/{{ $news->id }}" enctype="multipart/form-data">
              {{ csrf_field() }}
              <div class="form-group">
                <label>Headline</label>
                <input type="text" name="headline" class="form-control" value="{{ $news->headline }}" >
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control">{{ $news->description }}</textarea>
              </div>
              <div class="form-group">
                <label>Image</label>
                <p><img src="http://dy01r176shqrv.cloudfront.net/{{$news->image}}" style="width: 30%;margin-bottom: 20px;"/></p>
                <p class="get_choose_option" style="cursor: pointer;color: red;"><b>Edit Image</b></p>
                <input type="file" name="image_url" class="choose_image">
              </div>
              <div class="form-group">
                <label>Url</label>
                <input type="text" name="url" class="form-control" value="{{ $news->url }}" >
              </div>
              <div class="form-group">
                <label>Publish Date</label>
                <input type="text" name="publish_date" class="datepicker form-control" value="{{ $news->publish_date }}" >
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
@stop
@section('script')
<script>
    $( document ).ready(function() {
      $('.choose_image').hide();
    });
    $('.get_choose_option').click(function(){
      $('.choose_image').show();
    });
</script>
@endsection
