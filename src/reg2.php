<?php
	include_once("mindenelott.php");
	include_once("adatbazisnyitas.php");
	
/////////// SZUKSEGES ADATOK ELOKESZITESE /////////////////
	
	// POST VALTOZOK BIZTONSAGI MENTESE		
	if(empty($_POST["nev"])){
		print "
		<html>
		<head>";
		
		include_once("fejlecextra.php");
		print "
			<title>Regisztráció</title>
		</head>
		<body>
			<br/>
			<center>
				Hiba lépett fel! Az alábbi linkre kattintva próbáld meg isételten a regisztrációt!
				<br/>
				<br/>
				<A href=\"reg.php\">
					Megpróbálom még egyszer!
				</A>
			</center>
		</body>";
		exit();
	}
	
	if(empty($_SESSION["regszam"])){
		$_SESSION["regszam"] = 0;
	}
		
	$nev = mysql_escape_string($_POST["nev"]);
	$becenev = mysql_escape_string($_POST["becenev"]);
	$jelszo1 = mysql_escape_string($_POST["jelszo1"]);
	$jelszo2 = mysql_escape_string($_POST["jelszo2"]);
	$email = mysql_escape_string($_POST["email"]);
	$jelmondat = mysql_escape_string($_POST["jelmondat"]);
	$megoldas = mysql_escape_string($_POST["megoldas"]);
	

	
	
	// MAI NAPON REGISZTRALTAK SZAMA
	$tabla = "SELECT count(*) FROM regisztraciok WHERE reg_nap=\"".date("Y-m-d")."\"";
	if ($result = $conn->query($tabla)) {
		$row = $result->fetch_row();
		$mai_regisztraciok = $row[0];
		// eroforras felszabaditasa
		$result ->free();
	}
	
	// KIVALASZTOTT FELADAT MEGOLDASA
	$tabla = "SELECT * FROM robotcheck WHERE id=\"".$_SESSION["feladat_id"]."\"";
	if ($result = $conn->query($tabla)) {
		$row = $result->fetch_assoc();
		$jomegoldas = $row["megoldas"];
		// eroforras felszabaditasa
		$result->free();
	}
		
	// EGYEZO NEV KERESESE
	$vanmarnev;
	if($result = $conn->query("SELECT nev FROM regisztraciok WHERE nev=\"".$nev."\", AND (allapot_id=\"1\" OR allapot_id=\"2\") LIMIT 1")){
		$row = $result->fetch_assoc();
		$vanmarnev = $row["nev"];
		$result ->free();
	}
	else $vanmarnev = "";
	
	// EGYEZO E-MAIL KERESESE
	if($result = $conn->query("SELECT email FROM regisztraciok WHERE email=\"".$email."\", AND (allapot_id=\"1\" OR allapot_id=\"2\") LIMIT 1")){
		$row = $result->fetch_assoc();
		$vanmaremail = $row["email"];
	}
	else $vanmaremail = "";
	
	
/////////// ELLENORZO FUGGVENYEK /////////////////
	
	// osszes beviteli adat leellenorzese
	function regCheck($nev, $becenev, $jelszo1, $jelszo2, $email, $jelmondat, $megoldas, $elfogad){
		// elfogadta-e a felhasznalasi felteteleket
		if(!elfogadta($elfogad)) return false;
		// ures-e
		if(uresCheck($nev)) return false;
		if(uresCheck($jelszo1)) return false;
		if(uresCheck($jelszo2)) return false;
		if(uresCheck($megoldas)) return false;
		// tul hosszu-e
		if(!hosszCheck($nev, 32)) return false;
		if(!hosszCheck($becenev, 32)) return false;
		if(!hosszCheck($jelszo1, 32)) return false;
		if(!hosszCheck($jelszo2, 32)) return false;
		if(!hosszCheck($email, 64)) return false;
		if(!hosszCheck($jelmondat, 256)) return false;
		if(!hosszCheck($megoldas, 32)) return false;
		// jo e-mail-e
		if(!uresCheck($email)) if(!emailCheck($email)) return false;
		// jelszavak egyeznek-e
		if(!jelszoCheck($jelszo1, $jelszo2)) return false;
			
		return true;
	}

	// husszusagellenorzes
	function hosszCheck($mezo){
		if (count($mezo) <=32) return true;
		else return false;
	}
	
	// uressegellenorzes
	function uresCheck($mezo){
		if (empty($mezo)) return true;
		else return false;
	}

	// e-mail ellenorzes
	function emailCheck($mezo){
		$szoveg = $mezo;
		if (!strpos($szoveg,'@')>0 && strpos($szoveg,'@')<count($szoveg)) return false;
	    else return true;
	}
	
	// jelszoegyezes ellenorzes
	function jelszoCheck($jelszo1, $jelszo2){
	  $longer;
	  if(count($jelszo1) < count($jelszo2)) $longer=count($jelszo2);
	  else $longer=count($jelszo1);
	  for ($i=0; $i<$longer; $i++){
		if ($jelszo1[$i] != $jelszo2[$i]){
		  return false;
		}
	  }
	  return true;
	}
	
	// felhasznaloi feltetelek elfogadasanak ellenorzese
	function elfogadta($elfogad){
		if($elfogad) return true;
		else false;
	}
	
?>


<!-- ////////////////////// A HONLAP FEJLECE ///////////////////////// -->
<html>
<head>

	<?php
		include_once("fejlecextra.php");
	?>	
	<title>Regisztráció</title>
	
</head>


<!-- /////////////////////// A HONLAP TORZSE //////////////////////// -->

<body>
	<div class="focim">... naplója</div>
	<br/>
	<br/>
	<div class="vekonycontainer">
		<div class="keret">
			<div class="kitoltes">
				Regisztráció
			</div> 
			<div class="belsoszegely">
				<?php
				
					$gombszoveg = "";
					
					// FELTETELEK VIZSGALATA
					

					// nem regisztralt-e mar a kozelmultban
					if($_SESSION["regszam"] == 10){
						print "
						<br/>
						<center>
							Már korábban sikeresen létre lett hozva neked egy regisztráció. A linkre kattintva visszatérhetsz a főoldalra bejelentkezni azzal:
							<br/>
							<br/>
							<A href=\"naplo.php\">
								... naplója
							</A>
						</center>";
					}
					// nem regisztraltak-e ma tul sokan
					else if($mai_regisztraciok > 30){
						print "
						<br/>
						<center>
							Hiba történt a feldolgozás közben! Holnap próbáld meg újra!
							<br/>
							<br/>
							<A href=\"naplo.php\">
								... naplója
							</A>
						</center>";
					}
					// megfelelo karakterek lettek-e hasznalva
					/*else if(!(charcheck($nev) and charcheck($becenev))){
						print "
						<br/>
						<center>
							Sajnos a felhasználónévhez és a becenévhez csak a magyar abc betűit és számokat (esetleg szóközt) használhatsz. Kattints az alábbi linkre, és próbáld meg a regisztrációt újból!
							<br/>
							<br/>
						</center>";
						
						$gombszoveg = "Megpróbálom még egyszer!";
					}*/
					// helyesen lettek-e kitoltve a mezok
					else if(!regCheck($nev, $becenev, $jelszo1, $jelszo2, $email, $jelmondat, $megoldas, $_POST["elfogad"])){
						print "
						<br/>
						<center>
							Hiba van a beviteli mezőkben. Térj vissza az alábbi linkre kattintva, és figyelj rá, hogy az összes szükséges mezőt helyesen töltsd ki!
							<br/>
							<br/>
						</center>";
						
						$gombszoveg = "Megpróbálom még egyszer!";
					}
					// jo-e a feladatra a megoldas
					else if(!($jomegoldas == $megoldas))
					{
						print "
						<br/>
						<center>
							Sajnos a feladatra adott válasz nem helyes. Kattints az alábbi linkre, és próbáld meg a regisztrációt újból!
							<br/>
							<br/>
						</center>";
						
						$gombszoveg = "Megpróbálom még egyszer!";
					}
					// van-e mar ilyen felhasznalonev
					else if(!empty($vanmarnev)){
						print "
						<br/>
						<center>
							Ezzel a felhasználónévvel  már van regisztrált felhasználó. A linkre kattintva térj vissza, és adj meg másik nevet!
							<br/>
							<br/>
						</center>";
						
						$gombszoveg = "Megpróbálom még egyszer!";
					}
					// van-e mar ilyen e-mail cim
					else if(!empty($vanmaremail)){
						print "
						<br/>
						<center>
							Ezzel a felhasználónévvel  már van regisztrált felhasználó. A linkre kattintva térj vissza, és adj meg másik nevet!
							<br/>
							<br/>
						</center>";
						
						$gombszoveg = "Megpróbálom még egyszer!";
					}
					// ha minden feltetelnek eleget tesz
					else{
						//ido rogzitese
						$reg_nap = date('Y-m-d');
						$reg_ido = date('H:i:s');

						// adatok kimentese
						$tabla1 = "INSERT INTO regisztraciok (nev, becenev, jelszo, email, jelmondat, reg_nap, reg_ido, utolso_belepes, hozzaferesi_szint,allapot_id) VALUES (\"".$nev."\", \"".$becenev."\", \"".$jelszo1."\", \"".$email."\", \"".$jelmondat."\", \"".$reg_nap."\", \"".$reg_ido."\", \"".date("Y-m-d H-m-s")."\", 2, 1)";
						mysqli_select_db($conn, "regisztraciok");
						$retval = mysqli_query($conn, $tabla1);
						if(! $retval )
						{
						  die('A regisztráció technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
						}
						// generalt id kiolvasasa
						$tabla_miazid = "SELECT * FROM regisztraciok WHERE nev=\"".$nev."\"";
						if ($result_miazid = $conn->query($tabla_miazid)) {
							$row_miazid = $result_miazid->fetch_assoc();
							$elso_id = $row_miazid["id"];
							// eroforras felszabaditasa
							$result_miazid->free();
						}
						$tabla_elsoid = "UPDATE regisztraciok SET elso_id=\"".$elso_id."\" WHERE nev=\"".$nev."\"";
						mysqli_select_db($conn, "naplo");
						$retval_hozzaszolas_ideje = mysqli_query($conn, $tabla_elsoid);
						if(! $retval_hozzaszolas_ideje ) die('Regisztráció során hiba lépett fel: ' . mysqli_error($conn));
						// regisztracio megjegyzese
						$_SESSION["regszam"] = 1;
						
						// EREDMENYEK KOZLESE A FELHASZNALOVAL
						
						print "
						<br/>
						<center>
							A regisztráció sikeresen megtörtént! Az alábbi adatokkal létrejött a felhasználói fiók:
							<br/>
							<br/>
							<br/>";
							print "Felhasználónév: ".$nev;
							if(!empty($becenev))  print "
							<br/>
							Becenév: ".$becenev;
							print "
							<br/>
							E-mail cím: ".$email;
							if(!empty($jelmondat)) print "
							<br/>
							Jelmondat: ".$jelmondat;
							print "
							<br/>
							<br/>
							<br/>
							A linkre kattintva visszatérhetsz a főoldalra bejelentkezni:
							<br/>
							<br/>
							<A href=\"naplo.php\">
								... naplója
							</A>
						</center>";
					}
					
					// KAPCSOLAT BONTASA
					$conn->close();

					// VISSZANAVIGALO GOMB
					print "
					<form action=\"reg.php\" method=\"POST\">
						<input type=\"hidden\" name=\"nev\" value=\"".$_POST["nev"]."\">
						<input type=\"hidden\" name=\"becenev\" value=\"".$_POST["becenev"]."\">
						<input type=\"hidden\" name=\"email\" value=\"".$_POST["email"]."\">
						<input type=\"hidden\" name=\"jelmondat\" value=\"".$_POST["jelmondat"]."\">
						<center>
							<input type=\"submit\" class=\"postlink\" style=\"color: blue\" value=\"".$gombszoveg."\"/>
						</center>
					</form>";
					
				?>
					
			</div>
		</div>
	</div>
	<br>
	<br>
	
</body>

</html>