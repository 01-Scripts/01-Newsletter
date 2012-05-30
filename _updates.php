<?PHP
// 1.2.0 --> 1.3.0
if(isset($_REQUEST['update']) && $_REQUEST['update'] == "120_zu_130"){
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO `".$mysql_tables['settings']."` (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".mysql_real_escape_string($modul)."','0','1','9','use_cronjob','Newsletter per Cronjob versenden?','Legen Sie dazu einen regelm&auml;&szlig;igen Cronjob auf die Datei <i>01scripts/01module/01newsletter/_cronjob.php</i> an.<br /><a href=\"http://cronjob.01-scripts.de\" target=\"_blank\">Weitere Informationen zum Thema</a>','Ja|Nein','1|0','','0','0','0','0');";
	mysql_query($sql_insert) OR die(mysql_error());
	
	// Setting-Reihenfolge aktualisieren
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '10' WHERE `idname` = 'newslettersignatur' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '11' WHERE `idname` = 'use_nutzungsbedingungen' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '12' WHERE `idname` = 'nutzungsbedingungen' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	
	// Neue Datenbanktabelle für Versand per _cronjob.php
	mysql_query("CREATE TABLE IF NOT EXISTS `".$mysql_tables['temp_table']."` (
	  `id` int(10) NOT NULL AUTO_INCREMENT,
	  `timestamp` int(10) NOT NULL,
	  `message_id` int(10) NOT NULL,
	  `email` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");

	mysql_query("UPDATE ".$mysql_tables['module']." SET version = '1.3.0' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.2.0 nach 1.3.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.2.0 auf Version 1.3.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen Newsletter nun auch per Cronjob verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&modul=<?php echo $modul; ?>">neue Einstellung</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
// 1.1.0.0 --> 1.2.0
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1100_zu_120"){
	// Setting-Reihenfolge aktualisieren
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '9' WHERE `idname` = 'newslettersignatur' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '10' WHERE `idname` = 'use_nutzungsbedingungen' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '11' WHERE `idname` = 'nutzungsbedingungen' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".mysql_real_escape_string($modul)."','0','1','8','use_html','HTML-Newsletter versenden?','','Ja|Nein','1|0','','0','0','0','0');";
	$result = mysql_query($sql_insert) OR die(mysql_error());
	
	mysql_query("UPDATE ".$mysql_tables['module']." SET version = '1.2.0' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.1.0.0 nach 1.2.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.1.0.0 auf Version 1.2.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen auch Newsletter mit HTML verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&modul=<?php echo $modul; ?>">neue Einstellung</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
// 1.0.0.1 --> 1.1.0.0
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1001_zu_1100"){
	// Setting-Reihenfolge aktualisieren
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '6' WHERE `idname` = 'usecats' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	mysql_query("UPDATE `".$mysql_tables['settings']."` SET `sortid` = '8' WHERE `idname` = 'newslettersignatur' AND `modul` = '".mysql_real_escape_string($modul)."' LIMIT 1");
	
	// Neue Einstellungen anlegen:
	$sql_insert = "INSERT INTO ".$mysql_tables['settings']." (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
				('".mysql_real_escape_string($modul)."','0','1','5','send_benachrichtigung','E-Mail-Benachrichtigung','M&ouml;chten Sie eine Benachrichtigung per E-Mail erhalten, wenn sich ein neuer Benutzer für den Newsletter anmeldet?','Aktivieren|Deaktivieren','1|0','','0','0','0','0'),
				('".mysql_real_escape_string($modul)."','0','1','7','attachments','Dateianh&auml;nge verwenden?','','Ja|Nein','1|0','','1','1','0','0'),
				('".mysql_real_escape_string($modul)."','0','1','9','use_nutzungsbedingungen','Datenschutz / Nutzungsbedingungen aktivieren?','Beim Abonnieren des Newsletters muss den eingegebenen Datenschutz bzw. Nutzungsbedingungen zugestimmt werden.','Ja|Nein','1|0','','0','0','0','0'),
				('".mysql_real_escape_string($modul)."','0','1','10','nutzungsbedingungen','Datenschutz / Nutzungsbedingungen','','textarea','5|50','','','','0','0');";
	$result = mysql_query($sql_insert) OR die(mysql_error());
	
	// Neue Spalte attachments an Archiv-Tabelle anfügen
	mysql_query("ALTER TABLE `".$mysql_tables['archiv']."` ADD `attachments` VARCHAR( 250 ) DEFAULT NULL");
	
	// MySQL-Tabellen aktualisieren
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `acode` `acode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `editcode` `editcode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `delcode` `delcode` VARCHAR( 32 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `timestamp_reg` `timestamp_reg` INT( 15 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `catids` `catids` VARCHAR( 100 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `newcatids` `newcatids` VARCHAR( 100 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['emailadds']."` CHANGE `email` `email` VARCHAR( 50 ) NULL");
	mysql_query("ALTER TABLE `".$mysql_tables['archiv']."` CHANGE `uid` `uid` INT( 10 ) NOT NULL DEFAULT '0'");
	mysql_query("ALTER TABLE `".$mysql_tables['archiv']."` CHANGE `mailinhalt` `mailinhalt` TEXT NULL");
	mysql_query("ALTER TABLE `".$mysql_tables['mailcats']."` CHANGE `catname` `catname` VARCHAR( 100 ) NULL");
	
	mysql_query("UPDATE ".$mysql_tables['module']." SET version = '1.1.0.0' WHERE idname = '".mysql_real_escape_string($modul)."' LIMIT 1");
?>
<h2>Update Version 1.0.0.1 nach 1.1.0.0</h2>

<p class="meldung_erfolg">
	Das Update von Version 1.0.0.1 auf Version 1.1.0.0 wurde erfolgreich durchgef&uuml;hrt.<br /><br />
	Ab sofort k&ouml;nnen mit Newslettern auch Dateianh&auml;nge verschickt werden!<br />
	Beachten Sie dazu auch die <a href="settings.php?action=settings&modul=<?php echo $modul; ?>">neuen Einstellungen</a>!<br />
	<br />
	<a href="module.php">Zur&uuml;ck zur Modul-&Uuml;bersicht &raquo;</a>
</p>
<?PHP
	}
// 1.0.0.0 --> 1.0.0.1
elseif(isset($_REQUEST['update']) && $_REQUEST['update'] == "1000_zu_1001"){
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