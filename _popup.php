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
		
		// HTML ggf. berücksichtigen
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

?>