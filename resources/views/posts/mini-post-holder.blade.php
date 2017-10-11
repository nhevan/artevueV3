<div class="col-md-4 text-center user-holder-block">
	<a href="{{ route('posts.show', ['post' => $post->id]) }}" id="post-detail-{{$post->id}}" style="color: black;">
		<div class="mini-holder-wrapper">
			<h3>
			{{-- {{ str_limit($user->name, 22)}} --}}
			</h3>
			<b> {{ $post->owner->name }} </b>
			<br>
			<small>posted {{ $post->created_at->diffInDays() }} days ago ( {{ $post->like_count }} likes)</small>
			<br>
			{{-- <small>joined {{ $user->created_at->diffForHumans() }}</small> --}}
			<div class="profile-picture-holder">
				<img src="{{$cloudfront_url.$post->image}}" alt="post-image">
			</div>
		</div>
	</a>
	{{ Form::open(['method' => 'DELETE', 'route' => ['posts.destroy', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to delete this post ?")', 'style' => 'float:right; padding-left:10px;']) }}
	    {{ Form::submit('Delete', ['id' => 'delete-post-'.$post->id, 'class' => 'btn btn-xs btn-danger', 'style' => 'position: absolute; bottom: 35px; right: 20px; z-index: 999;']) }}
	{{ Form::close() }}

	@if ($post->is_undiscoverable)
		{{ Form::open(['method' => 'PATCH', 'route' => ['posts.swapDiscoverability', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to show this post in app explore screen?")']) }}
		    {{ Form::submit('Show', ['class' => 'btn btn-xs btn-success', 'style' => 'position: absolute; bottom: 35px; right: 70px; z-index: 999;']) }}
		{{ Form::close() }}
	@else
		{{ Form::open(['method' => 'PATCH', 'route' => ['posts.swapDiscoverability', $post->id], 'onsubmit' => 'return confirm("Are you sure you want to hide this post from app explore screen?")']) }}
		    {{ Form::submit('Hide', ['class' => 'btn btn-xs btn-warning', 'style' => 'position: absolute; bottom: 35px; right: 70px; z-index: 999;']) }}
		{{ Form::close() }}
	@endif
</div>