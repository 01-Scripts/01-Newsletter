<?php 
/*
	01-Newsletter - Copyright 2009-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Popup-Inhalt
	#fv.130#
*/

// Newsletter ausgeben
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "show_letter" &&
   isset($_REQUEST['var1']) && !empty($_REQUEST['var1']) && is_numeric($_REQUEST['var1'])){
   
	$list = mysql_query("SELECT betreff,mailinhalt,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_REQUEST['var1'])."'");
	while($row = mysql_fetch_array($list)){
		echo "<h2>".$row['betreff']."</h2>";
		
		// HTML ggf. ber�cksichtigen
		$found_html = FALSE;
		$arr = array("</p>","</table>","</a>","<img","<br />","<hr","<ul","<b","<i","<span","<td"); 
		foreach($arr as $search_needle){ 
			if(stristr($row['mailinhalt'], $search_needle) != FALSE){ 
				$found_html = TRUE;
				break;
				} 
			}
		
		if($found_html)
			echo $row['mailinhalt'];
		else
			echo "<p>".nl2br($row['mailinhalt'])."</p>";
		
		if(!empty($row['attachments']))
		    $attachments = explode("|",$row['attachments']);
		}
		
	// Attachments ggf. auflisten
	if(isset($attachments) && is_array($attachments)){
		echo "<h3>Dateianh&auml;nge</h3>";
		echo "<ul>";
		foreach($attachments as $attachment){
			if(in_array(getEndung($attachment),$picendungen))
				$dateiname_org		= $picuploaddir.$attachment; // ggf. inkl. Pfad
			else
				$dateiname_org		= $attachmentuploaddir.$attachment; // ggf. inkl. Pfad

			if(file_exists($dateiname_org) && $dateiname_org != $attachmentuploaddir && $dateiname_org != $picuploaddir){
				// Echten Dateinamen holen
				$list = mysql_query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".mysql_real_escape_string($attachment)."' LIMIT 1");
				$row = mysql_fetch_assoc($list);

				if(empty($row['orgname'])) $row['orgname'] = $attachment;
				
				echo "<li><a href=\"".$dateiname_org."\" target=\"_blank\">".$row['orgname']."</a></li>";
				}
			}
		}	
	
	}
	
	

	
// Newsletter versenden
if(isset($_GET['action']) && $_GET['action'] == "send_letter" &&
   isset($_GET['newsletter_id']) && !empty($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id']) &&
   isset($_GET['start']) && is_numeric($_GET['start']) &&
   isset($_GET['empf']) && ($_GET['empf'] == "all" || $_GET['empf'] == "cats" && isset($_GET['empfcats']) && !empty($_GET['empfcats']) && $settings['usecats'] == 1)){

	echo "<p class=\"meldung_hinweis\"><b>Newsletter werden verschickt...<br /><br />Bitte warten!</b></p>";

	// Empf�nger zusammenstellen
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
	$mail_body   = "";
	
	// Newsletter-Text holen:
	$getmail = mysql_query("SELECT betreff,mailinhalt,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_GET['newsletter_id'])."'");
	while($mailrow = mysql_fetch_assoc($getmail)){
		$mailinhalt = stripslashes($mailrow['mailinhalt']);
		$betreff    = stripslashes($mailrow['betreff']);
		if(!empty($mailrow['attachments']) && $settings['attachments'] == 1)
			$attachments = explode("|",$mailrow['attachments']);
		}

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
	
	include_once($modulpath.$tempdir."lang_vars.php");
	if($settings['use_html'])
	    $lang['austragen']	= "<br /><br />".$lang['austragen'];
	else
		$lang['austragen']	= "\n\n".$lang['austragen'];
	
	// Attachments ggf. anh�ngen
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
				// Dateinamen f�r E-Mail holen
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