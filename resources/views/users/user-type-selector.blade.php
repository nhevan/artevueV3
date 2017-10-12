<div style="margin-bottom: 20px">
	@foreach ($user_types as $user_type_id => $user_type_title)
		<a href="{{ route('users.index' , [ 'type' => $user_type_title ]) }}" class="btn btn-sm btn-primary">{{ $user_type_title }}</a>
	@endforeach
</div>
