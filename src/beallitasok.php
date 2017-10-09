<?php
	include_once("mindenelott.php");
	include_once("adatbazisnyitas.php");
	
/////////// SZUKSEGES ADATOK ELOKESZITESE /////////////////
	
	// szabalyszeru hozzaferes ellenorzese
	if(empty($_SESSION["felhasznalo"])){
		print "
			<br/>
			<center>
				Hiba lépett fel. Nem vagy bejelentkezve!
				<br/>
				<br/>
				
				<A href=\"naplo.php\">
					Vissza a naplóhoz!
				</A>
			</center>";
		exit();
	}

	// adatok kigyujtese
	$tabla_profil = "SELECT * FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\" LIMIT 1";
	if ($result_profil = $conn->query($tabla_profil)) {
		$row_profil = $result_profil->fetch_assoc();	
		$becenev = $row_profil["becenev"];
		$email = $row_profil["email"];
		$jelmondat = $row_profil["jelmondat"];
		$result_profil->free();
	}
	else print "Hiba történt az adatok lekérése közben.";
?><html>

<!-- ////////////////////// A HONLAP FEJLECE ///////////////////////// -->

<head>
	<?php
		include_once("fejlecextra.php");
	?>
	
	<!-- egyeb kodok csatolasa -->
	<script LANGUAGE="JavaScript" type="text/javascript" src="jquery.js"></script>
	
	<!-- JAVASCRIPT -->
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">

		// modosito gomb tiltasa
		$(document).ready(function() {
			$('#Button').attr('disabled', 'disabled');
		});	

		// osszes beviteli adat leellenorzese
		function regCheck(nev, becenev, jelszo1, jelszo2, email, jelmondat, jelszo3){
			// ures-e
			if(uresCheck(nev)) return false;
			if(uresCheck(jelszo3)) return false;
			// tul hosszu-e
			if(!hosszCheck(nev, 32)) return false;
			if(!hosszCheck(becenev, 32)) return false;
			if(!hosszCheck(jelszo1, 32)) return false;
			if(!hosszCheck(jelszo2, 32)) return false;
			if(!hosszCheck(email, 64)) return false;
			if(!hosszCheck(jelmondat, 256)) return false;
			// jo e-mail-e
			if(!uresCheck(email)) if(!emailCheck(email)) return false;
			// jelszavak egyeznek-e
			if(!jelszoCheck(jelszo1, jelszo2)) return false;
			// helyes kitöltes eseten regisztracio kuldesenek engedelyezese	
			$('#Button').removeAttr('disabled');
			return true;
		}

		// husszusagellenorzes
		function hosszCheck(mezo, hossz){
			if (mezo.value.length <= hossz) return true;
			else{
			alert("A 32 karakternél hosszabb felhasználónév, becenév, jelszó, robotellenőrzésre adott válasz és 64-nél hosszabb e-mail cím, illetve 256-nál hosszabb leírás nem megengedett.");
			return false;
			}
		}
		
		// uressegellenorzes
		function uresCheck(mezo){
			if (mezo.value == ""){
				alert("A csillaggal jelölt mezők kitöltése kötelező!");
				return true;
		}
		  else{
			return false;
		  }
		}
		
		// e-mail ellenorzes
		function emailCheck(mezo){
		  szoveg = mezo.value;
		  if (!(szoveg.indexOf('@')>0 && szoveg.indexOf('@')<szoveg.length-1)){
			alert("Hibás e-mail cím!");
			return false;
		  }
		  else return true;
		}

		// jelszoegyezes ellenorzes
		function jelszoCheck(jelszo1, jelszo2){
		  var longer
		  if(jelszo1.value.length < jelszo2.value.length) {longer=jelszo2.value.length;}
		  else {longer=jelszo1.value.length;}
		  for (var i=0; i<longer; i++){
			if (jelszo1.value.charAt(i) != jelszo2.value.charAt(i)){
			  alert("A jelszavak nem egyeznek!");
			  return false;
			}
		  }
		  return true;
		}

	</SCRIPT>
		
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
				Adatok módosítása
			</div>
			<div class="belsoszegely">
				<div class="figyelem">
					<div class="aprobetus">
						
							Ezen az oldalon lehetőséged van a megadott adataid szabad változtatására. Az összes módosítás két gombnyomással véglegesíthető. Fontos, hogy a mentéshez szükséges az éppen aktuális jelszó megadása is!
							<div style="color: gray;">
								A regisztráció törlésének ügyében ...nak kell üzenetet küldeni.
							</div>
					
					</div>
				</div>
				<br/>
				<br/>
				
				<form name="urlap" action="beallitasok2.php" method="POST">
				
					Felhasználónév*:
					<br/>
					<input type="text" name="nev" value="<?php print $_SESSION["felhasznalo"]; ?>"/>
					<div class="aprobetus" style="color: gray">
						max. 32 kar.
					</div>
					<br/>
					
					Becenév: 
					<br/>
					<input type="text" name="becenev" value="<?php print $becenev; ?>"/>
					<div class="aprobetus" style="color: gray">
						max. 32 kar.
					</div>
					<br/>
					
					E-mail cím:
					<br/>
					<input type="text" name="email" value="<?php print $email; ?>"/>
					<div class="aprobetus" style="color: gray">
						max. 64 kar.
					</div>
					<br/>

					Egy pár mondat valami olyanról, amit csak Te és ... tudtok: 
					<br/>
					<textarea name="jelmondat" rows="5" cols="40"><?php print $jelmondat; ?></textarea>
					<br/>
					<div class="aprobetus" style="color: gray">
						(Amennyiben nem ismerős vagy, arról írj itt pár szót, hogyan jutottál el a naplómhoz.)
						<br/>
						max. 256 kar.
					</div>
					<div class="aprobetus">
					</div>
					<br/>
					
					Jelszó módosítása:
					<br/>
					Új jelszó először:
					<input type="password" name="jelszo1"/>
					<br/>
					Új jelszó másodszor:
					<input type="password" name="jelszo2"/>
					<div class="aprobetus" style="color: gray">
						max. 32 kar.
					</div>
					<br/>
					<br/>
					
					Bármilyen változtatás végrehajtásához szükséges a jelenlegi jelszó*:
					<br/>
					<input type="password" name="jelszo3"/>
					<br/>
					<br/>
					
					<!-- regisztracio kuldese gomb-->
					<input type="submit"  id="Button" value="Módosítások küldése"/>
					<br/>
					<br/>
				
				</form>
				
				<form>
					<!-- megadott adatok ellenorzese gomb -->
					<input type="button" id="Button2" value="Kitöltés ellenőrzése" onClick="regCheck(urlap.nev, urlap.becenev, urlap.jelszo1, urlap.jelszo2, urlap.email, urlap.jelmondat, urlap.jelszo3)">
				</form>	
		
			<br/>
			<center>
				<A href="naplo.php">
					módosítás nélkül vissza a naplóhoz
				</A>
			</center>
			<br/>
			</div>
		</div>
	</div>
	<?php $conn->close(); ?>		
</body>
</html>