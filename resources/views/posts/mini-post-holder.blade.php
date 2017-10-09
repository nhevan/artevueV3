<div class="col-md-4 text-center user-holder-block">
	<a href="/posts/{{$post->id}}" style="color: black;">
		<div class="user-holder-wrapper">
			<h3>
			{{-- {{ str_limit($user->name, 22)}} --}}
			</h3>
			<b> {{ $post->owner->name }} </b>
			<br>
			{{-- <small>{{ $user->userType->title }}</small> --}}
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
	{{-- <a style='' href="#" class="btn btn-xs btn-danger">
		<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
	</a> --}}
</div>