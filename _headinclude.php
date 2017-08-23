<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: https://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Modulspezifische Grundeinstellungen, Variablendefinitionen etc.
				Wird automatisch am Anfang jeden Modulaufrufs automatisch includiert.
	#fv.140#
*/

// Modul-Spezifische MySQL-Tabellen
$mysql_tables['archiv'] 	= "01_".$instnr."_".$module[$modul]['nr']."_newsletterarchiv";
$mysql_tables['emailadds']	= "01_".$instnr."_".$module[$modul]['nr']."_emailadressen";
$mysql_tables['mailcats']	= "01_".$instnr."_".$module[$modul]['nr']."_newslettercats";
$mysql_tables['temp_table']	= "01_".$instnr."_".$module[$modul]['nr']."_send_newsletter_temp";

$addJSFile 	= "_javascript.js";			// Zusätzliche modulspezifische JS-Datei (im Modulverzeichnis!)
$addCSSFile = "modul.css";				// Zusätzliche modulspezifische CSS-Datei (im Modulverzeichnis!)
$mootools_use = array("moo_core","moo_more","moo_calendar","moo_slideh","moo_request");

// Welche PHP-Seiten sollen abhängig von $_REQUEST['loadpage'] includiert werden?
$loadfile['index'] 		= "index.php";			// Standardseite, falls loadpage invalid ist
$loadfile['newsletter']	= "newsletter.php";
$loadfile['emails']		= "emails.php";
$loadfile['csvimport']  = "import.php";
$loadfile['cats']		= "category.php";

// Weitere Pfadangaben
$tempdir	= "templates/";			// Template-Verzeichnis

// Weitere Variablen
$intervall  = 10;                   // Wie viele Mails sollen pro Schritt versendet werden?
$intervall_cron = 200;				// Wie viele Mails sollen pro Cronjob-Aufruf versendet werden?
$deletimer	= 30;					// Nicht-aktivierte E-Mail-Adressen nach x TAGEN löschen
$use_name   = TRUE;                 // Namensfeld für Newsletter verwenden?
$name_replace = "{#name#}";         // Wird beim Versand ggf. durch den Namen des Empfängers ersetzt
$email_max_len = 50;                // Maximale Anzahl an Zeichen für E-Mail-Adressen
$name_max_len = 50;                 // Maximale Anzahl an Zeichen für den Namen
$SMTPSecurity = "tls";              // 'tls' or 'ssl'
$SMTPAuth = TRUE;                   // SMTP Authentication verwenden bei Versand per SMTP?

?>