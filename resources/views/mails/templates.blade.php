@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="container-fluid">
				<div class="row">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th style="width: 50%" class="text-center">Template Name</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($templates as $template)
								<tr>
									<td>{{ $template->name }}</td>
									<td class="text-center">
										<a class="btn btn-sm btn-primary" href="{{ route('mail.test', ['mail_template' => $template->id]) }}">Send test email</a>
										<a class="btn btn-sm btn-primary" href="{{ route('mail.edit', ['mail_template' => $template->id]) }}">Edit Template</a>
										<a class="btn btn-sm btn-success" href="{{ route('mail.preview', ['mail_template' => $template->id]) }}" target="_blank">Preview Template</a>
									</td>
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