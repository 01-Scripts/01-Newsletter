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

$addJSFile 	= "_javascript.js";			// Zus�tzliche modulspezifische JS-Datei (im Modulverzeichnis!)
$addCSSFile = "modul.css";				// Zus�tzliche modulspezifische CSS-Datei (im Modulverzeichnis!)
$mootools_use = array("moo_core","moo_more","moo_calendar","moo_slideh","moo_request");

// Welche PHP-Seiten sollen abh�ngig von $_REQUEST['loadpage'] includiert werden?
$loadfile['index'] 		= "index.php";			// Standardseite, falls loadpage invalid ist
$loadfile['newsletter']	= "newsletter.php";
$loadfile['emails']		= "emails.php";
$loadfile['csvimport']  = "import.php";
$loadfile['cats']		= "category.php";

// Weitere Pfadangaben
$tempdir	= "templates/";			// Template-Verzeichnis

// Weitere Variablen
$intervall      = 10;               // Wie viele Mails sollen pro Schritt versendet werden?
$intervall_cron = 200;				// Wie viele Mails sollen pro Cronjob-Aufruf versendet werden?
$deletimer	    = 30;				// Nicht-aktivierte E-Mail-Adressen nach x TAGEN l�schen
$use_name       = TRUE;             // Namensfeld f�r Newsletter verwenden?
$email_max_len  = 50;               // Maximale Anzahl an Zeichen f�r E-Mail-Adressen
$name_max_len   = 50;               // Maximale Anzahl an Zeichen f�r den Namen

$SMTPSecurity   = "tls";            // 'tls' or 'ssl'
$SMTPAuth       = TRUE;             // SMTP Authentication verwenden bei Versand per SMTP?

// Dynamische Newsletter-Variablen
$replace_name = "{#name#}";         // Wird beim Versand ggf. durch den Namen des Empf�ngers ersetzt
$replace_year = "{#akt_jahr#}";     // Wird durch das aktuelle Jahr des geplanten Versandzeitpunkts ersetzt
$replace_date = "{#akt_date#}";     // Wird durch das aktuelle Datum des geplanten Versandzeitpunkts ersetzt
$format_date  = "d.m.Y"             // Formatstring f�r die Datums-Ausgabe: http://de2.php.net/manual/en/function.date.php

?>