/////////////////////////////////////////////////
// UNIVERSAL BULLETIN BOARD CODE INTERFACE v2.1
// By Mark Plachetta (astroboy@zip.com.au)
/////////////////////////////////////////////////

/////////////////////////////
// Basic browser detection
//
  var ie = (document.all) ? 1 : 0;
  var nn = (document.layers) ? 1 : 0;
  var n6 = (window.sidebar) ? 1 : 0;


/////////////////////////////
// Stylesheet output
//
  function writeStyle() {
    var html = '<style type="text/css"><!--\n';
       html += '.ibcButton { font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif; background-color:#cccccc;  }\n';
       html += '.ubbcButton { font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif; background-color:#ececec;}\n'; 
       html += '.ibcSelect{ font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif; background-color:#cccccc;  }\n';
       html += '.ibcButton { font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif; background-color:#ececec; }\n';
       html += ' --></style>';

    document.write(html);
  }


/////////////////////////////
// Interface output
//
  function makeInterface(images,flash,graphical) {
    var html = '<table border="0" cellpadding="2" cellspacing="0"><tr><td align=center>' + ((nn) ? '&nbsp;' : '');
       html += ' <select name="fcolor" class="ibcSelect" onchange="ubbFont(this);">';
       html += makeOption('','����',0);
       html += makeOption('skyblue','������-�������',1);
       html += makeOption('royalblue','�������',1);
       html += makeOption('blue','�����',1);
       html += makeOption('darkblue','Ҹ���-�����',1);
       html += makeOption('orange','���������',1);
       html += makeOption('orangered','���������',1);
       html += makeOption('crimson','��������',1);
       html += makeOption('red','�������',1);
       html += makeOption('firebrick','���������',1);
       html += makeOption('darkred','����������',1);
       html += makeOption('green','������',1);
       html += makeOption('limegreen','���������',1);
       html += makeOption('seagreen','��������',1);
       html += makeOption('deeppink','�������',1);
       html += makeOption('tomato','��������',1);
       html += makeOption('coral','Coral',1);
       html += makeOption('purple','���������',1);
       html += makeOption('indigo','����������',1);
       html += makeOption('burlywood','���������',1);
       html += makeOption('sandybrown','��������',1);
       html += makeOption('sienna','��������',1);
       html += makeOption('chocolate','����������',1);
       html += makeOption('teal','�������',1);
       html += makeOption('silver','����������',1);
       html += '</select> ';
       html += '<select name="fsize" class="ibcSelect" onchange="ubbFont(this);">';
        html += makeOption('','������',0);
       html += makeOption('1','������',0);
       html += makeOption('2','���������',0);
       html += makeOption('6','�������',0);
       html += makeOption('8','�������',0);
       html += makeOption('10','��������',0);
       html += '</select> ';
       html += '<select name="ffont" class="ibcSelect" onchange="ubbFont(this);">';
       html += makeOption('','�����',0);
       html += makeOption('arial','Arial',0);
       html += makeOption('courier','Courier',0);
       html += makeOption('impact','Impact',0);
       html += makeOption('tahoma','Tahoma',0);
       html += makeOption('times','Times',0);
       html += makeOption('verdana','Verdana',0);
       html += '</select> ';
       html += ' <select name="quicklist" class="ibcSelect" onchange="ubbList(this.options[this.selectedIndex].value);">';
       html += makeOption('','������',0);
       html += makeOption('1','1 �����',0);
       html += makeOption('2','2 ������',0);
       html += makeOption('3','3 ������',0);
       html += makeOption('4','4 �����',0);
       html += makeOption('5','5 �������',0);
       html += '</select> ';
         html += '<select name="talign" class="ibcSelect" onchange="ubbAlign(this.options[this.selectedIndex].value);">';
         html += makeOption('','������������',0);
         html += makeOption('left','�� ������ ����',0);
         html += makeOption('center','�� ������',0);
         html += makeOption('right','�� ������� ����',0);
         html += '</select> ';
       html += '</td></tr><tr><td align=center>' + ((nn) ? '&nbsp;' : '');
    if (graphical) {
       html += makeLink("ubbBasic('b');",'������[B]') + makeImage('/bold.gif','23','22','������[B]') + '</a>';
       html += makeLink("ubbBasic('i');",'���������[I]') + makeImage('/italics.gif','23','22','���������[I]') + '</a>';
       html += makeLink("ubbBasic('u');",'������������[U]') + makeImage('/underline.gif','23','22','������������[U]') + '</a>';
       html += makeLink("ubbBasic('s');",'�����������[S]') + makeImage('/strikethrough.gif','23','22','�����������[S]') + '</a>';
       html += makeLink("ubbBasic('move');",'����������[J]') + makeImage('/marque.gif','23','22','����������[J]') + '</a> ';
       html += makeLink("ubbBasic('fliph');",'����������� �������������[Z]') + makeImage('/horiz.gif','23','22','����������� �������������[Z]') + '</a>';
       html += makeLink("ubbBasic('flipv');",'����������� �����������[V]') + makeImage('/vert.gif','23','22','����������� �����������[V]') + '</a>';
       html += makeLink("ubbShadow();",'����[D]') + makeImage('/shadow.gif','23','22','����[D]') + '</a>';
       html += makeLink("ubbGlow();",'��������[G]') + makeImage('/glow.gif','23','22','��������[G]') + '</a>';
       html += makeLink("ubbSpoil();",'������� �����[Sp]') + makeImage('/spoiler.gif','23','22','������� �����[Sp]') + '</a>';
    } else {
       html += makeButton("ubbBasic('b');",' B ','������[B]','b') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('i');",' I ','���������[I]','i') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('u');",' U ','������������[U]','u') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('s');",' S ','�����������[S]','s');
         html += makeButton("ubbBasic('move');",'Marquee','����������[J]','j') + makeImage('/pixel.gif',1,1,'');
         html += makeButton("ubbBasic('fliph');",'Flip Lt-Rt','����������� �������������[Z]','z')  + makeImage('/pixel.gif',1,1,'');
         html += makeButton("ubbBasic('flipv');",'Flip Up-Dn','����������� �����������[V]','') + makeImage('/pixel.gif',1,1,'');
         html += ((shadow) ? makeButton("ubbShadow();",'Shadow','����[D]','d') : '') + makeImage('/pixel.gif',1,1,'');
         html += ((glow) ? makeButton("ubbGlow();",'Glow','��������[G]','g') : '') + makeImage('/pixel.gif',1,1,'');
         html += ((spoil) ? makeButton("ubbSpoil();",'---','������� �����[Sp]','0') : '') + makeImage('/pixel.gif',1,1,'');
    }

    if (graphical) {
       html += ' ' + makeLink("ubbsound('');",'����[S]') + makeImage('/sound.gif','23','22','����[S]') + '</a>';
       html += makeLink("ubbvideo('');",'�����[V]') + makeImage('/rplayer.gif','23','22','�����[V]') + '</a>';
       html += ' ' +makeLink("ubbBasic('code');",'��� [G]') + makeImage('/code.gif','23','22','��� [G]') + '</a>';
       html += makeLink("ubbBasic('quote');",'������[Q]') + makeImage('/quote.gif','23','22','������[Q]') + '</a>';
    } else {
       html += ' ' + makeLink("ubbsound('');",'����[S]') + makeImage('/sound.gif','23','22','����[S]') + '</a>';
       html += makeLink("ubbvideo('');",'�����[V]') + makeImage('/rplayer.gif','23','22','�����[V]') + '</a>';
       html += makeLink("ubbweb('');",'���[W]') + makeImage('/web.gif','23','22','���[W]') + '</a>';
       html += ' ' +makeButton("ubbBasic('code');",' # ','��� [C]','c') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('quote');",'Quote','������[Q]','q') + makeImage('/pixel.gif',1,1,'');
    }
       html += '</td></tr><tr><td align=center>';
    if (graphical) {
       html += makeLink("ubbList('');",'������ [L]') + makeImage('/list.gif','23','22','������ [L]') + '</a>';
       html += makeLink("ubbListItem();",'����� ������ [K]') + makeImage('/listitem.gif','23','22','����� ������ [K]') + '</a>';
       html += makeLink("ubbBasic('me');",'��������[M]') + makeImage('/me.gif','23','22','��������[M]') + '</a> ';
       html += makeLink("ubbHref();",'������ [H]') + makeImage('/url.gif','23','22','������ [H]') + '</a>';
       html += makeLink("ubbEmail();",'[E]mail') + makeImage('/email.gif','23','22','[E]mail') + '</a>';
       html += ((images) ? makeLink("ubbImage();",'��������[P]') + makeImage('/image.gif','23','22','��������[P]') + '</a>' : '');
       html += ((flash) ? makeLink("ubbFlash();",'[F]lash ������') + makeImage('/flash.gif','23','22','[F]lash ������') + '</a>' : '');
       html += ' ' + makeLink("ubbSmile();",'������') + makeImage('/smile.gif','23','22','������') + '</a>';
       html += ' ' + makeLink("ubbHelp();",'������') + makeImage('/help.gif','23','22','������') + '</a>';

    } else {
       html += makeButton("ubbList('');",'UL','������ [L]','l') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbListItem();",'LI','����� ������ [K]','k');
       html += makeButton("ubbBasic('me');",'Me','��������[M]','m') + ' ';
       html += makeButton("ubbHref();",'URL','������ [H]','h') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbEmail();",' @ ','[E]mail','e') + makeImage('/pixel.gif',1,1,'');
       html += ((images) ? makeButton("ubbImage();",'IMG','��������[P]','p') + makeImage('/pixel.gif',1,1,'') : '');
       html += ((flash) ? makeButton("ubbFlash();",'SWF','[F]lash ������','f') + makeImage('/pixel.gif',1,1,'') : '');
         html += ' ' + makeButton("ubbSmile();",' ? ','������','') + makeImage('/pixel.gif',1,1,'');
       html += ' ' + makeButton("ubbHelp();",' ? ','������','');
    }
          html += '</td></tr></table>';
    document.write(html);
  }


/////////////////////////////
// Code inserter
//
  function ubbCode(code) {
    if (form["Post"].createTextRange && form["Post"].caretPos) {
      var caretPos = form["Post"].caretPos;
      caretPos.text = code;
    } else { form["Post"].value += code; }
    form["Post"].focus();
  }


/////////////////////////////
// HTML shortcuts
//
  function makeButton(onclick,value,title,accesskey) {
    var html = '<input type="button" onclick="' + onclick;
       html += 'return false;" title="' + title;
       html += '" accesskey="' + accesskey + '" class="ubbcButton';
       html += '" value="' + value + '">';
    return html;
  }

  function makeLink(onclick,text) {
    var html = '<a href="#" onclick="' + onclick;
       html += 'return false;" onmouseover="return winStat(\'' + text;
       html += '\');" onmouseout="return winStat(\'\');">';
    return html;
  }

  function makeImage(source,width,height,alt) {
    var html = '<img src="' + ubbc_dir + source + '" width="' + width;
       html += '" height="' + height + '" border="0" alt="' + alt;
       html += '" align="absmiddle">';
    return html;
  }

  function makeOption(value,text,style) {
    var html = '<option value="' + value;
       html += ((style && ie) ? '" style="color:' + value : '');
       html += '">' + text + '</option>';
    return html;
  }


/////////////////////////////
// Misc utils
//
  function storeCaret(el) { 
    if (el.createTextRange) {
      el.caretPos = document.selection.createRange().duplicate();
    }
  }

  function getText() {
    if (ie) {
      return ((form["Post"].createTextRange && form["Post"].caretPos) ? form["Post"].caretPos.text : '');
    } else { return ''; }
  }

  function isUrl(text) {
    return ((text.indexOf('.') > 7) &&
            ((text.substring(0,7) == 'http://') ||
            (text.substring(0,6) == 'ftp://')));
  }

  function isEmail(str) {
    if (!reSupport) { return (str.indexOf(".") > 2) && (str.indexOf("@") > 0); }
    var r1 = new RegExp("(@.*@)|(\\.\\.)|(@\\.)|(^\\.)");
    var r2 = new RegExp("^.+\\@(\\[?)[a-zA-Z0-9\\-\\.]+\\.([a-zA-Z]{2,4}|[0-9]{1,3})(\\]?)$");
    return (!r1.test(str) && r2.test(str));
  }

  function winStat(txt) {
    window.status = txt;
    return true;
  }

  function returnFocus() {
    setTimeout('form["Post"].focus()',10);
  }

  function resetList(list) {
    setTimeout('form["'+list+'"].options[0].selected = true',10);
  }

  function ubbHelp() {
    var url = ubbc_dir + '/help/index.htm';
    var options = 'height=350,width=300,scrollbars=yes';
    window.open(url,'ubbc_help',options);
  }

function ubbSmile() {
    var url = 'http://mysmilies.com';
    var options = 'scrollbars=yes';
    window.open(url);
  }

  function removeElement(array,value) {
    array = array.split(',');
    for (i = 0; i < array.length; i++) {
      if (array[i] == value) { var pos = i; break; }
    }
    for (i = pos; i < (array.length-1); i++) {
      array[i] = array[i + 1];
    }
    array.length = array.length - 1;
    return array.join(',');
  }


/////////////////////////////
// Indivdual code types
//
  var openTags = new Array('');
  var closedTags = new Array('dummy','b','i','u','s','code','quote','me','list');
  function ubbBasic(code) {
    var text = getText();
    if (text) {
      code = '[' + code + ']' + text + '[/' + code + ']';
      ubbCode(code);
    } else {
      if (openTags.join(',').indexOf(','+code) != -1) {
        var tag = '[/' + code + ']';
        openTags = removeElement(openTags.join(','),code).split(',');
        closedTags[closedTags.length] = code;
      } else {
        var tag = '[' + code + ']';
        closedTags = removeElement(closedTags.join(','),code).split(',');
        openTags[openTags.length] = code;
      } ubbCode(tag);
    }
  }

  function ubbFont(list) {
    var attrib = list.name.substring(1,list.name.length);
    var value = list.options[list.selectedIndex].value;
    if (value && attrib) {
      var code = '[' + attrib + '=' + value + ']' + getText() + '[/' + attrib + ']';
      ubbCode(code);
    }
    resetList(list.name);
  }

 function ubbAlign(align) {
    if (!align) { return; }
    code = '[align=' + align + ']' + getText() + '[/align]';
    ubbCode(code);
    resetList("talign");
  }


  function ubbList(size) {
    var text = getText();
    if (!size && !text) { ubbBasic('list'); }
    else if (!size && text && reSupport) {
      var regExp = /\n/g;
      text = text.replace(regExp,'\n[*]');
      var code = '[list]\n[*]' + text + '\n[/list]\n';
      ubbCode(code);
    } else {
      if (text) { text += '\n'; }
      var code = text + '[list]\n';
      for (i = 0; i < size; i++) { code += '[*]\n'; }
      code += '[/list]\n';
      ubbCode(code);
      resetList("quicklist");
    }
  }

  function ubbListItem() {
    var code = '[*]' + getText();
    ubbCode(code);
  }

  function ubbHref() {
    var url = 'http://'; var desc = '';
    var text = getText();
    if (text) {
      if (isUrl(text)) { url = text; }
      else { desc = text; }
    }
    url = prompt('������� ������:',url) || '';
    desc = prompt('�������� ������:',desc) || url;
    if (!isUrl(url)) { returnFocus(); return; }
    var code = '[url=' + url + ']' + desc + '[/url]';
    ubbCode(code);
  }

  function ubbEmail() {
    var email = ''; var desc = '';
    var text = getText();
    if (text) {
      if (isEmail(text)) { email = text; }
      else { desc = text; }
    }
    email = prompt('������� E-mail �����:',email) || '';
    desc = prompt('������� ��������:',desc) || email;
    if (!isEmail(email)) { returnFocus(); return; }
    var code = '[email=' + email + ']' + desc + '[/email]';
    ubbCode(code);
  }

  function ubbImage() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\n������� URL ��������:","http://") || "";
    if (!url) { return; }
    var code = "[IMG]" + url + "[/IMG]";
    ubbCode(code);
  }



  function ubbFlash() {
    var url = 'http://'; var h = ''; var w = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    url = prompt('������� URL Flash �������:',url) || '';
    w = prompt('������� ������ Flash:\nMax = '+flash_w, w) || '';
    h = prompt('������� ������ Flash:\nMax = '+flash_h, h) || '';
    if (isNaN(w) || (w > flash_w)) { w = flash_w; }
    if (isNaN(h) || (h > flash_h)) { h = flash_h; }
    if (!isUrl(url)) { returnFocus(); return; }
    var code = ((text) ? text + ' ' : '') + '[flash=' + w + ',' + h + ']' + url + '[/flash]';
    ubbCode(code);
  }


function ubbGlow() {
    var color = ''; var write = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    color = prompt('������� ���� ��������:',color) || '';
    write = prompt('������� �����:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[glow=' + color + ']' + write + '[/glow]';
    ubbCode(code);
  }

  function ubbShadow() {
    var color = ''; var write = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    color = prompt('������� ���� ����:',color) || '';
    write = prompt('������� �����:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[shadow=' + color + ']' + write + '[/shadow]';
    ubbCode(code);
  }

  function ubbSpoil() {
    var write = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    write = prompt('������� ���������� �����:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[spoiler]' + write + '[/spoiler]';
    ubbCode(code);
  }

  function ubbsound() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\n������� URL �����:","http://") || "";
    if (!url) { return; }
    var code = '[sound]' + url + '[/sound]';
    ubbCode(code);
  }

  function ubbvideo() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\n������� URL �����:","http://") || "";
    if (!url) { return; }
    var code = '[video]' + url + '[/video]';
    ubbCode(code);
  }

  function ubbweb() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\n������� ������ ����������� �����:","http://") || "";
    if (!url) { return; }
    var code = '[web]' + url + '[/web]';
    ubbCode(code);
  }

/////////////////////////////
// Access Keys
//
  var keys = new Array('b','i','u','s','g','q','m','h','e','l','k');
  function checkKey() {
    if (event.ctrlKey) {
      for (i = 0; i < keys.length; i++) {
        if (String.fromCharCode(event.keyCode) == keys[i].toUpperCase()) {
          var found = keys[i]; break;
        }
      }
      if (found) {
        switch(found) {
          case 'h':ubbHref();break;
          case 'e':ubbEmail();break;
          case 'p':ubbImage();break;
          case 'f':ubbFlash();break;
          case 'l':ubbList();break;
          case 'k':ubbListItem();break;
          case 'g':ubbBasic('code');break;
          case 'q':ubbBasic('quote');break;
          case 'm':ubbBasic('me');break;
          default:ubbBasic(found);
        }
        this.cancelBubble = true;
        this.returnValue = false;
        return false;
      }
    }
  }


/////////////////////////////
// Replacing default iB JS
//
  function emoticon(theSmilie) {
    var text = getText() + ' ';
    var code = text + theSmilie + ' ';
    ubbCode(code);
  }


/////////////////////////////
// Initilization
//
  var form;
  writeStyle();
  var reSupport = 0;
  function ubbcInit(images,flash,graphical) {
    form = document.forms["REPLIER"];

    if (images) { keys[keys.length] = 'p'; }
    if (flash) { keys[keys.length] = 'f'; }
    if (graphical) { document.onkeydown = checkKey; }

    if (window.RegExp) {
      var tempStr = "a";
      var tempReg = new RegExp(tempStr);
      if (tempReg.test(tempStr)) { reSupport = 1; }
    }
  }
