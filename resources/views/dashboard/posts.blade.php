<ul class="list-group">
    <li class="list-group-item">
        <strong>Post Information</strong>
    </li>
    <li class="list-group-item">
        <span class="badge">{{ $analytics['total_posts'] }}</span> Total Posts
    </li>
    <li class="list-group-item">
        <span class="badge">{{ $analytics['total_public_posts'] }}</span> Public Posts
    </li>
    <li class="list-group-item">
        <span class="badge">{{ $analytics['total_private_posts'] }}</span> Private Posts
    </li>
    <li class="list-group-item">
        <span class="badge">{{ $analytics['total_posts_for_sale'] }}</span> Posts for sale
    </li>
    <li class="list-group-item">
        <span class="badge">{{ $analytics['total_posts_with_artist'] }}</span> Posts with Artist
    </li>

    @foreach ($analytics['top_3_post_locations'] as $top_location)
    
    <li class="list-group-item">
        <span class="badge">{{ $top_location['count'] }}</span> #{{ $loop->iteration }} Post Location : {{ $top_location['address_title'] }}
    </li>

    @endforeach
</ul>