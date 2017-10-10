<div class="col-md-4 text-center user-holder-block">
	<a href="/users/{{$user->id}}" id="user-detail-{{$user->id}}" style="color: black;">
		<div class="mini-holder-wrapper">
			<h3>
			{{ str_limit($user->name, 22)}}
			</h3>
			{{ $user->username }}
			<br>
			<small>{{ $user->userType->title }}</small>
			<br>
			<small>joined {{ $user->created_at->diffForHumans() }}</small>
			<div class="profile-picture-holder">
				<img src="{{$cloudfront_url.$user->profile_picture}}" alt="profile-holder">
			</div>
		</div>
	</a>
</div>