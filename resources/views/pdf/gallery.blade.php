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
	    <p style="text-align: right;color: gray">Created using ArteVue</p>
		<div style="text-align: center;">
			<h2><?= $data['gallery_name']; ?></h2>
			<p style="font-size:smaller;"><?= $data['gallery_description']; ?></p>
		</div>
		<div class="gallery" style="margin: 50px auto;width: 100%;border: 1px solid red">
			<table style="margin: 0 auto;text-align: center;width: 80%;" cellpadding="5">
				
			</table>
		</div>		
    </body>
</html>
