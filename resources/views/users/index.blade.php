@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
	 	@if (!empty($user_types))
			<div class="col-md-10 col-md-offset-1">
				<div class="container-fluid">
					<div class="row text-center">
						@component('users.user-type-selector', ['user_types' => $user_types])
						@endcomponent
					</div>
				</div>
			</div>
	 	@endif
		<div class="text-center">
			{{$users->links()}}
		</div>
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row">
					@foreach ($users as $user)
						@component('users.mini-user-holder', ['user' => $user])
						@endcomponent
					@endforeach
				</div>
			</div>
		</div>
		<div class="text-center">
			{{$users->links()}}
		</div>
	</div>
</div>
@endsection