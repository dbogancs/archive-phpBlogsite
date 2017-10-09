<?php 

	// CSATLAKOZAS
	$conn = new mysqli("...", "...", "...", "...");
	
	// KAPCSOLAT ELLENORZES
	if ($conn->connect_errno) {
		printf("Hiba történt az adatbázishoz csatlakozás közben. Hibaüzenet: %s\n", $conn->connect_error);
	}
	
	// KODOLASI BEALLITASOK
	mysqli_query($conn, "SET NAMES 'UTF8'");
	mysqli_query($conn, "SET CHARACTER SET UTF8");

 ?>