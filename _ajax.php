<?PHP
/* 
	01-Newsletter - Copyright 2009-2017 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo: 	Bearbeitung von eingehenden Ajax-Requests
	#fv.133#
*/

// Security: Only allow calls from _ajaxloader.php!
if(basename($_SERVER['SCRIPT_FILENAME']) != "_ajaxloader.php") exit;

// Vorlage / Entwurf löschen
if(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "del_vorlage" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id']) &&
   isset($_REQUEST['selindex']) && !empty($_REQUEST['selindex']) && is_numeric($_REQUEST['selindex'])){
    $list = $mysqli->query("SELECT * FROM ".$mysql_tables['archiv']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
	while($row = $list->fetch_assoc()){
		if($row['art'] == "e" && $row['uid'] == $userdata['id'] || $row['art'] == "v" && $userdata['vorlagen'] == 1){
			$mysqli->query("DELETE FROM ".$mysql_tables['archiv']." WHERE id = '".$row['id']."' LIMIT 1");
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
    $list = $mysqli->query("SELECT * FROM ".$mysql_tables['archiv']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
	while($row = $list->fetch_assoc()){
		// Sicherheitsabfrage
		if($row['art'] == "e" && $row['uid'] == $userdata['id'] || $row['art'] == "v"){
			if($row['utimestamp'] == 0) $row['utimestamp'] = time();
			    
			if($row['art'] == "e"){
				$betreff = "document.post.betreff.value = '".addcslashes(addslashes($row['betreff']),"\n\r")."';
				document.post.entwurfid.value = '".$row['id']."';\n";
				
				list($catmenge) = $mysqli->query("SELECT COUNT(*) FROM ".$mysql_tables['mailcats']."")->fetch_array(MYSQLI_NUM);
				if($row['kategorien'] == "all" && $settings['usecats'] == 1 && $catmenge > 1)
				    $betreff .= "document.post.empf[0].checked = true;";
				elseif(!empty($row['kategorien']) && $settings['usecats'] == 1 && $catmenge > 1){
					$cats = explode(",",$row['kategorien']);
					$betreff .= "document.post.empf[1].checked = true;\n";
					$betreff .= "var selObjArr = document.getElementsByName('empfcats[]');
					var selObj = selObjArr[0];
					var i;
					var count = 0;
					for(i=0; i<selObj.options.length; i++){
						selObj.options[i].selected = false;
						}\n";
					foreach($cats as $cat){
						$betreff .= "count = 0;
					for(i=0; i<selObj.options.length; i++){
						if(selObj.options[i].value == '".$cat."'){
					    	selObj.options[i].selected = true;
					    	}
						count++;
						}";
						}
					}
				}
			else
				$betreff = "document.post.entwurfid.value = 'x';";
				
			$attach = "";
			if(!empty($row['attachments'])){
				$attachments = explode("|",$row['attachments']);
				$z = 1;
				foreach($attachments as $attachment){
					$attach .= "\ndocument.post.attachment".$z.".value = '".$attachment."';
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
			ed.setContent('".utf8_encode(addcslashes($row['mailinhalt'],"'\n\r"))."');
			ed.setProgressState(0);";
			else
				$text = "document.post.mailtext.value = '".addcslashes(addslashes($row['mailinhalt']),"\n\r")."';";
			
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

	$mysqli->query("DELETE FROM ".$mysql_tables['mailcats']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");

	$catidlist = $mysqli->query("SELECT id,catids,newcatids FROM ".$mysql_tables['emailadds']."");
	while($row = $catidlist->fetch_assoc()){
		$testarray = explode(",",substr($row['catids'],1,strlen($row['catids'])-2));
		if(is_array($testarray) && count($testarray) > 1){
			unset($testarray[array_search($_REQUEST['id'],$testarray)]);

			$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET catids=',".implode(",",$testarray).",' WHERE id='".$mysqli->escape_string($row['id'])."'");
			}
		elseif($row['catids'] == ",".$_REQUEST['id'].","){
			// Wenn die einzige Kategorie eines Benutzers gelöscht wird, Benutzeraccunt löschen
			$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE id = '".$mysqli->escape_string($row['id'])."' LIMIT 1");
			}
		
		if($row['newcatids'] != "0"){
			$testarray2 = explode(",",substr($row['newcatids'],1,strlen($row['newcatids'])-2));
			if(is_array($testarray2) && count($testarray2) > 1){
				unset($testarray2[array_search($_REQUEST['id'],$testarray2)]);
	
				$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET newcatids=',".implode(",",$testarray2).",' WHERE id='".$mysqli->escape_string($row['id'])."'");
				}
			elseif($row['newcatids'] == ",".$_REQUEST['id'].","){
				$mysqli->query("UPDATE ".$mysql_tables['emailadds']." SET newcatids='0', editcode='0' WHERE id='".$mysqli->escape_string($row['id'])."'");
				}
			}
		}
	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
// E-Mail-Adresse löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delemailaddy" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
   	$list = $mysqli->query("SELECT email FROM ".$mysql_tables['emailadds']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
	$row_email = $list->fetch_assoc();

	$mysqli->query("DELETE FROM ".$mysql_tables['temp_table']." WHERE email = '".$mysqli->escape_string($row_email['email'])."' AND email != ''");
	   
	$mysqli->query("DELETE FROM ".$mysql_tables['emailadds']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");
   	
   	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
// Archiv-Eintrag löschen
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "delarchiv" &&
   isset($_REQUEST['id']) && !empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
   	$mysqli->query("DELETE FROM ".$mysql_tables['archiv']." WHERE id = '".$mysqli->escape_string($_REQUEST['id'])."' LIMIT 1");

   	echo "<script type=\"text/javascript\"> Success_delfade('id".$_REQUEST['id']."'); </script>";
	}
// CSV-Export
elseif(isset($_REQUEST['ajaxaction']) && $_REQUEST['ajaxaction'] == "csvexport" && $userdata['show_emails'] == 1 &&
   isset($_REQUEST['data']) && !empty($_REQUEST['data']) && (is_numeric($_REQUEST['data']) || $_REQUEST['data'] == "all")){
   		$cat[0] = "Alle Kategorien";
		$catidlist = $mysqli->query("SELECT id,catname FROM ".$mysql_tables['mailcats']."");
		while($row = $catidlist->fetch_assoc()){
			$cat[$row['id']] = $row['catname'];
		}

		if($_REQUEST['data'] == "all")
			_01newsletter_query_to_csv("SELECT email,catids FROM ".$mysql_tables['emailadds']." WHERE acode = '0'", "email_adresses-".date("d-m-Y").".csv", $cat);
		else
			_01newsletter_query_to_csv("SELECT email FROM ".$mysql_tables['emailadds']." WHERE acode = '0' AND (catids = '0' OR catids = ',0,' OR catids LIKE '%,".CleanStr($_REQUEST['data']).",%')", "email_adresses-".preg_replace('/[^a-zA-Z0-9\-_]/', '', $cat[$_REQUEST['data']])."-".date("d-m-Y").".csv");
   	}
else{
	echo "<script type=\"text/javascript\"> Failed_delfade(); </script>";
}
	
?>