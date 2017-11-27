@extends('layouts.app')
@section('content')

	<div class="container">
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="container-fluid">
					<div class="row">
						@include('backbutton')
						<form method="POST" action="{{ route('mail.update', ['template' => $template->id]) }}">
						
						<button 
							id='email-template-edit-submit-button-1' type="submit" 
							class="btn btn-sm btn-primary pull-right" 
							onclick="return confirm('Please confirm that you want to save the changes by clicking \'ok\' button.\nOtherwise click cancel.')">
						
							Save
						</button>

						<h3 class="text-center">Editing {{ $template->name }} template</h3>
						<hr>
							{{ csrf_field() }}

							<div class="form-group">
								<label>Sender Email address</label>
								<input type="text" name="sender_email" class="form-control" value="{{ $template->sender_email }}" >
							</div>

							<div class="form-group">
								<label>Sender Name</label>
								<input type="text" name="sender_name" class="form-control" value="{{ $template->sender_name }}" >
							</div>

							<div class="form-group">
								<label>Subject</label>
								<input type="text" name="subject" class="form-control" value="{{ $template->subject }}" >
							</div>

							<p>{{ $template->additional_info }}</p>
							
							<div class="form-group">
								<label>Content</label>
								<textarea rows="30" name='content'>{{ $template->content }}</textarea>
							</div>

							<button 
								id='email-template-edit-submit-button-2' type="submit" 
								class="btn btn-primary pull-right" 
								onclick="return confirm('Please confirm that you want to save the changes by clicking \'ok\' button.\nOtherwise click cancel.')">
							
								Save
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('script')
	<script src="https://cloud.tinymce.com/stable/tinymce.min.js"></script>
	<script>tinymce.init({ 
		selector:'textarea',
		plugins: "image imagetools" 
	});</script>
@append
