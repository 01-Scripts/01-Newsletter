-- 01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
-- Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
-- Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

-- Modul:		01newsletter
-- Dateiinfo:	SQL-Befehle für die Erstinstallation des 01-Newsletterscripts
-- #fv.140#
--  **  **  **  **  **  **  **  **  **  **  **  **  **  **  **  **  *  *

-- --------------------------------------------------------

SET AUTOCOMMIT=0;
START TRANSACTION;

-- --------------------------------------------------------

--
-- Neue Einstellungs-Kategorie für Modul anlegen
-- Einstellungen importieren
--

INSERT INTO 01prefix_settings (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,wert,nodelete,hide) VALUES
('#modul_idname#', 1, 3, 5, 'smtp_nl_settings', 'Newsletter Versandeinstellungen', NULL , NULL , NULL , NULL , NULL , NULL ,0,0),
('#modul_idname#', 0, 3, 1, 'smtp_nl', 'Wie m&ouml;chten Sie ausgehende Newsletter versenden?','','Standardversand per PHP mail()-Befehl|SMTP-Versand (SMTP-Server aus 01ACP Einstellungen)|SMTP-Versand (Nachfolgend angegebener SMTP-Server)','php|smtp_01acp|smtp_01newsletter','','php','php',0,0),
('#modul_idname#', 0, 3, 2, 'smtp_nl_host', 'SMTP-Server','','text','50','','','',0,0),
('#modul_idname#', 0, 3, 3, 'smtp_nl_port', 'SMTP-Server TCP Port','','text','50','','587','587',0,0),
('#modul_idname#', 0, 3, 4, 'smtp_nl_username', 'SMTP Username','','text','50','','','',0,0),
('#modul_idname#', 0, 3, 5, 'smtp_nl_password', 'SMTP Password','Das SMTP Passwort wird aus technischen Gr&uuml;nden unverschl&uuml;sselt gespeichert.','text','50','','','',0,0),
('#modul_idname#', 0, 1, 9,  'use_cronjob','Newsletter per Cronjob versenden?','Legen Sie dazu einen regelm&auml;&szlig;igen Cronjob auf die Datei <i>01scripts/01module/#modul_idname#/_cronjob.php</i> an.<br /><a href=\"http://cronjob.01-scripts.de\" target=\"_blank\">Weitere Informationen zum Thema</a>','Ja|Nein','1|0','','0','0','0','0'),
('#modul_idname#', 0, 1, 10, 'newslettersignatur', 'Signatur', 'Die Signatur wird automatisch an alle Newsletter angeh&auml;ngt.', 'textarea', '5|50', '', '', '', 0, 0),
('#modul_idname#', 0, 1, 11, 'use_nutzungsbedingungen','Datenschutz / Nutzungsbedingungen aktivieren?','Beim Abonnieren des Newsletters muss den eingegebenen Datenschutz bzw. Nutzungsbedingungen zugestimmt werden.','Ja|Nein','1|0','','0','0','0','0'),
('#modul_idname#', 0, 1, 12,'nutzungsbedingungen','Datenschutz / Nutzungsbedingungen','','textarea','5|50','','','','0','0'),
('#modul_idname#', 0, 1, 7, 'attachments','Dateianh&auml;nge verwenden?','','Ja|Nein','1|0','','1','1','0','0'),
('#modul_idname#', 0, 1, 8, 'use_html','HTML-Newsletter versenden?','','Ja|Nein','1|0','','0','0','0','0'),
('#modul_idname#', 0, 1, 1, 'newslettertitel', 'Titel des Newsletters', '', 'text', '50', '', '', '', 0, 0),
('#modul_idname#', 1, 2, 2, 'csssettings', 'CSS-Einstellungen', '', '', '', '', '', '', 0, 0),
('#modul_idname#', 0, 2, 1, 'extern_css', 'Externe CSS-Datei', 'Geben Sie einen absoluten Pfad inkl. <b>http://</b> zu einer externen CSS-Datei an.\nIst dieses Feld leer, wird die Datei templates/style.css aus dem Modulverzeichnis verwendet.', 'text', '50', '', '', '', 0, 0),
('#modul_idname#', 0, 1, 2, 'formzieladdr', 'Ziel-URL f&uuml;r Anmeldformular', 'Bitte geben Sie die absolute URL (inkl. http://) zu der Datei ein, <b>in die Sie die Datei 01newsletter/01newsletter.php</b> per PHP eingebunden haben.', 'text', '50', NULL, NULL, '', 0, 0),
('#modul_idname#', 0, 1 ,5 ,'send_benachrichtigung','E-Mail-Benachrichtigung','M&ouml;chten Sie eine Benachrichtigung per E-Mail erhalten, wenn sich ein neuer Benutzer für den Newsletter anmeldet?','Aktivieren|Deaktivieren','1|0', NULL, '0', '0', 0, 0),
('#modul_idname#', 0, 1, 6, 'usecats', 'Kategorien verwenden?', NULL, 'Ja|Nein', '1|0', NULL, '0', '0', 0, 0),
('#modul_idname#', 0, 1, 3, 'versandadresse','Versand-E-Mail-Adresse','','text','50','','','',0,0),
('#modul_idname#', 0, 1, 4, 'versand_altname','Alternativtext für Versandadresse','','text','50','','','',0,0),
('#modul_idname#', 1, 1, 1, 'newslettersettings', 'Einstellungen', NULL, '', '', NULL, NULL, NULL, 0, 0);



-- --------------------------------------------------------

--
-- Menüeinträge anlegen
--

INSERT INTO 01prefix_menue (name,link,modul,sicherheitslevel,rightname,rightvalue,sortorder,subof,hide) VALUES
('E-Mail-Adressen', '_loader.php?modul=#modul_idname#&amp;loadpage=emails', '#modul_idname#', 1, 'show_emails', '1', 3, 0, 0),
('Kategorien verwalten', '_loader.php?modul=#modul_idname#&amp;loadpage=cats', '#modul_idname#', 1, 'settings', '1', 4, 0, 0),
('Newsletter-Archiv', '_loader.php?modul=#modul_idname#&amp;action=archiv&amp;loadpage=newsletter', '#modul_idname#', 1, '#modul_idname#', '1', 2, 0, 0),
('<b>Newsletter schreiben</b>', '_loader.php?modul=#modul_idname#&amp;action=new&amp;loadpage=newsletter', '#modul_idname#', 1, '#modul_idname#', '1', 1, 0, 0);



-- --------------------------------------------------------

--
-- Benutzerrechte und Rechte-Kategorien anlegen
--

INSERT INTO 01prefix_rights (modul,is_cat,catid,sortid,idname,name,exp,formename,formwerte,input_exp,standardwert,nodelete,hide,in_profile) VALUES
('#modul_idname#', 0, 1, 1, 'show_emails', 'Darf E-Mail-Adressen einsehen?', '', 'Ja|Nein', '1|0', '', '0', 0, 0, 0),
('#modul_idname#', 0, 1, 2, 'vorlagen', 'Vorlagen erstellen und verwalten', '', 'Ja|Nein', '1|0', '', '0', 0, 0, 0),
('#modul_idname#', 1, 1, 1, '01newsletter_userrights', 'Benutzerrechte', NULL, '', '', NULL, NULL, 0, 0, 0);


ALTER TABLE `01prefix_user` ADD `#modul_idname#_show_emails` tinyint( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `01prefix_user` ADD `#modul_idname#_vorlagen` tinyint( 1 ) NOT NULL DEFAULT '0';

--
-- Dem Benutzer, der das Modul installiert hat die entsprechenden Rechte zuweisen
--

UPDATE `01prefix_user` SET `#modul_idname#_show_emails` = '1' WHERE `01prefix_user`.`id` = #UID_ADMIN_AKT# LIMIT 1;
UPDATE `01prefix_user` SET `#modul_idname#_vorlagen` = '1' WHERE `01prefix_user`.`id` = #UID_ADMIN_AKT# LIMIT 1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `01prefix_emailadressen`
--

CREATE TABLE `01modulprefix_emailadressen` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `acode` varchar(32) NOT NULL DEFAULT '0',
  `editcode` varchar(32) NOT NULL DEFAULT '0',
  `delcode` varchar(32) NOT NULL DEFAULT '0',
  `timestamp_reg` int(15) NOT NULL DEFAULT '0',
  `email` varchar(50),
  `name` varchar(50),
  `catids` varchar(100) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `01prefix_newsletterarchiv`
--

CREATE TABLE `01modulprefix_newsletterarchiv` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `art` char(1) NOT NULL DEFAULT 'a',
  `utimestamp` int(15) NOT NULL DEFAULT '0',
  `uid` int(10) NOT NULL DEFAULT '0',
  `betreff` varchar(250) DEFAULT NULL,
  `mailinhalt` text DEFAULT NULL,
  `kategorien` varchar(250) DEFAULT NULL,
  `attachments` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `01prefix_newsletterarchiv`
--

INSERT INTO `01modulprefix_newsletterarchiv` (`id`, `art`, `utimestamp`, `uid`, `betreff`, `mailinhalt`, `kategorien`) VALUES
(1, 'a', 1398074400, 1, '01-Scripts.de - Testeintrag', 'Das 01-Newsletter-Modul wurde erfolgreich installiert.\nDieser Eintrag kann nun gel&ouml;scht werden.', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `01prefix_newslettercats`
--

CREATE TABLE `01modulprefix_newslettercats` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `catname` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `01prefix_send_newsletter_temp`
--

CREATE TABLE IF NOT EXISTS `01modulprefix_send_newsletter_temp` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `utimestamp` int(15) NOT NULL DEFAULT '0',
  `message_id` int(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `name` varchar(50),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

COMMIT;