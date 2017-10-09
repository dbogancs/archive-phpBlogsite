<?php
	include_once("mindenelott.php");
	include_once("adatbazisnyitas.php");
	
/////////// SZUKSEGES ADATOK ELOKESZITESE /////////////////
	
	// POST VALTOZOK BIZTONSAGI MENTESE		
	if(empty($_POST["nev"])){
		print "
		<br/>
		<center>
			Hiba lépett fel. Az alábbi linkre kattintva próbáld meg isételten a regisztrációt!
			<br/>
			<br/>
			<A href=\"beallitasok.php\">
				Megpróbálom még egyszer!
			</A>
		</center>";
		exit();
	}
	
	$nev = mysql_escape_string($_POST["nev"]);
	$becenev = mysql_escape_string($_POST["becenev"]);
	$jelszo1 = mysql_escape_string($_POST["jelszo1"]);
	$jelszo2 = mysql_escape_string($_POST["jelszo2"]);
	$email = mysql_escape_string($_POST["email"]);
	$jelmondat = mysql_escape_string($_POST["jelmondat"]);
	$jelszo3 = $_POST["jelszo3"];
	
	// AZONOSITAS
	$azonosito;
	if($result = $conn->query("SELECT elso_id FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\" LIMIT 1")){
		$row = $result->fetch_assoc();
		$azonosito = $row["elso_id"];
		$result->free();
	}
	else $vanmarnev = "";
	
	// EGYEZO NEV KERESESE
	$vanmarnev;
	if($result = $conn->query("SELECT nev FROM regisztraciok WHERE nev=\"".$nev."\" and (allapot_id=\"1\" or allapot_id=\"2\") and NOT elso_id=\"".$azonosito."\" LIMIT 1")){
		$row = $result->fetch_assoc();
		$vanmarnev = $row["nev"];
		$sajat_id = $row["elso_id"];
		$result->free();
	}
	else $vanmarnev = "";
	
	// EGYEZO E-MAIL KERESESE
	if($result = $conn->query("SELECT email FROM regisztraciok WHERE email=\"".$email."\" and (allapot_id=\"1\" or allapot_id=\"2\") and NOT elso_id=\"".$azonosito."\" LIMIT 1")){
		$row = $result->fetch_assoc();
		$vanmaremail = $row["email"];
		$result->free();
	}
	else $vanmaremail = "";
	
	
	// MODOSITASOK SZAMA
	if($result = $conn->query("SELECT elso_id FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\" LIMIT 1")){
		$row = $result->fetch_assoc();
		$idtalal = $row["elso_id"];
		$result->free();
	}
	if($result = $conn->query("SELECT count(*) FROM regisztraciok WHERE elso_id=\"".$idtalal."\" and utolso_modositas LIKE\"%".date("Y-m-d")."%\" LIMIT 1")){
		$row = $result->fetch_row();
		$modszam = $row[0];
		$result->free();
	}
	else $vanmaremail = "";
	
/////////// ELLENORZO FUGGVENYEK /////////////////
	
	// osszes beviteli adat leellenorzese
	function regCheck($nev, $becenev, $jelszo1, $jelszo2, $email, $jelmondat, $jelszo3){
		// ures-e
		if(uresCheck($nev)) return false;
		if(uresCheck($jelszo3)) return false;
		// tul hosszu-e
		if(!hosszCheck($nev, 32)) return false;
		if(!hosszCheck($becenev, 32)) return false;
		if(!hosszCheck($jelszo1, 32)) return false;
		if(!hosszCheck($jelszo2, 32)) return false;
		if(!hosszCheck($email, 64)) return false;
		if(!hosszCheck($jelmondat, 256)) return false;
		if(!hosszCheck($jelszo3, 32)) return false;
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
	  for ($i=1; $i<$longer; $i++){
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
	
	<title>Adatok módosítása</title>
	
</head>


<!-- /////////////////////// A HONLAP TORZSE //////////////////////// -->

<body>
	<div class="focim">... naplója</div>
	<br/>
	<br/>
	
	<div class="vekonycontainer">
		<div class="keret">
			<div class="kitoltes">
				Profil módosítása
			</div>
			<div class="belsoszegely">
				<?php
				
					// FELTETELEK VIZSGALATA
					
					// tul sok modositas
					if($modszam>5){
						print "
						<br/>
						<center>
							Ma már eleget módosítottál. Legközelebb holnap lesz rá lehetőséged.
							<br/>
							<br/>
							<br/>
							
							A linkre kattintva visszatérhetsz a főoldalra:
							<br/>
							<br/>
							
							<A href=\"naplo.php\">
								... naplója
							</A>
						</center>";
					}
					// megfelelo karakterek lettek-e hasznalva ...

					// helyesen lettek-e kitoltve a mezok
					else if(!regCheck($nev, $becenev, $jelszo1, $jelszo2, $email, $jelmondat, $jelszo3)){
						print "
						<br/>
						<center>
							Hiba van a beviteli mezőkben. Térj vissza az alábbi linkre kattintva, és figyelj rá, hogy az összes szükséges mezőt helyesen töltsd ki!
							<br/>
							<br/>
							
							<A href=\"beallitasok.php\">
								Megpróbálom még egyszer!
							</A>
						</center>";
					}
					// van-e mar ilyen felhasznalonev
					else if(!empty($vanmarnev)){
							print "
							<br/>
							<center>
								Ezzel a felhasználónévvel  már van regisztrált felhasználó. A linkre kattintva térj vissza, és adj meg másik nevet!
								<br/>
								<br/>
								
								<A href=\"beallitasok.php\">
									Megpróbálom még egyszer!
								</A>
							</center>";
					}
					// van-e mar ilyen e-mail cim
					else if(!empty($vanmaremail)){
							print "
							<br/>
							<center>
								Ezzel az e-mail címmel  már van regisztrált felhasználó. A linkre kattintva térj vissza, és adj meg másik nevet!
								<br/>
								<br/>
								<A href=\"beallitasok.php\">
									Megpróbálom még egyszer!
								</A>
							</center>";
					}
					// ha minden feltetelnek eleget tesz
					else{
						// reg_nap reg_ido utolso_hozzaszolas utolso_belepes belepesek lekerdezesek es hozzaferesi_szint oroklese
						if($result_regi = $conn->query("SELECT * FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=1 LIMIT 1"))
						{
							$row_regi = $result_regi->fetch_assoc();
							$elso_id = $row_regi["elso_id"];
							$reg_nap = $row_regi["reg_nap"];
							$reg_ido = $row_regi["reg_ido"];
							$utolso_hozzaszolas = $row_regi["utolso_hozzaszolas"];
							$utolso_belepes = $row_regi["utolso_belepes"];
							$belepesek = $row_regi["belepesek"];
							$lekerdezesek = $row_regi["lekerdezesek"];
							$hozzaferesi_szint= $row_regi["hozzaferesi_szint"];
							
							if(empty($jelszo1)) $jelszo=$jelszo3;
							else $jelszo=$jelszo1;

							// ido rogzitese
							$mod_ido = date('Y-m-d H:i:s');

							// felhasznaloi adatok archivalasa
							mysqli_select_db($conn, "naplo");
							$retval_hozzaszolas_ideje = mysqli_query($conn, "UPDATE regisztraciok SET allapot_id=\"3\" WHERE nev=\"".$_SESSION["felhasznalo"]."\"");
							if(! $retval_hozzaszolas_ideje ) die('Üzenetküldés során hiba lépett fel: ' . mysqli_error($conn));

							// adatok kimentese
							$tabla1 = "INSERT INTO regisztraciok (nev, elso_id, becenev, jelszo, email, jelmondat, reg_nap, reg_ido, utolso_hozzaszolas, utolso_modositas, utolso_belepes, belepesek, lekerdezesek, hozzaferesi_szint, allapot_id) VALUES (\"".$nev."\", \"".$elso_id."\", \"".$becenev."\", \"".$jelszo."\", \"".$email."\", \"".$jelmondat."\", \"".$reg_nap."\", \"".$reg_ido."\", \"".$utolso_hozzaszolas."\", \"".$mod_ido."\", \"".$utolso_belepes."\", \"".$belepesek."\", \"".$lekerdezesek."\", \"".$hozzaferesi_szint."\", 1)";
							
							mysqli_select_db($conn, "regisztraciok");
							$retval = mysqli_query($conn, $tabla1);
							if(! $retval )
							{
							  die('A módosítás technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
							}
							else{
							// EREDMENYEK KOZLESE A FELHASZNALOVAL
								$_SESSION["felhasznalo"]=$nev;
								print "
								<br/>
								<center>
									A módosítás sikeresen megtörtént! Az alábbi adatokkal rendelkezik mostantól a felhasználói fiók:
									<br/>
									<br/>
									<br/>";
								print "
									Felhasználónév: ".$nev;
								if(!empty($becenev)) print "
									<br/>
									Becenév: ".$becenev;
								if(!empty($email)) print "
									<br/>
									E-mail cím: ".$email;
								if(!empty($jelmondat)) print "
									<br/>
									Jelmondat: ".$jelmondat;
								print "
									<br/>
									<br/>
									<br/>
									A linkre kattintva visszatérhetsz a főoldalra:
									<br/>
									<br/>
									<A href=\"naplo.php\">
										... naplója
									</A>
								</center>";
							}
							$result_regi->free();
						}
						else print "Nem található a felhasználó az adatbázisban!";
					}
					
					// KAPCSOLAT BONTASA
					$conn->close();
			
				?>
					
			</div>
		</div>
	</div>
	<br>
	<br>
	
</body>
</html>