<?php	
	include_once("mindenelott.php");
	include_once("hunsort.php");
	include_once("adatbazisnyitas.php");	
?>
	
<html>	
	
<head>

	<?php 
	include_once("fejlecextra.php");
	?>	
	<title>Információk</title>
	
</head>

<body>
		
	<div class="focim">
		... naplója
	</div>
	
	<br/>
	<br/>
		
	<div class="globalcontainer">
		<div class="keret">
			<div class="kitoltes">
				Információk
			</div>
			<div class="belsoszegely">			
				<div class="aprobetus">
					Minden bejegyzés a rá legjobban jellemző témakörbe van sorolva, és számos a tartalomra utaló címkével van ellátva. Minden szövegrészhez tartozik egy hozzáférési szint, amelyekből a legkisebb érvényes általánosan a bejegyzésre. Sok jelölés van a naplóban, aminek a jelentése talán kicsit felületes és semmitmondó. Mit jelent az a témakör, hogy 'probléma', vagy az a címke, hogy 'függvény'? Mit takar a 'regisztrálva publikus' hozzáférési szint? Itt sok hasznos infót találhatsz az irományaim rendezésével kapcsolatos apróságokról.
				</div>			
				<br/>
				<br/>
				
				<span style="font-weight: bold; font-size:20;">
					Témakörök
				</span>
				<br/>
				<br/>
				
				<div class="aprobetus">
					A témakörök szerinti színezésnek nálam hagyománya van. Annak idején, amelyik weboldalon a naplóírást elkezdtem, be lehetett állítani pár színt. Jó ötletnek találtam, és használni kezdtem. Viszont én túl következetes ember vagyok ahhoz, hogy a színeket véletlenszerűen válasszam meg, így különböző témakörökkel kezdtem őket azonosítani. Ezt a szokásom továbbra is megtartottam.
					
					Persze nem határozható meg egyértelműen minden bejegyzés témája, de vegyes színezés nincs! Ilyenkor mindig a hozzájuk legjobban illő témakört színét kapják. 
				</div>
				<br/>
				
				<table>
					<?php	
						// temak kiirasa
						$temasor=array();
						$tabla_temak = "SELECT * FROM temak";
						if ($result_temak = $conn->query($tabla_temak)) {
							while($row_temak = $result_temak->fetch_assoc()){	
								$temasor[] ="<!-- ".$row_temak["nev"]." -->
								<tr valign=TOP>
									<td >
										<div style=\"padding-right:20\">
											<span style=\"color: #".$row_temak["hexa_szin"].";\">
												".$row_temak["nev"].": 
											</span>
										</div>
									</td>
									<td>
										<div style=\"padding-bottom: 20;\">
											".$row_temak["leiras"]."
										</div>
									</td>
								</tr>";
							}
							$result_temak->free();
						}
						usort($temasor, "hunsort");
						foreach($temasor as $kiiras) print $kiiras;	
					?>
				</table>
				<br/>
				<br/>
				
				<span style="font-weight: bold; font-size:20;">
					Címkék
				</span>
				<br/>
				<br/>
				
				<div class="aprobetus">
					Naplóírásom első évét lezárva jöttem rá, hogy a bejegyzések sokasága egyre nehezebben áttekinthető. Külön bejegyzés szólt a bejegyzések összefoglalásáról, ahol mindegyik kapott rövid tartalmat és címkéket. Amikor a naplóm az első kezdetleges saját weboldalamra került át, a bejegyzések elengedhetetlen részévé váltak címkék, bár nem sok haszonnal. Most viszont már tudtam szűrőt készíteni, ahol többek között ezek szerint is lehet keresni. A bejegyzések címkéire kattintva a szűrő automatikusan beállítódik, és kikeresi az azonos címkéjű bejegyzéseket!
				</div>
				<br/>
				
				<table>
					<?php	
						// cimkenevek kiirasa			
						$cimkesor=array();
						$tabla_cimkek = "SELECT * FROM cimkek";
						if ($result_cimkek = $conn->query($tabla_cimkek)) {
							
							while($row_cimkek = $result_cimkek->fetch_assoc()){	
								$cimkesor[] ="<!-- ".$row_cimkek["nev"]." -->
								<tr valign=TOP>
									<td>
										<div style=\"padding-right: 20;\">
											".$row_cimkek["nev"].":
										</div>
									</td>
									<td>
										<div style=\"padding-bottom: 20;\">
											".$row_cimkek["leiras"]."
										</div>
									</td>
								</tr>";
							}
							$result_cimkek->free();
						}
						usort($cimkesor, "hunsort");
						foreach($cimkesor as $kiiras) print $kiiras;	
					?>
				</table>
				<br/>
				<br/>
				
				<span style="font-weight: bold; font-size:20;">
					Publikussági/hozzáférési szintek
				</span>
				<br/>
				<br/>
				
				<div class="aprobetus">
					A hozzáférési szint a legújabb jelenség a naplómban. Mióta a statikus weboldalról áttértem a dinamikusra, lehetőségek százai nyíltak meg előttem. Fontos számomra, hogy az írásaim ne kerüljenek illetéktelen kezekbe, ezért minden szövegrész egyedi elbírálás alá került publikusság tekintetében. A bejegyzés hozzáférési szintje a benne található legpublikusabb szövegrész szintje. Az olvasó a privátabb szakaszokon "[...]" jelet fog találni. Ezzel a kiragadásos módszerrel a bejegyzések nagyobb számban váltak elérhetővé. A szintek listázása után megtalálod a saját hozzáférési szinted alapján elérhető legprivátabb tartalmak megnevezését.
				</div>
				<br/>
				
				<table>
					<?php	
						// hozzáférési szintek kiirasa
						$hozzaferes_nevei_szintbol=array();
						$hozzaferes_id_nevbol=array();
						$hozzaferes_szintjei_idbol=array();
						// konvertálás azonosítóból valódi szintté
						$tabla_hozzaferes = "SELECT * FROM hozzaferes";
						if ($result_hozzaferes = $conn->query($tabla_hozzaferes)) {
							while($row_hozzaferes = $result_hozzaferes->fetch_assoc()){
								$hozzaferes_nevei_szintbol[$row_hozzaferes["szint"]] = $row_hozzaferes["nev"];
								$hozzaferes_leirasai_szintbol[$row_hozzaferes["szint"]] = $row_hozzaferes["leiras"];
								$hozzaferes_id_nevbol[$hozzaferes_nevei_szintbol[$row_hozzaferes["szint"]]]=$row_hozzaferes["id"];
								$hozzaferes_szintjei_idbol[$row_hozzaferes["id"]]=$row_hozzaferes["szint"];
								$hozzaferes_kiknek_szintbol[$row_hozzaferes["szint"]]=$row_hozzaferes["kiknek"];
							}
							$result_hozzaferes->free();
						}
						for($i=1; $i<=count($hozzaferes_szintjei_idbol); $i++){
							print "
							<tr valign=TOP>
								<td>
									<div style=\"padding-right: 20;\">
										".$i.".
									</div>
								</td>
								<td>
									<div style=\"padding-bottom: 20;\">
									".$hozzaferes_nevei_szintbol[$i].":
									</div>
								</td>
								<td>
									<div style=\"padding-bottom: 20;\">
										".$hozzaferes_leirasai_szintbol[$i]."
									</div>
								</td>
								<td>
									<div style=\"padding-bottom: 20;\">
										".$hozzaferes_kiknek_szintbol[$i]."
									</div>
								</td>
							</tr>";
						}
					?>
				</table>
				<br/>
				
				<span style="font-weight: bold">
					A te hozzérfésed:
				</span>
				<?php
					print $hozzaferes_nevei_szintbol[$_SESSION["felhasznalo_hozzaferesi_szintje"]];
				?> tartalmak
				<br/>
				<br/>
				<br/>
				<br/>
				
				<center>
					<A href="naplo.php">
						vissza a naplóhoz
					<A>
				</center>
				<br/>
				
			</div>
		</div>
	</div>
	<?php $conn->close(); ?>		
</body>
</html>