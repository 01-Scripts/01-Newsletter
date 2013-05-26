<?PHP
/*
	01-Newsletter - Copyright 2009-2013 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Auflistung aller eingetragener E-Mail-Adressen
	#fv.131#
*/

// Berechtigungsabfragen
if($userdata['show_emails'] == 1){

// Notice: Undefined index: ... beheben
if(!isset($_GET['search'])) 	$_GET['search'] = "";
if(!isset($_GET['sort'])) 		$_GET['sort'] = "";
if(!isset($_GET['orderby'])) 	$_GET['orderby'] = "";
if(!isset($_GET['site'])) 		$_GET['site'] = "";
if(!isset($_GET['catid']))		$_GET['catid'] = "";

list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM);

if(isset($_POST['action']) && $_POST['action'] == "doadd" &&
   isset($_POST['email']) && !empty($_POST['email']) && check_mail($_POST['email']) &&
   (($settings['usecats'] == 0 || $catmenge <= 1) || $settings['usecats'] == 1 && $catmenge > 1 && isset($_POST['cats']) && $_POST['cats'] != "")){
	echo "<h1>E-Mail-Adresse hinzuf&uuml;gen</h1>";
	
	// Kategorien parsen:
	if(isset($_POST['cats']) && $_POST['cats'] != "" && is_array($_POST['cats']) && !in_array("all",$_POST['cats']) && $settings['usecats'] == 1 && $catmenge > 1){
		$cats_string = ",";
		$cats_string .= implode(",",$_POST['cats']);
		$cats_string .= ",";
		}
	else
		$cats_string = 0;

	if(isset($_POST['acode']) && $_POST['acode'] == 1){
		$zahl = mt_rand(1, 9999999999999);
		$acode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_POST['email']);
		}
	else
		$acode = "0";
		
	// E-Mail-Adresse bereits vorhanden?
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE email = '".$mysqli->escape_string($_POST['email'])."' LIMIT 1");
	if($list->num_rows == 0){
	
		$sql_insert = "INSERT INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,catids,newcatids)
				   		VALUES(
						   '".$acode."',
						   '0',
						   '0',
						   '".time()."',
						   '".$mysqli->escape_string($_POST['email'])."',
						   '".$mysqli->escape_string($cats_string)."',
						   '0'
						   )";
		$mysqli->query($sql_insert) OR die($mysqli->error);
		
		if(isset($_POST['acode']) && $_POST['acode'] == 1){
			// Sprachvariablen einfügen
			include_once($tempdir."lang_vars.php");
			$lang['mail_acode'] = $settings['newslettertitel'].": ".$lang['mail_acode'];
			$lang['mail_ecode'] = $settings['newslettertitel'].": ".$lang['mail_ecode'];
			$lang['mail_dcode'] = $settings['newslettertitel'].": ".$lang['mail_dcode'];
			
			$mail_header = "From:".$settings['email_absender']."<".$settings['email_absender'].">\n";
			$mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
			$mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
			$empf = preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "",$_POST['email']);
	    	$empf = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "",$empf);
	
			mail($empf,$lang['mail_acode'],$mail_inhalt,$mail_header);
			}
			
		echo "<p class=\"meldung_erfolg\"><b>Neue E-Mail-Adresse wurde erfolgreich hinzugef&uuml;gt!</b><br />
		<br />
		<a href=\"".$filename."&amp;action=addemail\">Weitere E-Mail-Adresse hinzuf&uuml;gen &raquo;</a>
		</p>";
		}
	else
		echo "<p class=\"meldung_error\"><b>Diese E-Mail-Adresse ist bereits registriert!</b><br />
		<br />
		<a href=\"javascript:history.back();\">&laquo; Bitte gehen Sie zur&uuml;ck</a></p>";
	}
elseif(isset($_POST['action']) && $_POST['action'] == "doadd")
	echo "<p class=\"meldung_error\"><b>Sie haben nicht alle ben&ouml;tigten Felder ausgef&uuml;llt oder eine falsche E-Mail-Adresse eingegeben.</b><br />
	<br />
	<a href=\"javascript:history.back();\">&laquo; Bitte gehen Sie zur&uuml;ck</a></p>";

if(isset($_GET['action']) && $_GET['action'] == "addemail"){
	echo "<h1>E-Mail-Adresse hinzuf&uuml;gen</h1>";
?>
<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">

	<tr>
        <td class="tra" width="30%"><b>E-Mail-Adresse:</b></td>
        <td class="tra"><input type="text" name="email" value="" size="50" class="input_text" /></td>
    </tr>
<?php 
if($settings['usecats'] == 1 && $catmenge > 1){

	$mailcats = "<option value=\"all\" selected=\"selected\">Alle Kategorien</option>\n";
	
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
	while($row = $list->fetch_assoc()){
		$mailcats .= "<option value=\"".$row['id']."\">".stripslashes($row['catname'])."</option>\n";
		}
?>
	<tr>
        <td class="tra"><b>Kategorien w&auml;hlen:</b></td>
        <td class="tra"><select name="cats[]" multiple="multiple" class="input_select" size="5"><?php echo $mailcats; ?></select></td>
    </tr>
<?php } ?>
	<tr>
        <td class="trb"><b>Aktivierungscode verschicken?</b></td>
        <td class="trb"><input type="checkbox" name="acode" value="1" /></td>
    </tr>

	<tr>
        <td class="tra"><input type="hidden" name="action" value="doadd" /></td>
        <td class="tra" align="right"><input type="submit" value="Hinzuf&uuml;gen &raquo;" class="input" /></td>
    </tr>

</table>
</form>
<?PHP
	}
else{
?>
<h1>Eingetragene E-Mail-Adressen</h1>

<p>
<a href="_loader.php?modul=<?php echo $modul; ?>&amp;loadpage=emails&amp;action=addemail" class="actionbutton"><img src="images/icons/add.gif" alt="Plus-Zeichen" title="Neue E-Mail-Adresse hinzuf&uuml;gen" style="border:0; margin-right:10px;" />E-Mail-Adresse hinzuf&uuml;gen</a>
</p>

<form action="<?PHP echo $filename; ?>" method="get" style="float:left; margin-right:20px;">
	<input type="text" name="search" value="<?php echo (isset($_GET['search']) && !empty($_GET['search']))?$_GET['search']:"E-Mail-Adressen suchen";  ?>" size="30" onfocus="clearField(this);" onblur="checkField(this);" class="input_search" /> <input type="submit" value="Suchen &raquo;" class="input" />
	<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
	<input type="hidden" name="loadpage" value="emails" />
</form>
<?PHP
// Kategorien zählen um ggf. auszublenden
list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM);
if($catmenge > 0){
?>
<form action="<?PHP echo $filename; ?>" method="get">
	<select name="catid" size="1" class="input_select">
		<?PHP echo _01newsletter_CatDropDown($_GET['catid']); ?>
	</select>
	<input type="hidden" name="modul" value="<?PHP echo $modul; ?>" />
	<input type="hidden" name="loadpage" value="emails" />
	<input type="submit" value="Go &raquo;" class="input" />
</form>
<?PHP
	}
	$where = "";

	if(isset($_GET['sort']) && $_GET['sort'] == "desc") $sortorder = "DESC";
	else{ $sortorder = "ASC"; $_GET['sort'] = "ASC"; }
	
	if(isset($_GET['search']) && !empty($_GET['search'])) $where = " WHERE email LIKE '%".$mysqli->escape_string($_GET['search'])."%'";
	elseif(isset($_GET['catid']) && !empty($_GET['catid']) && is_numeric($_GET['catid'])) $where = " WHERE catids LIKE '%,".$mysqli->escape_string($_GET['catid']).",%' ";

	if(!isset($_GET['orderby'])) $_GET['orderby'] = "";
	switch($_GET['orderby']){
	  case "timestamp":
	    $orderby = "timestamp_reg";
	  break;
	  default:
	    $orderby = "email";
	  break;
	  }

	$sites = 0;
	$query = "SELECT * FROM ".$mysql_tables['emailadds']."".$where." ORDER BY ".$orderby." ".$sortorder;
	$query = makepages($query,$sites,"site",ACP_PER_PAGE);
?>

<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen">
    <tr>
		<td class="tra" width="35" align="center"><b>ID</b></td>
        <td class="tra"><b>E-Mail-Adresse</b>
			<a href="<?PHP echo $filename; ?>&amp;sort=asc"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename; ?>&amp;sort=desc"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td class="tra"><b>Registriert am</b>
			<a href="<?PHP echo $filename; ?>&amp;sort=asc&amp;orderby=timestamp"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename; ?>&amp;sort=desc&amp;orderby=timestamp"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td class="tra" width="100" align="center"><b>Aktiv?</b></td>
		<td class="tra nosort" width="25" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
    </tr>
<?PHP
	// Ausgabe der Datensätze (Liste) aus DB
	$count = 0;
	$list = $mysqli->query($query);
	while($row = $list->fetch_assoc()){
		if($count == 1){ $class = "tra"; $count--; }else{ $class = "trb"; $count++; }
		
		if(strlen($row['acode']) == 32) $aktiv = "-";
		else $aktiv = "<img src=\"images/icons/ok.gif\" alt=\"Gr&uuml;ner OK-Haken\" title=\"Adresse wurde best&auml;tigt und ist aktiv\" />";
		
		echo "    <tr id=\"id".$row['id']."\">
		<td class=\"".$class."\" align=\"center\">".$row['id']."</td>
		<td class=\"".$class."\">".stripslashes($row['email'])."</td>
		<td class=\"".$class."\">".date("d.m.Y",$row['timestamp_reg'])."</td>
		<td class=\"".$class."\" align=\"center\">".$aktiv."</td>
		<td class=\"".$class."\" align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Adresse l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delemailaddy&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
	</tr>";
		}
?>
</table>
<br />

<?php 
echo echopages($sites,"80%","site","search=".$_GET['search']."&amp;catid=".$_GET['catid']."&amp;sort=".$_GET['sort']."&amp;orderby=".$_GET['orderby']."");
	
	}

}else $flag_loginerror = true;

?>