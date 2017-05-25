<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div>
				{{ Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id]]) }}
				    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }}
				{{-- <a href="#" class="btn btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> Delete</a> --}}
			</div>
		</div>
	</div>
</div>