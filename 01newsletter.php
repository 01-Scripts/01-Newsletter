<?PHP
/*
	01-Newsletter - Copyright 2009-2013 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php

	Modul:		01newsletter
	Dateiinfo: 	Frontend-Ausgabe
	#fv.131#
*/

//Hinweis zum Einbinden des Artikelsystems per include();
/*Folgender PHP-Code n�tig:

<?PHP
$subfolder 		= "01scripts/";
$modul			= "01newsletter/";

include($subfolder."01module/".$modul."01newsletter.php");
?>

*/

$frontp = 1;
$flag_acp = FALSE;
if(!isset($flag_nocss)) $flag_nocss = FALSE;
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

$modulvz = $modul."/";
// Modul-Config-Dateien einbinden
include_once($moduldir.$modulvz."_headinclude.php");
include_once($moduldir.$modulvz."_functions.php");

// Variablen
$tempdir	= $moduldir.$modulvz.$tempdir;			// Template-Verzeichnis

// Sprachvariablen einf�gen
include_once($tempdir."lang_vars.php");
$lang['mail_acode'] = $settings['newslettertitel'].": ".$lang['mail_acode'];
$lang['mail_ecode'] = $settings['newslettertitel'].": ".$lang['mail_ecode'];
$lang['mail_dcode'] = $settings['newslettertitel'].": ".$lang['mail_dcode'];

$filename = $_SERVER['PHP_SELF'];
$mail_header = _01newsletter_getMailHeader();
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
if(isset($settings['extern_css']) && !empty($settings['extern_css']) && $settings['extern_css'] != "http://" && !$flag_nocss)
	$echo_css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$settings['extern_css']."\" />";
elseif(isset($settings['csscode']) && !empty($settings['csscode']) && !$flag_nocss)
	$echo_css = "<style type=\"text/css\">
".$settings['csscode']."
</style>";
else $echo_css = "";

// Main_Top einf�gen
include($tempdir."main_top.html");





// Wenn eine E-Mail-Adresse �bergeben wurde
if(isset($_REQUEST['email']) && !empty($_REQUEST['email']) && check_mail($_REQUEST['email'])){
	// �berpr�fen, ob E-Mail-Adresse bereits registriert ist
	// Ja ->	Bearbeiten-Seite anzeigen (Account l�schen / Kategorien ggf. �ndern)
	// Nein ->	E-Mail-Adresse neu registrieren bzw. Kategorie-Auswahl anzeigen
	$list = $mysqli->query("SELECT * FROM ".$mysql_tables['emailadds']." WHERE email = '".$mysqli->escape_string($_REQUEST['email'])."' LIMIT 1");
	
	// E-Mail-Adresse bereits vorhanden?
	if($list->num_rows > 0){
		while($row = $list->fetch_assoc()){
			// E-Mail-Adresse wurde bereits registriert aber noch nicht aktiviert
			if(strlen($row['acode']) == 32){
				// Neuen Aktivierungscode verschicken
				if(isset($_REQUEST['action']) && $_REQUEST['action'] == "newacode"){
					$mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$row['acode']),$lang['mailinhalt_acode']);
					$mail_inhalt = str_replace("#acode#",$row['acode'],$mail_inhalt);
					
					mail($row['email'],$lang['mail_acode'],$mail_inhalt,$mail_header);
					
					$meldung = $lang['meldung_newacode'];
					
					include($tempdir."meldungen.html");
					}
				else{
					$meldung = $lang['meldung_acode'];
					$meldung .= "<br /><a href=\"".addParameter2Link($system_link,"action=newacode")."\">".$lang['resend_acode']."</a>";
					
					include($tempdir."meldungen.html");
					}
				
				$formcodename = "acode";
				include($tempdir."formular_acode.html");
				}
			// E-Mail-Adresse ist bereits registriert und aktiviert
			else{
				// Kategorien �ndern
				if(isset($_REQUEST['action']) && $_REQUEST['action'] == "changecats"){
					// Kategorien parsen:
					if(isset($_REQUEST['cats']) && $_REQUEST['cats'] != "" && is_array($_REQUEST['cats']) && !in_array("all",$_REQUEST['cats'])){
						$cats_string = ",";
						$cats_string .= implode(",",$_REQUEST['cats']);
						$cats_string .= ",";
						}
					else
						$cats_string = 0;
	
					$zahl = mt_rand(1, 9999999999999);
					$ecode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);
					
					$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET newcatids = '".$mysqli->escape_string($cats_string)."', editcode='".$ecode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");
					
					$mail_inhalt = str_replace("#ecodelink#",addParameter2Link($settings['formzieladdr'],"ecode=".$ecode),$lang['mailinhalt_ecode']);
					$mail_inhalt = str_replace("#ecode#",$ecode,$mail_inhalt);

					mail($row['email'],$lang['mail_ecode'],$mail_inhalt,$mail_header);
					
					$meldung = $lang['meldung_ecode_send'];
					$formcodename = "ecode";

					include($tempdir."meldungen.html");
					include($tempdir."formular_acode.html");
					}
				// Neuen Edit-Code versenden
				elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "newecode"){
					$zahl = mt_rand(1, 9999999999999);
					$ecode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);

					$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET editcode='".$ecode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");

					$mail_inhalt = str_replace("#ecodelink#",addParameter2Link($settings['formzieladdr'],"ecode=".$ecode),$lang['mailinhalt_ecode']);
					$mail_inhalt = str_replace("#ecode#",$ecode,$mail_inhalt);

					mail($row['email'],$lang['mail_ecode'],$mail_inhalt,$mail_header);

					$meldung = $lang['meldung_newacode'];
					$formcodename = "ecode";
			
					include($tempdir."meldungen.html");
					include($tempdir."formular_acode.html");
					}
				// L�sch-Wunsch eintragen
				elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "delabo"){
					$zahl = mt_rand(1, 9999999999999);
					$dcode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);

					$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET delcode='".$dcode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");

					$mail_inhalt = str_replace("#dcodelink#",addParameter2Link($settings['formzieladdr'],"dcode=".$dcode),$lang['mailinhalt_dcode']);
					$mail_inhalt = str_replace("#dcode#",$dcode,$mail_inhalt);

					mail($row['email'],$lang['mail_dcode'],$mail_inhalt,$mail_header);

					$meldung = $lang['meldung_dcode_send'];
					$formcodename = "dcode";

					include($tempdir."meldungen.html");
					include($tempdir."formular_acode.html");
					}
				// Neuen L�sch-Code versenden
				elseif(isset($_REQUEST['action']) && $_REQUEST['action'] == "newdcode"){
					$zahl = mt_rand(1, 9999999999999);
					$dcode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);

					$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET delcode='".$dcode."' WHERE email='".$mysqli->escape_string($row['email'])."' LIMIT 1");

					$mail_inhalt = str_replace("#dcodelink#",addParameter2Link($settings['formzieladdr'],"dcode=".$dcode),$lang['mailinhalt_ecode']);
					$mail_inhalt = str_replace("#dcode#",$dcode,$mail_inhalt);

					mail($row['email'],$lang['mail_dcode'],$mail_inhalt,$mail_header);

					$meldung = $lang['meldung_newacode'];
					$formcodename = "dcode";

					include($tempdir."meldungen.html");
					include($tempdir."formular_acode.html");
					}
				// Normale "Einstellungen bearbeiten"-Seite anzeigen 
				else{
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
						
						$mailcats .= "<option value=\"".$rowcats['id']."\"".$sel2.">".htmlentities(stripslashes($rowcats['catname']),$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
						}
					
					$dellink = addParameter2Link($filename,"action=delabo");
					$dellink = addParameter2Link($dellink,"email=".$row['email']);
					
					include($tempdir."formular_account.html");
					}
				}
			}
		}
	// E-Mail-Adresse noch nicht vorhanden -> Registrieren
	else{
		// Adresse direkt registrieren und Aktivierungsmail verschicken
		if($settings['usecats'] == 0 && ($settings['use_nutzungsbedingungen'] == 0 || $settings['use_nutzungsbedingungen'] == 1 && empty($settings['nutzungsbedingungen']))){
			$zahl = mt_rand(1, 9999999999999);
			$acode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);
			
			$sql_insert = "INSERT INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,catids,newcatids)
				   		VALUES(
						   '".$acode."',
						   '0',
						   '0',
						   '".time()."',
						   '".$mysqli->escape_string($_REQUEST['email'])."',
						   '0',
						   '0'
						   )";
			$mysqli->query($sql_insert) OR die(mysql_error());
			
			$mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
			$mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
			$empf = preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "",$_REQUEST['email']);
		    $empf = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "",$empf);
			
			mail($empf,$lang['mail_acode'],$mail_inhalt,$mail_header);
			
			$meldung = $lang['meldung_registriert'];
			$meldung .= "<br /><a href=\"".addParameter2Link($system_link,"action=newacode")."\">".$lang['resend_acode']."</a>";

			include($tempdir."meldungen.html");
			}
		// Kategorien anzeigen
		else{
			// Daten in Datenbank speichern
			if(isset($_REQUEST['sendregform']) && !empty($_REQUEST['sendregform']) &&
			   ($settings['use_nutzungsbedingungen'] == 0 || $settings['use_nutzungsbedingungen'] == 1 && empty($settings['nutzungsbedingungen']) ||
			    isset($_REQUEST['ok_nutzungsbed']) && $_REQUEST['ok_nutzungsbed'] == 1)){
				// Kategorien parsen:
				if(isset($_REQUEST['cats']) && !empty($_REQUEST['cats']) && is_array($_REQUEST['cats']) && !in_array("all",$_REQUEST['cats'])){
					$cats_string = ",";
					$cats_string .= implode(",",$_REQUEST['cats']);
					$cats_string .= ",";
					}
				else
					$cats_string = 0;
					
				$zahl = mt_rand(1, 9999999999999);
				$acode = md5(time().$_SERVER['REMOTE_ADDR'].$zahl.$_REQUEST['email']);
				
				$sql_insert = "INSERT INTO ".$mysql_tables['emailadds']." (acode,editcode,delcode,timestamp_reg,email,catids,newcatids)
				   		VALUES(
						   '".$acode."',
						   '0',
						   '0',
						   '".time()."',
						   '".$mysqli->escape_string($_REQUEST['email'])."',
						   '".$mysqli->escape_string($cats_string)."',
						   '0'
						   )";
				$mysqli->query($sql_insert) OR die($mysqli->error);
				
				$mail_inhalt = str_replace("#acodelink#",addParameter2Link($settings['formzieladdr'],"acode=".$acode),$lang['mailinhalt_acode']);
				$mail_inhalt = str_replace("#acode#",$acode,$mail_inhalt);
				$empf = preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)]/im", "",$_REQUEST['email']);
		    	$empf = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "",$empf);
	
				mail($empf,$lang['mail_acode'],$mail_inhalt,$mail_header);
				
				$meldung = $lang['meldung_registriert'];
				$meldung .= "<br /><a href=\"".addParameter2Link($system_link,"action=newacode")."\">".$lang['resend_acode']."</a>";
	
				include($tempdir."meldungen.html");
				}
			// Formular ausgeben
			else{
				$mailcats = "<option value=\"all\" selected=\"selected\">".$lang['allcats']."</option>\n";
				
				$list = $mysqli->query("SELECT * FROM ".$mysql_tables['mailcats']." ORDER BY catname");
				while($row = $list->fetch_assoc()){
					$mailcats .= "<option value=\"".$row['id']."\">".htmlentities(stripslashes($row['catname']),$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
					}
				
				include($tempdir."formular_registrierung.html");
				}
			}
		}
	
	}
// Aktivierungscode �bergeben?
elseif(isset($_REQUEST['acode']) && !empty($_REQUEST['acode']) && strlen($_REQUEST['acode']) == 32){
	if($settings['send_benachrichtigung'] == 1){
		$list = $mysqli->query("SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode='".$mysqli->escape_string($_REQUEST['acode'])."' AND acode != '0' LIMIT 1");
		$row_email = $list->fetch_assoc();
		}
	
	$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET acode = '0' WHERE acode='".$mysqli->escape_string($_REQUEST['acode'])."' AND acode != '0' LIMIT 1");
	
	if($mysqli->affected_rows == 1){
		$meldung = $lang['meldung_activated'];
		
		// E-Mail f�r Neuregistrierung an Admin versenden
		if($settings['send_benachrichtigung'] == 1)
			mail($settings['email_absender'],$settings['sitename']." - ".$lang['neue_reg_betreff'],$lang['neue_reg_body'].$row_email['email']."

---
Webmailer",$mail_header);
		
		include($tempdir."meldungen.html");
		}
	else{
		$meldung = $lang['meldung_wrongacode'];
		$formaction = "newacode";
		$formcodename = "acode";

		include($tempdir."meldungen.html");
		include($tempdir."formular_resendcode.html");
		include($tempdir."formular_acode.html");
		}
	}
// Bearbeitungscode �bergeben?
elseif(isset($_REQUEST['ecode']) && !empty($_REQUEST['ecode']) && strlen($_REQUEST['ecode']) == 32){
	$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET catids = newcatids, newcatids = '0', editcode='0' WHERE editcode='".$mysqli->escape_string($_REQUEST['ecode'])."' AND editcode != '0' LIMIT 1");

	if($mysqli->affected_rows == 1){
		$meldung = $lang['meldung_changes'];
		include($tempdir."meldungen.html");
		}
	else{
		$meldung = $lang['meldung_wrongacode'];
		$formaction = "newecode";
		$formcodename = "ecode";

		include($tempdir."meldungen.html");
		include($tempdir."formular_resendcode.html");
		include($tempdir."formular_acode.html");
		}
	}
// L�schcode �bergeben?
elseif(isset($_REQUEST['dcode']) && !empty($_REQUEST['dcode']) && strlen($_REQUEST['dcode']) == 32 && $_REQUEST['dcode'] != 0){
	$list = $mysqli->query("SELECT email FROM ".$mysql_tables['emailadds']." WHERE delcode='".$mysqli->escape_string($_REQUEST['dcode'])."' AND delcode != '0' LIMIT 1");
	$row_email = mysql_fetch_assoc($list);
	
	$mysqli->query("DELETE FROM ".$mysql_tables['temp_table']." WHERE email = '".$mysqli->escape_string($row_email['email'])."' AND email != ''");
	$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE delcode='".$mysqli->escape_string($_REQUEST['dcode'])."' AND delcode != '0' LIMIT 1");

	if($mysqli->affected_rows == 1){
		$meldung = $lang['meldung_deleted'];
		include($tempdir."meldungen.html");
		}
	else{
		$meldung = $lang['meldung_wrongacode'];
		$formaction = "newdcode";
		$formcodename = "dcode";

		include($tempdir."meldungen.html");
		include($tempdir."formular_resendcode.html");
		include($tempdir."formular_acode.html");
		}
	}
// Fehlermeldung: Fehlerhafte E-Mail-Adresse eingegeben anzeigen
elseif(isset($_REQUEST['email']) && !empty($_REQUEST['email'])){
	$meldung = $lang['meldung_falschemail'];

	include($tempdir."meldungen.html");
	include($tempdir."formular_addemail.html");
	}
// Nichts �bergeben -> Register-Formular anzeigen
else{
	include($tempdir."formular_addemail.html");
	}


// Main_Bottom einf�gen
include($tempdir."main_bottom.html");

?>