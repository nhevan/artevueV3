@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Headline</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Publish Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($events as $event)
                  <tr>
                    <td>{{ $event->headline }}</td>
                    <td>{{ $event->description }}</td>
                    <td>{{ $event->location }}</td>
                    <td>{{ $event->publish_date }}</td>
                    <td><a href="/events/view/{{ $event->id}}" class="btn btn-primary pull-right">View</a></td>
                    <td><a href="/events/delete/{{ $event->id}}" class="btn btn-primary pull-right">Delete</a></td>
                  </tr>
                  @endforeach
                </tbody>
          </table>
        </div>
    </div>
</div>
@endsection
