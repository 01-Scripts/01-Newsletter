<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: https://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Datei f¸r den Versand aller Newsletter. Kann auch ¸ber einen Cronjob angesprochen werden
	#fv.140#
*/

// Variablen definieren
$flag_acp = FALSE;
$pfad_info = pathinfo($_SERVER['SCRIPT_FILENAME']);
$subfolder = $pfad_info['dirname']."/../../";
$modul = substr(strrchr($pfad_info['dirname'],"/"),1);

// Config-Dateien einbinden
include($subfolder."01acp/system/headinclude.php");
include($subfolder."01acp/system/includes/PHPMailerAutoload.php");
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

// Mail-Header
$mail = new PHPMailer;
_01newsletter_configurePHPMailer($mail);
$mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead

// Steht ein wiederkehrender Versand an (monatlich/j‰hrlich)?
$x = 0;
$getrmails = $mysqli->query("SELECT id,art,utimestamp,kategorien FROM ".$mysql_tables['archiv']." WHERE (art = 'm' OR art = 'y') AND utimestamp <= '".time()."'");
while($mrow = $getrmails->fetch_assoc()){
	// Aktuelle Empf‰nger zusammenstellen
	if($mrow['kategorien'] == "all" || empty($mrow['kategorien']))
		$query = "SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0'";
	else{
			if(strpos($mrow['kategorien'], ",") !== FALSE){
				$cats = explode(",", $mrow['kategorien']);

				$cw = "catids = '0' OR catids = ',0,'";
				foreach($cats as $cat){
					$cw .= " OR catids LIKE '%,".$cat.",%'";
				}
			}else{
				$cw = "catids = '0' OR catids = ',0,' OR catids LIKE '%,".$mrow['kategorien'].",%'";
			}

			$query = "SELECT email,name FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (".$cw.")";
		}

	$values = "";
	$emaillist = $mysqli->query($query);
	while($erow = $emaillist->fetch_assoc()){
		if($x > 0) $values .= ",\n";
		
		$values .= "('".$mrow['utimestamp']."','".$mrow['id']."','".$erow['email']."','".$erow['name']."')";
		
		$x++;
	}

	if(!empty($values))
		$mysqli->query("INSERT INTO ".$mysql_tables['temp_table']." (utimestamp, message_id, email, name) VALUES ".$values.";") OR die($mysqli->error);

	// Versandzeitpunkt um ein Monat bzw. Jahr nach hinten verschieben
	$datum = date("j.n.Y", $mrow['utimestamp']);
	$send_date = explode(".",$datum);
	if($mrow['art'] == 'm'){
		// Sind wir im Dezember? ‹berlauf in den Januar des darauf folgenden Jahres
		if($send_date[1] == 12){
			$send_date[1] = 1;
			$send_date[2]++;
		}
		else
			$send_date[1]++;	
	}
	else
		$send_date[2]++;

	$timestamp = mktime("0", "0", "1", $send_date[1], $send_date[0], $send_date[2]);
	$mysqli->query("UPDATE ".$mysql_tables['archiv']." SET utimestamp='".$timestamp."' WHERE id='".$mrow['id']."' LIMIT 1");
}

// Message_ids f¸r zutreffende Newsletter holen
$getmessage_ids = $mysqli->query("SELECT utimestamp,message_id FROM ".$mysql_tables['temp_table']." WHERE utimestamp <= '".time()."'".$where." GROUP BY message_id ORDER BY utimestamp");
while($msgids = $getmessage_ids->fetch_assoc()){
	if($c == $limit) break;
	
	if(!$is_cronjob)
    	echo "<p>Newsletter werden versendet. Bitte warten...</p>";
	
	// Newsletter-Text & Inhalte holen:
	$getmail = $mysqli->query("SELECT id,utimestamp,betreff,mailinhalt,kategorien,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".$msgids['message_id']."' LIMIT 1");
	while($mailrow = $getmail->fetch_assoc()){
		if($c == $limit) break;
		
		$mailinhalt	= $mailrow['mailinhalt'];
		$mailinhalt = str_replace($replace_year,date("Y",$mailrow['utimestamp']),$mailinhalt);
		$mailinhalt = str_replace($replace_date,date($format_date,$msgids['utimestamp']),$mailinhalt);
		$mailinhalt = str_replace($replace_send,$mail->From,$mailinhalt);

		if(!empty($mailrow['attachments']) && $settings['attachments'] == 1)
			$attachments = explode("|",$mailrow['attachments']);
	
		$mail->Subject = $mailrow['betreff'];
	
		// Vararbeitung HTML-Mailinhalt
		if($settings['use_html']){
			$mail->isHTML(true);

			$mailinhalt = str_replace("../01pics/",$settings['absolut_url']."01pics/",$mailinhalt);
			$mailinhalt = str_replace("../01files/",$settings['absolut_url']."01files/",$mailinhalt);
			$mailinhalt .= "<br /><br />";		// To add space before the link to unsubscribe
			}
		else{
			$mailinhalt .= "\n\n";		// To add space before the link to unsubscribe
			}
	
		// Attachments ggf. anh‰ngen
		if($settings['attachments'] == 1 && isset($attachments) && is_array($attachments)){
			foreach($attachments as $attachment){
				if(in_array(getEndung($attachment),$picendungen))
					$dateiname_org		= $picuploaddir.$attachment; 		// ggf. inkl. Pfad
				else
					$dateiname_org		= $attachmentuploaddir.$attachment; // ggf. inkl. Pfad
	
				if(file_exists($dateiname_org) && $dateiname_org != $attachmentuploaddir && $dateiname_org != $picuploaddir){
					// Dateinamen f¸r E-Mail holen
					$list = $mysqli->query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".$mysqli->escape_string($attachment)."' LIMIT 1");
					$row = $list->fetch_assoc();
	
					if(empty($row['orgname'])) $row['orgname'] = $attachment;

					$mail->addAttachment($dateiname_org, $row['orgname']);
					}
				}
			}
	
		// Mails verschicken
		$errors = array();
		$list = $mysqli->query("SELECT id,email,name FROM ".$mysql_tables['temp_table']." WHERE utimestamp <= '".time()."' AND message_id = '".$msgids['message_id']."' LIMIT ".$mysqli->escape_string($limit)."");
		while($row = $list->fetch_assoc()){
			$r = 0;

			if($use_name && !empty($row['name']))
				$t_mailinhalt = str_replace($replace_name," ".$row['name'],$mailinhalt);
		   	else
		   		$t_mailinhalt = str_replace($replace_name,"",$mailinhalt);

		   	$t_mailinhalt = str_replace($replace_mail,$row['email'],$t_mailinhalt);

			// Individellen Abmelde-Link ggf. ersetzen
			if($settings['use_html'])
				$t_mailinhalt = str_replace("{#abmeldelink#}","<a href=\"".addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true)."\">".$lang['austragen_html']."</a>",$t_mailinhalt,$r);
			else
				$t_mailinhalt = str_replace("{#abmeldelink#}",addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true),$t_mailinhalt,$r);
			// Wenn kein individueller Abmelde-Link verwendet wurde, standardm‰ﬂig anh‰ngen
			if($r == 0){
				if($settings['use_html'])
					$abmeldelink = "<br /><a href=\"".addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true)."\">".$lang['austragen_html']."</a>";
				else
					$abmeldelink = addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true);
					
				$t_mailinhalt .= str_replace("{#abmeldelink#}",$abmeldelink,$lang['austragen']);
			}
			
			if($settings['use_html'])
				$mail->msgHTML($t_mailinhalt, dirname(__FILE__));
			else
				$mail->Body = $t_mailinhalt;

			$mail->addAddress($row['email']);
			if(!$mail->send())
				$errors[] = $row['email'];
			else
				$c++;

			// Nach Versand Eintrag aus Tabelle lˆschen:
			$mysqli->query("DELETE FROM ".$mysql_tables['temp_table']." WHERE id = '".$row['id']."' LIMIT 1");
			$mail->clearAddresses();
			
			if($c == $limit) break;
		}

		// Statusmail bei Fehlern w‰hren des Newsletter-Versands
		if(is_array($errors) && !empty($errors) && count($errors) > 0){
			$statusmailtext = "Guten Tag,

an folgende Adressaten konnte leider ihr Newsletter ".$mailrow['betreff']." nicht versendet werden:
".implode(",", $errors)."

Bitte ¸berpr¸fen Sie die Adressen und entfernen Sie sie ggf. aus den Empf‰ngerlisten.

---
Webmailer (01-Newsletterscript)
".$settings['absolut_url']."01acp/";

			$statusmail = new PHPMailer;
			_01newsletter_configurePHPMailer($statusmail);
			$statusmail->addAddress($settings['email_absender']);
			$statusmail->Subject = "Fehler beim Newsletter-Versand";
			$statusmail->Body    = $statusmailtext;
			$statusmail->send();
		}

		$mail->clearAttachments();
		unset($attachments);
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