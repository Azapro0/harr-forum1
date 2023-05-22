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
       html += makeOption('','Цвет',0);
       html += makeOption('skyblue','Светло-голубой',1);
       html += makeOption('royalblue','Голубой',1);
       html += makeOption('blue','Синий',1);
       html += makeOption('darkblue','Тёмно-синий',1);
       html += makeOption('orange','Оранжевый',1);
       html += makeOption('orangered','Морковный',1);
       html += makeOption('crimson','Бордовый',1);
       html += makeOption('red','Красный',1);
       html += makeOption('firebrick','Кирпичный',1);
       html += makeOption('darkred','Коричневый',1);
       html += makeOption('green','Зелёный',1);
       html += makeOption('limegreen','Салатовый',1);
       html += makeOption('seagreen','Болотный',1);
       html += makeOption('deeppink','Розовый',1);
       html += makeOption('tomato','Томатный',1);
       html += makeOption('coral','Coral',1);
       html += makeOption('purple','Сиреневый',1);
       html += makeOption('indigo','Фиолетовый',1);
       html += makeOption('burlywood','Горчичный',1);
       html += makeOption('sandybrown','Песчаный',1);
       html += makeOption('sienna','Кофейный',1);
       html += makeOption('chocolate','Шоколадный',1);
       html += makeOption('teal','Морской',1);
       html += makeOption('silver','Серебряный',1);
       html += '</select> ';
       html += '<select name="fsize" class="ibcSelect" onchange="ubbFont(this);">';
        html += makeOption('','Размер',0);
       html += makeOption('1','Мелкий',0);
       html += makeOption('2','Небольшой',0);
       html += makeOption('6','Средний',0);
       html += makeOption('8','Большой',0);
       html += makeOption('10','Огромный',0);
       html += '</select> ';
       html += '<select name="ffont" class="ibcSelect" onchange="ubbFont(this);">';
       html += makeOption('','Шрифт',0);
       html += makeOption('arial','Arial',0);
       html += makeOption('courier','Courier',0);
       html += makeOption('impact','Impact',0);
       html += makeOption('tahoma','Tahoma',0);
       html += makeOption('times','Times',0);
       html += makeOption('verdana','Verdana',0);
       html += '</select> ';
       html += ' <select name="quicklist" class="ibcSelect" onchange="ubbList(this.options[this.selectedIndex].value);">';
       html += makeOption('','Список',0);
       html += makeOption('1','1 Пункт',0);
       html += makeOption('2','2 Пункта',0);
       html += makeOption('3','3 Пункта',0);
       html += makeOption('4','4 Пункт',0);
       html += makeOption('5','5 Пунктов',0);
       html += '</select> ';
         html += '<select name="talign" class="ibcSelect" onchange="ubbAlign(this.options[this.selectedIndex].value);">';
         html += makeOption('','Выравнивание',0);
         html += makeOption('left','По левому краю',0);
         html += makeOption('center','По центру',0);
         html += makeOption('right','По правому краю',0);
         html += '</select> ';
       html += '</td></tr><tr><td align=center>' + ((nn) ? '&nbsp;' : '');
    if (graphical) {
       html += makeLink("ubbBasic('b');",'Жирный[B]') + makeImage('/bold.gif','23','22','Жирный[B]') + '</a>';
       html += makeLink("ubbBasic('i');",'Наклонный[I]') + makeImage('/italics.gif','23','22','Наклонный[I]') + '</a>';
       html += makeLink("ubbBasic('u');",'Подчёркнутый[U]') + makeImage('/underline.gif','23','22','Подчёркнутый[U]') + '</a>';
       html += makeLink("ubbBasic('s');",'Зачёркнутый[S]') + makeImage('/strikethrough.gif','23','22','Зачёркнутый[S]') + '</a>';
       html += makeLink("ubbBasic('move');",'Движущийся[J]') + makeImage('/marque.gif','23','22','Движущийся[J]') + '</a> ';
       html += makeLink("ubbBasic('fliph');",'Перевёрнутый горизонтально[Z]') + makeImage('/horiz.gif','23','22','Перевёрнутый горизонтально[Z]') + '</a>';
       html += makeLink("ubbBasic('flipv');",'Перевёрнутый вертикально[V]') + makeImage('/vert.gif','23','22','Перевёрнутый вертикально[V]') + '</a>';
       html += makeLink("ubbShadow();",'Тень[D]') + makeImage('/shadow.gif','23','22','Тень[D]') + '</a>';
       html += makeLink("ubbGlow();",'Свечение[G]') + makeImage('/glow.gif','23','22','Свечение[G]') + '</a>';
       html += makeLink("ubbSpoil();",'Скрытый текст[Sp]') + makeImage('/spoiler.gif','23','22','Скрытый текст[Sp]') + '</a>';
    } else {
       html += makeButton("ubbBasic('b');",' B ','Жирный[B]','b') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('i');",' I ','Наклонный[I]','i') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('u');",' U ','Подчёркнутый[U]','u') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('s');",' S ','Зачёркнутый[S]','s');
         html += makeButton("ubbBasic('move');",'Marquee','Движущийся[J]','j') + makeImage('/pixel.gif',1,1,'');
         html += makeButton("ubbBasic('fliph');",'Flip Lt-Rt','Перевёрнутый горизонтально[Z]','z')  + makeImage('/pixel.gif',1,1,'');
         html += makeButton("ubbBasic('flipv');",'Flip Up-Dn','Перевёрнутый вертикально[V]','') + makeImage('/pixel.gif',1,1,'');
         html += ((shadow) ? makeButton("ubbShadow();",'Shadow','Тень[D]','d') : '') + makeImage('/pixel.gif',1,1,'');
         html += ((glow) ? makeButton("ubbGlow();",'Glow','Свечение[G]','g') : '') + makeImage('/pixel.gif',1,1,'');
         html += ((spoil) ? makeButton("ubbSpoil();",'---','Скрытый текст[Sp]','0') : '') + makeImage('/pixel.gif',1,1,'');
    }

    if (graphical) {
       html += ' ' + makeLink("ubbsound('');",'Звук[S]') + makeImage('/sound.gif','23','22','Звук[S]') + '</a>';
       html += makeLink("ubbvideo('');",'Видео[V]') + makeImage('/rplayer.gif','23','22','Видео[V]') + '</a>';
       html += ' ' +makeLink("ubbBasic('code');",'Код [G]') + makeImage('/code.gif','23','22','Код [G]') + '</a>';
       html += makeLink("ubbBasic('quote');",'Цитата[Q]') + makeImage('/quote.gif','23','22','Цитата[Q]') + '</a>';
    } else {
       html += ' ' + makeLink("ubbsound('');",'Звук[S]') + makeImage('/sound.gif','23','22','Звук[S]') + '</a>';
       html += makeLink("ubbvideo('');",'Видео[V]') + makeImage('/rplayer.gif','23','22','Видео[V]') + '</a>';
       html += makeLink("ubbweb('');",'Веб[W]') + makeImage('/web.gif','23','22','Веб[W]') + '</a>';
       html += ' ' +makeButton("ubbBasic('code');",' # ','Код [C]','c') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbBasic('quote');",'Quote','Цитата[Q]','q') + makeImage('/pixel.gif',1,1,'');
    }
       html += '</td></tr><tr><td align=center>';
    if (graphical) {
       html += makeLink("ubbList('');",'Список [L]') + makeImage('/list.gif','23','22','Список [L]') + '</a>';
       html += makeLink("ubbListItem();",'Пункт списка [K]') + makeImage('/listitem.gif','23','22','Пункт списка [K]') + '</a>';
       html += makeLink("ubbBasic('me');",'Действие[M]') + makeImage('/me.gif','23','22','Действие[M]') + '</a> ';
       html += makeLink("ubbHref();",'Ссылка [H]') + makeImage('/url.gif','23','22','Ссылка [H]') + '</a>';
       html += makeLink("ubbEmail();",'[E]mail') + makeImage('/email.gif','23','22','[E]mail') + '</a>';
       html += ((images) ? makeLink("ubbImage();",'Картинка[P]') + makeImage('/image.gif','23','22','Картинка[P]') + '</a>' : '');
       html += ((flash) ? makeLink("ubbFlash();",'[F]lash объект') + makeImage('/flash.gif','23','22','[F]lash объект') + '</a>' : '');
       html += ' ' + makeLink("ubbSmile();",'Смайлы') + makeImage('/smile.gif','23','22','Смайлы') + '</a>';
       html += ' ' + makeLink("ubbHelp();",'Помощь') + makeImage('/help.gif','23','22','Помощь') + '</a>';

    } else {
       html += makeButton("ubbList('');",'UL','Список [L]','l') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbListItem();",'LI','Пункт списка [K]','k');
       html += makeButton("ubbBasic('me');",'Me','Действие[M]','m') + ' ';
       html += makeButton("ubbHref();",'URL','Ссылка [H]','h') + makeImage('/pixel.gif',1,1,'');
       html += makeButton("ubbEmail();",' @ ','[E]mail','e') + makeImage('/pixel.gif',1,1,'');
       html += ((images) ? makeButton("ubbImage();",'IMG','Картинка[P]','p') + makeImage('/pixel.gif',1,1,'') : '');
       html += ((flash) ? makeButton("ubbFlash();",'SWF','[F]lash объект','f') + makeImage('/pixel.gif',1,1,'') : '');
         html += ' ' + makeButton("ubbSmile();",' ? ','Смайлы','') + makeImage('/pixel.gif',1,1,'');
       html += ' ' + makeButton("ubbHelp();",' ? ','Помощь','');
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
    url = prompt('Введите ссылку:',url) || '';
    desc = prompt('Описание ссылки:',desc) || url;
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
    email = prompt('Введите E-mail адрес:',email) || '';
    desc = prompt('Введите описание:',desc) || email;
    if (!isEmail(email)) { returnFocus(); return; }
    var code = '[email=' + email + ']' + desc + '[/email]';
    ubbCode(code);
  }

  function ubbImage() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\nВведите URL картинки:","http://") || "";
    if (!url) { return; }
    var code = "[IMG]" + url + "[/IMG]";
    ubbCode(code);
  }



  function ubbFlash() {
    var url = 'http://'; var h = ''; var w = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    url = prompt('Введите URL Flash объекта:',url) || '';
    w = prompt('Введите ширину Flash:\nMax = '+flash_w, w) || '';
    h = prompt('Введите высоту Flash:\nMax = '+flash_h, h) || '';
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
    color = prompt('Введите цвет свечения:',color) || '';
    write = prompt('Введите текст:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[glow=' + color + ']' + write + '[/glow]';
    ubbCode(code);
  }

  function ubbShadow() {
    var color = ''; var write = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    color = prompt('Введите цвет тени:',color) || '';
    write = prompt('Введите текст:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[shadow=' + color + ']' + write + '[/shadow]';
    ubbCode(code);
  }

  function ubbSpoil() {
    var write = '';
    var text = getText();
    if (text && isUrl(text)) { url = text; text = ''; }
    write = prompt('Введите скрываемый текст:',write) || '';
    var code = ((text) ? text + ' ' : '') + '[spoiler]' + write + '[/spoiler]';
    ubbCode(code);
  }

  function ubbsound() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\nВведите URL звука:","http://") || "";
    if (!url) { return; }
    var code = '[sound]' + url + '[/sound]';
    ubbCode(code);
  }

  function ubbvideo() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\nВведите URL видео:","http://") || "";
    if (!url) { return; }
    var code = '[video]' + url + '[/video]';
    ubbCode(code);
  }

  function ubbweb() {
    var text = getText();
    var url = (text && isUrl) ? text : prompt("\nВведите ссылку копируемого сайта:","http://") || "";
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
