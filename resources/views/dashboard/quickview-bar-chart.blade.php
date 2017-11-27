<canvas id="quickview-bar-chart" width="400" height="120"></canvas>
@php
    $total_new_users = 0;
    foreach ($dataset['new_users'] as $new_users) {
        $total_new_users += $new_users;
    }

    $total_new_posts = 0;
    foreach ($dataset['new_posts'] as $new_posts) {
        $total_new_posts += $new_posts;
    }

    $total_new_likes = 0;
    foreach ($dataset['new_likes'] as $new_likes) {
        $total_new_likes += $new_likes;
    }

    $total_new_comments = 0;
    foreach ($dataset['new_comments'] as $new_comments) {
        $total_new_comments += $new_comments;
    }

    $total_new_pins = 0;
    foreach ($dataset['new_pins'] as $new_pins) {
        $total_new_pins += $new_pins;
    }

    $total_new_messages = 0;
    foreach ($dataset['new_messages'] as $new_messages) {
        $total_new_messages += $new_messages;
    }
@endphp

@section('script')
    <script>
        var quickview_bar = document.getElementById("quickview-bar-chart");
        var quickview_bar_chart_data = {
            datasets: [{
                label: 'Users',
                backgroundColor: '#48b8b7',
                borderColor: '#48b8b7',
                fill: false,
                data: [ {{ $total_new_users }}]
            }, {
                label: 'Posts',
                backgroundColor: '#fc5a7b',
                borderColor: '#fc5a7b',
                fill: false,
                data: [ {{ $total_new_posts }}]
            }, {
                label: 'Likes',
                backgroundColor: '#ff8931',
                borderColor: '#ff8931',
                fill: false,
                data: [ {{ $total_new_likes }}]
            }, {
                label: 'Comments',
                backgroundColor: '#ffbe43',
                borderColor: '#ffbe43',
                fill: false,
                data: [ {{ $total_new_comments }}]
            }, {
                label: 'Pins',
                backgroundColor: '#515151',
                borderColor: '#515151',
                fill: false,
                data: [ {{ $total_new_pins }}]
            }, {
                label: 'Messages',
                backgroundColor: '#319b2d',
                borderColor: '#319b2d',
                fill: false,
                data: [ {{ $total_new_messages }}]
            }]
        };
        new Chart(quickview_bar, {
            type: 'horizontalBar',
            data: quickview_bar_chart_data,
            options:
            {
                scales:
                {
                    xAxes: [{
                        display: true
                    }]
                }
            }
        });
    </script>
@append