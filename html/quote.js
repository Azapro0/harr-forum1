function ins(name){
if (document.REPLIER) {
var input=document.REPLIER.Post;
input.value=input.value+"[b]"+name+"[/b]"+" \n";
}
}

function Insert(text){ 
if (text!="") paste("[quote]"+text+"[/quote]\n", 0);
}

function paste(text, flag){ 
if (document.REPLIER) {
if ((document.selection)&&(flag)) {
	document.REPLIER.Post.focus();
	document.REPLIER.document.selection.createRange().text = text;
} else document.REPLIER.Post.value += text;
}
}

function get_selection() {
   if (document.getSelection){
	selection = document.getSelection();
	selection = selection.replace(/\r\n\r\n/gi, "_doublecaret_");
	selection = selection.replace(/\r\n/gi, " ");
       while (selection.indexOf("  ") !=-1) selection = selection.replace(/  /gi, ""); 
	selection = selection.replace(/_doublecaret_/gi, "\r\n\r\n");
  } else
      selection = document.selection.createRange().text;
}