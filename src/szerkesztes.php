<?php

/////////////// MINDENEK ELOTT ///////////////
		
	include_once("mindenelott.php");
	
	if(empty($_SESSION["felhasznalo_hozzaferesi_szintje"]) OR count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"]) die("Nincs jogosultságod az oldal megtekintéséhez!");

	include_once("adatbazisnyitas.php");
	
//////////////// ADATOK LEREHOZASA ES FELDOLGOZASA ///////////////////

	// OSSZES VALTOZO

	$sorszam="";
	$cim="";
	$datum="";
	$ev="";
	$fejezet="";
	$temak=array();
	$tema_id="1";
	$osszcimkek=array();
	$cimkek=array();
	$cimkek_id=array();
	$leellenorzott=0;
	$ajanlott=0;
	$tartalom="";
	$bejegyzes_hozzaferesi_szintje="1";
	$szovegreszek=array();
	$szoveg_hozzaferes = array();
	$hozzaferes_nevei_szintbol=array();
	$hozzaferes_id_nevbol=array();
	$hozzaferes_szintjei_idbol=array();

	// ALTALANOSAN ERVENYES ADATOK
	
	// temak
	
	$tabla="temak";
	$oszlop="nev";
	
	$eredmeny = array();
	$parancs = "SELECT `id`, `".$oszlop."` FROM ".$tabla."";
	if ($result = $conn->query($parancs)) {
		while($row = $result->fetch_assoc()){	
			$eredmeny[$row["id"]] = $row[$oszlop];
		}
		$result->free();
	}
	$temak= $eredmeny;
	
	
	// cimkek
	$tabla="cimkek";
	$oszlop="nev";
	
	$eredmeny = array();
	$parancs = "SELECT `id`, `".$oszlop."` FROM ".$tabla."";
	if ($result = $conn->query($parancs)) {
		while($row = $result->fetch_assoc()){	
			$eredmeny[$row["id"]] = $row[$oszlop];
		}
		$result->free();
	}
	$osszcimkek= $eredmeny;
	
	
	// hozzaferes
	$tabla_hozzaferes = "SELECT * FROM hozzaferes";
	if ($result_hozzaferes = $conn->query($tabla_hozzaferes)) {
		while($row_hozzaferes = $result_hozzaferes->fetch_assoc()){
			$hozzaferes_nevei_szintbol[$row_hozzaferes["szint"]] = $row_hozzaferes["nev"];
			$hozzaferes_id_nevbol[$hozzaferes_nevei_szintbol[$row_hozzaferes["szint"]]]=$row_hozzaferes["id"];
			$hozzaferes_szintjei_idbol[$row_hozzaferes["id"]]=$row_hozzaferes["szint"];
		}
		$result_hozzaferes->free();
	}


	// UJ BEJEGYZES
	
	if(empty($_REQUEST["bejegyzes"]) AND count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"] AND !isset($_POST["sorszam"])){
		$sysmsg = "uj bejegyzes";
		// uj sorszam
		$tabla = "SELECT count(*) FROM bejegyzesek";
		if ($result = $conn->query($tabla)){
			$row = $result->fetch_row();
			$sorszam = $row[0] - 11;
			$result->free();
		}
		
		// uj datum
		$datum = date("Y-m-d");
		
		// uj ev uj fejezet legfrissebb bejegyzes alapjan
		$tabla = "SELECT * FROM bejegyzesek WHERE sorszam=\"".($sorszam - 1)."\"";
		if ($result = $conn->query($tabla)){
			$row = $result->fetch_assoc();
			$ev = $row["ev"];
			$fejezet = $row["fejezet"];
			$result->free();
		}
		
		// nem kitoltott reszek
		$ellenorzott="0";
		$szovegreszek[]="";
		
		// uj bejegyzes elmentese
		$tabla = "INSERT INTO `bejegyzesek`(`sorszam`, `cim`, `datum`, `tartalom`, `tema_id`, `ev`, `fejezet`, `hozzaferesi_szint`, `leellenorzott`, `ajanlott`) VALUES (\"".$sorszam."\", \"".$cim."\", \"".$datum."\", \"".$tartalom."\", \"".$tema_id."\", \"".$ev."\", \"".$fejezet."\", \"".$bejegyzes_hozzaferesi_szintje."\", \"".$leellenorzott."\", \"".$ajanlott."\")";
		mysqli_select_db($conn, "naplo");
		$retval = mysqli_query($conn, $tabla);
		if(! $retval )
		{
		  die('A bejegyzés mentése technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
		}
	}
	
	
	// BEJEGYZES MENTESE (+HELYREALLITAS)
	
	else if(isset($_REQUEST["bejegyzes"]) AND count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"] AND isset($_POST["sorszam"])){
	
		// valtozok helyreallitasa POSTbol
		
		// egyszerubbek
		if(!empty($_POST["szovegszam"])) $szovegszam = $_POST["szovegszam"];
		else $szovegszam = 0; 
		$sorszam=mysql_escape_string($_POST["sorszam"]);
		$cim=mysql_escape_string($_POST["cim"]);
		$datum=mysql_escape_string($_POST["datum"]);
		$ev=mysql_escape_string($_POST["ev"]);
		$fejezet=mysql_escape_string($_POST["fejezet"]);		
		$tema_id=mysql_escape_string($_POST["tema"]);
		$tartalom=$_POST["tartalom"];
		$tartalom = str_replace("\\","",$tartalom);
		// ellenorzott
		if(!empty($_POST["leellenorzott"]))$leellenorzott=1;
		else $leellenorzott=0;
		// ajanlott
		if(!empty($_POST["ajanlott"]))$ajanlott=1;
		else $ajanlott=0;
		// cimkek (kivalasztott)
		for($i=1, $kiirt=0; $kiirt<count($osszcimkek); $i++){
			if(!empty($osszcimkek[$i])){
				if(!empty($_POST["cimke".$i])){
					$cimkek[$i]=$osszcimkek[$i];
					$cimkek_id[]=$i;
				}
				$kiirt++;
			}
		}
		// szovegreszek
		for($i=0, $j=0; $i<$_POST["szovegszam"]; $i++){
			if(!empty($_POST["szovegresz".$i])){
				$szovegreszek[]= str_replace("\\","",$_POST["szovegresz".$i]);
				
			}
		}
		// hozzaferesi szintek (bejegyzes + szovegreszek)
		for($i=0, $bejegyzes_hozzaferesi_szintje=count($hozzaferes_nevei_szintbol); !empty($_POST["hozzaferes".$i]); $i++){
			$szoveg_hozzaferes[$i]=$_POST["hozzaferes".$i];
			if($bejegyzes_hozzaferesi_szintje>$szoveg_hozzaferes[$i]){
				$bejegyzes_hozzaferesi_szintje=$szoveg_hozzaferes[$i];
			};
		}
		
		// mentes
		
		// bejegyzesadatok frissitese	
		$tabla = "UPDATE `bejegyzesek` SET `cim`=\"".$cim."\", `datum`=\"".$datum."\", `tartalom`=\"".mysql_escape_string($tartalom)."\", `tema_id`=\"".$tema_id."\", `ev`=\"".$ev."\", `fejezet`=\"".$fejezet."\", `hozzaferesi_szint`=\"".$hozzaferes_id_nevbol[$hozzaferes_nevei_szintbol[$bejegyzes_hozzaferesi_szintje]]."\", `leellenorzott`=\"".$leellenorzott."\", ajanlott=\"".$ajanlott."\" WHERE `sorszam`=\"".$sorszam."\"";

		mysqli_select_db($conn, "naplo");
		$retval = mysqli_query($conn, $tabla);
		if(! $retval )
		{
		  die('A bejegyzés mentése technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
		}
		// regi szovegek torlese
		$tabla = "DELETE FROM `szovegek` WHERE bejegyzes_sorszam=\"".$sorszam."\"";
		mysqli_select_db($conn, "naplo");
		$retval = mysqli_query($conn, $tabla);
		if(! $retval )
		{
		  die('Adatok nem lettek törölve: ' . mysql_error());
		}
		// uj szovegek hozzaadasa
		for($i=0; $i<count($szovegreszek); $i++){
			$j=$i + 1;
			$tabla = "INSERT INTO `szovegek`(`bejegyzes_sorszam`, `bejegyzesbeli_sorszam`, `hozzaferesi_szint`, `szoveg`) VALUES (\"".$sorszam."\",\"".$j."\",\"".$hozzaferes_id_nevbol[$hozzaferes_nevei_szintbol[$szoveg_hozzaferes[$i]]]."\",\"".mysql_escape_string($szovegreszek[$i])."\")";

			mysqli_select_db($conn, "naplo");
			$retval = mysqli_query($conn, $tabla);
			if(! $retval )
			{
			  die('A bejegyzés mentése technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
			}
		}
		// regi cimkek torlese
		$tabla = "DELETE FROM `kapcs_bej_cim` WHERE bejegyzes_sorszam=\"".$sorszam."\"";
		mysqli_select_db($conn, "naplo");
		$retval = mysqli_query($conn, $tabla);
		if(! $retval )
		{
		  die('Adatok nem lettek törölve: ' . mysql_error());
		}
		// uj cimkek hozzaadasa
		for($i=0; $i<count($cimkek_id); $i++){
			$tabla = "INSERT INTO `kapcs_bej_cim`(`bejegyzes_sorszam`, `cimke_id`) VALUES (\"".$sorszam."\",\"".$cimkek_id[$i]."\")";
			mysqli_select_db($conn, "naplo");
			$retval = mysqli_query($conn, $tabla);
			if(! $retval )
			{
			  die('A bejegyzés mentése technikai okok miatt nem hajtható végre: ' . mysqli_error($conn));
			}
		}
		
		// plusz szovegdobozok hozzaadasa
		
		for($i=0; $i<$_POST["pluszbox"]; $i++)
		{
			$szovegreszek[]="";
		}
	}
	
	
	// REGI BEJEGYZES MODOSITASA
	
	else if(isset($_REQUEST["bejegyzes"]) AND count($hozzaferes_nevei_szintbol)==$_SESSION["felhasznalo_hozzaferesi_szintje"] AND !isset($_POST["nev"])){
		
		// valtozok kiolvasasa es helyreallitasa
		
		$sorszam = $_REQUEST["bejegyzes"];
		$tabla_bejegyzes = "SELECT * FROM bejegyzesek WHERE sorszam=\"".$_REQUEST["bejegyzes"]."\"";
		if ($result_bejegyzes = $conn->query($tabla_bejegyzes)){
			$row_bejegyzes = $result_bejegyzes->fetch_assoc();	
		
			// valtozok biztositasa
			$fejezet = $row_bejegyzes["fejezet"];
			$tema_id = $row_bejegyzes["tema_id"];
			$cim = $row_bejegyzes["cim"];
			$datum = $row_bejegyzes["datum"];
			$ev = $row_bejegyzes["ev"];
			$tartalom = $row_bejegyzes["tartalom"];
			$tartalom = str_replace("\\","",$tartalom);
			$bejegyzes_hozzaferesi_szintje = $hozzaferes_szintjei_idbol[$row_bejegyzes["hozzaferesi_szint"]];
			$leellenorzott = $row_bejegyzes["leellenorzott"];
			$ajanlott = $row_bejegyzes["ajanlott"];
			
			// cimkek azonositoinak lekerese
			$tabla_cimkeid = "SELECT * FROM kapcs_bej_cim WHERE bejegyzes_sorszam=\"".$_REQUEST["bejegyzes"]."\"";
			if ($result_cimkeid = $conn->query($tabla_cimkeid)) {
				while ($row_cimkeid = $result_cimkeid->fetch_assoc()) {
					$tabla_cimkenev = "SELECT nev FROM cimkek WHERE id=\"".$row_cimkeid["cimke_id"]."\"";
					if ($result_cimkenev = $conn->query($tabla_cimkenev)) {
						$row_cimkenev = $result_cimkenev->fetch_assoc();
						$cimkek[$row_cimkeid["cimke_id"]] = $row_cimkenev["nev"];
						
						$result_cimkenev->free();
					}
				}
				$result_cimkeid->free();
			}
		
			// szovegreszek kigyujtese
			for($i=1; ; $i++){
				$tabla_szoveg = "SELECT * FROM szovegek WHERE bejegyzes_sorszam=\"".$_REQUEST["bejegyzes"]."\" AND bejegyzesbeli_sorszam=\"".$i."\"";
				if ($result_szoveg = $conn->query($tabla_szoveg)) {
				
					if($row_szoveg = $result_szoveg->fetch_assoc()){
						$szovegreszek[] = str_replace("\\","",$row_szoveg["szoveg"]);
						$szoveg_hozzaferes[] = $hozzaferes_szintjei_idbol[$row_szoveg["hozzaferesi_szint"]];
					}
					else break;
					$result_szoveg->free();
				}
				
			}
			$result_bejegyzes->free();
		}
	}
	else $sysmsg="Nincs jogosultságod az oldal megtekintéséhez!";
	
	
////////////////////// KAPCSOLAT BONTASA AZ ADATBAZISSAL ////////////////////////
	$conn->close();

?>


<!-- ////////////////////// A HONLAP FEJLECE ///////////////////////// -->
<html>
 
<head>
	
	<?php
		include_once("fejlecextra.php");
	?>
	
	<title>Szerkesztés</title>
	
</head>


<!-- /////////////////////// A HONLAP TORZSE //////////////////////// -->

<body>
	<div class="focim">... naplója</div>
	
	<br/>
	<div class="globalcontainer">
		<div class="keret">
			<div class="belsoszegely">
				<form action="szerkesztes.php?bejegyzes=<?php print $sorszam; ?>" method="POST">
					<?php //print $sysmsg; ?>
					Sorszám:&nbsp;<input type="text" name="sorszam" value="<?php print $sorszam; ?>"/><br/>
					Cím:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="cim" value="<?php print $cim; ?>"/><br/>
					Dátum:&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="datum" value="<?php print $datum; ?>"/><br/>
					Év:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="ev" value="<?php print $ev; ?>"/><br/>
					Fejezet:&nbsp;&nbsp;<input type="text" name="fejezet" value="<?php print $fejezet; ?>"/><br/>
					Téma:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					
					<select name="tema">
					<?php	
						for($i=1; $i<=count($temak); $i++){
							if(!empty($temak[$i])){
								print "<option value=\"".$i."\"";
								if($i==$tema_id) { print " selected"; }
								print " >".$temak[$i]."</option>";
							}
						}
					?>
					</select>
					<br/>
					<br/>
					
					Címkék:
					<span class="aprobetus">
					<?php 
						for($i=0, $j=0; $j<count($osszcimkek); $i++){
							
							if(!empty($osszcimkek[$i])){
								print $osszcimkek[$i].": <input type=\"checkbox\" name=\"cimke".$i."\" value=\"true\" ";
								if(!empty($cimkek[$i])) { print "checked";}
								print " >&nbsp;&nbsp;&nbsp;";
								$j++;
							}
						}
					?>
					</span>
					<br/>
					<br/>
					
					Leellenőrzött: <input type="checkbox" id="leellenorzott" name="leellenorzott" value="true" <?php if($leellenorzott==true) print " checked"; ?>/>
					<br/>
					
					Ajánlott: <input type="checkbox" id="ajanlott" name="ajanlott" value="true" <?php if($ajanlott==true) print " checked"; ?>/>
					<br/>
					<br/>
					
					Tartalom:<br/><textarea name="tartalom" rows="5" cols="90"><?php print $tartalom; ?></textarea>
					<br/>
					<br/>
					
					Bejegyzés:
					<?php
						// szovegreszek kiirasa
						for($i=0; $i<count($szovegreszek); $i++){
							print "<br/><br/>";
							print "<span class=\"aprobetus\">Szövegrész ".($i + 1).":</span><br/>";
							print "<textarea name=\"szovegresz".$i."\" rows=\"10\" cols=\"90\">";
							print $szovegreszek[$i];
							print "</textarea>";
							print "<br/><span class=\"aprobetus\">Hozzáférés szintje: </span>";
							print "<select name=\"hozzaferes".$i."\">";
						
							// hozzaferesi szintek a szovegreszekhez
							for($j=1; $j<=count($hozzaferes_nevei_szintbol); $j++){
								print "<option value=\"".$j."\"";
								if(!empty($szoveg_hozzaferes[$i])) if($j == $szoveg_hozzaferes[$i]) { print "selected"; }
								print " >".$hozzaferes_nevei_szintbol[$j]."</option>";
							}
							print "</select>";
							print "<input type=\"hidden\" name=\"szovegszam\" value=\"".count($szovegreszek)."\">";
						}
					?>				
					<br/>
					<br/>
					<br/>
					
					Üres szövegdobozok hozzáfűzése: 
					<input type="text" name="pluszbox" value="0"/>
					<br/>
					<br/>
					
					<input type="submit" name="mentes" value="Mentés">
				</form>
				<br/>
				<br/>
				
				<center>
					<A href="naplo.php">
						Vissza a naplóhoz
					</A>
				</center>
				
			</div>
		</div>
	</div>

</body>
</html>