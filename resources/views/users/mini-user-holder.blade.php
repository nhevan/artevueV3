<a href="/users/{{$user->id}}" style="color: black;">
	<div class="col-md-4 text-center">
		<div class="user-holder-block">
			<h3>
			{{ str_limit($user->name, 22)}}
			</h3>
			<small>{{ $user->userType->title }}</small>
			<br>
			<small>joined {{ $user->created_at->diffForHumans() }}</small>
			<div class="profile-picture-holder">
				<img src="{{$cloudfront_url.$user->profile_picture}}" alt="profile-holder">
			</div>
		</div>
	</div>
</a>