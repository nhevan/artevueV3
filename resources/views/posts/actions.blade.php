<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="text-right">
				{{-- {{ Form::open(['method' => 'DELETE', 'route' => ['posts.destroy', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to delete this post ?")', 'style' => 'float:right; padding-left:10px;']) }}
				    {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
				{{ Form::close() }} --}}

				@if ($post->is_undiscoverable)
					{{ Form::open(['method' => 'PATCH', 'route' => ['posts.swapDiscoverability', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to show this post in app explore screen?")']) }}
				    {{ Form::submit('Show in explore', ['class' => 'btn btn-success']) }}
					{{ Form::close() }}
				@else
					{{ Form::open(['method' => 'PATCH', 'route' => ['posts.swapDiscoverability', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to hide this post from app explore screen?")']) }}
				    {{ Form::submit('Hide from explore', ['class' => 'btn btn-success']) }}
					{{ Form::close() }}
				@endif
			</div>
		</div>
	</div>
</div>