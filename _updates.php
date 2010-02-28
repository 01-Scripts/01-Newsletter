<?PHP
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "1000_zu_1001"){

// Setting-Reihenfolge aktualisieren
mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '5' WHERE `idname` = 'usecats' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '6' WHERE `idname` = 'newslettersignatur' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");

// Neue Einstellungen anlegen:
$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
			('".mysql_real_escape_string($modul)."','0','1','3','versandadresse','Versand-E-Mail-Adresse','','text','50','','','','0','0'),
			('".mysql_real_escape_string($modul)."','0','1','4','versand_altname','Alternativtext für Versandadresse','','text','50','','','','0','0');";
$result = mysql_query($sql_insert) OR die(mysql_error());

mysql_query("UPDATE ".$mysql_tables['module']." SET version = '1.0.0.1' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.0.0.0 nach 1.0.0.1</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.0.0.0 auf Version 1.0.0.1 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Es sind <a href="settings.php?action=settings&modul=<?php echo $modul; ?>">neue Einstellungen vorhanden</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
?>