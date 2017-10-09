<?php 

	session_start();
	
	$sysmsg=""; // ide lehet a felhasznalo fele tovabbitando uzeneteket irni
	
	if(empty($_SESSION["felhasznalo_hozzaferesi_szintje"])) $_SESSION["felhasznalo_hozzaferesi_szintje"] = 1;

 ?>