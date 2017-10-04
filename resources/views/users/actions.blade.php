<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div>
				<a href="/user-posts/{{$user->id}}" class="btn btn-primary">View Posts</a>
				{{ Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'onsubmit' => 'return confirm("are you sure you want to delete this user ?")', 'style' => 'display:inline-block']) }}
				    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>