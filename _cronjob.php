<?PHP
/*
	01-Newsletter - Copyright 2009-2013 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Datei für den Versand aller Newsletter. Kann auch über einen Cronjob angesprochen werden
	#fv.131#
*/

// Variablen definieren
$flag_acp = FALSE;
$pfad_info = pathinfo($_SERVER['SCRIPT_FILENAME']);
$subfolder = $pfad_info['dirname']."/../../";
$modul = substr(strrchr($pfad_info['dirname'],"/"),1);

// Config-Dateien einbinden
include($subfolder."01acp/system/headinclude.php");
include_once($modulpath.$tempdir."lang_vars.php");

$c			= 0;
$where		= "";
$is_cronjob	= TRUE;

if(isset($_GET['message_id']) && is_numeric($_GET['message_id']) && $_GET['message_id'] > 0){
	$limit		= $intervall;
	$is_cronjob	= FALSE;
	$where		= " AND message_id = '".$mysqli->escape_string($_GET['message_id'])."'";
	}
else
	$limit = $intervall_cron;
	
// Cronjob in den Einstellungen aktiviert?
if($is_cronjob && $settings['use_cronjob'] == 0){
	echo "<p>Bitte aktivieren Sie die Cronjob-Funktion in den Einstellungen des <a href=\"../../01acp/\">ACP</a>!</p>";
    exit;
    }

// Message_ids für zutreffende Newsletter holen
$getmessage_ids = $mysqli->query("SELECT message_id FROM ".$mysql_tables['temp_table']." WHERE timestamp <= '".time()."'".$where." GROUP BY message_id ORDER BY timestamp");
while($msgids = $getmessage_ids->fetch_assoc()){
	if($c == $limit) break;
	
	if(!$is_cronjob)
    	echo "<p>Newsletter werden versendet. Bitte warten...</p>";
	
	// Newsletter-Text & Inhalte holen:
	$getmail = $mysqli->query("SELECT id,timestamp,betreff,mailinhalt,kategorien,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".$msgids['message_id']."' LIMIT 1");
	while($mailrow = $getmail->fetch_assoc()){
		if($c == $limit) break;
		
		$betreff				= stripslashes($mailrow['betreff']);
		$mailinhalt				= stripslashes($mailrow['mailinhalt']);
		$catarray				= explode(",",stripslashes($mailrow['kategorien']));
		if(!empty($mailrow['attachments']) && $settings['attachments'] == 1)
			$attachments = explode("|",$mailrow['attachments']);
	
		// Mail-Header
		$mail_header = _01newsletter_getMailHeader();
		$mail_body   = "";
	
		// Vararbeitung HTML-Mailinhalt
		if($settings['use_html']){
			$mailinhalt = str_replace("../01pics/",$settings['absolut_url']."01pics/",$mailinhalt);
			$mailinhalt = str_replace("../01files/",$settings['absolut_url']."01files/",$mailinhalt);
			$mailinhalt .= "<br /><br />";		// To add space before the link to unsubscribe
	
			$mailinhalt_header = "<html>
		<head>
		<title>".$betreff."</title></head>
	
		<body>";
			$mailinhalt_footer = "</body>
	
		</html>";
			}
		else{
			$mailinhalt_header = $mailinhalt_footer = "";
			$mailinhalt .= "\n\n";		// To add space before the link to unsubscribe
			}
	
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
					$list = $mysqli->query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".$mysqli->escape_string($attachment)."' LIMIT 1");
					$row = $list->fetch_assoc();
	
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
		$errors = array();
		$list = $mysqli->query("SELECT id,email FROM ".$mysql_tables['temp_table']." WHERE timestamp <= '".time()."' AND message_id = '".$msgids['message_id']."' LIMIT ".$mysqli->escape_string($limit)."");
		while($row = $list->fetch_assoc()){
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
	
				if(mail($row['email'],$betreff,$mail_body.$inhalt_add.$inhalt_attachment,$mail_header))
					$c++;
				else
					$errors[] = $row['email'];
				}
			else
				if(mail($row['email'],$betreff,$mailinhalt.str_replace("#abmeldelink#",$abmeldelink,$lang['austragen']),$mail_header))
					$c++;
				else
					$errors[] = $row['email'];	

			// Nach Versand Eintrag aus Tabelle löschen:
			$mysqli->query("DELETE FROM ".$mysql_tables['temp_table']." WHERE id = '".$row['id']."' LIMIT 1");
			
			if($c == $limit) break;
			}

			// Traten Fehler auf?
			if(is_array($errors) && !empty($errors) && count($errors) > 0){
				mail($settings['email_absender'],"Fehler beim Newsletter-Versand","Guten Tag,

an folgende Adressaten konnte leider ihr Newsletter ".$betreff." nicht versendet werden:
".implode(",", $errors)."

Bitte überprüfen Sie die Adressen und entfernen Sie sie ggf. aus den Empfängerlisten.

---
Webmailer (01-Newsletterscript)
".$settings['absolut_url']."01acp/",$mail_header);
			}

		}
		
	// Automatische Weiterleitung
	if(!$is_cronjob){
    	echo "<script type=\"text/javascript\">function redirect(){ window.location='_cronjob.php?message_id=".$msgids['message_id']."'; } redirect();</script>";
		echo "<a href=\"_cronjob.php?message_id=".$msgids['message_id']."\">Weiter</a>";
		}
	}
	
$menge = $getmessage_ids->num_rows;
if(!$is_cronjob && $menge == 0)
    echo "<p>Es wurden alle Newsletter erfolgreich versendet</p>";
	
if($is_cronjob)
	echo "<p>Es wurden ".$c." Newsletter verschickt</p>";
		
?>