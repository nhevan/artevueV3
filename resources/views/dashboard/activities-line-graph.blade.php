<form method="GET" action="/dashboard" class="navbar-form" style="text-align: center;">
    {{ csrf_field() }}
    <div class="form-group">
        <input name='start_date' type="date" class="form-control input-sm" placeholder="Start Date" value="{{ \Carbon\Carbon::parse($dataset['start_date'])->format("Y-m-d")}}">
        <input name='end_date' type="date" class="form-control input-sm" placeholder="End Date" value="{{ \Carbon\Carbon::parse($dataset['end_date'])->format("Y-m-d") }}">

        <select class="form-control input-sm" name='interval'>
          <option>Select Interval</option>
          <option value="hour" {{ $dataset['interval'] == 'hour' ? "selected":"" }} >Hour</option>
          <option value="day" {{ $dataset['interval'] == 'day' ? "selected":"" }} >Day</option>
          <option value="month" {{ $dataset['interval'] == 'month' ? "selected":"" }} >Month</option>
        </select>
    </div>
    <button type="submit" class="btn btn-info btn-sm">Show</button>
    - or -
    <a href="/dashboard" class="btn btn-success btn-sm">See Today</a>
</form>    

<canvas id="activities-line-chart" width="400" height="100"></canvas>

@section('script')
    <script>
        var activities_chart = document.getElementById("activities-line-chart");
        var chart_data = {
            labels: [
                    @foreach ($dataset['x_axis']['axis_points'] as $x) 
                        "{{ \Carbon\Carbon::parse($x)->format($dataset['x_axis']['axis_label_format']) }}"
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ],
            datasets: [{
                label: 'Users',
                backgroundColor: '#48b8b7',
                borderColor: '#48b8b7',
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
                label: 'Posts',
                backgroundColor: '#fc5a7b',
                borderColor: '#fc5a7b',
                fill: false,
                data: [
                    @foreach ($dataset['new_posts'] as $type => $new_posts) 
                        {{ $new_posts }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }, {
                label: 'Likes',
                backgroundColor: '#ff8931',
                borderColor: '#ff8931',
                fill: false,
                data: [
                    @foreach ($dataset['new_likes'] as $type => $new_likes) 
                        {{ $new_likes }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }, {
                label: 'Comments',
                backgroundColor: '#ffbe43',
                borderColor: '#ffbe43',
                fill: false,
                data: [
                    @foreach ($dataset['new_comments'] as $type => $new_comments) 
                        {{ $new_comments }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }, {
                label: 'Pins',
                backgroundColor: '#515151',
                borderColor: '#515151',
                fill: false,
                data: [
                    @foreach ($dataset['new_pins'] as $type => $new_pins) 
                        {{ $new_pins }}
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                ]
            }, {
                label: 'Messages',
                backgroundColor: '#319b2d',
                borderColor: '#319b2d',
                fill: false,
                data: [
                    @foreach ($dataset['new_messages'] as $type => $new_messages) 
                        {{ $new_messages }}
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