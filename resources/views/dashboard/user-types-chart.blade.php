<canvas id="user-types-chart" width="400" height="400"></canvas>

@section('script')
    <script>
        var ctx = document.getElementById("user-types-chart");
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            options: {
		        title: {
		            display: true,
		            text: 'User Types'
		        }
		    },
            data: {
                labels: [
                			@foreach ($dataset as $type => $user_type) 
                    			"{{ $type }}"
		                    	@if (!$loop->last)
		                    		,
		                    	@endif
                    		@endforeach
                		],
                datasets: [{
                    label: '# of users',
                    data: [ 
                    		@foreach ($dataset as $type => $user_type) 
                    			{{ $user_type }}
		                    	@if (!$loop->last)
		                    		,
		                    	@endif
                    		@endforeach
	                    ],
                    backgroundColor: [
                        '#ff5879',
                        '#ff9438',
                        '#ffc64c',
                        '#42b8b8',
                        '#38a533',
                        '#af543a',
                        '#8344ff',
                        '#5b5b5b'
                    ]
                }]
            }
        });
        </script>
@append