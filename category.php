<?PHP
/*
	01-Newsletter - Copyright 2009 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Newsletter-Kategorieverwaltung
	#fv.1000#
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
				   VALUES ('".mysql_real_escape_string($_POST['catname'])."')";
	mysql_query($sql_insert) OR die(mysql_error());

	echo "<p class=\"meldung_erfolg\">Neue Kategorie wurde erfolgreich angelegt.</p>";
	}
elseif(isset($_POST['action']) && $_POST['action'] == "newcat"){
	echo "<p class=\"meldung_error\">Bitte geben Sie einen Namen f&uuml;r die neue Kategorie ein!</p>";
	}
?>

<form action="<?PHP echo $filename; ?>" method="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">

    <tr>
        <td width="30%" class="tra"><b>Bitte Kategoriename eingeben</b></td>
        <td width="20%" class="tra">&nbsp;</td>
    </tr>

    <tr>
        <td class="trb"><input type="text" name="catname" size="30" class="input_text" /></td>
        <td class="trb" align="right"><input type="submit" value="Anlegen &raquo;" class="input" /><input type="hidden" name="action" value="newcat" /></td>
    </tr>
</table>
</form>

<h2>Kategorien</h2>

<?php 
if(isset($_POST['action']) && $_POST['action'] == "rename_cats"){
	$list = mysql_query("SELECT * FROM ".$mysql_tables['mailcats']."");
	while($row = mysql_fetch_array($list)){
		if(isset($_POST['catname_'.$row['id']]) && !empty($_POST['catname_'.$row['id']]))
			mysql_query("UPDATE ".$mysql_tables['mailcats']." SET catname='".mysql_real_escape_string($_POST['catname_'.$row['id']])."' WHERE id='".mysql_real_escape_string($row['id'])."'");
		}

	echo "<p class=\"meldung_erfolg\">Kategorien wurden umbenannt.</p>";
	}
?>

<form action="<?PHP echo $filename; ?>" method="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">

    <tr>
        <td class="tra"><b>Kategoriename</b></td>
        <td class="tra" align="center"><b>Eingetragene Adressen</b></td>
        <td width="30" class="tra" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Kategorie l&ouml;schen" /></td>
    </tr>

<?PHP
$count = 0;
$list = mysql_query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
while($row = mysql_fetch_array($list)){
    if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }

    $listcats = mysql_query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE catids = '0' OR catids = ',0,' OR catids LIKE '%,".$row['id'].",%'");
	echo "<tr id=\"id".$row['id']."\">
              <td align=\"left\" class=\"".$class."\"><input type=\"text\" name=\"catname_".$row['id']."\" value=\"".stripslashes($row['catname'])."\" size=\"40\" class=\"input_text\" /></td>
			  <td align=\"center\" class=\"".$class."\">".mysql_num_rows($listcats)."</td>
			  <td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Eintrag l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delcat&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
          </tr>";
    }
if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
echo "<tr>
	<td align=\"center\" class=\"".$class."\" colspan=\"2\"><input type=\"submit\" name=\"save\" value=\"&Auml;nderungen speichern\" class=\"input\" /></td>
</tr>";
?>
</table>
<input type="hidden" name="action" value="rename_cats" />
</form>
<br />





<?PHP
}else $flag_loginerror = true;

// 01-Newslettersystem Copyright 2009 by Michael Lorer - 01-Scripts.de
?>