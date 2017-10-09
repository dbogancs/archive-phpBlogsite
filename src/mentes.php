
<html>
<head>

	<?php
		include_once("fejlecextra.php");
	?>
		
	<title>
		... naplója
	</title>

</head>

<body>
	
	<div class="focim">... naplója</div>
	<br/>
	
	<?php
	include_once("allinone.php");
	mail(	"cogito.ergo.sum@postafiok.hu",  
			 "DN full save ".date("Y-m-d H:i:s"),  
			 "<html><head><title>DN full save ".date("Y-m-d H:i:s")."</title></head><body>$naplo</body></html>",
			"MIME-Version: 1.0\r\n"."Content-type: text/html; charset=utf-8\r\n");  
	include_once("allinone2.php");
	mail(	"cogito.ergo.sum@postafiok.hu",  
			 "DN classic save ".date("Y-m-d H:i:s"),  
			 "<html><head><title>DN full save ".date("Y-m-d H:i:s")."</title></head><body>$naplo</body></html>",
			"MIME-Version: 1.0\r\n"."Content-type: text/html; charset=utf-8\r\n");  			 
	
	?>
	
	<br>
	
	<div class="vekonycontainer">
		<center>
			<A href="naplo.php">
				A napló tartalma sikeresen elküldve!
			</A>
		</center>
	</div>
	<br>
	<br>
	
</body>

</html>
