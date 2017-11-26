<canvas id="activities-line-chart" width="400" height="100"></canvas>

{{-- "{{ \Carbon\Carbon::parse($x)->format("y-m-d") }}" --}}
{{-- "{{ \Carbon\Carbon::parse($x)->format("ga") }}" --}}
@section('script')
    <script>
        var activities_chart = document.getElementById("activities-line-chart");
        var chart_data = {
            labels: [
                    @foreach ($dataset['x_axis']['axis_points'] as $x) 
                        "{{ \Carbon\Carbon::parse($x)->format("M") }}"
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ],
            datasets: [{
                label: 'New Users',
                backgroundColor: '#48b8b7',
                fill: false,
                data: [
                    @foreach ($dataset['new_users'] as $type => $new_users) 
                        {{ $new_users }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }, {
                label: 'Female',
                backgroundColor: '#fc5a7b',
                data: [
                    @foreach ($dataset as $type => $user_type) 
                        {{ sizeof($user_type) }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }]

        };
        console.log(chart_data);
        new Chart(activities_chart, {
            type: 'line',
            data: chart_data
        });
    </script>
@append