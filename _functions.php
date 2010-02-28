<?PHP
/* 
	01-Newsletter - Copyright 2009-2010 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.1001#
*/

/* SYNTAKTISCHER AUFBAU VON FUNKTIONSNAMEN BEACHTEN!!!
	_ModulName_beliebigerFunktionsname()
	Beispiel: 
	if(!function_exists("_example_TolleFunktion")){
		_example_TolleFunktion($parameter){ ... }
		}
*/

// Globale Funktionen - nötig!

// Funktion wird zentral aufgerufen, wenn ein Benutzer gelöscht wird.
/*$userid			UserID des gelöschten Benutzers
  $username			Username des gelöschten Benutzers
  $mail				E-Mail-Adresse des gelöschten Benutzers

RETURN: TRUE/FALSE
*/
if(!function_exists("_01newsletter_DeleteUser")){
function _01newsletter_DeleteUser($userid,$username,$mail){
global $mysql_tables;



return TRUE;
}
}








// Dropdown-Box aus angelegten Kategorien generieren (ohne Select-Tag)
/*
RETURN: Option-Elemente für Select-Formularelement
*/
if(!function_exists("_01newsletter_CatDropDown")){
function _01newsletter_CatDropDown($sel){
global $mysql_tables;

$list = mysql_query("SELECT id,catname FROM ".$mysql_tables['mailcats']." ORDER BY catname");
while($row = mysql_fetch_assoc($list)){
	if(isset($sel) && !empty($sel) && is_numeric($sel) && $sel == $row['id']) $select = " selected=\"selected\"";
	else $select = "";
	
	$return .= "<option value=\"".$row['id']."\"".$select.">".stripslashes($row['catname'])."</option>\n";
	}

return $return;
}
}






// Passenden $mail_header zurückgeben
/*

RETURN: Mailheader
*/
if(!function_exists("_01newsletter_getMailHeader")){
function _01newsletter_getMailHeader(){
global $settings;

if(!empty($settings['versandadresse']) && !empty($settings['versand_altname']))
	return "From:".$settings['versand_altname']."<".$settings['versandadresse'].">\n";
elseif(!empty($settings['versandadresse']))
    return "From:".$settings['versandadresse']."<".$settings['versandadresse'].">\n";
else
	return "From:".$settings['email_absender']."<".$settings['email_absender'].">\n";
}
}










// Userstatistiken holen
/*$userid			UserID, zu der die Infos geholt werden sollen

RETURN: Array(
			statcat[x] 		=> "Statistikbezeichnung für Frontend-Ausgabe"
			statvalue[x] 	=> "Auszugebender Wert"
			)
  */
if(!function_exists("_01newsletter_getUserstats")){
function _01newsletter_getUserstats($userid){
global $mysql_tables,$modul,$module;

if(isset($userid) && is_integer(intval($userid))){
	$newslettermenge = 0;
	list($newslettermenge) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM ".$mysql_tables['archiv']." WHERE art = 'a' AND uid = '".mysql_real_escape_string($userid)."'"));
	
	$ustats[] = array("statcat"	=> "Versendete Newsletter (".$module[$modul]['instname']."):",
					  "statvalue"	=> $newslettermenge);
	return $ustats;
	}
else
	return false;
return false;
}
}

// 01-Newsletter Copyright 2009-2010 by Michael Lorer - 01-Scripts.de
?>