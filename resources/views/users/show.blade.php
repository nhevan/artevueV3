@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row" style="display: flex; flex-direction: row;">
					@component('users.mini-user-holder', ['user' => $user])
					@endcomponent
					@component('users.metainfo', ['user' => $user])
					@endcomponent
				</div>
				<div class="row text-right">
					@component('users.actions', ['user' => $user])
					@endcomponent
				</div>
			</div>
		</div>
	</div>
</div>
@endsection