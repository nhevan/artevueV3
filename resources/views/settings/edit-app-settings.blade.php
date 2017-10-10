@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="container-fluid">
				<div class="row">
					
					<h4 class="text-center">
						<strong>App version settings </strong>
					</h4>
					
					<form method="POST" action="{{ route('settings.edit-app-settings') }}">
					{{ csrf_field() }}
						<table class="table table-striped table-bordered table-hover">
							<thead>
								<tr>
									<th class="text-center">Setting</th>
									<th class="text-center">Value</th>
									<th class="text-center">Description</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($app_settings as $app_setting)
									<tr>
										<td>{{ $app_setting->key }}</td>
										<td>
											<div class="form-group">
												{{ Form::text($app_setting->key.'-value', $app_setting->value) }}
											</div>
										</td>
										<td>
											{{-- {{ $app_setting->description }} --}}
											<div class="form-group">
												{{ Form::text($app_setting->key.'-description', $app_setting->description) }}
											</div>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
						<div class="form-group">
							<button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Update Settings</button>
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