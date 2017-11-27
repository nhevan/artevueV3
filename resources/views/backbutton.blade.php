<button class="btn btn-sm btn-info" onclick="goBack()">Go Back</button>

@section('script')
	<script>
		function goBack() {
		    window.history.back();
		}
	</script>
@append