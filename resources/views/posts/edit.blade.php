@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row">
					<form method="POST" action="/post/{{$post->id}}">
						{{ csrf_field() }}

						<div class="form-group">
							<label for="description">Description:</label>
							{{ Form::textarea('description', $post->description, ['class' => 'form-control', 'size' => '30x5']) }}
						</div>

						<div class="form-group">
							<label for="price">Price:</label>
							{{ Form::number('price', $post->price, ['class' => 'form-control', 'step' => '0.01']) }}
						</div>

						<div class="form-group">
							<label for="address_title">Address title:</label>
							{{ Form::text('address_title', $post->address_title, ['class' => 'form-control']) }}
						</div>

						<div class="form-group">
							<label for="address">Address:</label>
							{{ Form::text('address', $post->address, ['class' => 'form-control']) }}
						</div>

						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Submit Changes</button>
						</div>
					</form>
		            @if (count($errors) > 0)
		                <div class="alert alert-danger">
		                    <ul>
		                        @foreach ($errors->all() as $error)
		                            <li>{{ $error }}</li>
		                        @endforeach
		                    </ul>
		                </div>
		            @endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection