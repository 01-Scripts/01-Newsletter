<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: https://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Auflistung aller eingetragener E-Mail-Adressen
	#fv.140#
*/

include_once("system/includes/PHPMailerAutoload.php");

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
		$acode = md5(time().$_SERVER['REMOTE_ADDR'].mt_rand(1, 9999999999999).$_POST['email']);
		}
	else
		$acode = "0";

	$name = NULL;
	if(isset($_POST['name']) && !empty($_POST['name']))
		$name = CleanStr($_REQUEST['name']);
		
	// E-Mail-Adresse bereits vorhanden?
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE email = '".$mysqli->escape_string($_POST['email'])."' LIMIT 1");
	if($list->num_rows == 0){
	
		$sql_insert = "INSERT INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,name,catids)
				   		VALUES(
						   '".$acode."',
						   '0',
						   '0',
						   '".time()."',
						   '".$mysqli->escape_string(strtolower($_POST['email']))."',
						   '".$mysqli->escape_string($name)."',
						   '".$mysqli->escape_string($cats_string)."'
						   )";
		$mysqli->query($sql_insert) OR die($mysqli->error);
		
		if(isset($_POST['acode']) && $_POST['acode'] == 1){
			// Sprachvariablen einfügen
			include_once($tempdir."lang_vars.php");
			$lang['mail_acode'] = $settings['newslettertitel'].": ".$lang['mail_acode'];
			
			$mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
			$mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
			$mail_inhalt = str_replace($replace_name," ".$name,$mail_inhalt);
	
			$mail = new PHPMailer;
			_01newsletter_configurePHPMailer($mail);
			$mail->addAddress($_POST['email']);
			$mail->Subject = $lang['mail_acode'];
			$mail->Body    = $mail_inhalt;
			$mail->send();
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

if(isset($_GET['action']) && $_GET['action'] == "export"){
	list($emailmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['emailadds']." WHERE acode = '0'")->fetch_array(MYSQLI_NUM);
?>
<h1>E-Mail-Adresse exportieren</h1>

<p class="meldung_hinweis">
	Beim Export einzelner Kategorien sind auch alle Abonnenten enthalten, die <b>alle</b> Kategorien abonniert haben!
</p>

<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

	<tr>
        <td width="30%">Alle E-Mail-Adressen</td>
        <td><a href="_ajaxloader.php?<?PHP echo "modul=".$modul.""; ?>&amp;ajaxaction=csvexport&amp;data=all"><?PHP echo $emailmenge; ?> Adressen exportieren</a></td>
    </tr>

    <?PHP
    $catidlist = $mysqli->query("SELECT id,catname FROM ".$mysql_tables['mailcats']." ORDER BY catname");
	while($row = $catidlist->fetch_assoc()){
		$listcats = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (catids = '0' OR catids = ',0,' OR catids LIKE '%,".$row['id'].",%')");
		echo "	<tr>
        <td>".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."</td>
        <td><a href=\"_ajaxloader.php?modul=".$modul."&amp;ajaxaction=csvexport&amp;data=".$row['id']."\">".$listcats->num_rows." Adressen exportieren</a></td>
    </tr>";
	}
    ?>

</table>

<?PHP
	} // Ende: Export
elseif(isset($_GET['action']) && $_GET['action'] == "addemail"){
?>
<h1>E-Mail-Adresse hinzuf&uuml;gen</h1>

<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

	<tr>
        <td width="30%"><b>E-Mail-Adresse:</b></td>
        <td><input type="text" name="email" value="" size="50" class="input_text" maxlength="<?PHP echo $email_max_len; ?>" /></td>
    </tr>
<?php if ($use_name == TRUE): ?>
	<tr>
        <td><b>Name:</b></td>
        <td><input type="text" name="name" value="" size="50" class="input_text" maxlength="<?PHP echo $name_max_len; ?>" /></td>
    </tr>
<?php endif; ?>
<?php 
if($settings['usecats'] == 1 && $catmenge > 1){

	$mailcats = "<option value=\"all\" selected=\"selected\">Alle Kategorien</option>\n";
	
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
	while($row = $list->fetch_assoc()){
		$mailcats .= "<option value=\"".$row['id']."\">".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
		}
?>
	<tr>
        <td><b>Kategorien w&auml;hlen:</b></td>
        <td><select name="cats[]" multiple="multiple" class="input_select" size="5"><?php echo $mailcats; ?></select></td>
    </tr>
<?php } ?>
	<tr>
        <td><b>Aktivierungscode verschicken?</b></td>
        <td><input type="checkbox" name="acode" value="1" /></td>
    </tr>

	<tr>
        <td><input type="hidden" name="action" value="doadd" /></td>
        <td align="right"><input type="submit" value="Hinzuf&uuml;gen &raquo;" class="input" /></td>
    </tr>

</table>
</form>
<?PHP
	}
else{
?>
<h1>E-Mail-Adressen</h1>

<p>
	<a href="_loader.php?modul=<?php echo $modul; ?>&amp;loadpage=emails&amp;action=addemail" class="actionbutton"><img src="images/icons/add.gif" alt="Plus-Zeichen" title="Neue E-Mail-Adresse hinzuf&uuml;gen" style="border:0; margin-right:10px;" />E-Mail-Adresse hinzuf&uuml;gen</a>
	<a href="_loader.php?modul=<?php echo $modul; ?>&amp;loadpage=csvimport&amp;action=import" class="actionbutton"><img src="images/icons/icon_upload.gif" alt="Symbol: Hochladen" title="E-Mail-Adresse importieren" style="border:0; margin-right:10px; width: 16px; height: 16px;" />E-Mail-Adresse importieren</a>
	<a href="_loader.php?modul=<?php echo $modul; ?>&amp;loadpage=emails&amp;action=export" class="actionbutton"><img src="<?PHP echo $modulpath."images/icon_export.png"; ?>" alt="Symbol: Exportieren" title="E-Mail-Adresse exportieren" style="border:0; margin-right:10px; width: 16px; height: 16px;" />E-Mail-Adresse exportieren</a>
</p>

<form action="<?PHP echo $filename; ?>" method="get" style="float:left; margin-right:20px;">
	<input type="text" name="search" value="<?php echo (isset($_GET['search']) && !empty($_GET['search']))?$_GET['search']:"Suche...";  ?>" size="30" onfocus="clearField(this);" onblur="checkField(this);" class="input_search" /> <input type="submit" value="Suchen &raquo;" class="input" />
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
	
	if(isset($_GET['search']) && !empty($_GET['search'])) $where = " WHERE email LIKE '%".CleanStr($_GET['search'])."%' OR name LIKE '%".CleanStr($_GET['search'])."%'";
	elseif(isset($_GET['catid']) && !empty($_GET['catid']) && is_numeric($_GET['catid'])) $where = " WHERE catids LIKE '%,".CleanStr($_GET['catid']).",%' ";

	if(!isset($_GET['orderby'])) $_GET['orderby'] = "";
	switch($_GET['orderby']){
	  case "timestamp":
	    $orderby = "timestamp_reg";
	  case "name":
	    $orderby = "name";
	  break;
	  default:
	    $orderby = "email";
	  break;
	  }

	$sites = 0;
	$query = "SELECT * FROM ".$mysql_tables['emailadds']."".$where." ORDER BY ".$orderby." ".$sortorder;
	$query = makepages($query,$sites,"site",ACP_PER_PAGE);
?>

<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">
    <tr>
		<td width="35" align="center"><b>ID</b></td>
        <td><b>E-Mail-Adresse</b>
			<a href="<?PHP echo $filename; ?>&amp;sort=asc"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename; ?>&amp;sort=desc"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
        <td><b>Name</b>
			<a href="<?PHP echo $filename; ?>&amp;sort=asc&amp;orderby=name"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename; ?>&amp;sort=desc&amp;orderby=name"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td><b>Registriert am</b>
			<a href="<?PHP echo $filename; ?>&amp;sort=asc&amp;orderby=timestamp"><img src="images/icons/sort_asc.gif" alt="Icon: Pfeil nach oben" title="Aufsteigend sortieren" /></a>
			<a href="<?PHP echo $filename; ?>&amp;sort=desc&amp;orderby=timestamp"><img src="images/icons/sort_desc.gif" alt="Icon: Pfeil nach unten" title="Absteigend sortieren (DESC)" /></a>
		</td>
		<td width="100" align="center"><b>Aktiv?</b></td>
		<td class="nosort" width="25" align="center"><!--Löschen--><img src="images/icons/icon_trash.gif" alt="M&uuml;lleimer" title="Datei l&ouml;schen" /></td>
    </tr>
<?PHP
	// Ausgabe der Datensätze (Liste) aus DB
	$list = $mysqli->query($query);
	while($row = $list->fetch_assoc()){
		
		if(strlen($row['acode']) == 32) $aktiv = "-";
		else $aktiv = "<img src=\"images/icons/ok.gif\" alt=\"Gr&uuml;ner OK-Haken\" title=\"Adresse wurde best&auml;tigt und ist aktiv\" />";
		
		echo "    <tr id=\"id".$row['id']."\">
		<td align=\"center\">".$row['id']."</td>
		<td>".$row['email']."</td>
		<td>".htmlentities($row['name'],$htmlent_flags,$htmlent_encoding_acp)."</td>
		<td>".date("d.m.Y",$row['timestamp_reg'])."</td>
		<td align=\"center\">".$aktiv."</td>
		<td align=\"center\"><img src=\"images/icons/icon_delete.gif\" alt=\"L&ouml;schen - rotes X\" title=\"Adresse l&ouml;schen\" class=\"fx_opener\" style=\"border:0; float:left;\" align=\"left\" /><div class=\"fx_content tr_red\" style=\"width:60px; display:none;\"><a href=\"#foo\" onclick=\"AjaxRequest.send('modul=".$modul."&ajaxaction=delemailaddy&id=".$row['id']."');\">Ja</a> - <a href=\"#foo\">Nein</a></div></td>
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