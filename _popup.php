<?php 
/*
	01-Newsletter - Copyright 2009 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Popup-Inhalt
	#fv.1001#
*/

// Newsletter ausgeben
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "show_letter" &&
   isset($_REQUEST['var1']) && !empty($_REQUEST['var1']) && is_numeric($_REQUEST['var1'])){
   
	$list = mysql_query("SELECT betreff,mailinhalt FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_REQUEST['var1'])."'");
	while($row = mysql_fetch_array($list)){
		echo "<h2>".$row['betreff']."</h2>";
		echo "<p>".nl2br($row['mailinhalt'])."</p>";
		}
	}
	
	

	
// Newsletter versenden
if(isset($_GET['action']) && $_GET['action'] == "send_letter" &&
   isset($_GET['newsletter_id']) && !empty($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id']) &&
   isset($_GET['start']) && is_numeric($_GET['start']) &&
   isset($_GET['empf']) && ($_GET['empf'] == "all" || $_GET['empf'] == "cats" && isset($_GET['empfcats']) && !empty($_GET['empfcats']) && $settings['usecats'] == 1)){

	echo "<p class=\"meldung_hinweis\"><b>Newsletter werden verschickt...<br /><br />Bitte warten!</b></p>";

	// Empfänger zusammenstellen
	$kategorien = "";
	if($_GET['empf'] == "all")
		$query = "SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0' LIMIT ".mysql_real_escape_string($_GET['start']).",".mysql_real_escape_string($intervall)."";
	elseif($_GET['empf'] == "cats" && $settings['usecats'] == 1){
		// Vorhandene Kategorien in Array einlesen
		$chosencats = array();
		$listcats = mysql_query("SELECT id,catname FROM ".$mysql_tables['mailcats']."");
		while($rowcats = mysql_fetch_assoc($listcats)){
			$chosencats[$rowcats['id']] = stripslashes($rowcats['catname']);
			}

		$where = "catids = '0' OR catids = ',0,'";
		$catarray = explode(",",$_GET['empfcats']);
		foreach($catarray as $cat){
			$where .= " OR catids LIKE '%,".$cat.",%'";
			$kategorien .= $chosencats[$cat].", ";
			}
			
		$query = "SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (".$where.") LIMIT ".mysql_real_escape_string($_GET['start']).",".mysql_real_escape_string($intervall)."";
		}

	$mail_header = _01newsletter_getMailHeader();
	
	// Newsletter-Text holen:
	$getmail = mysql_query("SELECT betreff,mailinhalt,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_GET['newsletter_id'])."'");
	while($mailrow = mysql_fetch_assoc($getmail)){
		$mailinhalt = stripslashes($mailrow['mailinhalt']);
		$betreff    = stripslashes($mailrow['betreff']);
		if(!empty($mailrow['attachments']) && $settings['attachments'] == 1)
			$attachments = explode("|",$row['attachments']);
		}
		
	if(!empty($settings['newslettersignatur']) && isset($_POST['use_signatur']) && $_POST['use_signatur'] == 1){
		$mailinhalt .= "\n\n".$settings['newslettersignatur'];
		}

	include_once($modulpath.$tempdir."lang_vars.php");
	$lang['austragen']	= "\n\n".$lang['austragen'];
	
	if($settings['attachments'] == 1 && isset($attachments)){
		$cup = 0;
		$boundary = strtoupper(md5(uniqid(time())));

		$mail_header .= "\nMIME-Version: 1.0"."";
		$mail_header .= "\nContent-Type: multipart/mixed;  boundary=\"".$boundary."\"";
		$mail_header .= "\n\nThis is a multi-part message in MIME format  --  Dies ist eine mehrteilige Nachricht im MIME-Format";
		
		$header_attachment = "";
		foreach($attachments as $attachment){
			$dateiname_org		= $attachmentuploaddir.$attachment; // ggf. inkl. Pfad
		
			if(file_exists($dateiname_org)){
				// Dateinamen für E-Mail holen
				$list = mysql_query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".mysql_real_escape_string($attachment)."' LIMIT 1");
				$row = mysql_fetch_assoc($list); 
				
			    $file_content = fread(fopen($dateiname_org,"r"),filesize($dateiname_org));
			    $file_content = chunk_split(base64_encode($file_content));
		
			    $header_attachment .= "\nContent-Type: ".mime_content_type($dateiname_org)."; name=\"".$row['name']."\"";
			    $header_attachment .= "\nContent-Transfer-Encoding: base64";
			    $header_attachment .= "\nContent-Disposition: attachment; filename=\"".$row['name']."\"";
			    $header_attachment .= "\n\n".$file_content."";
			    $header_attachment .= "\n--".$boundary."";
			    
			    $cup++;
				}
			}
		}

	$list = mysql_query($query);
	while($row = mysql_fetch_assoc($list)){
		$abmeldelink = addParameter2Link($settings['formzieladdr'],"email=".$row['email']."&send=Go&action=edit",true);

		if($settings['attachments'] == 1 && isset($attachments) && $cup > 0){
			$header_add = "\n--".$boundary."";
			$header_add .= "\nContent-Type: text/plain";
			$header_add .= "\nContent-Transfer-Encoding: 8bit";
			$header_add .= "\n\n".$mailinhalt.str_replace("#abmeldelink#",$abmeldelink,$lang['austragen'])."";
			$header_add .= "\n--".$boundary."";
			
			mail($row['email'],$betreff,"",$mail_header.$header_add.$header_attachment);
			}
		else{
			mail($row['email'],$betreff,$mailinhalt.str_replace("#abmeldelink#",$abmeldelink,$lang['austragen']),$mail_header);
			}
		}
	$empfmenge = mysql_num_rows($list);

	// Abbruch nachdem alle Mails versendet wurden:
	if($empfmenge < $intervall)
	    echo "<p class=\"meldung_erfolg\"><b>Es wurden ".($_GET['sent']+$empfmenge)." Newsletter erfolgreich verschickt.</b><br />
				Der Versand ist hiermit beendet.</p>";
	else{
		$new_start = $_GET['start']+$intervall;
		echo "<script type=\"text/javascript\">redirect(\"popups.php?modul=".$modul."&action=send_letter&newsletter_id=".$_GET['newsletter_id']."&start=".$new_start."&empf=".stripslashes($_GET['empf'])."&empfcats=".stripslashes($_GET['empfcats'])."&sent=".($_GET['sent']+$empfmenge)."\");</script>";
		echo "<a href=\"popups.php?modul=".$modul."&amp;action=send_letter&amp;newsletter_id=".$_GET['newsletter_id']."&amp;start=".$new_start."&amp;empf=".stripslashes($_GET['empf'])."&amp;empfcats=".stripslashes($_GET['empfcats'])."&amp;sent=".($_GET['sent']+$empfmenge)."\">Weiter</a>";
		}

	}

?>