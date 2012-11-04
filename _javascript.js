/* 
	01-Newsletter - Copyright 2009-2012 by Michael Lorer - 01-Scripts.de
	Lizenz: Creative-Commons: Namensnennung-Keine kommerzielle Nutzung-Weitergabe unter gleichen Bedingungen 3.0 Deutschland
	Weitere Lizenzinformationen unter: http://www.01-scripts.de/lizenz.php
	
	Modul:		01newsletter
	Dateiinfo:	JavaScript-Funktionen des 01-Newsletterscripts (stehen nur innerhalb des 01ACP zur Verfügung)
	Unkomprimierte Version der Datei: https://github.com/01-Scripts/01-Newsletter/blob/V1.3.0/01newsletter/_javascript.js
	#fv.130#
*/

function toggleSignatur(){
$('signatur').slide('toggle');
}

function toggleSendLater(){
$('select_time').slide('toggle');
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
	
	var InsertContent = '';
	
	for (var i = 1; i <= counter; i++){
		if(i < counter){
		var GetContent = eval('document.post.attachment'+i+'.value');
			if(GetContent == undefined || GetContent == '') GetContent = '';
			}
		else{ var GetContent = ''; }
		
		InsertContent = InsertContent+'<input type="text" name="attachment'+i+'" value="'+GetContent+'" readonly="readonly" size="25" class="input_text" onchange="alert(1);" /> <input type="button" name="filebutton" value="Dateien Durchsuchen..." onclick="popup(\'uploader\',\'file\',\'post\',\'attachment'+i+'\',620,480)" class="input" /> <input type="button" name="filebutton" value="Bilder Durchsuchen..." onclick="popup(\'uploader\',\'pic\',\'post\',\'attachment'+i+'\',620,480)" class="input" /> <input type="button" name="empty_file" value="Anhang entfernen" onclick="javascript:post.attachment'+i+'.value=\'\';" class="input" /><br />\n';
		}
	
	$('writeroot').set('html', InsertContent);
	}