@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row">
					<form method="POST" action="{{ route('user.edit-username', ['user' => $user->id]) }}" >
						{{ csrf_field() }}
						{{ method_field('PATCH') }}
						
						<div class="form-group">
							<label>Enter new username</label>
							<input type="text" name="username" class="form-control" value='{{ $user->username }}' required>
						</div>

						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Change username</button>
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