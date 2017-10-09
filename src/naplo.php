<?php
	
/////////////// MINDENEK ELOTT ///////////////

	include_once("mindenelott.php");
	include_once("hunsort.php");

	setlocale(LC_ALL,'hungarian'); 
	
	include_once("adatbazisnyitas.php");
	
///////////// HOZZAFERES-KONVERTALO VALTOZOK //////////////////////
	
	$hozzaferes_nevei_szintbol=array();
	$hozzaferes_id_nevbol=array();
	$hozzaferes_szintjei_idbol=array();

	// Konvertálás azonosítóból valódi szintté
	$tabla = "SELECT * FROM hozzaferes";
	if ($result = $conn->query($tabla)) {
		while($row = $result->fetch_assoc()){
			$hozzaferes_nevei_szintbol[$row["szint"]] = $row["nev"];
			$hozzaferes_id_nevbol[$hozzaferes_nevei_szintbol[$row["szint"]]]=$row["id"];
			$hozzaferes_szintjei_idbol[$row["id"]]=$row["szint"];
		}
		$result->free();
	}

	
//////////////// ELOZMENY /////////////////////	
	
	if(isset($_SESSION["felhasznalo"]) AND isset($_REQUEST["bejegyzes"])){
	
		$tabla_korabbielozmeny = "SELECT * FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\" LIMIT 1";
		if ($result_korabbielozmeny = $conn->query($tabla_korabbielozmeny)) {
			$row_korabbielozmeny = $result_korabbielozmeny->fetch_assoc();
		}

		$tabla_hozzaszolasok = "UPDATE regisztraciok SET elozmenyek=\"".$_REQUEST["bejegyzes"]." ".date("Y-m-d H:i:s")."; ".$row_korabbielozmeny["elozmenyek"]."\" WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\"";		
		mysqli_select_db($conn, "naplo");
		$retval_hozzaszolasok = mysqli_query($conn, $tabla_hozzaszolasok);
		if(! $retval_hozzaszolasok ) die('Hiba lépett fel: ' . mysqli_error($conn));
	
	}
	// nem regisztralt felhasznalo
	else if(isset($_REQUEST["bejegyzes"])){
		$tabla_korabbielozmeny = "SELECT * FROM regisztraciok WHERE nev=\"Névtelen\" and allapot_id=\"1\" LIMIT 1";
		if ($result_korabbielozmeny = $conn->query($tabla_korabbielozmeny)) {
			$row_korabbielozmeny = $result_korabbielozmeny->fetch_assoc();
		}

		$tabla_hozzaszolasok = "UPDATE regisztraciok SET elozmenyek=\"".$_REQUEST["bejegyzes"]." ".date("Y-m-d H:i:s")."; ".$row_korabbielozmeny["elozmenyek"]."\" WHERE nev=\"Névtelen\" and allapot_id=\"1\"";		
		mysqli_select_db($conn, "naplo");
		$retval_hozzaszolasok = mysqli_query($conn, $tabla_hozzaszolasok);
		if(! $retval_hozzaszolasok ) die('Hiba lépett fel: ' . mysqli_error($conn));
	}
	
////////////// BEJELENTKEZES ///////////////////

	// nem tortent bejelentkezes
	if(empty($_POST["nev"]) or empty($_POST["jelszo"])){
		// korabban mar bejelentkezett
		if(!empty($_SESSION["felhasznalo"])){ 

			// LEKERDEZESSZAM NOVELESE
		
			// korabbi lekerdezesszam kikeresese
			$tabla = "SELECT * FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" LIMIT 1";
			if ($result = $conn->query($tabla)) {
				$row = $result->fetch_assoc();
				// novelt lekerdezesszam visszairasa
				$ujlekerdezes=$row["lekerdezesek"]+1;
				$tabla = "UPDATE regisztraciok SET lekerdezesek=\"".$ujlekerdezes."\" WHERE nev=\"".$_SESSION["felhasznalo"]."\"";
				mysqli_select_db($conn, "naplo");
				$retval = mysqli_query($conn, $tabla);
				if(! $retval ) die('Hiba lépett fel: ' . mysqli_error($conn));
				// eroforras felszabaditasa
				$result->free();
			}
		}
	}
	// helyes adatok a bejelentkezeshez
	else {
		// valtozok biztositasa
		$nev = mysql_escape_string($_POST["nev"]);
		$jelszo = mysql_escape_string($_POST["jelszo"]);
		
		// BELEPESI KISERLET
		
		// felhasznalo kikeresese
		$tabla = "SELECT * FROM regisztraciok WHERE nev=\"".$nev."\" AND (allapot_id=1 OR allapot_id=2) LIMIT 1";
		if ($result = $conn->query($tabla)) {
			$row = $result->fetch_assoc();
			
			// jelszo egyeztetese
			if($row["jelszo"]==$jelszo){
			
				// utolso belepes ota eltelt ido: sikeres belepes
				if(strtotime(date("Y-m-d"))-strtotime($row["utolso_belepes"])< 16200000 )
				{
					$_SESSION["felhasznalo"]= $row["nev"];
					$_SESSION["felhasznalo_hozzaferesi_szintje"]=$hozzaferes_szintjei_idbol[$row["hozzaferesi_szint"]];
					
					// BELEPESSZAM NOVELESE
					
					$belepesek_szama = $row["belepesek"]+1;
					$tabla = "UPDATE regisztraciok SET belepesek=\"".$belepesek_szama."\", utolso_belepes=\"".date("Y-m-d H:i:s")."\" WHERE nev=\"".$_SESSION["felhasznalo"]."\" and allapot_id=\"1\"";		
					mysqli_select_db($conn, "naplo");
					$retval = mysqli_query($conn, $tabla);
					if(! $retval ) die('A hozzászólás technikai okok miatt nem menthető el: ' . mysqli_error($conn));
				}
				else{
					$sysmsg = "Szomorú tény, hogy több mint fél éve nem jártál itt... Biztonsági okokból a felhasználói fiókod inaktív állapotba került. Regisztrálj újra, vagy - amennyiben extra hozzáférési jogaid voltak - vedd fel a kapcsolatot ...val a régi aktiválásához!";
					
					// inaktiv allapotba helyezes
					$tabla = "UPDATE regisztraciok SET allapot_id=\"2\" WHERE nev=\"".$nev."\" and (allapot_id=\"1\" or allapot_id=\"2\")";		
					mysqli_select_db($conn, "naplo");
					$retval = mysqli_query($conn, $tabla);
					if(! $retval ) die('A bejelentkezés technikai okok miatt nem lehetséges: ' . mysqli_error($conn));
				}
			}
			// sikertelen belepes: rossz jelszo
			else $sysmsg = "<br/>Hibás felhasználónév vagy jelszó<br/>Az alábbi linken kérhetsz jelszóemlékeztetőt:<br/><A href=\"emlekezteto.php\">Emlékeztető e-mail küldése<A>";
			
			// eroforras felszabaditasa
			$result->free();
		}
		// sikertelen belepes: rossz felhasznalonev
		else $sysmsg = "<br/>Hibás felhasználónév vagy jelszó";
	} 
	
	
////////////////// HOZZASZOLAS /////////////////////////
	
	// kuldve lett ervenyes hozzaszolas
	if(!empty($_POST["hozzaszolas"]) and !empty($_SESSION["felhasznalo"])){
		
		// valtozo biztositasa
		$hozzaszolas = $_POST["hozzaszolas"];
		
		// HOZZASZOLASI IDO ELLENORZESE
		
		$tabla = "SELECT utolso_hozzaszolas FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" LIMIT 1";
		
		// komplikalt vizsgalata az uzenet hosszanak: vacakolt, de igy most MUKODIK!
		$hossz=strlen($hozzaszolas);
		$hosszabb=strlen($hozzaszolas) - 1000;
		$sysmsg=$hossz;
		if($hossz < 1001) $sysmsg=1;
		else $sysmsg=0;
		if($sysmsg==0 ){
			$sysmsg="Sajnos az üzeneted ".$hossz." hosszú, ami ".$hosszabb." karakterrel több a megengedettnél!";
		}
		else if ($result = $conn->query($tabla)) {
			$row = $result->fetch_assoc();
			
			// ma meg nem kuldott uzenetet
			if($row["utolso_hozzaszolas"]!=date("Y-m-d")){
			
				// HOZZASZOLAS MENTESE
				$hozzaszolas = mysql_escape_string($_POST["hozzaszolas"]);
				$tabla = "INSERT INTO hozzaszolasok (kuldo, kuldes_ido, uzenet) VALUES (\"".$_SESSION["felhasznalo"]."\", \"".date("Y-m-d H:i:s")."\", \"".$hozzaszolas."\")";
				mysqli_select_db($conn, "naplo");
				$retval = mysqli_query($conn, $tabla);
				if(! $retval ) die('A hozzászólás technikai okok miatt nem menthető el: ' . mysqli_error($conn));
				
				// UJ HOZZASZOLASI IDO MENTESE
				
				mysqli_select_db($conn, "naplo");
				$retval = mysqli_query($conn, "UPDATE regisztraciok SET utolso_hozzaszolas=\"".date("Y-m-d")."\" WHERE nev=\"".$_SESSION["felhasznalo"]."\"");
				if(! $retval ) die('Üzenetküldés során hiba lépett fel: ' . mysqli_error($conn));
				// uzenet a felhasznalonak
				$sysmsg = "Elküldve";
				
			}
			// ma mar kuldott uzenetet
			else $sysmsg = "<br/>Ma már elküldtél egy üzenetet! Legközelebb másnap van rá lehetőség. Ha mindenképp ma szeretnéd felvenni ...val a kapcsolatot, írj az e-mail címére: ...@....hu";
			// eroforras felszabaditata
			$result->free();
		}
		// uzenetben nem megengedett karakterek
		else $sysmsg = "Az üzenet nem lett elküldve, mert nem megengedett karaktereket tartalmazott.";
	}
	

//////////////// JELMAGYARAZAT OSSZEALLITASA + TEMASZINEK LEMENTESE ///////////////

		// temak kigyujtese
		$tabla = "SELECT * FROM temak";
		if ($result = $conn->query($tabla)) {
		$jelek=array();
		$jelszin=array();
		$temakereso=array();
		// temanevek megszinezese es mentese
			while($row = $result->fetch_assoc()){	
					$jelek[] = "<!-- ".$row["nev"]." --><span style=\"color: #".$row["hexa_szin"].";\">".$row["nev"]."</span>&nbsp;&nbsp; ";	
					$jelszin[$row["id"]] = $row["hexa_szin"];
					$temakereso[]=$row["nev"];
					$temakereso_idk_nevbol[$row["nev"]]=$row["id"];
			}
			// eroforras felszabaditata
			$result->free();
		}
		usort($jelek, "hunsort");

//////////////// BEJEGYZESEK CIMLISTAJA + A KIVALASZTOTT ADATAI ///////////////////

	// bejegyzesek kikeresese
	$szuro="";
	$tabla_bejegyzes="";
	
	if(empty($_SESSION["cimke"])) $_SESSION["cimke"]=0;
	if(empty($_SESSION["tema"])) $_SESSION["tema"]=0;
	if(empty($_SESSION["ajanlott"])) $_SESSION["ajanlott"]=0;
	
	if(isset($_POST["cimke"])) $_SESSION["cimke"]=$_POST["cimke"];
	if(isset($_POST["tema"])) $_SESSION["tema"]=$_POST["tema"];
	if(isset($_POST["ajanlott"])) $_SESSION["ajanlott"]=$_POST["ajanlott"];


	
	$tabla_bejegyzes = "SELECT * FROM bejegyzesek";

	if ($result_bejegyzes = $conn->query($tabla_bejegyzes)){
	
		// valtozok biztositasa
		$lista[][]=array();
		$lista_ev_sorszambol=array();
		$lista_fejezet_sorszambol=array();
		$lista_bejegyzes_sorszambol=array();
		$lista_sorszamok=array();
				
		$szin;
		$cim;
		$datum;
		$ev;
		$tartalom;
		$cimkek=array();
		global $bejegyzes_hozzaferesi_szintje;
		$referencia;
		$cimkereferencia;
		
		// CIMKEK
		$cimkekereso_nevek=array();
		$tabla_cimkek = "SELECT * FROM cimkek";
		if ($result_cimkek = $conn->query($tabla_cimkek)) {
			while ($row_cimkek = $result_cimkek->fetch_assoc()) {
							
				$cimkekereso_idk_nevbol[$row_cimkek["nev"]]=$row_cimkek["id"];
				$cimkekereso_nevek_idbol[$row_cimkek["id"]] = $row_cimkek["nev"];
				$cimkekereso_nevek[] = $row_cimkek["nev"];
				
			}
		$result_cimkek->free();
		}
		
		// CIMLISTA OSSZEALLITASA	
		for ($i=0; $row_bejegyzes = $result_bejegyzes->fetch_assoc(); $i++) { 
			$ev = $row_bejegyzes["ev"];
			
			if($_SESSION["felhasznalo_hozzaferesi_szintje"]>=$hozzaferes_szintjei_idbol[3])
				// sorszamozas (nulladik evnek kulon)
				if($row_bejegyzes["ev"]<1){
					$realsorszam=$row_bejegyzes["sorszam"]+12;
					$veglegessorszam="0.".$realsorszam.". ";
				}
				else $veglegessorszam=$row_bejegyzes["sorszam"].". ";
			else $veglegessorszam="";
			
			// hivatkozas hozzaadasa ha megtekintheti
			if($hozzaferes_szintjei_idbol[$row_bejegyzes["hozzaferesi_szint"]]<=$_SESSION["felhasznalo_hozzaferesi_szintje"]){
				$referencia = "href=\"naplo.php?bejegyzes=".$row_bejegyzes["sorszam"]. "#bejegyzes\"";
				if($row_bejegyzes["ajanlott"]==1) $kiemeles="font-weight: bold;";
				else $kiemeles="";
			}
			else $referencia = "";
			
			// ellenorizetlen bejegyzes megjelolese
			if($row_bejegyzes["leellenorzott"]==0){
				$ell="*";
			}
			else $ell="";
			
			

			// bejegyzeslista sorainak elmentese
			if(($referencia == "" and $_SESSION["felhasznalo_hozzaferesi_szintje"] >= $hozzaferes_szintjei_idbol[4]) or $referencia != ""){
			
				$bejegyzes_hozzaferesi_szintje = $hozzaferes_szintjei_idbol[$row_bejegyzes["hozzaferesi_szint"]];
			
				$tabla_bejegyzeslista="";

				if(isset($_SESSION["cimke"])){

					if(0!=$_SESSION["cimke"]) {
						$tabla_bejegyzeslista = "SELECT * FROM bejegyzesek b JOIN kapcs_bej_cim c ON (b.sorszam = c.bejegyzes_sorszam) WHERE sorszam=\"".$row_bejegyzes["sorszam"]."\"";
					}
					else{
						$tabla_bejegyzeslista = "SELECT * FROM bejegyzesek WHERE sorszam=\"".$row_bejegyzes["sorszam"]."\"";
					}
				}
				else{
					$tabla_bejegyzeslista = "SELECT * FROM bejegyzesek WHERE sorszam=\"".$row_bejegyzes["sorszam"]."\"";
				}	

				$szuro="";

				if(isset($_SESSION["tema"])) if($_SESSION["tema"]!=0) $szuro.=" AND tema_id=\"".$_SESSION["tema"]."\"";
				if(isset($_SESSION["cimke"])) if($_SESSION["cimke"]!=0) $szuro.=" AND cimke_id=\"".$_SESSION["cimke"]."\"";
				if(isset($_SESSION["ajanlott"])) if($_SESSION["ajanlott"]==true) $szuro.=" AND ajanlott=\"1\"";

				$tabla_bejegyzeslista .= $szuro;
				
				if($result_bejegyzeslista = $conn->query($tabla_bejegyzeslista)){
			
					if($row_bejegyzeslista = $result_bejegyzeslista->fetch_assoc()){
					
						$lista_ev_sorszambol[$row_bejegyzeslista["sorszam"]]=$row_bejegyzeslista["ev"];
						$lista_fejezet_sorszambol[$row_bejegyzeslista["sorszam"]]=$row_bejegyzeslista["fejezet"];
						$lista_bejegyzes_sorszambol[$row_bejegyzeslista["sorszam"]]= "<A ".$referencia."><span style=\"color: ".$jelszin[$row_bejegyzeslista["tema_id"]]."; ".$kiemeles."\">".$ell.$veglegessorszam.$row_bejegyzeslista["cim"]." ".$row_bejegyzeslista["datum"]."</span></A><br/>";
						$lista_sorszamok[]=$row_bejegyzeslista["sorszam"];
					}
				}
				
				
				
				
				
				$lista2[$row_bejegyzes["ev"]][$row_bejegyzes["fejezet"]][$row_bejegyzes["sorszam"]] = "<!-- ".$row_bejegyzes["cim"]." --><A ".$referencia."><span style=\"color: ".$jelszin[$row_bejegyzes["tema_id"]]."; ".$kiemeles."\">".$row_bejegyzes["cim"]." ".$row_bejegyzes["datum"]."</span></A>"; // linkek a bejegyzes cimkeihez
				
				// EXTRA ADATOK A KIVALASZTOTT BEJEGYZESHEZ
				
				if(isset($_REQUEST["bejegyzes"]) and 1==($_REQUEST["bejegyzes"]==$row_bejegyzes["sorszam"])){ // ha bejegyzest hivnak le
					// valtozok biztositasa
					$cim = $row_bejegyzes["cim"];
					$datum = $row_bejegyzes["datum"];
					$tartalom = str_replace("\r\n","<br/>",$row_bejegyzes["tartalom"]);
					$bejegyzes_ev = $row_bejegyzes["ev"];
					
					// cimkek azonositoinak lekerese
					$tabla_cimkeid = "SELECT * FROM kapcs_bej_cim WHERE bejegyzes_sorszam=\"".$row_bejegyzes["sorszam"]."\"";
					if ($result_cimkeid = $conn->query($tabla_cimkeid)) {
						// cimkek elnevezeseinek kikeresese azonosito alapjan
						while ($row_cimkeid = $result_cimkeid->fetch_assoc()) {
							
							$cimkek[]= "
							<!-- ".$cimkekereso_nevek_idbol[$row_cimkeid["cimke_id"]]." -->
							<form action=\"naplo.php?bejegyzes=".$_REQUEST["bejegyzes"]."#bejegyzes\" method=\"POST\">
								<input type=\"hidden\" name=\"cimke\" value=\"".$row_cimkeid["cimke_id"]."\">
								<input type=\"submit\" class=\"postlink\" value=\"- ".$cimkekereso_nevek_idbol[$row_cimkeid["cimke_id"]]."\"/>
							</form>";
						}
						// eroforras felszabaditasa
						$result_cimkeid->free();
					}
				}
			}
		}			
		// eroforras felszabaditasa
		$result_bejegyzes->free();
	}
	

////////////////////// KIVALASZTOTT BEJEGYZES TARTALMA ////////////////////////

	// kikereses ha kivalasztott egy bejegyzest
	if(isset($_REQUEST["bejegyzes"])){
		$szovegreszek="";
		$szovegszam=0;		
		
		$tabla = "SELECT COUNT(*) FROM szovegek WHERE bejegyzes_sorszam=\"".$_REQUEST["bejegyzes"]."\"";
		if($result = $conn->query($tabla)){
			$row = $result->fetch_row();
			$szovegszam = $row[0];
			$result->free();
		}
		
		$row_szoveg="semmi";
		for($i=1; $i<=$szovegszam; $i++){
			
			$tabla = "SELECT * FROM szovegek WHERE bejegyzes_sorszam=\"".$_REQUEST["bejegyzes"]."\" AND bejegyzesbeli_sorszam=\"".$i."\"";
			if($result = $conn->query($tabla)){
				// valtozok biztositasa
				
				// hozzaferheto szovegreszek kigyujtese
				if(!($row = $result->fetch_assoc())) break;
				$a=$hozzaferes_szintjei_idbol[$row["hozzaferesi_szint"]];
				$b=$_SESSION["felhasznalo_hozzaferesi_szintje"];
				if($a<=$b){
					
					$szovegreszek .= str_replace("\r\n","<br/>",$row["szoveg"]);
					
				}
				else $szovegreszek .= "[...]<br/><br/>";
				
				// eroforras felszabaditasa
				
				
				
			}$result->free();
		}
		// HIVATKOZASOK ELHELYEZESE A SZOVEGEKBEN
		
		$elofordult_mar[]=array();
		$elofordult;
		$cimkek2=array();
		
		for($i=0; $i<strlen($szovegreszek); $i++){

			if($szovegreszek[$i]=="-"){
				if($szovegreszek[ ($i - 3) ] == "-"){
					// bejegyzesre utalo datumot talalt
					$tabla = "SELECT * FROM bejegyzesek WHERE datum=\"".substr($szovegreszek, $i-7, 10)."\"";
					// kikeresi a datumhoz tartozo bejegyzest
					if ($result = $conn->query($tabla)){		
						$row = $result->fetch_assoc();
						
						if(true){
							// korabban elhelyezett azonos hivatkozast keres
							$elofordult=0;
							for($j=0; $j<count($elofordult_mar); $j++)
							{	
								if($elofordult_mar[$j]==$row["datum"]) $elofordult=1;
							}							
							// akkor csereli le ha meg nem volt
							if($elofordult==0 and isset($lista2[$row["ev"]][$row["fejezet"]][$row["sorszam"]])){
								// elso ev miat korrekcio szukseges
								$linkelt_bejegyzes = $lista2[$row["ev"]][$row["fejezet"]][$row["sorszam"]];
								$cimkek2[]=$linkelt_bejegyzes; //listaba menti
								$elofordult_mar[]=$row["datum"]; //megjegyzi
								// elhelyei a hivatkozasokat
								$szovegreszek = str_replace($row["cim"]." ".$row["datum"] ,$linkelt_bejegyzes , $szovegreszek);
								
							}
						}
						$result->free();
					}
					
					
				}
			}
		}
		
		$szovegreszek = str_replace("\\","",$szovegreszek);

		// rendezes
		usort($cimkek2, "hunsort");
		usort($cimkek, "hunsort");

	}
	

?>

<html>

<head>

<!-- ////////////////////// A HONLAP FEJLECE ///////////////////////// -->

	<?php
		include_once("fejlecextra.php");
	?>
	<title>... naplója</title>
	
</head>

<body>

<!-- /////////////////////// A HONLAP TORZSE //////////////////////// -->


	<div class="globalcontainer">
		<div class="focim">... naplója</div>
		<br/>
		<br/>
		
		<!-- BEJELENTKEZO SAV -->
	
		<div class="keret">
			<div class="kitoltes">
				<div class="felhasznalo">
					<div class="belepes">
						<?php
							// bejelentkezo mezok
							if(!isset($_SESSION["felhasznalo"])){
								print"
								<form action=\"naplo.php\" method=\"POST\">
									Felhasználónév: <input type=\"text\" name=\"nev\">
									Jelszó: <input type=\"password\" name=\"jelszo\">
									<input type=\"submit\" value=\"Belépés\">
								</form>";
							}
							// bejelentkezetteknek csak sajat felhasznalonevuk kiirasa
							else {
								print "Bejelentkezett felhasználó: ".$_SESSION["felhasznalo"]." <a href=\"beallitasok.php\">[adatok módosítása]</a>";
								if(count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"]) {
									print " <a href=\"szerkesztes.php\">[új]</a>";
									print " <a href=\"allinone.php\">[A1]</a>";
									print " <a href=\"allinone2.php\">[A2]</a>";
								}
							}
						?>
					</div>	
					
					<div class="regisztracio">
						<?php
							// hivatkozas regisztraciora
							if(!isset($_SESSION["felhasznalo"])) print "vagy <A href=\"reg.php\">regisztráció itt</A>!";
							// regisztraltaknak kilepogomb
							else{ print "
								<form action=\"logout.php\" method=\"POST\">
									<input type=\"submit\" value=\"Kilépés\">
								</form>";
							}
						?>
					</div>
					
					<!-- rendszeruzenetek -->
					
					<?php
						print "<div class=\"figyelem\" style=\"font-weight: normal\">".$sysmsg."</div>";
					?>
				</div>
			</div>
		</div>
		<br/>
		
		<!-- INFORMÁCIÓK -->
		
		<div class="keret">
			<div class="kitoltes">
				<div class="koszonto">
					<!-- koszonto -->
					Légy üdvözölve ... naplójában!
				</div>
			</div>
			<div class="szabalyok">
				<div class="segitseg">
					<!-- rovid leirasa a naplonak -->
					
					Néha előfordul, hogy az ember nem érti, mik miért történnek körülötte a nagyvilágban, és igyekszik ezeket megfejteni. Néha belső ellentmondásokba ütközik, amiket megpróbál föloldani. Ilyen és ehhez hasonló dolgokkal foglalkozom a naplómban. Elmélkedem, problémákat fogalmazok és oldok meg, értelmezek dolgokat, de olykor önmagamat bemutató írásaim is vannak, vagy elmesélek egy történetet, kifejtem a véleményemet. Erről szól a naplóm: ... naplója!
					
					<br/>
					<br/>
					
					Saját honlapot készítettem, amit a saját igényeimre szabtam. A bejegyzéseket különböző szűrők alapján lehet listázni, a felhasználóknak hozzáférési szintjük van, és így tovább.
					<br/>
					Ha tetszenek az írásaim, nagy megtiszteltetésnek érezném a regisztrációdat! Amennyiben <A href="reg.php">regisztrálsz</A>, szívesen rendelkezésedre bocsájtok pár további bejegyzést az érdeklődésedhez igazítva.
					
					<br/>
					<br/>
					
					Végül még fontosnak tartom azt is hangsúlyozni, hogy az oldalt én programoztam le, így hibák előfordulhatnak. Kérlek értesíts, ha bármi rendelleneset észlesz, vagy ötleted van pár további funkcióhoz! Ha tapasztalt webmester vagy, szívesen elfogadnék pár tanácsot weboldalfejlesztéssel kapcsolatban. Minden segítséget előre is köszönök!

					<br/>
					<br/>					
					
					E-mail címem: ...@....hu
					
				</div>
				<div class="jelmagyarazat" style="font-weight: normal;">
					<!-- jelmagyarazat -->
					Jelmagyarázat a bejegyzésekhez:
					<br/>
					<br/>
					<div class="aprobetus">
						- megírt, de még leellenőrizetlen bejegyzések meg vannak csillagozva
						<br/>
						- a jobban sikerült bejegyzéseket vastag betűkkel emelem ki
						<br/>  
						- a bejegyzések egy-egy nagyobb témakörbe tartoznak, amit színekkel jelölök:
					</div>
					<br/>
					<?php
						for($i = 0; $i<count($jelek); $i++){
							print $jelek[$i];
						}
					?>
					
					<br/>
					<span class="aprobetus" style="float:right">
						<A href="info.php">
							bővebb információk
						</A>
					</span>
					<br/>
				
					
				</div>
			</div>
		</div>
		<br/>
		
		<!-- BEJEGYZESSZURO -->	
	
		<div class="keret">
			<div class="kitoltes">
				<div class="balratart">
					<form action="naplo.php#bejegyzes" method="POST">
						
						Szűrő:
					
				
						<span style="font-weight:normal;">
							téma:
						</span>
							
						<select name="tema">
							<option value="0">összes</option>
							<?php
								usort($temakereso, 'hunsort');
								for($i=0; $i<count($temakereso); $i++){
									print "<option value=\"".$temakereso_idk_nevbol[$temakereso[$i]]."\"";
									if(isset($_SESSION["tema"])) if($_SESSION["tema"]==$temakereso_idk_nevbol[$temakereso[$i]]) {
										print " selected";
									}
									print ">".$temakereso[$i]."</option>";
								}
							?>
						</select>
							
						<span style="font-weight:normal;">
							címke:
						</span>
						
						<select name="cimke">
							<option value="0">összes</option>
							<?php
								usort($cimkekereso_nevek, 'hunsort');
								for($i=0; $i<count($cimkekereso_nevek); $i++){
									print "<option value=\"".$cimkekereso_idk_nevbol[$cimkekereso_nevek[$i]]."\"";
									if(isset($_SESSION["cimke"])) if($_SESSION["cimke"]==$cimkekereso_idk_nevbol[$cimkekereso_nevek[$i]]){
										print " selected";
									}
									print ">".$cimkekereso_nevek[$i]."</option>";
								}
							?>
						</select>
						
						<span style="font-weight:normal;">
							minőség:
						</span>
						
						<select name="ajanlott">
							<option value="0" <?php if($_SESSION["ajanlott"]==0) { print " selected"; }?>>összes</option>
							<option value="1" <?php if($_SESSION["ajanlott"]==1) { print " selected"; }?>>csak ajánlottak</option>
						</select>
						<!--<input type="checkbox" id="ajanlott" name="ajanlott" value="true" <?php if(isset($_POST["ajanlott"])) { print " checked"; }?>/>-->
	
						<input type="submit" name="szures" value="Szűrés">

					</form>
				</div>
				<div class="jobbratart">
					<form action="naplo.php#bejegyzes" method="POST">
						<input type="hidden" name="cimke" value="0">
						<input type="hidden" name="tema" value="0">
						<input type="hidden" name="ajanlott" value="0">
						<input type="submit" name="osszes" value="Összes">
					</form>
				</div>
			</div>
		</div>
		
		<!-- BEJEGYZESLISTA -->	
		
		<a name="bejegyzes" id="bejegyzes"><br/></a>
		
		<div class="torzs">
			<div class="lista">
				<div class="keret">
					<div class="kitoltes">
						Bejegyzések
					</div>
					<div class="belsoszegely">
						<?php 

						if(!empty($lista_sorszamok))	{
							sort($lista_sorszamok);

							$ev=-1;
							$fejezet=-1;
							$evek=$lista_ev_sorszambol[$lista_sorszamok[count($lista_sorszamok)-1]];
							
							for($i=count($lista_sorszamok)-1; $i>=0; $i--){
							
								$j=$lista_sorszamok[$i];
							
								if($ev!=$lista_ev_sorszambol[$j]){
									$ev=$lista_ev_sorszambol[$j];
									print "<br/>";
									print "<b><a name=\"bejegyzes\" id=\"".$ev."\">".$ev.". Év tartalma </a></b>";
									print "<br/>";
								}
								if($fejezet!=$lista_fejezet_sorszambol[$j]){
									$fejezet=$lista_fejezet_sorszambol[$j];
									print "<br/>".$fejezet.". fejezet<br/><br/>";
								}
								print $lista_bejegyzes_sorszambol[$j];
							}
						}
						?>		
					</div>
				</div>
			</div>
			
			<!-- KIVALASZTOTT BEJEGYZES -->
			
			<div class="bejegyzes">		
				<?php 
					// cim datum tartalom es cimer tablazatba rendezese
					if(isset($_REQUEST["bejegyzes"]) AND $bejegyzes_hozzaferesi_szintje<=$_SESSION["felhasznalo_hozzaferesi_szintje"]){ print
						"<div class=\"keret\">
							<div class=\"kitoltes\"> '".$cim." ".$datum."' című bejegyzés összefoglalása ";
							
							if(count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"])
							{
								print "<A href=\"szerkesztes.php?bejegyzes=".$_REQUEST["bejegyzes"]."\">[szerkesztés]<A/>";
							}
							print"</div>
							<div class=\"tartalom\">
								<span style=\"font-weight: bold;\">
									Tartalom:
								</span>
								<br/>
								<br/>
								".$tartalom."
							</div>";

								print"
								<div class=\"cimeressor\">
									<IMG style=\"width: 250px; float: right;\" SRC=\"cimer".$bejegyzes_ev.".jpg\">
									<div class=\"cimkek\">	<div class=\"belsoszegely\">
										<span style=\"font-weight: bold;\">
											Címkék:
										</span>
										<br/>
										<br/>";
										// cimkek felsorolasa
										for($i=0; $i<count($cimkek); $i++){
											print $cimkek[$i];
										}
										for($i=0; $i<count($cimkek2); $i++){
			
											print "- ".$cimkek2[$i]."<br/>";

										}
										print"
									</div>
								</div>
									
							</div>
							
							</div>
							
							<br/>
							
							<div class=\"keret\">
								<div class=\"kitoltes\">".$cim." ".$datum;
								
								if(count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"]) print " <A href=\"szerkesztes.php?bejegyzes=".$_REQUEST["bejegyzes"]."\">[szerkesztés]<A/>";
								
								print "</div>
								<div class=\"bejegyzesszoveg\">";
									// szovegreszek kiirasa
									
										print $szovegreszek;
									
									print"
								</div>
							</div>
							<br/>
						";
					}
					
					// HOZZASZOLAS MEZOJE
					
					if($_SESSION["felhasznalo_hozzaferesi_szintje"]>$hozzaferes_szintjei_idbol[1]){


					$tabla_hozzaszolas = "SELECT utolso_hozzaszolas FROM regisztraciok WHERE nev=\"".$_SESSION["felhasznalo"]."\" LIMIT 1";
						if ($result_hozzaszolas = $conn->query($tabla_hozzaszolas)) {
							$row_hozzaszolas = $result_hozzaszolas->fetch_assoc();
							
							// ma meg nem kuldott uzenetet
							if($row_hozzaszolas["utolso_hozzaszolas"]!=date("Y-m-d")){

									print
									"<div class=\"keret\">
										<div class=\"kitoltes\">
											Küldesz üzenetet?
										</div>
										<div class=\"bejegyzesszoveg\">
											<div class=\"aprobetus\">
												Észrevételed van? Hibát szeretnél jelenteni? Egy ötletedet szeretnéd megosztani a weboldallal kapcsolatban? Esetleg egy bejegyzéshez szólnál hozzá, véleményedet fejtenéd ki egy témában? Itt lehetőség nyílik számodra <span style=\"font-weight:bold;\">naponta egyszer, legfeljebb 1000 karakter terjedelemben</span> ...nak üzenetet hagyni! Az esetleges válasz az általad megadott e-mail címedre fog érkezni.
												<br/>
												<br/>
												<form action=\"naplo.php\" method=\"POST\">
													<center>
														<textarea name=\"hozzaszolas\" rows=\"5\" cols=\"55\"></textarea>
														<br/>
														<input type=\"submit\" value=\"Küldés\"/>
													</center>
												</form>
											</div>
										</div>
									</div>";
								}
							}
						}
						$conn->close();
					?>
			</div>
		</div>
	</div>
	<br/>
	<br/>
	<br/>

</body>
</html>