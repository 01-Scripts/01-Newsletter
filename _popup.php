<?php 
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: https://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Programmcode für Modul-Popup-Fenster
	#fv.140#
*/

// Newsletter ausgeben
if(isset($_REQUEST['action']) && $_REQUEST['action'] == "show_letter" &&
   isset($_REQUEST['var1']) && !empty($_REQUEST['var1']) && is_numeric($_REQUEST['var1'])){
   
	$list = $mysqli->query("SELECT betreff,mailinhalt,attachments FROM ".$mysql_tables['archiv']." WHERE id = '".$mysqli->escape_string($_REQUEST['var1'])."'");
	while($row = $list->fetch_assoc()){
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
				$dateiname_org		= $picuploaddir.$attachment;		// ggf. inkl. Pfad
			else
				$dateiname_org		= $attachmentuploaddir.$attachment; // ggf. inkl. Pfad

			if(file_exists($dateiname_org) && $dateiname_org != $attachmentuploaddir && $dateiname_org != $picuploaddir){
				// Echten Dateinamen holen
				$list = $mysqli->query("SELECT orgname FROM ".$mysql_tables['files']." WHERE name = '".$mysqli->escape_string($attachment)."' LIMIT 1");
				$row = $list->fetch_assoc();

				if(empty($row['orgname'])) $row['orgname'] = $attachment;
				
				echo "<li><a href=\"".$dateiname_org."\" target=\"_blank\">".$row['orgname']."</a></li>";
				}
			}
		}	
	
	}
// Newsletter-Text bearbeiten (Formular)
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "edit_letter" &&
   isset($_REQUEST['var1']) && !empty($_REQUEST['var1']) && is_numeric($_REQUEST['var1'])){
   
	$list = $mysqli->query("SELECT id,betreff,mailinhalt FROM ".$mysql_tables['archiv']." WHERE id = '".$mysqli->escape_string($_REQUEST['var1'])."' AND (art = 'm' OR art = 'y' OR art = 'a' AND utimestamp > UNIX_TIMESTAMP()) LIMIT 1");
	$row = $list->fetch_assoc();
	echo "<h2>Bearbeiten</h2>";

	if($settings['use_html'])
    echo loadTinyMCE("advanced","","","","top");
?>
<form action="<?PHP echo $filename; ?>" method="post" name="post">

	<input type="text" name="betreff" value="<?php echo stripslashes($row['betreff']); ?>" size="70" class="input_text" />
	<br /><br />
	<textarea name="mailinhalt" rows="15" cols="80"><?php echo stripslashes($row['mailinhalt']); ?></textarea>
	<br /><br />
	<input type="hidden" name="id" value="<?PHP echo $row['id']; ?>" />
	<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
	<input type="hidden" name="action" value="save" />
	<input type="reset" class="input" value="Reset" />
	<input type="submit" name="senden" value="Text speichern" class="input" id="button2" />

</form>
<?PHP	
	}
elseif(isset($_POST['action']) && $_POST['action'] == "save" && isset($_POST['id']) && !empty($_POST['id']) && is_numeric($_POST['id'])){
	$mysqli->query("UPDATE ".$mysql_tables['archiv']." SET betreff = '".$mysqli->escape_string($_POST['betreff'])."', mailinhalt = '".$mysqli->escape_string($_POST['mailinhalt'])."' WHERE id = '".$mysqli->escape_string($_POST['id'])."' AND (art = 'm' OR art = 'y' OR art = 'a' AND utimestamp > UNIX_TIMESTAMP()) LIMIT 1");

	echo "<p class=\"meldung_erfolg\"><b>Text erfolgreich gespeichert!</b></p>";
}
elseif(isset($_POST['action']) && $_POST['action'] == "save"){
	echo "<p class=\"meldung_error\"><b>Sie haben nicht alle ben&ouml;tigten Felder ausgef&uuml;llt.</b><br />
	<br />
	<a href=\"javascript:history.back();\">&laquo; Bitte gehen Sie zur&uuml;ck</a></p>";
}

?>