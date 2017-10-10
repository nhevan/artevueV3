@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="container-fluid">
				<div class="row">
					
					<h4 class="text-center">
						<strong>App version settings </strong>
						<a href="{{ route('settings.edit-app-settings') }}" id="edit-app-settings" class="pull-right btn btn-xs btn-primary">Edit</a>
					</h4>

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
									<td>{{ $app_setting->value }}</td>
									<td>{{ $app_setting->description }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>

					<h4 class="text-center">
						<strong>Explore weight distribution settings</strong>
						<a href="{{ route('settings.edit-weight-settings') }}" id="edit-weight-settings" class="pull-right btn btn-xs btn-primary">Edit</a>
					</h4>
					<table class="table table-striped table-bordered table-hover">
						<thead>
							<tr>
								<th class="text-center">Setting</th>
								<th class="text-center">Value</th>
								<th class="text-center">Description</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($weight_settings as $weight_setting)
								<tr>
									<td>{{ $weight_setting->key }}</td>
									<td>{{ $weight_setting->value }}</td>
									<td>{{ $weight_setting->description }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>
</div>
@endsection