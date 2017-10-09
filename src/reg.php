<?php
	include_once("mindenelott.php");
	include_once("adatbazisnyitas.php");

/////////// SZUKSEGES ADATOK ELOKESZITESE /////////////////
	
	// KORABBAN KITOLTOTT ADATOK
	
	if(isset($_POST["nev"])) $nev=$_POST["nev"];
	else $nev="";
	if(isset($_POST["becenev"])) $becenev=$_POST["becenev"];
	else $becenev="";
	if(isset($_POST["email"])) $email=$_POST["email"];
	else $email="";
	if(isset($_POST["jelmondat"])) $jelmondat=$_POST["jelmondat"];
	else $jelmondat="";
	
	// REGISZTRACIOK SZAMANAK DEFINIALASA
	if(empty($_SESSION["regszam"])){
		$_SESSION["regszam"] = 0;
	}

	// FELADAT KIVALASZTASA
	
	$tabla0 = "SELECT COUNT(*) FROM robotcheck";
	if ($result0 = $conn->query($tabla0)) {
		$row1 = $result0->fetch_row();
		// feladatsorszam kivalasztasa
		$feladat_id = rand(1, $row1[0]);
		$tabla1 = "SELECT * FROM robotcheck WHERE id=\"".$feladat_id."\" LIMIT 1";
		if ($result1 = $conn->query($tabla1)) {
			// feladat és szorszam elmentese
			$row1 = $result1->fetch_assoc();
			$feladat = $row1["feladat"];
			$_SESSION["feladat_id"] = $feladat_id;
			// eroforras felszabaditasa
			$result1->free();
		}
		// eroforras felszabaditasa
		$result0->free();
	}
	
?><html>




<head>
<!-- ////////////////////// A HONLAP FEJLECE ///////////////////////// -->

	<?php
		include_once("fejlecextra.php");
	?>

	<!-- egyeb kodok csatolasa -->
	<script LANGUAGE="JavaScript" type="text/javascript" src="jquery.js">
	</script>
	
	<!-- JAVASCRIPT -->
	
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
	
		// regisztralo gomb tiltasa
		$(document).ready(function() {
			$('#Button').attr('disabled', 'disabled');
		});	

		// osszes beviteli adat leellenorzese
		function regCheck(nev, becenev, jelszo1, jelszo2, email, jelmondat, megoldas, elfogad){
			// elfogadta-e a felhasznalasi felteteleket
			if(!elfogadta(elfogad)) return false;
			// ures-e
			if(uresCheck(nev)) return false;
			if(uresCheck(jelszo1)) return false;
			if(uresCheck(jelszo2)) return false;
			
			if(uresCheck(megoldas)) return false;
			// tul hosszu-e
			if(!hosszCheck(nev, 32)) return false;
			if(!hosszCheck(becenev, 32)) return false;
			if(!hosszCheck(jelszo1, 32)) return false;
			if(!hosszCheck(jelszo2, 32)) return false;
			if(!hosszCheck(email, 64)) return false;
			if(!hosszCheck(jelmondat, 256)) return false;
			if(!hosszCheck(megoldas, 32)) return false;
			// jo e-mail-e
			if(email.value != "") if(!emailCheck(email)) return false;
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
		  if (mezo.value != "") return false;
		  else{
			alert("A csillaggal jelölt mezők kitöltése kötelező!");
			return true;
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
		
		// felhasznaloi feltetelek elfogadasanak ellenorzese
		function elfogadta(elfogad){
			if(elfogad.checked == true) return true;
			else {
				alert("A fogadalom megtétele nélkül nem lehet regisztrálni.");
				return false;
			}
		}
		
	</SCRIPT>
			<SCRIPT LANGUAGE="JavaScript" type="text/javascript">

		
function proba3(){
			alert("siker!");
			document.getElementById("demo").innerHTML='Ellenőrizve';
		}
		</SCRIPT>
	<title>Regisztráció</title>
	
</head>


<body>

<!-- /////////////////////// A HONLAP TORZSE //////////////////////// -->
		
	<div class="focim">... naplója</div>
	<br/>
	<br/>
		
	<div class="vekonycontainer">
		<div class="keret">
			<div class="kitoltes">
				Regisztráció
			</div>
			<div class="belsoszegely">
				<div class="figyelem">
					<div class="aprobetus">
						<br/>
						
							<!-- Regisztrálással ... naplójának további bejegyzési válnak elérhetővé. Ezek többnyire önismereti kérdésekkel foglalkoznak. ... sok időt tölt személyisége megértésével, és tetteiben rendszeresen ok-okozati összefüggéseket keres. Ezen gondolatoknak úgyszintén megvan a helyük a naplóban.
							<br/>
							Amennyiben csak az általános elmélkedések érdekelnek, felesleges a regisztráció, ellenben ha sokkal mélyebb mondanivalójú tartalmakra is kíváncsi vagy, akkor a regisztráción túl még fel is kell venned a kapcsolatot ...val a hozzáférési jogaid módosításához. (Az extra jogokat természetesen csak kiváltságos emberek kaphatják meg, akikben maximálisan meg lehet bízni!) -->
							
							A naplómnak akadnak kevésbé publikus, bizalmasan kezelendő részei. Hozzáférési szinteket határoztam meg, amik a bizalmon alapulnak. Ennek az első lépcsőfoka, hogy az illető regisztrál. Ha valaki komolyabban érdeklődik a tartalmak iránt, már egy szebben megfogalmazott bemutatkozással is több hozzáférést kaphat, rövid elbeszélgetés után pedig akár még többet. <A href="info.php">A szintek részleteiről itt olvashatsz.</A>	
						<br/>
						
							Tehát töltsd ki az űrlapot szándékaid komolyságának megfelelően! (Minden megadott adatot bizalmasan kezelek, és a későbbiekben módodban áll módosítani őket!)	
						<br/>
						<br/>
						<br/>
						<br/>
					
					</div>
				</div>
			
				<form name="urlap" action="reg2.php" method="POST">
				
					Felhasználónév*:
					
					<br/>
					<input type="text" name="nev" value="<?php print $nev; ?>"/>
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
					<div class="aprobetus">
						(Olyan név, ami alapján beazonosíthatlak.)
					</div>
					<br/>
					
					Jelszó először*:
					
					<br/>
					<input type="password" name="jelszo1"/>
					<div class="aprobetus" style="color: gray">
						max. 32 kar.
					</div>
			
					Jelszó másodszor*:
					
					<br/>
					<input type="password" name="jelszo2"/>
					<div class="aprobetus" style="color: gray">
						max. 32 kar.
					</div>
					<div class="aprobetus">
						(A megadott jelszó számomra látható lesz! Habár nem áll szándékomban visszaélni vele, mégis így érzem korrektnek, hogy ezt elmondom. Biztonsági okokbók érdemes más webhelyekétől eltérő jelszót megadni.)
					</div>
					<br/>
					
					E-mail cím:
					
					<br/>
					<input type="text" name="email" value="<?php print $email; ?>"/><div class="aprobetus" style="color: gray">max. 64 kar.</div>
					
					<div class="aprobetus">
						(Nem kötelező megadni, de erősen ajánlott. Nem foglak zaklatni vagy spam-ekkel elhalmozni. Ide érkezik a jelszóemlékeztető, a hozzászólásodra a válaszom , illetve regisztrációnál komolyabb szándékot érezve egyetlen egyszer a kapcsolatot megpróbálom fölvenni.)
					</div>
					
					<br/>
					
					Egy pár mondat valami olyanról, amit csak Te és ... tudtok:
					
					<br/>
					<textarea name="jelmondat" rows="5" cols="40"><?php print $jelmondat; ?></textarea>
					<div class="aprobetus" style="color: gray">
						max. 256 kar.
					</div>
					<div class="aprobetus">
						(Amennyiben nem ismerős vagy, arról írj itt pár szót, hogyan jutottál el a naplómhoz. Ha érdekelnének további tartalmak, írd meg itt azt is. Az ide leírtak befolyásolhatják a későbbi hozzáférésedet.)
					</div>
					<br/>
					<br/>
					<br/>
					
					Bizonyítékul, hogy nem vagy robot, <?php print $feladat; ?>*:
					
					<br/>
					<input type="text" name="megoldas"/>
					<br/>
					<div class="aprobetus" style="color: gray">
						Ügyelj a helyesírásra!
					</div>
					<br/>
					
					Az alábbi jelölőnégyzet kipipálásával megfogadod, hogy az itt olvasottakat bizalommal kezeled, soha nem élsz vissza velük, semmilyen módon nem terjeszted a napló tartalmát, továbbá esküszöl, hogy nem vagy családtag*:
					
					<br/>
					<input type="checkbox" name="elfogad" value="true"/>
					<br/>
					<br/>
					<br/>
					
					
					<!-- regisztracio kuldese gomb-->
					<input type="submit"  id="Button" value="Regisztráció küldése" />
					<br/>
					<br/>
				
				</form>
				
				<button onclick="regCheck(urlap.nev, urlap.becenev, urlap.jelszo1, urlap.jelszo2, urlap.email, urlap.jelmondat, urlap.megoldas, urlap.elfogad)">Kitöltés ellenőrzése</button>
				
			<br/>
			
			<center>
				<A href="naplo.php">
					vissza a naplóhoz
				</A>
			</center>
			<br/>
			
			</div>
		</div>
	</div>
	<?php $conn->close(); ?>		
</body>
</html>