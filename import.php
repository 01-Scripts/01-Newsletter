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

function PrintImportTableHeader($col){
	echo "        <td><select name=\"destination_col_".$col."\" class=\"input_select\" size=\"1\"><option value=\"NONE\">- Import-Ziel w&auml;hlen -</option><option value=\"NONE\">Nicht importieren</option><option value=\"email\">E-Mail-Adresse</option><option value=\"name1\">1. Namensbestandteil</option><option value=\"name2\">2. Namensbestandteil</option></td>\n";
}

list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM);

if(isset($_POST['action']) && $_POST['action'] == "doimport" && 
    isset($_POST['rows_max']) && is_numeric($_POST['rows_max']) && $_POST['rows_max'] >= 1 &&
    (($settings['usecats'] == 0 || $catmenge <= 1) || $settings['usecats'] == 1 && $catmenge > 1 && isset($_POST['cats']) && $_POST['cats'] != "")){

    if(isset($_POST['acode']) && $_POST['acode'] == 1){
        include_once($tempdir."lang_vars.php");

        // Mail-Header
        $mail = new PHPMailer;
        _01newsletter_configurePHPMailer($mail);
        $mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead

        $mail->Subject = $settings['newslettertitel'].": ".$lang['mail_acode'];
    }

    // Kategorien parsen:
    if(isset($_POST['cats']) && $_POST['cats'] != "" && is_array($_POST['cats']) && !in_array("all",$_POST['cats']) && $settings['usecats'] == 1 && $catmenge > 1){
        $cats_string = ",";
        $cats_string .= implode(",",$_POST['cats']);
        $cats_string .= ",";
        }
    else
        $cats_string = 0;

    $x = 0; $values = "";
    for ($row=0; $row < $_POST['rows_max']; $row++){
        if(isset($_POST['row_'.$row.'_email']) && check_mail($_POST['row_'.$row.'_email'])){
            $acode = "0"; $name = "";

            // Adress already exists?
            list($mail_exists) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['emailadds']." WHERE email = '".$mysqli->escape_string(strtolower($_POST['row_'.$row.'_email']))."'")->fetch_array(MYSQLI_NUM);
            if($mail_exists >= 1)
                continue;

            if(isset($_POST['row_'.$row.'_name']) && !empty($_POST['row_'.$row.'_name']))
                $name = CleanStr($_POST['row_'.$row.'_name']);

            if(isset($_POST['acode']) && $_POST['acode'] == 1){
                $acode = md5(time().mt_rand(1, 9999999999999).$_POST['row_'.$row.'_email']);

                $mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
                $mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
                $mail_inhalt = str_replace($replace_name," ".$name,$mail_inhalt);

                $mail->Body  = $mail_inhalt;
                $mail->addAddress($_POST['row_'.$row.'_email']);
                $mail->send();
                $mail->clearAddresses();
            }

            if($x > 0) $values .= ",\n";

            $values .= "('".$acode."','0','0','".time()."','".$mysqli->escape_string(strtolower($_POST['row_'.$row.'_email']))."','".$name."','".$mysqli->escape_string($cats_string)."')";
            
            $x++;
        }
    }

    if($values != "")
        $mysqli->query("INSERT IGNORE INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,name,catids) VALUES ".$values.";") OR die($mysqli->error);
?>
<h1>E-Mail-Adressen importiert</h1>

<p class="meldung_erfolg">
    <b><?PHP echo $x; ?> von <?PHP echo $_POST['rows_max']; ?> E-Mail-Adresse wurde erfolgreich importiert!</b>
</p>

<?PHP
}
elseif(isset($_POST['action']) && $_POST['action'] == "datacheck" && 
	isset($_POST['cols_max']) && is_numeric($_POST['cols_max']) && $_POST['cols_max'] >= 1 &&
	isset($_POST['rows_max']) && is_numeric($_POST['rows_max']) && $_POST['rows_max'] >= 1){

	// E-Mail und Namen(s) Spalten festlegen. Bei Mehrfach-Auswahl gelingt die Spalte am weitesten "links"
	for ($col=($_POST['cols_max']-1); $col >= 0; $col--) {
		if(isset($_POST['destination_col_'.$col]) && !empty($_POST['destination_col_'.$col])){
	    	switch($_POST['destination_col_'.$col]){
	    		case "email":
	    			$col_email = $col;
	    		break;
	    		case "name1":
	    			$col_name1 = $col;
	    		break;
	    		case "name2":
	    			$col_name2 = $col;
	    		break;
	    		default:
	    		break;
	    	}
	    }
    }

    // Es muss zumindest eine Spalte für die E-Mail-Adresse gewählt sein
    if(!isset($col_email)){
    	echo "<h1>E-Mail-Adressen importieren (Schritt 3/3)</h1>";
    	echo "<p class=\"meldung_error\"><b>Fehler:</b> Es wurden keine E-Mail-Adressen zum Import gew&auml;hlt</p>";
    }
    else{
?>
<h1>E-Mail-Adressen importieren (Schritt 3/3)</h1>

<p class="meldung_hinweis">
	Bitte &uuml;berpr&uuml;fen Sie die zu importierenden Daten. Fehlerhafte E-Mail-Adresse sind <b class="red">rot markiert</b> und k&ouml;nnen nicht importiert werden.<br />
	Sie k&ouml;nnen die Adressen ggf. korrigieren.
</p>

<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

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
        <td></td>
    </tr>
<?php } ?>
	<tr>
        <td><b>Aktivierungscode verschicken?</b></td>
        <td><input type="checkbox" name="acode" value="1" /></td>
        <td></td>
    </tr>

	<tr>
        <td><b>E-Mail-Adresse</b></td>
        <td><b>Name</b></td>
        <td></td>
    </tr>

<?PHP
	for ($row=0; $row < $_POST['rows_max']; $row++){
        echo "    <tr>\n";
        if(isset($_POST['row_'.$row.'_col_'.$col_email]) && !empty($_POST['row_'.$row.'_col_'.$col_email])){
        	if(!check_mail($_POST['row_'.$row.'_col_'.$col_email]))
        		$class = " bcred";
        	else
        		$class = "";
        	echo "        <td><input type=\"text\" name=\"row_".$row."_email\" value=\"".htmlentities($_POST['row_'.$row.'_col_'.$col_email],$htmlent_flags,$htmlent_encoding_acp)."\" size=\"40\" class=\"input_text".$class."\" /></td>\n";
        }

        if(isset($col_name1) || isset($col_name2)){
        	if(isset($col_name1) && isset($_POST['row_'.$row.'_col_'.$col_name1]) && !empty($_POST['row_'.$row.'_col_'.$col_name1]))
        		$name = CleanStr($_POST['row_'.$row.'_col_'.$col_name1]);
        	else
        		$name = "";
        	if(isset($col_name2) && isset($_POST['row_'.$row.'_col_'.$col_name2]) && !empty($_POST['row_'.$row.'_col_'.$col_name2]))
        		$name .= " ".CleanStr($_POST['row_'.$row.'_col_'.$col_name2]);
        	else
        		$name .= "";

        	echo "        <td><input type=\"text\" name=\"row_".$row."_name\" value=\"".htmlentities(trim($name),$htmlent_flags,$htmlent_encoding_acp)."\" size=\"30\" class=\"input_text\" /></td>\n";
        }
        else
        	echo "        <td></td>\n";
        echo "        <td></td>\n";
        echo "    </tr>\n";
	}
?>

	<tr>
        <td colspan="2"><input type="hidden" name="rows_max" value="<?PHP echo $row; ?>" /><input type="hidden" name="action" value="doimport" /></td>
        <td align="right"><input type="submit" value="Importieren &raquo;" class="input" /></td>
    </tr>

</table>
</form>

<?PHP
	} // Ende: Es muss zumindest eine Spalte für die E-Mail-Adresse gewählt sein
}
elseif(isset($_POST['action']) && $_POST['action'] == "doupload" && isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != ""){
?>
<h1>E-Mail-Adressen importieren (Schritt 2/3)</h1>

<p class="meldung_hinweis">
	Legen Sie nun &uuml;ber die Dropdown-Boxen fest welche Spalte als E-Mail-Adresse importiert werden soll und welche Spalte(n) den zu importierenden Namen enth&auml;lt.<br />
	Sollte Vor- und Nachname in zwei separaten Spalten gespeichert sein, w&auml;hlen Sie bitte den Vornamen als 1. Namensbestandteil und den Nachnamen als 2.Namensbestandteil.
	Beide Felder werden beim Import dann zusammengef&uuml;gt.<br />
	<br />
	<b>Im n&auml;chsten Schritt erhalten Sie eine Vorschau der Daten die importiert werden und k&ouml;nnen die zu abonnierenden Kategorien ausw&auml;hlen.</b>
</p>

<form action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">
<?PHP
	if(isset($_POST['delimiter']) && !empty($_POST['delimiter']) && strlen($_POST['delimiter']) == 1)
		$delimiter = $_POST['delimiter'];
	else
		$delimiter = ";";
	if(isset($_POST['enclosure']) && !empty($_POST['enclosure']) && strlen($_POST['enclosure']) == 1)
		$enclosure = $_POST['enclosure'];
	else
		$enclosure = '"';
	if(isset($_POST['escape']) && !empty($_POST['escape']) && strlen($_POST['escape']) == 1)
		$escape = $_POST['escape'];
	else
		$escape = "\\";
	if(isset($_POST['skip']) && !empty($_POST['skip']) && is_numeric($_POST['skip']))
		$skip = $_POST['skip'];
	else
		$skip = 0;


	@ini_set('auto_detect_line_endings',TRUE);
	$row = 0;
	$skipRows = 0;
	$num = 0;
	if (($handle = fopen($_FILES['csv_file']['tmp_name'], "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== FALSE) {
	    	if($skipRows < $skip){
	    		$skipRows++;
	    		continue;
	    	}

	    	// Erste Zeile gibt die maximale Anzahl an Spalten vor
	    	if($num == 0){
	        	$num = count($data);
				for ($col=0; $col < $num; $col++) {
		        	PrintImportTableHeader($col);
		        }
	    	}

	        echo "    <tr>\n";
	        for ($col=0; $col < $num; $col++) {
	        	echo "        <td><input type=\"text\" name=\"row_".$row."_col_".$col."\" value=\"".htmlentities($data[$col],$htmlent_flags,$htmlent_encoding_acp)."\" size=\"15\" class=\"input_text\" readonly=\"readonly\" /></td>\n";
	        }
	        echo "    </tr>\n";

	        $row++;
	    }
	    fclose($handle);
	}
	@ini_set('auto_detect_line_endings',FALSE);
?>
	<tr>
        <td colspan="<?PHP echo ($num-1); ?>"><input type="hidden" name="cols_max" value="<?PHP echo $num; ?>" /><input type="hidden" name="rows_max" value="<?PHP echo $row; ?>" /><input type="hidden" name="action" value="datacheck" /></td>
        <td align="right"><input type="submit" value="Weiter &raquo;" class="input" /></td>
    </tr>

</table>
</form>
<?PHP
}
elseif(isset($_GET['action']) && $_GET['action'] == "import"){
?>
<h1>E-Mail-Adressen importieren (Schritt 1/3)</h1>

<p class="meldung_hinweis">
	Sie haben die M&ouml;glichkeit E-Mail-Adressen aus einer CSV-Datei zu importieren. Die Datei sollte in einer Spalte E-Mail-Adressen in einer zweiten 
	Spalte optional den Empf&auml;ngernamen enthalten.<br />
	Passen Sie die nachfolgenden Optionen ggf. an das spezifische Format ihrer CSV-Datei an. Ausf&uuml;hrliche Informationen zum Import finden Sie <a href="https://www.01-scripts.de/01newsletter.php?install#csvimport" target="_blank">hier</a>.<br />
	Im n&auml;chsten Schritt haben Sie die M&ouml;glichkeit die Daten vor dem Import zu überpr&uuml;fen und Kategorien f&uuml;r den Import zu w&auml;hlen.
</p>

<p class="meldung_frage">
	Eine Excel-Datei kann &uuml;ber <code>Datei -&gt; Exportieren -&gt; Dateityp &auml;ndern -&gt; CSV</code> als CSV-Datei gespeichert werden.
</p>

<form enctype="multipart/form-data" action="<?PHP echo $filename; ?>" method="post" name="post">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="5" class="rundrahmen trab">

	<tr>
        <td width="30%"><b>CSV-Datei:</b></td>
        <td><input type="file" name="csv_file" class="input_text" /></td>
    </tr>

	<tr>
        <td><b>Feld-Trennzeichen:</b></td>
        <td><input type="text" name="delimiter" value=";" size="1" class="input_text fieldcenter" maxlength="1" /></td>
    </tr>

	<tr>
        <td><b>Feld-Begrenzungs Zeichen:</b></td>
        <td><input type="text" name="enclosure" value='"' size="1" class="input_text fieldcenter" maxlength="1" /></td>
    </tr>

	<tr>
        <td><b>Maskierungs-Zeichen:</b></td>
        <td><input type="text" name="escape" value="\" size="1" class="input_text fieldcenter" maxlength="1" /></td>
    </tr>

	<tr>
        <td><b>Anzahl Zeilen &uuml;berspringen:</b></td>
        <td><input type="text" name="skip" value="1" size="1" class="input_text fieldcenter" maxlength="5" /></td>
    </tr>

	<tr>
        <td><input type="hidden" name="action" value="doupload" /></td>
        <td align="right"><input type="submit" value="Weiter &raquo;" class="input" /></td>
    </tr>

</table>
</form>

<p class="meldung_hinweis">
	Bitte beachten Sie, dass das ungefragte Zusenden von E-Mail-Newslettern an Empf&auml;nger ohne deren Zustimmung nicht gestattet ist 
	und zu kostenpflichtigen Abmahnungen führen kann. <a href="https://www.e-recht24.de/artikel/ecommerce/6534-newsletter-rechtssicher-erstellen-und-versenden.html" target="_blank">Weitere Informationen zum Thema</a>.
</p>

<?PHP
}


}else $flag_loginerror = true;

?>