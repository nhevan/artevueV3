@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="text-center">
			{{$posts->links()}}
		</div>
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row">
					@if(count($posts) == 0)
					    <p style="margin-top: 50px" class="text-center">
							No posts found
					    </p>
					@endempty
					@foreach ($posts as $post)
						@component('posts.mini-post-holder', ['post' => $post])
						@endcomponent
					@endforeach
				</div>
			</div>
		</div>
		<div class="text-center">
			{{$posts->links()}}
		</div>
	</div>
</div>
@endsection