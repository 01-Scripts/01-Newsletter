/* 
	01-Newsletter - Copyright 2009-2010 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo:	JavaScript-Funktionen
*/

function toggleSignatur(){
$('signatur').slide('toggle');
}


function alsVorlage(){
$('button1').slide('toggle');
$('button2').slide('toggle');
$('vorlagenname').slide('toggle');
}

var counter = 1;

function InsertNewAttachmentField() {

	var counter = $('attachfieldcounter').get('value');
	counter++;
	$('attachfieldcounter').set('value',counter);
	
	var InsertContent = '<input type="text" name="attachment'+counter+'" value="" readonly="readonly" size="25" class="input_text" /> <input type="button" name="filebutton" value="Durchsuchen..." onclick="popup(\'uploader\',\'file\',\'post\',\'attachment'+counter+'\',620,480)" class="input" /> <input type="button" name="empty_file" value="Anhang entfernen" onclick="javascript:post.attachment'+counter+'.value=\'\';" class="input" /><br />\n';
	
	var htmlcontent = $('writeroot').get('html')+InsertContent;
	$('writeroot').set('html', htmlcontent);
	}