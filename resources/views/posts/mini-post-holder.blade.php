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
</div>