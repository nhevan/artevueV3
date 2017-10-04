@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row" style="display: flex; flex-direction: row;">
					@component('posts.mini-post-holder', ['post' => $post])
					@endcomponent
					@component('posts.metainfo', ['post' => $post])
					@endcomponent
				</div>
				{{-- <div class="row">
					<div class="col-md-10">
						<strong>Hashtags Used</strong>
						<hr>
						<p>{{ $post->hashtags }}</p>
					</div>
				</div> --}}
				<div class="row text-right">
					@component('posts.actions', ['post' => $post])
					@endcomponent
				</div>
			</div>
		</div>
	</div>
</div>
@endsection