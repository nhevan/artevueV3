@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
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