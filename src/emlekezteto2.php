<?php 

	include_once("mindenelott.php");
	include_once("adatbazisnyitas.php");

	// valtozok biztositasa
	$nev = mysql_escape_string($_POST["nev"]);
	$email = mysql_escape_string($_POST["email"]);
		
	// felhasznalo kikeresese
	$tabla = "SELECT * FROM regisztraciok WHERE nev=\"".$nev."\" LIMIT 1";
	if ($result = $conn->query($tabla)) {
		$row = $result->fetch_assoc();
		// email egyeztetese
		if($row["email"]==$email){	
			mail($email,  
			 "Jelszóemlékeztető",  
			 "A regisztrációhoz a következő jelszót adtad meg: ".$row_email["jelszo"]);  
		}
		$result->free();
	}
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
						Amennyiben regisztrált felhasználónevet és a hozzá tartozó e-mail címet adtad meg, úton van az emlékeztető!
						<br/>
						
						<A href="naplo.php">
							Vissza a naplóhoz
						</A>
					</div>
				</div>
			</div>
		</div>
	</div>
	<br>
	<br>
	<?php $conn->close(); ?>
</body>
</html>