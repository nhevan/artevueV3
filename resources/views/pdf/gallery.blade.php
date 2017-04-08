<!doctype html>
<html>
    <head>
        <title>Artevue</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="chrome=1">
        <meta name="viewport" content="width=device-width">
        <!--[if lt IE 9]>
        <![endif]-->
    </head>
    <style>
    body{
    	font-size: 18px;
    }
    </style>
    <body>
	    <p style="text-align: right;color: gray;font-size: smaller">Created using ArteVue</p>
		<div style="text-align: center;">
			<h2>{{ $data['gallery_name'] }}</h2>
			<p style="font-size:smaller;">{{ $data['gallery_description'] }}</p>
		</div>
		<div class="gallery" style="margin: 50px auto;width: 100%;border: 0px solid red">
			<table style="margin: 0 auto;text-align: center;width: 80%;" cellpadding="5">
				@php 
					$count = 0;
				@endphp
				@foreach ($data['gallery_images'] as $image)
					@php
					if ($count == 0)
						echo "<tr style='margin: 2cm;page-break-inside:avoid;'>";
					@endphp
					<td style="text-align: center;border: 0px solid red">
						<img style="width: 6cm;" src="http://dy01r176shqrv.cloudfront.net/{{$image['image']}}"/>
						<p style="width: 6cm;font-size: 3mm;text-align: center;margin:0 auto;">{{ $image['description'] }}</p>
					</td>
					@php 
						$count++;
						if($count == 2) { 
							echo "</tr><br/>";
							$count = 0;
							continue;
						}
					@endphp
				@endforeach
				@php 
					if($count == 1 ) echo "</tr>"; 
				@endphp
			</table>
		</div>		
    </body>
</html>
