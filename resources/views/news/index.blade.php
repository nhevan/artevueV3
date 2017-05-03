@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
        <a href="/news/show-create-form" class="btn btn-primary pull-right">Add News</a>
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Headline</th>
                    <th>Description</th>
                    <th>Publish Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($newses as $news)
                  <tr>
                    <td>{{ $news->headline }}</td>
                    <td>{{ $news->description }}</td>
                    <td>{{ $news->publish_date }}</td>
                    <td><a href="/news/{{ $news->id}}" class="btn btn-primary pull-right">View</a></td>
                    <td><a href="/news/delete/{{ $news->id}}" class="btn btn-primary pull-right" onclick="return confirm('Are you sure you want to delete this news?');">Delete</a></td>
                  </tr>
                  @endforeach
                </tbody>
          </table>
        </div>
    </div>
</div>
@endsection
