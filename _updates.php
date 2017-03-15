<?PHP
// 1.3.2 --> 1.4.0
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "132_zu_140"){

	
	// Versionsnummer aktualisieren
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.4.0' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.3.2 nach 1.4.0</h2>

<div class="meldung_erfolg">
	Das Update von Version 1.3.2 auf Version 1.4.0 wurde erfolgreich durchgef&uuml;hrt.<br />
	<br />
	<br />

	<b>Mit dem Update wurde unter anderem folgendes verbessert:</b>
	<ul>

		<li>Diverse Fehler behoben. Siehe <a href="http://www.01-scripts.de/down/01newsletter_changelog.txt" target="_blank">changelog.txt</a></li>
	</ul>
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</div>
<?PHP
}
// 1.3.1 --> 1.3.2
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "131_zu_132"){

	// #745 - CSS-Code aus Datenbank/Settings in Datei auslagern
	$mysqli->query("UPDATE ".$mysql_tables['settings']." SET 
	`exp` = 'Geben Sie einen absoluten Pfad inkl. <b>http://</b> zu einer externen CSS-Datei an.\nIst dieses Feld leer, wird die Datei templates/style.css aus dem Modulverzeichnis verwendet.'
	WHERE `modul` = '".$mysqli->escape_string($modul)."' AND `idname` = 'extern_css' LIMIT 1");
	$mysqli->query("DELETE FROM ".$mysql_tables['settings']." WHERE `modul` = '".$mysqli->escape_string($modul)."' AND `idname` = 'csscode' LIMIT 1");

	// #339 Versand per SMTP
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide)
	            VALUES 
	            ('".$mysqli->escape_string($modul)."', 1, 3, 5, 'smtp_nl_settings', 'Newsletter Versandeinstellungen', NULL , NULL , NULL , NULL , NULL , NULL ,0,0),
	            ('".$mysqli->escape_string($modul)."', 0, 3, 1, 'smtp_nl', 'Wie m&ouml;chten Sie ausgehende Newsletter versenden?','','Standardversand per PHP mail()-Befehl|SMTP-Versand (SMTP-Server aus 01ACP Einstellungen)|SMTP-Versand (Nachfolgend angegebener SMTP-Server)','php|smtp_01acp|smtp_01newsletter','','php','php',0,0),
	            ('".$mysqli->escape_string($modul)."', 0, 3, 2, 'smtp_nl_host', 'SMTP-Server','','text','50','','','',0,0),
	            ('".$mysqli->escape_string($modul)."', 0, 3, 3, 'smtp_nl_port', 'SMTP-Server TCP Port','','text','50','','587','587',0,0),
	            ('".$mysqli->escape_string($modul)."', 0, 3, 4, 'smtp_nl_username', 'SMTP Username','','text','50','','','',0,0),
	            ('".$mysqli->escape_string($modul)."', 0, 3, 5, 'smtp_nl_password', 'SMTP Password','Das SMTP Passwort wird aus technischen Gr&uuml;nden unverschl&uuml;sselt gespeichert.','text','50','','','',0,0);";
	$mysqli->query($sql_insert) OR die($mysqli->error);
	
	// Versionsnummer aktualisieren
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.3.2' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.3.1 nach 1.3.2</h2>

<div class="meldung_erfolg">
	Das Update von Version 1.3.1 auf Version 1.3.2 wurde erfolgreich durchgef&uuml;hrt.<br />
	<br />
	<b>Achtung:</b><br />
	Mit diesem Update wurde der CSS-Code zur Gestaltung des 01-Newsletter in eine separate Datei ausgelagert
	und kann nicht mehr im 01ACP in den Einstellungen direkt bearbeitet werden.<br />
	Der CSS-Code befindet sich nun in der Datei <i>01module/01newsletter/templates/style.css</i> und kann
	dort bearbeitet werden.<br />
	<br />

	<b>Mit dem Update wurde unter anderem folgendes verbessert:</b>
	<ul>
		<li><b>Versand per SMTP hinzugef&uuml;gt</b> (neue Einstellungen erforderlich)</li>
		<li>Seite zum Erstellen eines neuen Newsletters &uuml;berarbeitet</li>
		<li>Archivierten Newsletter mit einem Klick als Vorlage f&uuml;r einen neuen Newsletter verwenden</li>
		<li>Diverse Fehler behoben. Siehe <a href="http://www.01-scripts.de/down/01newsletter_changelog.txt" target="_blank">changelog.txt</a></li>
	</ul>
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</div>
<?PHP
}
// 1.3.0 --> 1.3.1
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "130_zu_131"){

	// Spaltenname 'timestamp' umbenennen in 'utimestamp' #694
	$mysqli->query("ALTER TABLE ".$mysql_tables['archiv']." CHANGE `timestamp` `utimestamp` INT( 15 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE ".$mysql_tables['temp_table']." CHANGE `timestamp` `utimestamp` INT( 15 ) NOT NULL DEFAULT '0'");

	// Versionsnummer aktualisieren
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.3.1' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.3.0 nach 1.3.1</h2>

<div class="meldung_erfolg">
	Das Update von Version 1.3.0 auf Version 1.3.1 wurde erfolgreich durchgef&uuml;hrt.<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</div>
<?PHP
}
// 1.2.0 --> 1.3.0
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "120_zu_130"){
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO `".$mysql_tables['settings']."` (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".$mysqli->escape_string($modul)."','0','1','9','use_cronjob','Newsletter per Cronjob versenden?','Legen Sie dazu einen regelm&auml;&szlig;igen Cronjob auf die Datei <i>01scripts/01module/01newsletter/_cronjob.php</i> an.<br /><a href=\"http://cronjob.01-scripts.de\" target=\"_blank\">Weitere Informationen zum Thema</a>','Ja|Nein','1|0','','0','0','0','0');";
	$mysqli->query($sql_insert) OR die($mysqli->error);
	
	// Setting-Reihenfolge aktualisieren
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '10' WHERE `idname` = 'newslettersignatur' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '11' WHERE `idname` = 'use_nutzungsbedingungen' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '12' WHERE `idname` = 'nutzungsbedingungen' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	
	// Neue Datenbanktabelle für Versand per _cronjob.php
	$mysqli->query("CREATE TABLE IF NOT EXISTS `".$mysql_tables['temp_table']."` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `timestamp` int(10) NOT NULL,
	  `message_id` int(10) NOT NULL,
	  `email` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.3.0' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.2.0 nach 1.3.0</h2>

<div class="meldung_erfolg">
	Das Update von Version 1.2.0 auf Version 1.3.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen Newsletter nun auch automatisiert per Cronjob verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&amp;modul=<?php echo $modul; ?>">neue Einstellung</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</div>
<?PHP
	}
// 1.1.0.0 --> 1.2.0
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1100_zu_120"){
	// Setting-Reihenfolge aktualisieren
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '9' WHERE `idname` = 'newslettersignatur' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '10' WHERE `idname` = 'use_nutzungsbedingungen' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '11' WHERE `idname` = 'nutzungsbedingungen' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".$mysqli->escape_string($modul)."','0','1','8','use_html','HTML-Newsletter versenden?','','Ja|Nein','1|0','','0','0','0','0');";
	$result = $mysqli->query($sql_insert) OR die($mysqli->error);
	
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.2.0' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.1.0.0 nach 1.2.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.1.0.0 auf Version 1.2.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen auch Newsletter mit HTML verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&amp;modul=<?php echo $modul; ?>">neue Einstellung</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
// 1.0.0.1 --> 1.1.0.0
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1001_zu_1100"){
	// Setting-Reihenfolge aktualisieren
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '6' WHERE `idname` = 'usecats' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '8' WHERE `idname` = 'newslettersignatur' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".$mysqli->escape_string($modul)."','0','1','5','send_benachrichtigung','E-Mail-Benachrichtigung','M&ouml;chten Sie eine Benachrichtigung per E-Mail erhalten, wenn sich ein neuer Benutzer für den Newsletter anmeldet?','Aktivieren|Deaktivieren','1|0','','0','0','0','0'),
				('".$mysqli->escape_string($modul)."','0','1','7','attachments','Dateianh&auml;nge verwenden?','','Ja|Nein','1|0','','1','1','0','0'),
				('".$mysqli->escape_string($modul)."','0','1','9','use_nutzungsbedingungen','Datenschutz / Nutzungsbedingungen aktivieren?','Beim Abonnieren des Newsletters muss den eingegebenen Datenschutz bzw. Nutzungsbedingungen zugestimmt werden.','Ja|Nein','1|0','','0','0','0','0'),
				('".$mysqli->escape_string($modul)."','0','1','10','nutzungsbedingungen','Datenschutz / Nutzungsbedingungen','','textarea','5|50','','','','0','0');";
	$result = $mysqli->query($sql_insert) OR die($mysqli->error);
	
	// Neue Spalte attachments an Archiv-Tabelle anfügen
	$mysqli->query("ALTER TABLE `".$mysql_tables['archiv']."` ADD `attachments` VARCHAR( 250 ) DEFAULT NULL");
	
	// MySQL-Tabellen aktualisieren
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `acode` `acode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `editcode` `editcode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `delcode` `delcode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `timestamp_reg` `timestamp_reg` INT( 15 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `catids` `catids` VARCHAR( 100 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `newcatids` `newcatids` VARCHAR( 100 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `email` `email` VARCHAR( 50 ) NULL");
	$mysqli->query("ALTER TABLE `".$mysql_tables['archiv']."` CHANGE `uid` `uid` INT( 10 ) NOT NULL DEFAULT '0'");
	$mysqli->query("ALTER TABLE `".$mysql_tables['archiv']."` CHANGE `mailinhalt` `mailinhalt` TEXT NULL");
	$mysqli->query("ALTER TABLE `".$mysql_tables['mailcats']."` CHANGE `catname` `catname` VARCHAR( 100 ) NULL");
	
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.1.0.0' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.0.0.1 nach 1.1.0.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.0.0.1 auf Version 1.1.0.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen mit Newslettern auch Dateianh&auml;nge verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&amp;modul=<?php echo $modul; ?>">neuen Einstellungen</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
// 1.0.0.0 --> 1.0.0.1
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1000_zu_1001"){
	// Setting-Reihenfolge aktualisieren
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '5' WHERE `idname` = 'usecats' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	$mysqli->query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '6' WHERE `idname` = 'newslettersignatur' AND `modul` = '".$mysqli->escape_string($modul)."' LIMIT 1");
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".$mysqli->escape_string($modul)."','0','1','3','versandadresse','Versand-E-Mail-Adresse','','text','50','','','','0','0'),
				('".$mysqli->escape_string($modul)."','0','1','4','versand_altname','Alternativtext für Versandadresse','','text','50','','','','0','0');";
	$result = $mysqli->query($sql_insert) OR die($mysqli->error);
	
	$mysqli->query("UPDATE ".$mysql_tables['module']." SET version = '1.0.0.1' WHERE idname = '".$mysqli->escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.0.0.0 nach 1.0.0.1</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.0.0.0 auf Version 1.0.0.1 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Es sind <a href="settings.php?action=settings&amp;modul=<?php echo $modul; ?>">neue Einstellungen vorhanden</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
?>