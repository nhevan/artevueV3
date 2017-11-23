<canvas id="male-female-chart" width="400" height="110"></canvas>


@section('script')
    <script>
        var male_female = document.getElementById("male-female-chart");
        var male_female_chart_data = {
            datasets: [{
                label: 'Male',
                backgroundColor: '#48b8b7',
                data: [
                    {{ $analytics['total_male_users'] }}
                ]
            }, {
                label: 'Female',
                backgroundColor: '#fc5a7b',
                data: [
                    {{ $analytics['total_female_users'] }}
                ]
            }]

        };
        new Chart(male_female, {
            type: 'horizontalBar',
            data: male_female_chart_data,
            options:
            {
                scales:
                {
                    xAxes: [{
                        display: false
                    }]
                }
            }
        });
    </script>
@append