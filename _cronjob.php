<?PHP
/*
	01-Newsletter - Copyright 2009-2011 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Cronjob-Datei für den zeitverzögerten Versand von Newslettern
	#fv.120s#
*/

// Variablen definieren
$flag_acp = FALSE;
$subfolder = "/usr/www/users/intern76/_Artikelsystem/01scripts/";
$modul = "01newsletter";

// Config-Dateien einbinden
include($subfolder."01acp/system/headinclude.php");
include_once($modulpath.$tempdir."lang_vars.php");

// Vorhandene Kategorien in Array einlesen
$chosencats = array();
$listcats = mysql_query("SELECT id,catname FROM ".$mysql_tables['mailcats']."");
while($rowcats = mysql_fetch_assoc($listcats)){
	$chosencats[$rowcats['id']] = stripslashes($rowcats['catname']);
	}

// Newsletter-Text & Inhalte holen:
$getmail = mysql_query("SELECT id,timestamp,betreff,mailinhalt,kategorien,attachments FROM ".$mysql_tables['archiv']." WHERE art = 'e' AND timestamp <= '".time()."' AND timestamp > 0");
while($mailrow = mysql_fetch_assoc($getmail)){
	$betreff				= stripslashes($mailrow['betreff']);
	$mailinhalt				= stripslashes($mailrow['mailinhalt']);
	$catarray				= explode(",",stripslashes($mailrow['kategorien']));
	if(!empty($mailrow['attachments']) && $settings['attachments'] == 1)
		$attachments = explode("|",$mailrow['attachments']);

	// Empfänger zusammenstellen
	$kategorien = "";
	if($mailrow['kategorien'] == "all")
		$query = "SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0'";
	elseif(!empty($mailrow['kategorien']) && $settings['usecats'] == 1){
		$where = "catids = '0' OR catids = ',0,'";
		foreach($catarray as $cat){
			$where .= " OR catids LIKE '%,".$cat.",%'";
			$kategorien .= $chosencats[$cat].", ";
			}
	
		$query = "SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (".$where.")";
		}
	
	// Mail-Header
	$mail_header = _01newsletter_getMailHeader();
	$mail_body   = "";
	
	// Vararbeitung HTML-Mailinhalt
	if($settings['use_html']){
		$mailinhalt = str_replace("../01pics/",$settings['absolut_url']."01pics/",$mailinhalt);
		$mailinhalt = str_replace("../01files/",$settings['absolut_url']."01files/",$mailinhalt);
	
		$mailinhalt_header = "<html>
	<head>
	<title>".$betreff."</title></head>
	
	<body>";
		$mailinhalt_footer = "</body>
	
	</html>";
		}
	else{
		$mailinhalt_header = $mailinhalt_footer = "";
		}
	
	if($settings['use_html'])
	    $lang['austragen']	= "<br /><br />".$lang['austragen'];
	else
		$lang['austragen']	= "\n\n".$lang['austragen'];
	
	// Attachments ggf. anhängen
	if($settings['attachments'] == 1 && isset($attachments) && is_array($attachments)){
		$cup = 0;
		$boundary = strtoupper(md5(uniqid(time())));
	
		$mail_header .= "\nMIME-Version: 1.0"."";
		$mail_header .= "\nContent-Type: multipart/mixed;  boundary=\"".$boundary."\"";
	
		$mail_body .= "\nMIME-Version: 1.0"."";
		$mail_body .= "\nContent-Type: multipart/mixed;  boundary=\"".$boundary."\"";
		$mail_body .= "\n\nThis is a multi-part message in MIME format  --  Dies ist eine mehrteilige Nachricht im MIME-Format";
	
		$inhalt_attachment = "";
		foreach($attachments as $attachment){
			if(in_array(getEndung($attachment),$picendungen))
				$dateiname_org		= $picuploaddir.$attachment; // ggf. inkl. Pfad
			else
				$dateiname_org		= $attachmentuploaddir.$attachment; // ggf. inkl. Pfad
	
			if(file_exists($dateiname_org) && $dateiname_org != $attachmentuploaddir && $dateiname_org != $picuploaddir){
				// Dateinamen für E-Mail holen
				$list = mysql_query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".mysql_real_escape_string($attachment)."' LIMIT 1");
				$row = mysql_fetch_assoc($list);
	
				if(empty($row['orgname'])) $row['orgname'] = $attachment;
	
			    $file_content = fread(fopen($dateiname_org,"r"),filesize($dateiname_org));
			    $file_content = chunk_split(base64_encode($file_content));
	
			    $inhalt_attachment .= "\nContent-Type: ".mime_content_type($dateiname_org)."; name=\"".stripslashes($row['orgname'])."\"";
			    $inhalt_attachment .= "\nContent-Transfer-Encoding: base64";
			    $inhalt_attachment .= "\nContent-Disposition: attachment; filename=\"".stripslashes($row['orgname'])."\"";
			    $inhalt_attachment .= "\n\n".$file_content."";
			    $inhalt_attachment .= "\n--".$boundary."";
	
			    $cup++;
				}
			}
		if(!empty($header_attachment)) $header_attachment .= "--";
		}
	
	if($settings['use_html'] && ($settings['attachments'] != 1 || !isset($attachments) || $cup <= 0)){
		$mail_header .= "\nMIME-Version: 1.0"."";
		$mail_header .= "\nContent-type: text/html; charset=iso-8859-1";
		}
	
	// Mails verschicken
	$list = mysql_query($query);
	while($row = mysql_fetch_assoc($list)){
		if($settings['use_html'])
			$abmeldelink = "<br /><a href=\"".addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true)."\">".$lang['austragen_html']."</a>";
		else
			$abmeldelink = addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true);
	
		if($settings['attachments'] == 1 && isset($attachments) && $cup > 0){
			$inhalt_add = "\n--".$boundary."";
			if($settings['use_html'])
				$inhalt_add .= "\nContent-type: text/html; charset=iso-8859-1";
			else
				$inhalt_add .= "\nContent-Type: text/plain";
	
			$inhalt_add .= "\nContent-Transfer-Encoding: 8bit";
			$inhalt_add .= "\n\n".$mailinhalt_header.$mailinhalt.str_replace("#abmeldelink#",$abmeldelink,$lang['austragen']).$mailinhalt_footer."";
			$inhalt_add .= "\n--".$boundary."";
	
			mail($row['email'],$betreff,$mail_body.$inhalt_add.$inhalt_attachment,$mail_header);
			}
		else{
			mail($row['email'],$betreff,$mailinhalt.str_replace("#abmeldelink#",$abmeldelink,$lang['austragen']),$mail_header);
			}
		}
	$empfmenge = mysql_num_rows($list);
	
	mysql_query("UPDATE ".$mysql_tables['archiv']." SET art = 'a', kategorien = '".mysql_real_escape_string($kategorien)."' WHERE id='".$mailrow['id']."' LIMIT 1");
	
	echo "<p>Es wurden ".$empfmenge." Newsletter verschickt</p>";
	}
		
?>