<?PHP
/* 
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.133#
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

return TRUE;

}
}

// Funktion wird zentral aufgerufen, wenn das Modul gelöscht werden soll
/*
RETURN: TRUE
*/
if(!function_exists("_01newsletter_DeleteModul")){
function _01newsletter_DeleteModul(){
global $mysqli,$mysql_tables,$modul;

$modul = $mysqli->escape_string($modul);

// MySQL-Tabellen löschen
$mysqli->query("DROP TABLE `".$mysql_tables['archiv']."`");
$mysqli->query("DROP TABLE `".$mysql_tables['emailadds']."`");
$mysqli->query("DROP TABLE `".$mysql_tables['mailcats']."`");

// Rechte entfernen
$mysqli->query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_vorlagen`");
$mysqli->query("ALTER TABLE `".$mysql_tables['user']."` DROP `".$modul."_show_emails`");

return TRUE;
}
}








// Dropdown-Box aus angelegten Kategorien generieren (ohne Select-Tag)
/*
RETURN: Option-Elemente für Select-Formularelement
*/
if(!function_exists("_01newsletter_CatDropDown")){
function _01newsletter_CatDropDown($sel){
global $mysqli,$mysql_tables;

$list = $mysqli->query("SELECT id,catname FROM ".$mysql_tables['mailcats']." ORDER BY catname");
while($row = $list->fetch_assoc()){
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
	return "From:".$settings['versand_altname']."<".$settings['versandadresse'].">";
elseif(!empty($settings['versandadresse']))
    return "From:".$settings['versandadresse']."<".$settings['versandadresse'].">";
else
	return "From:".$settings['email_absender']."<".$settings['email_absender'].">";
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
global $mysqli,$mysql_tables,$modul,$module;

if(isset($userid) && is_integer(intval($userid))){
	$newslettermenge = 0;
	list($newslettermenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['archiv']." WHERE art = 'a' AND uid = '".$mysqli->escape_string($userid)."'")->fetch_array(MYSQLI_NUM);
	
	$ustats[] = array("statcat"	=> "Versendete Newsletter (".$module[$modul]['instname']."):",
					  "statvalue"	=> $newslettermenge);
	return $ustats;
	}
else
	return false;

}
}










// Mime-Typen von Dateien bestimmen
/*$filename		Dateiname zu dem der Dateityp bestimmt werden soll
  
RETURN: Mime-Typ
  */
if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}


// Daten via MySQL-Query aus Datenbank in CSV-Datei exportieren
/*$query           Valid MySQL-Query
  $filename        Filename for CSV-Export file
  $lookup          $cat['id']=name
  $attachment      true = send to browser, false = save as file locally
  $headers         Add first row with column description

RETURN: NULL
  */
if(!function_exists("_01newsletter_query_to_csv")){
function _01newsletter_query_to_csv($query, $filename, $lookup = false, $attachment = true, $headers = true) {
    global $mysqli;
    
    if($attachment) {
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename='.$filename);
        $fp = fopen('php://output', 'w');
    } else {
        $fp = fopen($filename, 'w');
    }
    
    $result = $mysqli->query($query);

    if($headers) {
        // output header row (if at least one row exists)
        $row = $result->fetch_assoc();
        if($row) {
            fputcsv($fp, array_keys($row), ";");
            $result->data_seek(0); // reset pointer back to beginning
        }
    }
    
    while($row = $result->fetch_assoc()){
        if(isset($lookup) && is_array($lookup) && !empty($lookup)){
            if($row['catids'] == '0') $row['catids'] = ",0,";
            $row['catids'] = implode(",", array_intersect_key($lookup, array_flip(explode(",",substr($row['catids'],1,-1)))));
        }

        fputcsv($fp, $row, ";");
        //print_r($row);
    }
    
    fclose($fp);
}
}
?>