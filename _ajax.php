<?PHP
/* 
	01-Newsletter - Copyright 2009-2011 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo: 	Bearbeitung von eingehenden Ajax-Requests
	#fv.120#
*/

// Vorlage / Entwurf löschen
if(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "del_vorlage" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
   isset($_REQUEST['selindex']) && !empty($_REQUEST['selindex']) && is_numeric($_REQUEST['selindex'])){
    $list = mysql_query("SELECT * FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");
	while($row = mysql_fetch_assoc($list)){
		if($row['art'] == "e" && $row['uid'] == $userdata['id'] || $row['art'] == "v" && $userdata['vorlagen'] == 1){
			mysql_query("DELETE FROM ".$mysql_tables['archiv']." WHERE id = '".$row['id']."' LIMIT 1");
			echo "<script type=\"text/javascript\">
			document.post.vorlage.options[".$_REQUEST['selindex']."] = null;
			hide_always('delvorlage');
			Success_standard();
			</script>";
			}
		else
			echo "<script type=\"text/javascript\"> Failed_delfade(); </script>";
		}
    }
// Newsletter schreiben -> Vorlage/Entwurf laden
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "load_vorlage" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
    $list = mysql_query("SELECT * FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");
	while($row = mysql_fetch_assoc($list)){
		// Sicherheitsabfrage
		if($row['art'] == "e" && $row['uid'] == $userdata['id'] || $row['art'] == "v"){
			if($row['art'] == "e")
				$betreff = "document.post.betreff.value = '".utf8_encode(addcslashes($row['betreff'],"\n\r"))."';
				document.post.entwurfid.value = '".$row['id']."';";
			else
				$betreff = "document.post.entwurfid.value = 'x';";
				
			$attach = "";
			if(!empty($row['attachments'])){
				$attachments = explode("|",$row['attachments']);
				$z = 1;
				foreach($attachments as $attachment){
					$attach .= "\ndocument.post.attachment".$z.".value = '".utf8_encode($attachment)."';
					InsertNewAttachmentField();";
					$z++;
					}
				}
				
			if($row['art'] == "e" && $row['uid'] == $userdata['id'] || $row['art'] == "v" && $userdata['vorlagen'] == 1)
				$showdelvorlage = "show_always('delvorlage');";
			else
				$showdelvorlage = "hide_always('delvorlage');";				
			
			// Mailtext
			if($settings['use_html'])
			    $text = "var ed = tinyMCE.get('mailtext');
			ed.setProgressState(1);
			ed.setContent('".utf8_encode(addcslashes($row['mailinhalt'],"\n\r"))."');
			ed.setProgressState(0);";
			else
				$text = "document.post.mailtext.value = '".utf8_encode(addcslashes($row['mailinhalt'],"\n\r"))."';";
			
			echo "<script type=\"text/javascript\">
			".$showdelvorlage."
			".$betreff."
			".$attach."
			".$text."
			Stop_Loading_standard();
			</script>";
			}
		else
			echo "<script type=\"text/javascript\"> Failed_delfade(); </script>";
		}
    }
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "load_vorlage" &&
   isset($_REQUEST['id']) && $_REQUEST['id'] == "x"){
    $c = 0;
    }
// Kategorie löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delcat" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){

	mysql_query("DELETE FROM ".$mysql_tables['mailcats']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");

	$catidlist = mysql_query("SELECT id,catids,newcatids FROM ".$mysql_tables['emailadds']."");
	while($row = mysql_fetch_array($catidlist)){
		$testarray = explode(",",substr($row['catids'],1,strlen($row['catids'])-2));
		if(is_array($testarray) && count($testarray) > 1){
			unset($testarray[array_search($_REQUEST['id'],$testarray)]);

			mysql_query("UPDATE ".$mysql_tables['emailadds']." SET catids=',".implode(",",$testarray).",' WHERE id='".mysql_real_escape_string($row['id'])."'");
			}
		elseif($row['catids'] == ",".$_REQUEST['id'].","){
			// Wenn die einzige Kategorie eines Benutzers gelöscht wird, Benutzeraccunt löschen
			mysql_query("DELETE FROM ".$mysql_tables['emailadds']." WHERE id = '".mysql_real_escape_string($row['id'])."' LIMIT 1");
			}
		
		if($row['newcatids'] != "0"){
			$testarray2 = explode(",",substr($row['newcatids'],1,strlen($row['newcatids'])-2));
			if(is_array($testarray2) && count($testarray2) > 1){
				unset($testarray2[array_search($_REQUEST['id'],$testarray2)]);
	
				mysql_query("UPDATE ".$mysql_tables['emailadds']." SET newcatids=',".implode(",",$testarray2).",' WHERE id='".mysql_real_escape_string($row['id'])."'");
				}
			elseif($row['newcatids'] == ",".$_REQUEST['id'].","){
				mysql_query("UPDATE ".$mysql_tables['emailadds']." SET newcatids='0', editcode='0' WHERE id='".mysql_real_escape_string($row['id'])."'");
				}
			}
		}
	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
// E-Mail-Adresse löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delemailaddy" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
   	mysql_query("DELETE FROM ".$mysql_tables['emailadds']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");
   	
   	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
// Archiv-Eintrag löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delarchiv" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
   	mysql_query("DELETE FROM ".$mysql_tables['archiv']." WHERE id = '".mysql_real_escape_string($_REQUEST['id'])."' LIMIT 1");

   	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
else
	echo "<script type=\"text/javascript\"> Failed_delfade(); </script>";
	
?>