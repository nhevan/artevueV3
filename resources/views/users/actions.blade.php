<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div>
				<a href="/user-posts/{{$user->id}}" class="btn btn-primary">View Posts</a>
				<a href="/change-password-form/{{$user->id}}" id="change-password" class="btn btn-primary">Change password</a>
				<a href="/send-reset-password-email/{{$user->id}}" class="btn btn-primary">Email new password</a>
				{{ Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'onsubmit' => 'return confirm("are you sure you want to delete this user ?")', 'style' => 'display:inline-block']) }}
				    {{ Form::submit('Delete User', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>