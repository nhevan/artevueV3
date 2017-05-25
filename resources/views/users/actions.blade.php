<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div>
				{{ Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'onsubmit' => 'return confirm("are you sure you want to delete this user ?")']) }}
				    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>