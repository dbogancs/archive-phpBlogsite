<?php
	include_once("mindenelott.php");
?>
	
<html>

<head>
	<?php
	include_once("fejlecextra.php");
	?>
	<title>Emlékeztető</title>
</head>


<body>
<div class="globalcontainer">
	<div class="focim">... naplója</div>
	<br/>
		<div class="keret">
			<div class="kitoltes">
				<div class="felhasznalo">
					
						<form action="emlekezteto2.php" method="POST">
							Add meg felhasználóneved és a regisztrált e-mail címed, amire a jelszóemlékeztetőt lehet küldeni!
							<br/>
							<br/>
							Felhasználónév: <input type="text" name="nev">
							E-mail: <input type="text" name="email">
							<input type="submit" value="Belépés">
						</form>
					
				</div>
			</div>
		</div>
	</div>
</div>
<br>
<br>

</body>