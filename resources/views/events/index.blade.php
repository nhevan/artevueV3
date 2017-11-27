@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
        <a href="/events/show-create-form" class="btn btn-primary pull-right">Add Event</a>
            <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Headline</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>City</th>
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
                    <td>{{ $event->city or 'not mentioned' }}</td>
                    <td>{{ $event->publish_date }}</td>
                    <td><a href="/events/{{ $event->id }}" class="btn btn-primary pull-right">View</a></td>
                    <td><a href="/events/edit/{{ $event->id }}" class="btn btn-primary pull-right">Edit</a></td>
                    <td><a href="/events/delete/{{ $event->id }}" class="btn btn-primary pull-right" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a></td>
                  </tr>
                  @endforeach
                </tbody>
          </table>
        </div>
        <div class="text-center">
          {{$events->links()}}
        </div>
    </div>
</div>
@endsection
