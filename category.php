<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: https://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Newsletter-Kategorieverwaltung
	#fv.140#
*/

// Berechtigungsabfragen
if($userdata['settings'] == 1){
?>
<h1>Kategorien verwalten</h1>

<h2>Neue Kategorie anlegen</h2>

<?php
// Neue Kategorie anlegen
if(isset($_POST['action']) && $_POST['action'] == "newcat" && isset($_POST['catname']) && !empty($_POST['catname'])){

	$sql_insert = "INSERT INTO ".$mysql_tables['mailcats']." (catname)
				   VALUES ('".$mysqli->escape_string($_POST['catname'])."')";
	$mysqli->query($sql_insert) OR die($mysqli->error);

	echo "<p class=\"meldung_erfolg\">Neue Kategorie wurde erfolgreich angelegt.</p>";
	}
elseif(isset($_POST['action']) && $_POST['action'] == "newcat"){
	echo "<p class=\"meldung_error\">Bitte geben Sie einen Namen f&uuml;r die neue Kategorie ein!</p>";
	}
?>

<form action="<?PHP echo $filename; ?>" method="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

    <tr>
        <td width="30%"><b>Bitte Kategoriename eingeben</b></td>
        <td width="20%">&nbsp;</td>
    </tr>

    <tr>
        <td><input type="text" name="catname" size="30" class="input_text" /></td>
        <td align="right"><input type="submit" value="Anlegen &raquo;" class="input" /><input type="hidden" name="action" value="newcat" /></td>
    </tr>
</table>
</form>

<h2>Kategorien</h2>

<?php 
if(isset($_POST['action']) && $_POST['action'] == "rename_cats"){
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']."");
	while($row = $list->fetch_assoc()){
		if(isset($_POST['catname_'.$row['id']]) && !empty($_POST['catname_'.$row['id']]))
			$mysqli->query("UPDATE ".$mysql_tables['mailcats']." SET catname='".$mysqli->escape_string($_POST['catname_'.$row['id']])."' WHERE id='".$mysqli->escape_string($row['id'])."'");
		}

	echo "<p class=\"meldung_erfolg\">Kategorien wurden umbenannt.</p>";
	}
?>

<form action="<?PHP echo $filename; ?>" method="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

    <tr>
        <td><b>Kategoriename</b></td>
        <td align="center"><b>Eingetragene Adressen</b></td>
        <td width="30" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Kategorie l&ouml;schen" /></td>
    </tr>

<?PHP
$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
while($row = $list->fetch_assoc()){
    $listcats = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE catids = '0' OR catids = ',0,' OR catids LIKE '%,".$row['id'].",%'");
	echo "<tr id=\"id".$row['id']."\">
              <td align=\"left\"><input type=\"text\" name=\"catname_".$row['id']."\" value=\"".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."\" size=\"40\" class=\"input_text\" /></td>
			  <td align=\"center\"><a href=\"_loader.php?modul=".$modul."&amp;loadpage=emails&amp;catid=".$row['id']."\">".$listcats->num_rows."</a></td>
			  <td align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Eintrag l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delcat&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
          </tr>";
    }
echo "<tr>
	<td align=\"center\" colspan=\"3\"><input type=\"submit\" name=\"save\" value=\"&Auml;nderungen speichern\" class=\"input\" /></td>
</tr>";
?>
</table>
<input type="hidden" name="action" value="rename_cats" />
</form>
<br />

<?PHP
}else $flag_loginerror = TRUE;

?>