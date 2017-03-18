<?PHP
/*
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Frontend-Ausgabe
	#fv.140#
*/

//Hinweis zum Einbinden des Artikelsystems per include();
/*Folgender PHP-Code nötig:

<?PHP
$subfolder 		= "01scripts/";
$modul			= "01newsletter/";

include($subfolder."01module/".$modul."01newsletter.php");
?>

*/

$frontp = 1;
$flag_acp = FALSE;
if(!isset($flag_nocss)) $flag_nocss = FALSE;
if(!isset($flag_utf8))  $flag_utf8	= FALSE;
if(!isset($flag_second)) $flag_second = FALSE;

if(isset($subfolder) && !empty($subfolder)){
    if(substr_count($subfolder, "/") < 1){ $subfolder .= "/"; }
	}
elseif(isset($_GET['rss']) && ($_GET['rss'] == "show_rssfeed" || $_GET['rss'] == "show_commentrssfeed"))
   $subfolder = "../../";
else
	$subfolder = "";

// Globale Config-Datei einbinden
include_once($subfolder."01_config.php");
include_once($subfolder."01acp/system/headinclude.php");
if(!$flag_second) include_once($subfolder."01acp/system/functions.php");
if(!$flag_second) include_once($subfolder."01acp/system/includes/PHPMailerAutoload.php");

$modulvz = $modul."/";
// Modul-Config-Dateien einbinden
include_once($moduldir.$modulvz."_headinclude.php");
include_once($moduldir.$modulvz."_functions.php");

// Variablen
$tempdir	= $moduldir.$modulvz.$tempdir;			// Template-Verzeichnis

// Sprachvariablen einfügen
include_once($tempdir."lang_vars.php");
$lang['mail_acode'] = $settings['newslettertitel'].": ".$lang['mail_acode'];
$lang['mail_ecode'] = $settings['newslettertitel'].": ".$lang['mail_ecode'];
$lang['mail_dcode'] = $settings['newslettertitel'].": ".$lang['mail_dcode'];

$filename = $_SERVER['PHP_SELF'];
$meldung = "";

// Notice: Undefined index: ... beheben
if(!isset($_REQUEST['email']))		$_REQUEST['email'] = "";
if(!isset($_REQUEST['action']))		$_REQUEST['action'] = "";
if(!isset($_REQUEST['cats']))		$_REQUEST['cats'] = "";
if(!isset($_REQUEST['sendregform']))$_REQUEST['sendregform'] = "";
if(!isset($_REQUEST['acode']))		$_REQUEST['acode'] = "";
if(!isset($_REQUEST['ecode']))		$_REQUEST['ecode'] = "";
if(!isset($_REQUEST['dcode']))		$_REQUEST['dcode'] = "";

//Link-String generieren
$system_link = addParameter2Link($filename,"email=".$_REQUEST['email']);

// externe CSS-Datei / CSS-Eigenschaften?
if(isset($settings['extern_css']) && !empty($settings['extern_css']) && filter_var($settings['extern_css'], FILTER_VALIDATE_URL) !== FALSE && !$flag_nocss)
	$echo_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$settings['extern_css']."\" />";
elseif(!$flag_nocss)
	$echo_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$tempdir."style.css\" />";
else $echo_css = "";

// Main_Top einfügen
include($tempdir."main_top.html");

// Infos einer ggf. übergebene E-Mail-Adresse aus Datenbank auslesen
$row = _01newsletter_getEmailData($_REQUEST['email']);


// Neuregistrierung oder Fake-Neuregistrierung
if(isset($_REQUEST['sendregform']) && !empty($_REQUEST['sendregform']) &&
	isset($_REQUEST['email']) && !empty($_REQUEST['email']) && strlen($_REQUEST['email']) <= $email_max_len && check_mail($_REQUEST['email']) &&
   ($settings['use_nutzungsbedingungen'] == 0 || $settings['use_nutzungsbedingungen'] == 1 && empty($settings['nutzungsbedingungen']) ||
    isset($_REQUEST['ok_nutzungsbed']) && $_REQUEST['ok_nutzungsbed'] == 1)){
	// E-Mail-Adresse wurde bereits registriert aber noch nicht aktiviert --> Eintrag löschen und neu registrieren
    if(!empty($row['email']) && check_mail($row['email']) && strlen($row['acode']) == 32){
    	$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE email = '".$mysqli->escape_string($row['email'])."' LIMIT 1");
    	$row = FALSE;
    }

	// Echte Registrierung
	if($row == FALSE){
	    // Kategorien parsen:
	    if(isset($_REQUEST['cats']) && !empty($_REQUEST['cats']) && is_array($_REQUEST['cats']) && !in_array("all",$_REQUEST['cats'])){
	        $cats_string = ",";
	        $cats_string .= implode(",",$_REQUEST['cats']);
	        $cats_string .= ",";
	        }
	    else
	        $cats_string = 0;
	        
	    $acode = md5(time().$_SERVER['REMOTE_ADDR'].mt_rand(1, 9999999999999).$_REQUEST['email']);

	    $name = NULL;
	    if($flag_utf8 && $use_name && isset($_REQUEST['name']) && !empty($_REQUEST['name']))
			$name = CleanStr(utf8_decode($_REQUEST['name']));
		elseif($use_name && isset($_REQUEST['name']) && !empty($_REQUEST['name']))
			$name = CleanStr($_REQUEST['name']);
	    
	    $sql_insert = "INSERT INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,name,catids)
	            VALUES(
	               '".$acode."',
	               '0',
	               '0',
	               '".time()."',
	               '".$mysqli->escape_string(strtolower($_REQUEST['email']))."',
	               '".$mysqli->escape_string($name)."',
	               '".$mysqli->escape_string($cats_string)."'
	               )";
	    $mysqli->query($sql_insert) OR die($mysqli->error);
	    
	    $mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
	    $mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
	    if($use_name)
	    	$mail_inhalt = str_replace($name_replace," ".$name,$mail_inhalt);
	   	else
	   		$mail_inhalt = str_replace($name_replace,"",$mail_inhalt);

	    $mail = new PHPMailer;
	    _01newsletter_configurePHPMailer($mail);
	    $mail->addAddress($_REQUEST['email']);
	    $mail->Subject = $lang['mail_acode'];
	    $mail->Body    = $mail_inhalt;
	    $mail->send();
	}
    
    $meldung = $lang['meldung_registriert'];

    include($tempdir."meldungen.html");
    $formcodename = "acode";
    include($tempdir."formular_acode.html");
    }
// Registrierungsanfrage
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "add" || isset($_REQUEST['sendregform']) && !empty($_REQUEST['sendregform'])){
	$mailcats = "<option value=\"all\" selected=\"selected\">".$lang['allcats']."</option>\n";

	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
	while($row = $list->fetch_assoc()){
		$mailcats .= "<option value=\"".$row['id']."\">".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
	}

	include($tempdir."formular_registrierung.html");
}


// Abonnement-Daten in DB ändern
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "update_account" && strlen($row['editcode']) == 32 && $row['editcode'] == $_POST['ecode']){
	// Kategorien parsen:
	if(isset($_REQUEST['cats']) && $_REQUEST['cats'] != "" && is_array($_REQUEST['cats']) && !in_array("all",$_REQUEST['cats'])){
		$cats_string = ",";
		$cats_string .= implode(",",$_REQUEST['cats']);
		$cats_string .= ",";
		}
	else
		$cats_string = 0;

    $name = NULL;
    if($flag_utf8 && $use_name && isset($_REQUEST['name']) && !empty($_REQUEST['name']))
		$name = CleanStr(utf8_decode($_REQUEST['name']));
	elseif($use_name && isset($_REQUEST['name']) && !empty($_REQUEST['name']))
		$name = CleanStr($_REQUEST['name']);

	$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET catids = '".$mysqli->escape_string($cats_string)."', editcode='', name='".$mysqli->escape_string($name)."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");
	
	$meldung = $lang['meldung_changes'];
	include($tempdir."meldungen.html");
	}
// Eindeutigen Editier-Code zur Bearbeitung des eigenen Abonnements versenden
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "edit" || isset($_REQUEST['action']) && $_REQUEST['action'] == "update_account"){
	if(!empty($row['email']) && check_mail($row['email'])){
	    $ecode = md5(time().$_SERVER['REMOTE_ADDR'].mt_rand(1, 9999999999999).$_REQUEST['email']);
	    
	    $mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET editcode='".$ecode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");
	    
	    $mail_inhalt = str_replace("#ecodelink#",addParameter2Link($settings['formzieladdr'],"ecode=".$ecode),$lang['mailinhalt_ecode']);
	    $mail_inhalt = str_replace("#ecode#",$ecode,$mail_inhalt);

	    $mail = new PHPMailer;
	    _01newsletter_configurePHPMailer($mail);
	    $mail->addAddress($row['email']);
	    $mail->Subject = $lang['mail_ecode'];
	    $mail->Body    = $mail_inhalt;
	    $mail->send();
	}
    
    $meldung = $lang['meldung_ecode_send'];
    $formcodename = "ecode";

    include($tempdir."meldungen.html");
    include($tempdir."formular_acode.html");
    }


// Lösch-Wunsch eintragen / Neuen Lösch-Code versenden
elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "delabo"){
	if(!empty($row['email']) && check_mail($row['email'])){
		$dcode = md5(time().$_SERVER['REMOTE_ADDR'].mt_rand(1, 9999999999999).$_REQUEST['email']);

		$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET delcode='".$dcode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");

		$mail_inhalt = str_replace("#dcodelink#",addParameter2Link($settings['formzieladdr'],"dcode=".$dcode),$lang['mailinhalt_dcode']);
		$mail_inhalt = str_replace("#dcode#",$dcode,$mail_inhalt);

		$mail = new PHPMailer;
		_01newsletter_configurePHPMailer($mail);
		$mail->addAddress($row['email']);
		$mail->Subject = $lang['mail_dcode'];
		$mail->Body    = $mail_inhalt;
		$mail->send();
	}

	$meldung = $lang['meldung_dcode_send'];
	$formcodename = "dcode";

	include($tempdir."meldungen.html");
	include($tempdir."formular_acode.html");
	}


// Aktivierungscode übergeben?
elseif(isset($_REQUEST['acode']) && !empty($_REQUEST['acode']) && strlen($_REQUEST['acode']) == 32){
	if($settings['send_benachrichtigung'] == 1){
		$list = $mysqli->query("SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode='".$mysqli->escape_string($_REQUEST['acode'])."' AND acode != '0' LIMIT 1");
		$row_email = $list->fetch_assoc();
		}
	
	$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET acode = '0' WHERE acode='".$mysqli->escape_string($_REQUEST['acode'])."' AND acode != '0' LIMIT 1");
	
	if($mysqli->affected_rows == 1){
		$meldung = $lang['meldung_activated'];
		
		// E-Mail für Neuregistrierung an Admin versenden
		if($settings['send_benachrichtigung'] == 1){
			$mail = new PHPMailer;
			_01newsletter_configurePHPMailer($mail);
			$mail->addAddress($settings['email_absender']);
			$mail->Subject = $settings['sitename']." - ".$lang['neue_reg_betreff'];
			$mail->Body    = $lang['neue_reg_body'].$row_email['email']."\n\n---\nWebmailer";
			$mail->send();
		}
		
		include($tempdir."meldungen.html");
		}
	else{
		$meldung = $lang['meldung_wrongacode'];

		include($tempdir."meldungen.html");
		}
	}


// Bearbeitungscode übergeben?
elseif(isset($_REQUEST['ecode']) && !empty($_REQUEST['ecode']) && strlen($_REQUEST['ecode']) == 32){
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE editcode = '".$mysqli->escape_string($_REQUEST['ecode'])."' LIMIT 1");
	// Eintrag für übergebenen ecode vorhanden?
	if($list->num_rows > 0){
		while($row = $list->fetch_assoc()){

			// Kategorien aktiviert?
			$mailcats = "";
			if($settings['usecats'] == 1){
				$cats_reg = array();
				if($row['catids'] == "0"){ $sel1 = " selected=\"selected\""; }
				else{
					$sel1 = "";
					$cats_reg = explode(",",$row['catids']);
					}
					
				$mailcats = "<option value=\"all\"".$sel1.">".$lang['allcats']."</option>\n";

				$listcats = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
				while($rowcats = $listcats->fetch_assoc()){
					if($sel1 == "" && in_array($rowcats['id'],$cats_reg)) $sel2 = " selected=\"selected\"";
					else $sel2 = "";
					
					$mailcats .= "<option value=\"".$rowcats['id']."\"".$sel2.">".htmlentities($rowcats['catname'],$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
					}
			}

			$name = "";
		    if(isset($row['name']) && $row['name'] != NULL)
				$name = htmlentities($row['name'],$htmlent_flags,$htmlent_encoding_pub);
			
			$dellink = addParameter2Link($filename,"action=delabo");
			$dellink = addParameter2Link($dellink,"email=".$row['email']);
			
			include($tempdir."formular_account.html");
		}
	}
	else{
		$meldung = $lang['meldung_wrongacode'];

		include($tempdir."meldungen.html");
	}
}
// Löschcode übergeben?
elseif(isset($_REQUEST['dcode']) && !empty($_REQUEST['dcode']) && strlen($_REQUEST['dcode']) == 32){
	$list = $mysqli->query("SELECT email FROM ".$mysql_tables['emailadds']." WHERE delcode='".$mysqli->escape_string($_REQUEST['dcode'])."' AND delcode != '0' LIMIT 1");
	if($list->num_rows == 1){
		$row_email = $list->fetch_assoc();
		
		$mysqli->query("DELETE FROM ".$mysql_tables['temp_table']." WHERE email = '".$mysqli->escape_string($row_email['email'])."' AND email != ''");
		$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE delcode='".$mysqli->escape_string($_REQUEST['dcode'])."' AND delcode != '0' LIMIT 1");
	}

	if($mysqli->affected_rows == 1){
		$meldung = $lang['meldung_deleted'];
		include($tempdir."meldungen.html");
	}
	else{
		$meldung = $lang['meldung_wrongacode'];

		include($tempdir."meldungen.html");
	}
}
// Nichts übergeben -> Register-Formular anzeigen
else
	include($tempdir."formular_addemail.html");


// Main_Bottom einfügen
include($tempdir."main_bottom.html");
?>