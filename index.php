<?PHP
/*
	01-Newsletter - Copyright 2009-2010 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.1001#
*/
?>

<div class="acp_startbox">
<p align="center"><b class="yellow"><?PHP echo $module[$modul]['instname']; ?></b></p>

<div class="acp_innerbox">
	<h4>Informationen</h4>
	<p>
	<b>Modul-Version:</b> <?PHP echo $module[$modul]['version']; ?><br /><br />
	
	<b>Verschickte Newsletter:</b> <?PHP list($sentmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['archiv']." WHERE art='a'")); echo $sentmenge; ?><br />
	<br />
<?php if($settings['usecats'] == 1){ ?>
	<b>Kategorien:</b> <?PHP list($catmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")); echo $catmenge; ?><br />
<?php } ?>
	<b>E-Mail-Adressen:</b> <?PHP list($emailmenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['emailadds']." WHERE acode = '0'")); echo $emailmenge; ?>
	</p>
</div>

<div class="acp_innerbox">
	<h4>Verschickte Newsletter</h4>
	<?php 
	$list = mysql_query("SELECT id,timestamp,betreff,mailinhalt FROM ".$mysql_tables['archiv']." WHERE art='a' ORDER BY timestamp DESC LIMIT 4");
	while($row = mysql_fetch_array($list)){
		echo "<p><b><a href=\"javascript:modulpopup('".$modul."','show_letter','".$row['id']."','','',510,450);\">".stripslashes($row['betreff'])."</a></b><br />
		<span class=\"small\"><i>Verschickt am ".date("d.m.Y",$row['timestamp'])." um ".date("G:i",$row['timestamp'])."Uhr</i></span><br />
		".substr(stripslashes(strip_tags($row['mailinhalt'])),0,100)."...
		</p>";
		}
	?>

</div>

<br />

<?php 
// Nicht-aktivierte E-Mail-Adressen nach x-Tagen löschen
mysql_query("DELETE FROM ".$mysql_tables['emailadds']." WHERE acode != '0' AND timestamp_reg < '".(time()-($deletimer*60*60*24))."'");
?>

</div>