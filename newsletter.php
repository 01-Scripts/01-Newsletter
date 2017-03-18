<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Neuen Newsletter verfassen (Formular) und absenden
	#fv.140#
*/

// Formular abgesendet (Entwurf / Vorlage / für Versand speichern)
if(isset($_POST['action']) && $_POST['action'] == "send" &&
   isset($_POST['mailtext']) && !empty($_POST['mailtext']) &&
   (
   		(isset($_POST['vorlagenname']) && !empty($_POST['vorlagenname']) && isset($_POST['savevorlage']) && !empty($_POST['savevorlage']) && $userdata['vorlagen'] == 1) || 

   		isset($_POST['betreff']) && !empty($_POST['betreff']) &&
   		isset($_POST['empf']) && ($_POST['empf'] == "all" || $_POST['empf'] == "test" && !empty($_POST['testempf']) || $_POST['empf'] == "cats" && isset($_POST['empfcats']) && !empty($_POST['empfcats']) && $settings['usecats'] == 1)
   )
   		){
	
	// Empfänger zusammenstellen (zum Speichern bei Entwürfen)
	$save_cat = "all";
	if($_POST['empf'] == "cats" && $settings['usecats'] == 1 && isset($_POST['empfcats']) && is_array($_POST['empfcats']))
		$save_cat = implode(",",$_POST['empfcats']);

	// Empfänger zusammenstellen (zur Ermittlung der betreffenden E-Mail-Adressen und zum Speichern im Klartext beim Versand im Archiv)
	if(isset($_POST['senden']) && !empty($_POST['senden'])){
		$save_cat = "";
		if($_POST['empf'] == "all")
			$query = "SELECT email,name FROM ".$mysql_tables['emailadds']." WHERE acode = '0'";
		elseif($_POST['empf'] == "cats" && $settings['usecats'] == 1){
			// Vorhandene Kategorien in Array einlesen
			$chosencats = array();
			$listcats = $mysqli->query("SELECT id,catname FROM ".$mysql_tables['mailcats']."");
			while($rowcats = $listcats->fetch_assoc()){
				$chosencats[$rowcats['id']] = $rowcats['catname'];
				}
	
			$where = "catids = '0' OR catids = ',0,'";
			foreach($_POST['empfcats'] as $cat){
				$where .= " OR catids LIKE '%,".$cat.",%'";
				$save_cat .= $chosencats[$cat].", ";
				}
	
			$query = "SELECT email,name FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (".$where.")";
			}
		elseif($_POST['empf'] == "test"){
			if(!check_mail(trim($_POST['testempf'])))
				$row['email'] = explode(",", trim($_POST['testempf']));
			else
				$row['email'][0] = trim($_POST['testempf']);
			}
		}
	// bei Vorlagen
	elseif(isset($_POST['savevorlage']) && !empty($_POST['savevorlage']) && $userdata['vorlagen'] == 1)
	    $save_cat = "";

    // Newsletter-Signatur anhängen (nur bei Versand)
	if(!empty($settings['newslettersignatur']) && isset($_POST['use_signatur']) && $_POST['use_signatur'] == 1 && isset($_POST['senden']) && !empty($_POST['senden'])){
		if($settings['use_html'])
		    $mailinhalt = $_POST['mailtext']."<br /><br />".nl2br($settings['newslettersignatur']);
		else	
			$mailinhalt = $_POST['mailtext']."\n\n".$settings['newslettersignatur'];
		}
	else
		$mailinhalt = $_POST['mailtext'];
		
	// Anhänge
	if($settings['attachments'] == 1 && isset($_POST['attachfieldcounter']) && is_numeric($_POST['attachfieldcounter']) && $_POST['attachfieldcounter'] > 0){
		$attachments = array();
		for($x=1;$x<=$_POST['attachfieldcounter'];$x++){
			if(isset($_POST['attachment'.$x]) && !empty($_POST['attachment'.$x]))
				$attachments[] = $_POST['attachment'.$x]; 
			}
		$attachment_string = implode("|",$attachments);
		}
	else $attachment_string = "";
	
	// Betreff
	if(isset($_POST['vorlagenname']) && !empty($_POST['vorlagenname']) && isset($_POST['savevorlage']) && !empty($_POST['savevorlage']) && $userdata['vorlagen'] == 1)
		$titel = $_POST['vorlagenname'];
	else
		$titel = $_POST['betreff'];
	
	// Timestamp für zeitversetzten Versand anpassen
	if(isset($_POST['send_time']) && !empty($_POST['send_time']) && !isset($_POST['savevorlage'])){
		$send_date = explode(".",$_POST['send_time']);
		$timestamp = mktime("0", "0", "1", $send_date[1], $send_date[0], $send_date[2]);
		}
	elseif(isset($_POST['entwurf']) && !empty($_POST['entwurf']))
	    $timestamp = 0;	// leer bei Entwurf
	else
		$timestamp = time();
	
	// Speicherart ermitteln
	if(isset($_POST['senden']) && !empty($_POST['senden']))
	    $art = "a";
	elseif(isset($_POST['entwurf']) && !empty($_POST['entwurf']))
	    $art = "e";
	elseif(isset($_POST['savevorlage']) && !empty($_POST['savevorlage']) && $userdata['vorlagen'] == 1)
	    $art = "v";
	
	// Newsletter in MySQL-Tabelle eintragen
	if(isset($_POST['entwurfid']) && !empty($_POST['entwurfid']) && is_numeric($_POST['entwurfid'])){
		$mysqli->query("UPDATE ".$mysql_tables['archiv']." SET art = '".$art."', utimestamp = '".$timestamp."', betreff = '".$mysqli->escape_string($titel)."', mailinhalt = '".$mysqli->escape_string($mailinhalt)."', kategorien = '".$mysqli->escape_string($save_cat)."', attachments = '".$mysqli->escape_string($attachment_string)."' WHERE id='".$mysqli->escape_string($_POST['entwurfid'])."' AND uid = '".$userdata['id']."' LIMIT 1");
		$var = $_POST['entwurfid'];
		}
	else{
		$sql_insert = "INSERT INTO ".$mysql_tables['archiv']." (art,utimestamp,uid,betreff,mailinhalt,kategorien,attachments)
		   		VALUES(
				   '".$art."',
				   '".$timestamp."',
				   '".$userdata['id']."',
				   '".$mysqli->escape_string($titel)."',
				   '".$mysqli->escape_string($mailinhalt)."',
				   '".$mysqli->escape_string($save_cat)."',
				   '".$mysqli->escape_string($attachment_string)."'
				   )";
		$mysqli->query($sql_insert) OR die($mysqli->error);
		$var = $mysqli->insert_id;
		}
	
	// Bei Versand: Empfänger in temporäre Tabelle übertragen
	if(isset($_POST['senden']) && !empty($_POST['senden'])){
		$x = 0; $values = "";
		if($_POST['empf'] == "test"){
			foreach($row['email'] as $email){
				if(check_mail(trim($email))){
					if($x > 0) $values .= ",\n";

					$values .= "('".$timestamp."','".$var."','".$mysqli->escape_string(trim($email))."', '')";

					$x++;
				}
			}
		}
		else{
			$list = $mysqli->query($query);
			while($row = $list->fetch_assoc()){
				if($x > 0) $values .= ",\n";
				
				$values .= "('".$timestamp."','".$var."','".$row['email']."','".$row['name']."')";
				
				$x++;
				}
		}
		
		if($values != "")
			$mysqli->query("INSERT INTO ".$mysql_tables['temp_table']." (utimestamp, message_id, email, name) VALUES ".$values.";") OR die($mysqli->error);
		
		// Newsletter sofort via IFrame verschicken
		if(($settings['use_cronjob'] == 0 || $_POST['empf'] == "test") && $values != "" && !mysql_error()){
			echo "<h1>Newsletter wird verschickt...</h1>";
			
			echo "<iframe src=\"".$modulpath."_cronjob.php?message_id=".$var."\" width=\"90%\" height=\"300\" name=\"send_newsletter\">
<p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis aufrufen: <a href=\"".$modulpath."_cronjob.php?message_id=".$var."\" target=\"_blank\">Newsletter versenden</a></p>
</iframe>";
			}
		// Fehlermeldung, wenn die eingegebene Test-E-Mail-Adresse fehlerhaft ist
		elseif($_POST['empf'] == "test")
			echo "<p class=\"meldung_error\"><b>Der Test-Newsletter konnte nicht versendet werden, da keine gültige E-Mail-Adresse eingegeben wurde.</b></p>";			
		// Newsletter wird später via Cronjob verschickt
		else
			echo "<p class=\"meldung_erfolg\"><b>Der Newsletter wurde erfolgreich gespeichert und wird zum gew&uuml;nschten Zeitpunkt automatisch per Cronjob versendet.</b><br />
<br />
<a href=\"".$filename."&amp;action=new\">Einen neuen Newsletter verfassen &raquo;</a></p>";
		}

	// Meldungen ausgeben
	if(isset($_POST['entwurf']) && !empty($_POST['entwurf']))
	    echo "<p class=\"meldung_erfolg\"><b>Der Newsletter wurde als Entwurf gespeichert.</b><br />
<br />
<a href=\"".$filename."&amp;action=new\">Einen neuen Newsletter verfassen &raquo;</a></p>";
	elseif(isset($_POST['savevorlage']) && !empty($_POST['savevorlage']) && $userdata['vorlagen'] == 1)
	    echo "<p class=\"meldung_erfolg\"><b>Der Newsletter wurde als Vorlage gespeichert.</b><br />
<br />
<a href=\"".$filename."&amp;action=new\">Einen neuen Newsletter verfassen &raquo;</a></p>";

	}
// Formular anzeigen
elseif(isset($_POST['action']) && $_POST['action'] == "send" ||
   isset($_GET['action']) && $_GET['action'] == "new"){
	if(!isset($_POST['betreff'])) $_POST['betreff'] = $settings['newslettertitel']." - ";
	if(!isset($_POST['mailtext'])) $_POST['mailtext'] = "";
	
	if($settings['use_html'])
	    echo loadTinyMCE("advanced","","","","top");

	// Inhalt von archiviertem Newsletter laden?
	if(isset($_GET['copyid']) && is_numeric($_GET['copyid']) && $_GET['copyid'] > 0){
		$list = $mysqli->query("SELECT * FROM ".$mysql_tables['archiv']." WHERE art = 'a' AND id = '".$mysqli->escape_string($_GET['copyid'])."' LIMIT 1");
		$row = $list->fetch_assoc();
		if(isset($row['betreff']) && !empty($row['betreff'])){
			$_POST['betreff'] = $row['betreff'];
			$_POST['mailtext'] = $row['mailinhalt'];
		}
	}
	
	echo "<h1>Newsletter verfassen</h1>";
	
	// Nicht alle nötigen Felder ausgefüllt -> Fehlermeldung
	if(isset($_POST['action']) && $_POST['action'] == "send")
		echo "<p class=\"meldung_error\">Sie haben nicht alle ben&ouml;tigten Pflichtfelder ausgef&uuml;llt!</p>";

?>

<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">

<?php 
list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM);
?>
    <tr class="trb">
		<td colspan="2"><h2>Empf&auml;nger</h2></td>
	</tr>

	<tr>
        <td align="center"><input type="radio" name="empf" value="all"<?php if(isset($_POST['empf']) && $_POST['empf'] == "all" || $settings['usecats'] != 1 || $catmenge < 1) echo " checked=\"checked\""; ?> /></td>
        <td><b>An alle Abonnenten</b></td>
    </tr>
<?php if($settings['usecats'] == 1 && $catmenge >= 1){ ?>    
    <tr>
        <td align="center"><input type="radio" name="empf" value="cats"<?php if(isset($_POST['empf']) && $_POST['empf'] == "cats") echo " checked=\"checked\""; ?> /></td>
        <td><b>An Abonnenten einer bestimmten Kategorien:</b><br />
		<select name="empfcats[]" size="5" multiple="multiple" class="input_select" style="width:275px">
			<?php 
			$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
			while($row = $list->fetch_assoc()){
				if(isset($_POST['empfcats']) && is_array($_POST['empfcats']) && in_array($row['id'],$_POST['empfcats'])) $sel = " selected=\"selcted\"";
				else $sel = "";
				
				echo "<option value=\"".$row['id']."\"".$sel.">".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
				}
			?>
		</select><br /><span class="small">Zur Mehrfachauswahl STRG-Taste gedr&uuml;ckt halten.</span>
		</td>
    </tr>
<?php } ?>
	<tr>
        <td align="center"><input type="radio" name="empf" value="test"<?php if(isset($_POST['empf']) && $_POST['empf'] == "test") echo " checked=\"checked\""; ?> /></td>
        <td>
        	<b>Test-Newsletter an folgende Adressen versenden:</b><br />
        	<input type="text" size="35" name="testempf" value="<?php if(isset($_POST['testempf'])){ echo $_POST['testempf']; } ?>" class="input_text" style="width:275px"> <span class="small">(kommasepariert)</span>
        </td>
    </tr>

    <tr class="trb">
		<td colspan="2"><h2>Nachrichtentext</h2></td>
	</tr>
<?php
list($evmenge) = @$mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['archiv']." WHERE art = 'e' AND uid = '".$userdata['id']."' OR art = 'v'")->fetch_array(MYSQLI_NUM);
if($evmenge >= 1){

	$c = 0;
	$seloptions = "";
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['archiv']." WHERE art = 'e' AND uid = '".$userdata['id']."' OR art = 'v' ORDER BY art,utimestamp DESC");
	while($row = $list->fetch_assoc()){
		if($c == 0 && $row['art'] == "e"){
			$seloptions = "<option value=\"x\" style=\"background-color: #282858; color:#FFF;\">Gespeicherten Entwurf laden:</option>\n";
			$c = 1;
			}
		elseif($c <= 1 && $row['art'] == "v"){
			$seloptions .= "<option value=\"x\" style=\"background-color: #282858; color:#FFF;\">Eine Vorlage laden:</option>\n";
			$c = 2;
			}
		
		if($row['utimestamp'] == 0) $row['utimestamp'] = time();
		$seloptions .= "<option value=\"".$row['id']."\">".date("d.m.Y",$row['utimestamp'])." - ".$row['betreff']."</option>\n";
		}
?>
    <tr>
        <td><b>Entwurf / Vorlage</b></td>
        <td>
			<select name="vorlage" size="1" class="input_select" style="float: left; width:275px;" onchange="Start_Loading_standard(); AjaxRequest.send('modul=<?php echo $modul; ?>&ajaxaction=load_vorlage&id='+this.options[this.selectedIndex].value+'');">
				<?php echo $seloptions; ?>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<div id="delvorlage" style="display:none; float: left;">
				<img src="images/icons/icon_delete.gif" alt="L&ouml;schen - rotes X" title="Entwurf / Vorlage wirklich l&ouml;schen?" class="fx_opener" style="border:0;" align="left" /><div class="fx_content tr_red" style="width:160px; border:1px solid #000; display:none;"><a href="#foo" onclick="AjaxRequest.send('modul=<?php echo $modul; ?>&ajaxaction=del_vorlage&id='+document.post.vorlage.options[document.post.vorlage.selectedIndex].value+'&selindex='+document.post.vorlage.selectedIndex+'');">Eintrag l&ouml;schen</a> - <a href="#foo">Abbrechen</a></div>
			</div>
		</td>
    </tr>
<?php 
	}
?>
    <tr>
        <td style="width:100px"><h3>Betreff</h3></td>
        <td><input type="text" name="betreff" value="<?php echo htmlentities($_POST['betreff'],$htmlent_flags,$htmlent_encoding_acp); ?>" size="57" class="input_text" style="font-size:14pt" /></td>
    </tr>
    
	<tr>
		<td colspan="2">
			<textarea name="mailtext" rows="15" cols="80"><?php echo $_POST['mailtext']; ?></textarea>
<?php if ($use_name == TRUE): ?>
			<br />
			<span class="small">Fügen Sie <b><?PHP echo $name_replace; ?></b> in den Newsletter ein, um es beim Versand durch den Namen des Empfängers ersetzen zu lassen!</span>
<?php endif; ?>
		</td>
	</tr>
<?php if($settings['attachments'] == 1){ ?>	
    <tr class="trb">
		<td colspan="2"><h2 style="float:left; margin-right:10px;">Dateianh&auml;nge</h2><br /><a href="javascript:InsertNewAttachmentField();"><img src="images/icons/add.gif" alt="Plus-Zeichen" title="Dateianhang hinzuf&uuml;gen" style="margin-right: 3px; margin-bottom:-3px;" />Anhang hinzuf&uuml;gen</a></td>
	</tr>	
	<tr>
		<td colspan="2">
			<div id="writeroot">
			<?php if(!isset($_POST['attachfieldcounter']) || isset($_POST['attachfieldcounter']) && (empty($_POST['attachfieldcounter']) || $_POST['attachfieldcounter'] < 1 || !is_numeric($_POST['attachfieldcounter']))) $_POST['attachfieldcounter'] = 1; ?>
			
			<?php for($x=1;$x<=$_POST['attachfieldcounter'];$x++){ ?>
				<input type="text" name="attachment<?php echo $x; ?>" value="<?php if(isset($_POST['attachment'.$x]) && !empty($_POST['attachment'.$x])){ echo $_POST['attachment'.$x]; } ?>" readonly="readonly" size="25" class="input_text" />
				<input type="button" name="filebutton" value="Dateien Durchsuchen..." onclick="popup('uploader','file','post','attachment<?php echo $x; ?>',620,480)" class="input" />
				<input type="button" name="filebutton" value="Bilder Durchsuchen..." onclick="popup('uploader','pic','post','attachment<?php echo $x; ?>',620,480)" class="input" />
				<input type="button" name="empty_file" value="Anhang entfernen" onclick="javascript:post.attachment<?php echo $x; ?>.value='';" class="input" />
				<br />
			<?php } ?>
			</div>
		</td>
	</tr>
		
<?php } ?>
    <tr class="trb">
		<td colspan="2"><h2>Weitere Optionen</h2></td>
	</tr>
<?php if($settings['use_cronjob'] == 1){ ?>
    <tr>
        <td><input type="text" name="send_time" class="DatePicker" size="10" value="<?php if(isset($_POST['send_time']) && !empty($_POST['send_time'])){ echo $_POST['send_time']; }else{ echo date("d.m.Y"); } ?>" /></td>
        <td><b>Versanddatum</b></td>
    </tr>
<?php } ?>
<?php if(!empty($settings['newslettersignatur'])){ ?>	
	<tr>
		<td align="center"><input type="checkbox" name="use_signatur" value="1" checked="checked" /></td>
		<td><b>Signatur anh&auml;ngen</b></td>
	</tr>
<?php } ?>
<?php if($userdata['vorlagen'] == 1){ ?>
	<tr>
		<td align="center"><input type="checkbox" name="als_vorlage" value="1" onclick="alsVorlage();" /></td>
		<td>
			<b>Newsletter als Vorlage speichern</b>
			<div id="vorlagenname">
				Vorlage speichern als: <input type="text" name="vorlagenname" size="50" class="input_text" /> <input type="submit" name="savevorlage" value="Speichern" class="input" />
			</div>
		</td>
	</tr>
<?php } ?>
	<tr id="savetr">
		<td><input type="submit" name="entwurf" value="Entwurf speichern" class="input" id="button1" /></td>
		<td align="right"><input type="submit" name="senden" value="Newsletter versenden / F&uuml;r den Versand speichern" class="input" id="button2" /></td>
	</tr>

</table>

<input type="hidden" name="entwurfid" value="x" />
<input type="hidden" name="action" value="send" />
<input type="hidden" name="attachfieldcounter" value="<?php echo $_POST['attachfieldcounter']; ?>" id="attachfieldcounter" />
</form>

<script type="text/javascript">
<?php if($userdata['vorlagen'] == 1){ ?>
$('vorlagenname').slide('hide');
<?php } ?>
</script>

<?PHP
	} // Ende: Formular-Ausgabe
else{
?>
<script type="text/javascript">
function newsletterpopup(action,var1,var2,var3,w,h) {
window.open('_ajaxloader.php?modul=<?PHP echo $modul; ?>&action='+action+'&var1='+var1+'&var2='+var2+'&var3='+var3+'','_blank','width='+w+',height='+h+',scrollbars=yes,resizable=yes,status=no,toolbar=no,left=400,top=150');
}
</script>

<h1>Newsletter</h1>

<p>
<a href="_loader.php?modul=<?php echo $modul; ?>&amp;loadpage=newsletter&amp;action=new" class="actionbutton"><img src="images/icons/add.gif" alt="Plus-Zeichen" title="Neuen Newsletter schreiben" style="border:0; margin-right:10px;" />Newsletter schreiben</a>
</p>

<form action="<?PHP echo $filename; ?>" method="get" style="float:left; margin-right:20px;">
	<input type="text" name="search" value="<?php echo (isset($_GET['search']) && !empty($_GET['search']))?$_GET['search']:"Archiv durchsuchen";  ?>" size="30" onfocus="clearField(this);" onblur="checkField(this);" class="input_search" /> <input type="submit" value="Suchen &raquo;" class="input" />
	<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
	<input type="hidden" name="loadpage" value="newsletter" />
</form>

<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">
    <tr>
		<td style="width:120px"><b>Versanddatum</b></td>
        <td style="width:400px"><b>Betreff</b></td>
		<td><b>Kategorien</b></td>
		<td><b>Absender</b></td>
		<td class="nosort" style="width:25px" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
    </tr>
<?PHP
	if(isset($_GET['search']) && !empty($_GET['search'])) $where = " AND (betreff LIKE '%".$mysqli->escape_string($_GET['search'])."%' OR mailinhalt LIKE '%".$mysqli->escape_string($_GET['search'])."%' OR kategorien LIKE '%".$mysqli->escape_string($_GET['search'])."%')";
	else $where = "";
	
	// Ausgabe der Datensätze (Liste) aus DB
	$query = "SELECT * FROM ".$mysql_tables['archiv']." WHERE art = 'a'".$where." ORDER BY utimestamp DESC";
	$query = makepages($query,$sites,"site",ACP_PER_PAGE);
	
	$list = $mysqli->query($query);
	while($row = $list->fetch_assoc()){
		
		$data = getUserdatafields($row['uid'],"username");

		echo "    <tr id=\"id".$row['id']."\">
		<td valign=\"bottom\">".date("d.m.Y, H:i",$row['utimestamp'])." Uhr</td>
		<td onmouseover=\"fade_element('copyid_".$row['id']."')\" onmouseout=\"fade_element('copyid_".$row['id']."')\"><a href=\"#\" onclick=\"javascript:modulpopup('".$modul."','show_letter','".$row['id']."','','',510,450);\">".stripslashes($row['betreff'])."</a> <div class=\"moo_inlinehide\" id=\"copyid_".$row['id']."\"><a href=\"_loader.php?modul=".$modul."&amp;loadpage=newsletter&amp;action=new&amp;copyid=".$row['id']."\"><img src=\"".$modulpath."images/icon_copy.gif\" alt=\"Symbol: Kopieren\" title=\"Inhalt in einen neuen Newsletter übernehmen\" /></a></div></td>
		<td valign=\"bottom\">".stripslashes($row['kategorien'])."</td>
		<td valign=\"bottom\">".$data['username']."</td>
		<td valign=\"bottom\" align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Archiveintrag l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delarchiv&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
	</tr>";
		}
?>
</table>

<?PHP
	}
?>