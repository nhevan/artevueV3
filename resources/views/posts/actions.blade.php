<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div>
				{{ Form::open(['method' => 'DELETE', 'route' => ['posts.destroy', $post->id], 'onsubmit' => 'return confirm("are you sure you want to delete this post ?")']) }}
				    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>