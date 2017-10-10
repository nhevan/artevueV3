<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="text-right">
				{{ Form::open(['method' => 'GET', 'route' => ['posts.edit-form', $post->id], 'style' => 'float:right; padding-left:10px;']) }}
				    {{ Form::submit('Edit Post', ['class' => 'btn btn-primary', 'id' => 'edit-post']) }}
				{{ Form::close() }}
			</div>
		</div>
	</div>
</div>