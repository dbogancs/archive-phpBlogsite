<?php
	include_once("mindenelott.php");
?>

<html>

<header>

	<?php
		include_once("fejlecextra.php");
	?>
	<title>Kijelentkezes</title>
	
</header>

<body>
	<?php
		// FELHASZNALO KIJELENTKEZTETESE
		unset($_SESSION["felhasznalo"]); 
		unset($_SESSION["felhasznalo_hozzaferesi_szintje"]);
	?>

<div class="focim">Viszlát!</div>
<A href="naplo.php">vissza a naplóhoz</A>

</body>

</html>