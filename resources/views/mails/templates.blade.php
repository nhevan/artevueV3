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
								<th class="text-center">Template Name</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Welcome Email Template</td>
								<td>
									<a class="btn btn-primary" href="/test-welcome-email/1">Send test email</a>
									<a class="btn btn-primary" href="/test-welcome-email/1">Edit Template</a>
									<a class="btn btn-success" href="/test-welcome-email/1">Preview Template</a>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
{{-- 
<!DOCTYPE html>
<html>
<head>
  <script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
  <script>tinymce.init({ 
  	selector:'textarea',
  	plugins: "image imagetools" 
  });</script>
</head>
<body>
<form method="POST" action="/test-tinymce">
	{{ csrf_field() }}
	<textarea name='custom-input'>Next, start a free trial!</textarea>

	<button type="submit" class="btn btn-primary" style="margin: 0 auto;display: block;">Submit</button>
</form>
</body>
</html> --}}