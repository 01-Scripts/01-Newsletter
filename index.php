<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Modul-Startseite innerhalb des 01ACP
	#fv.132#
*/
?>

<div class="acp_startbox">
<p align="center"><b class="yellow"><?PHP echo $module[$modul]['instname']; ?></b></p>

<div class="acp_innerbox">
	<h4>Informationen</h4>
	<p>
	<b>Modul-Version:</b> <?PHP echo $module[$modul]['version']; ?><br /><br />

	<b>Verschickte Newsletter:</b> <?PHP list($sentmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['archiv']." WHERE art='a'")->fetch_array(MYSQLI_NUM); echo $sentmenge; ?><br />
	<br />
<?php if($settings['usecats'] == 1){ ?>
	<b>Kategorien:</b> <?PHP list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM); echo $catmenge; ?><br />
<?php } ?>
	<b>E-Mail-Adressen:</b> <?PHP list($emailmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['emailadds']." WHERE acode = '0'")->fetch_array(MYSQLI_NUM); echo $emailmenge; ?>
	</p>
	
	<form action="_loader.php" method="get">
		<p><input type="text" name="search" value="Archiv durchsuchen" size="20" onfocus="clearField(this);" onblur="checkField(this);" class="input_search" /> <input type="submit" value="Suchen &raquo;" class="input" />
		<input type="hidden" name="action" value="archiv" />
		<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
		<input type="hidden" name="loadpage" value="newsletter" /></p>
	</form>
</div>

<div class="acp_innerbox">
	<h4>Verschickte Newsletter</h4>
	<?php 
	$list = $mysqli->query("SELECT id,utimestamp,betreff,mailinhalt FROM ".$mysql_tables['archiv']." WHERE art='a' ORDER BY utimestamp DESC LIMIT 4");
	while($row = $list->fetch_assoc()){
		echo "<p><b><a href=\"javascript:modulpopup('".$modul."','show_letter','".$row['id']."','','',510,450);\">".$row['betreff']."</a></b><br />
		<span class=\"small\"><i>Verschickt am ".date("d.m.Y",$row['utimestamp'])." um ".date("G:i",$row['utimestamp'])."Uhr</i></span><br />
		".substr(strip_tags($row['mailinhalt']),0,100)."...
		</p>";
		}
	?>

</div>

<br />

<?php 
// Nicht-aktivierte E-Mail-Adressen nach x-Tagen löschen
$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE acode != '0' AND timestamp_reg < '".(time()-($deletimer*60*60*24))."'");
?>

</div>