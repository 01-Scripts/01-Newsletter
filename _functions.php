<?PHP
/* 
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo: 	Modulspezifische Funktionen
	#fv.132#
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
	
	$return .= "<option value=\"".$row['id']."\"".$select.">".htmlentities($row['catname'],$htmlent_flags,$htmlent_encoding_acp)."</option>\n";
	}

return $return;
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


// PHPMailer-Instanz mit Defaultwerten konfigurieren
/*$mail     Zu konfigurierende PHPMailer-Instanz

RETURN: -
*/
if(!function_exists("_01newsletter_configurePHPMailer")){
function _01newsletter_configurePHPMailer(&$mail){
    global $settings,$SMTPSecurity,$SMTPAuth;

    // Versand soll per SMTP erfolgen
    if($settings['smtp_nl'] == "smtp_01newsletter" && !empty($settings['smtp_nl_host'])){
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $settings['smtp_nl_host'];
        $mail->SMTPAuth = $SMTPAuth;                          // Enable SMTP authentication
        $mail->Username = $settings['smtp_nl_username'];
        $mail->Password = $settings['smtp_nl_password'];
        $mail->SMTPSecure = $SMTPSecurity;                    // Enable TLS encryption, `ssl` also accepted
        $mail->Port = (!empty($settings['smtp_nl_port'])) ? $settings['smtp_nl_port'] : 587;
    }
    elseif($settings['smtp_nl'] == "smtp_01acp" && !empty($settings['smtp_host'])){
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $settings['smtp_host'];
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $settings['smtp_username'];
        $mail->Password = $settings['smtp_password'];
        $mail->SMTPSecure = "tls";                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = (!empty($settings['smtp_port'])) ? $settings['smtp_port'] : 587;
    }

    if(!empty($settings['versandadresse']) && !empty($settings['versand_altname'])){
        $mail->From = $settings['versandadresse'];
        $mail->FromName = $settings['versand_altname'];
    }
    elseif(!empty($settings['versandadresse'])){
        $mail->From = $settings['versandadresse'];
        $mail->FromName = $settings['versandadresse'];
    }
    else{
        $mail->From = $settings['email_absender'];
        $mail->FromName = $settings['email_absender'];
    }

    $mail->Encoding = "quoted-printable"; // Resolve Encoding issues with PHP >= 5.6 when sending from the Frontend
}
}
?>