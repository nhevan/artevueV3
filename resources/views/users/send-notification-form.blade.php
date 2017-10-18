@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4">
			<div class="container-fluid">
				<div class="row">
					<form method="POST" action="{{ route('user.send-notification', ['user' => $user->id]) }}">
						{{ csrf_field() }}
						<div class="form-group">

							<label>Send personal notification to {{ isset($user->username) ? $user->username : 'all users' }}</label>
							<input type="text" name="notification" class="form-control" required>
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Send notification</button>
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