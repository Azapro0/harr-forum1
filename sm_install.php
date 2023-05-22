<?php


/*
+--------------------------------------------------------------------------
|   INVISION POWER BOARD SAFE MODE INSTALL SCRIPT v1.3
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Script written by Matthew Mecham
|   > Date started: 30th March 2002
|   > Update started: 17th October 2002
|
+--------------------------------------------------------------------------
*/

error_reporting  (E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0);

//+---------------------------------------
// ENTER YOUR PATH TO THE DIRECTORY THIS SCRIPT
// IS IN.
//
// Tips:
//
// If you are using Windows and Apache, do not
// use backslashes, use normal forward slashes.
// You may need to remove the drive letter also
// Example: C:\apache\htdocs\ibforums\ will need
// to be: /apache/htdocs/ibforums/
//
// If you are using Windows and IIS, then you will 
// need to enter double backslashes.
//
// In all cases, please enter a trailing slash (or
// trailing backslashes...)
//+---------------------------------------

$root = "./";



//+---------------------------------------

$template = new template;
$std      = new installer;

$VARS = $std->parse_incoming();

//+---------------------------------------
// What are we doing then? Eh? I'm talking to you!
//+---------------------------------------

if ( file_exists($root.'install.lock') )
{
	install_error("Файл установки заблокирован!<br>Удалите (через FTP) файл 'install.lock', находящийся в этой директории.");
	exit();
}


switch($VARS['a'])
{
	case '1':
		do_setup_form();
		break;
		
	case '2':
		do_install();
		break;
		
	case 'templates':
		do_templates();
		break;
		
	case '3':
		do_finish();
		break;
		
	default:
		do_intro();
		break;
}

function do_finish()
{
	global $std, $template, $root, $VARS, $SQL;
	
	// Attempt to lock the install..
	
	if ($FH = @fopen( $root.'install.lock', 'w' ) )
	{
		@fwrite( $FH, 'bleh', 4 );
		@fclose($FH);
		
		@chmod( $root.'install.lock', 0666 );
		
		$template->print_top('Success!');
	
		$msg="Файл установки форума заблокирован (для переустановки форума, необходимо удалить файл 'install.lock'). Для полной безопасности форума, строго рекомендуем удалить файл установки sm_install.php.
			 <br><br>
			 <center><b><a href='index.php?act=Login&CODE=00'>НАЖМИТЕ СЮДА ДЛЯ ВХОДА НА ФОРУМ!</a></center>";
	}
	else
	{
		$template->print_top('Готово!');
		
		$msg = "СТРОГО РЕКОМЕНДУЕТСЯ УДАЛИТЬ ФАЙЛ УСТАНОВКИ ФОРУМА ('sm_install.php') ПЕРЕД ПРОДОЛЖЕНИЕМ!<br>Этим Вы обезопасите свой форм от удаления со стороны недругов!
				<br><br>
				<center><b><a href='index.php?act=Login&CODE=00'>НАЖМИТЕ СЮДА ДЛЯ ВХОДА НА ФОРУМ!</a></center>";
	}
	
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>Поздравления</div>
	<div class='tablepad'>
	<b>Установка форума завершена!</b>
	<br><br>
	$msg
	</div>
	</div>
	</div>";
						 
	$template->output();
	
	
	
}


//+---------------------------------------
// Install the template files, woohoo and stuff
//+---------------------------------------


function do_templates()
{
	global $std, $template, $root, $VARS, $HTTP_POST_VARS;
	
	//-----------------------------------
	// IMPORT $INFO!
	//-----------------------------------
	
	if ($root == './')
	{
		$root = str_replace( '\\', '/', getcwd() ) . '/';
	}
	
	$require = $root."conf_global.php";
	
	if ( ! file_exists($require) )
	{
		install_error("Невозможно определить местонахождение '$require'. Возможно Вам потребуется ввести полный путь к этому скрипту установки форума. Для этого, откройте данный файл установки в любом текстовом редакторе и введите полный путь в переменной \$root - не забудьте при этом добавить конечный слэш (обратную косую черту). Пользователям NT, необходимо ввести двойной слэш.");
	}
	
	include($require);
	
	//-----------------------------------
	// Attempt a DB connection..
	//-----------------------------------
	
	if ( ! $connect_id = mysql_connect( $INFO['sql_host'],$INFO['sql_user'],$INFO['sql_pass'] ) )
	{
		install_error("Невозможно подключиться к базе mySQL. Проверьте наличие файла 'conf_global.php' в той же директории, где находится данный скрипт установки форума.");
	}
	
		
	if ( ! mysql_select_db($INFO['sql_database'], $connect_id) )
	{
		install_error("База mySQL с названием '{$VARS['sql_database']}' не найдена. Обратитесь в техническую поддержку Вашего хоста, для уточнения необходимых данных.");
	}
	
	//-----------------------------------
	// Lets open the style file
	//-----------------------------------
	
	$style_file = $root.'install_templates.txt';
	
	if ( ! file_exists($style_file) )
	{
		install_error("Невозможно определить местонахождение '$style_file'. <br><br>Проверьте нгаличие этого файла в той же директории, где находится данный скрипт установки форума.<br><br>Возможно Вам потребуется ввести полный путь к этому скрипту установки форума. Для этого, откройте данный файл установки в любом текстовом редакторе и введите полный путь в переменной \$root - не забудьте при этом добавить конечный слэш (обратную косую черту). Пользователям NT, необходимо ввести двойной слэш.");
	}
	
	if ( $fh = fopen( $style_file, 'r' ) )
	{
		$data = fread($fh, filesize($style_file) );
		fclose($fh);
	}
	else
	{
		install_error("Невозможно открыть '$style_file'");
	}
	
	if (strlen($data) < 100)
	{
		install_error("Ошибка 1:'$style_file' загружен не полностью. Перезагрузите его на сервер снова, прямо поверх существующего.'");
	}
	
	// Chop up the data file.
	
	$template_rows = explode( "||~&~||", $data );
	
	$crows = count($template_rows);  //we're counting crows :o
	
	if ( $crows < 100 )
	{
		install_error("Ошибка 2: (Поиск $crows rows) '$style_file' загружен не полностью. Перезагрузите его на сервер снова, прямо поверх существующего.'");
	}
	
	//-----------------------------------
	// Lets populate the database!
	//-----------------------------------
	
	foreach( $template_rows as $q )
	{

	   $q = trim($q);
	   
	   if (strlen($q) < 5)
	   {
	       continue;
	   }
	   
	   $query = "INSERT INTO ".$INFO['sql_tbl_prefix']."skin_templates (set_id, group_name, section_content, func_name, func_data, updated, can_remove) VALUES $q";
		   
	   if ( ! mysql_query($query, $connect_id) )
	   {
		   install_error("Ошибка mySQL: ".mysql_error());
	   }
   }
   
   
   // ARE WE DONE? REALLY? COOL!!
   
   $template->print_top('Готово!');
   
   $template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>Поздравления</div>
	<div class='tablepad'>
	<b>Template files installed!</b>
	<br><br>
	Процесс установки форума завершён.
	<br>
	Нажмите на ссылку ниже, для блокирования файла установки
	<br><br>
	<center><b><a href='sm_install.php?a=3'>НАЖМИТЕ СЮДА ДЛЯ ЗАВЕРШЕНИЯ</a></center>
	</div>
	</div>
	</div>";
   
	$template->output();
	
}

//+---------------------------------------


function do_install()
{
	global $std, $template, $root, $VARS, $HTTP_POST_VARS;
	
	// Ok, lets check for blankies...
	
	$NEW_INFO = array();
	
	$need = array('board_url','sql_host','sql_database','sql_user','adminname','adminpassword','adminpassword2','email');
	
	//-----------------------------------
	
	foreach($need as $greed)
	{
		if ($VARS[ $greed ] == "")
		{
			install_error("Необходимо заполнить все поля. Заполнение поля 'SQL Table prefix' необязательно.");
		}
	}
	
	//-----------------------------------
	
	$VARS['board_url'] = preg_replace( "#/$#", "", $VARS['board_url'] );
	
	if ($VARS['sql_tbl_prefix'] == "")
	{
		$VARS['sql_tbl_prefix'] = 'ibf_';
	}
	
	//-----------------------------------
	// Did the admin passy and passy2 match?
	//-----------------------------------
	
	if ($VARS['adminpassword2'] != $VARS['adminpassword'])
	{
		install_error("Ваши пароли не идентичны");
	}
	
	/*if ( ! preg_match( "!^http://!", $VARS['board_url'] ) )
	{
		install_error("Ссылка форума должна начинаться с 'http://'");
	}*/
	
	//-----------------------------------
	// IMPORT $INFO!
	//-----------------------------------
	
	if ($root == './')
	{
		$root = str_replace( '\\', '/', getcwd() ) . '/';
	}
	
	$require = $root."conf_global.php";
	
	if ( ! file_exists($require) )
	{
		install_error("Невозможно определить местонахождение '$require'. Возможно Вам потребуется ввести полный путь к этому скрипту установки форума. Для этого, откройте данный файл установки в любом текстовом редакторе и введите полный путь в переменной \$root - не забудьте при этом добавить конечный слэш (обратную косую черту). Пользователям NT, необходимо ввести двойной слэш.");
	}
	
	//@chmod( "conf_global.php", 0666 );
	
	include($require);
	
	//echo("here");
	//exit();
	
	if ( count($INFO) < 1 )
	{
		install_error("Ошибка в файле 'conf_global.php' ({$VARS['base_dir']}conf_global.php). Перезагрузите его в режиме ASCII.");
	}
	
	//-----------------------------------
	// Attempt a DB connection..
	//-----------------------------------
	
	if ( ! $connect_id = mysql_connect( $VARS['sql_host'],$HTTP_POST_VARS['sql_user'],$HTTP_POST_VARS['sql_pass'] ) )
	{
		install_error("Невозможно подключиться к базе mySQL. Проверьте введённые Вами данные SQL.");
	}
	
		
	if ( ! mysql_select_db($VARS['sql_database'], $connect_id) )
	{
		install_error("mySQL не может обнаружить базу данных с названием '{$VARS['sql_database']}'. Проверьте введённое Вами название базы SQL.");
	}
	
	//-----------------------------------
	// Attempt to write the config file.
	//-----------------------------------
	
    $new  = array( 'base_dir'       => $root,
				   'board_url'      => $VARS['board_url'],
				   'sql_host'       => $VARS['sql_host'],
				   'sql_database'   => $VARS['sql_database'],
				   'sql_user'       => $HTTP_POST_VARS['sql_user'],
				   'sql_pass'       => $HTTP_POST_VARS['sql_pass'],
				   'sql_tbl_prefix' => $VARS['sql_tbl_prefix'],
				   
				   'html_dir'       => $root."html/",
				   'html_url'       => $VARS['board_url']."/html",
				   'upload_dir'     => $root."uploads",
				   'upload_url'     => $VARS['board_url']."/uploads",
				   'email_in'       => $VARS['email'],
				   'email_out'      => $VARS['email'],
				   'ban_names'      => "",
				   'ban_email'      => "",
				   'ban_ip'         => "",
				   'force_login'    => 0,
				   'load_limit'     => "",
				   'board_start'    => time(),
				   'installed'      => 1,
				   'guests_ava'     => 1,
				   'guests_img'		=> 1,
				   'guests_sig'		=> 1,
				   'print_headers'  => 0,
				   'guest_name_pre' => "Гость_",
				 );
					 
	 foreach( $new as $k => $v )
	 {
		 // Update the old...
		 
		 $v = preg_replace( "/'/", "\\'" , $v );
		 $v = preg_replace( "/\r/", ""   , $v );
		 
		 $INFO[ $k ] = $v;
	 }
	 
	 $file_string = "<?php\n";
		
	 foreach( $INFO as $k => $v )
	 {
		 if ($k == 'skin' or $k == 'languages')
		 {
			continue;
		 }
		 $file_string .= '$INFO['."'".$k."'".']'."\t\t\t=\t'".$v."';\n";
	 }
	 
	 $file_string .= "\n".'?'.'>';   // Question mark + greater than together break syntax hi-lighting in BBEdit 6 :p
	 
	 if ( $fh = fopen( $require, 'w' ) )
	 {
		 fputs($fh, $file_string, strlen($file_string) );
		 fclose($fh);
	 }
	 else
	 {
		 install_error("Невозможно произвести запись в файл 'conf_global.php'");
	 }
	 
	 //-----------------------------------
	 // What MySQL version are we running?
	 //-----------------------------------
	 
	 $a = mysql_query("SELECT VERSION() AS version", $connect_id);
		
	 if ( ! $row = mysql_fetch_array($a) )
	 {
		 $b = mysql_query("SHOW VARIABLES LIKE 'version'", $connect_id);
		 $row = mysql_fetch_array($b);
	 }
	 
	 $mysql_version = 32100;
	 
	 $no_array = explode( '.', preg_replace( "/^(.+?)[-_]?/", "\\1", $row['version']) );
	 
	 $one   = (!isset($no_array) || !isset($no_array[0])) ? 3  : $no_array[0];
	 $two   = (!isset($no_array[1]))                      ? 21 : $no_array[1];
	 $three = (!isset($no_array[2]))                      ? 0  : $no_array[2];
	 
	 $mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));
	 
	 //-----------------------------------
	 // Lets populate the database!
	 //-----------------------------------
	 
	 $SQL = get_sql();
	 
	 foreach( $SQL as $q )
	 {
	 	if ($VARS['sql_tbl_prefix'] != "ibf_")
        {
           $q = preg_replace("/ibf_(\S+?)([\s\.,]|$)/", $VARS['sql_tbl_prefix']."\\1\\2", $q);
        }
        
        $q = str_replace( "<%time%>", time(), $q );
        
        if ( preg_match("/CREATE TABLE (\S+) \(/", $q, $match) )
        {
        	if ($match[1])
        	{
        		$the_query = "DROP TABLE if exists ".$match[1];
        		
        		if (! mysql_query($the_query, $connect_id) )
        		{
        			install_error("Ошибка mySQL: ".mysql_error());
        		}
        	}
        }
        
        if ( $mysql_version < 32323 )
        {
        	$q = str_replace( "KEY forum_id (forum_id,approved,pinned),FULLTEXT KEY title (title)", 'KEY forum_id (forum_id,approved,pinned)', $q );
        	$q = str_replace( "KEY forum_id (forum_id,post_date),FULLTEXT KEY post (post)"        , 'KEY forum_id (forum_id,post_date)'      , $q );
        }
        	
        if ( ! mysql_query($q, $connect_id) )
        {
        	install_error("Ошибка mySQL: ".mysql_error()."<br /><b>Query:</b>".$q);
        }
	}
	
	// Insert the admin...
	
	$passy = md5($VARS['adminpassword']);
	$time  = time();
	
	$query = "INSERT INTO ".$VARS['sql_tbl_prefix']."members (id, name, mgroup, password, email, joined, ip_address, posts, title, last_visit, last_activity) ".
		     "VALUES(1, '{$VARS['adminname']}', 4, '$passy', '{$VARS['email']}', '$time', '127.0.0.1', '0', 'Administrator', '$time', '$time')";
		     
	if ( ! mysql_query($query, $connect_id) )
	{
		install_error("Ошибка mySQL: ".mysql_error());
		
	}
	
	// ARE WE DONE? REALLY? COOL!!
	
	$template->print_top('Готово!');
	
	$template->contents .= "
	<div class='centerbox'>
	<div class='tableborder'>
	<div class='maintitle'>Поздравления</div>
	<div class='tablepad'>
	<b>Ваш форум почти успешно установлен!</b>
	<br><br>
	Процесс установки почти завершён.
	<br>
	Следующий и последний шаг, установит файлы шаблона в Вашу базу данных.
	<br><br>
	<center><b><a href='sm_install.php?a=templates'>НАЖМИТЕ СЮДА ДЛЯ ПРОДОЛЖЕНИЯ</a></center>
	</div>
	</div>
	</div>";
						 
	$template->output();
	
}




function do_setup_form()
{
	global $std, $template, $root, $HTTP_SERVER_VARS;
	
	$template->print_top('Set Up form');
	
	//--------------------------------------------------
	
	$this_url = str_replace( "/sm_install.php", "", $HTTP_SERVER_VARS['HTTP_REFERER']);
	
	if ( ! $this_url )
	{
		$this_url = substr($HTTP_SERVER_VARS['SCRIPT_NAME'],0, -15);
		
    	if ($this_url == '')
    	{
    		$this_url == '/';
    	}
    	$this_url = 'http://'.$HTTP_SERVER_VARS['SERVER_NAME'].$this_url; 
    } 
	
	
	//--------------------------------------------------
	
	$template->contents .= "
	
	<form action='sm_install.php' method='POST'>
	<input type='hidden' name='a' value='2'>
	<div class='centerbox'>
	
	<div class='tableborder'>
	<div class='maintitle'>Данные Вашего сервера</div>
	<div class='pformstrip'>В этой секции необходимо ввести все пути и ссылки Вашего форума.</div>
	<table width='100%' cellspacing='1'>
	<tr>
	  <td class='pformleftw'><b>Ссылка скрипта</b><br>Эта ссылка (необходимо ввести с http://) к папке, где находится данный скрипт установки форума</td>
	  <td class='pformright'><input type='text' id='textinput' name='board_url' value='$this_url'></td>
	</tr>
	</table>
	</div>
	<div class='fade'>&nbsp;</div>
	
	<br />
	
	<div class='tableborder'>
	<div class='maintitle'>Данные Вашего сервера</div>
	<div class='pformstrip'>В этой секции необходимо ввести все данные Вашей базы SQL. Если Вам неизвестны эти данные, свяжитесь с Вашим хостером для уточнения. Вы можете использовать как уже существующую базу данных, так и создать новую базу данных.</div>
	<table width='100%' cellspacing='1'>
	<tr>
	  <td class='pformleftw'><b>SQL Host</b><br>(localhost is usually sufficient)</td>
	  <td class='pformright'><input type='text' id='textinput' name='sql_host' value='localhost'></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Название базы SQL</b></td>
	  <td class='pformright'><input type='text' id='textinput' name='sql_database' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Имя пользователя базы SQL</b></td>
	  <td class='pformright'><input type='text' id='textinput' name='sql_user' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Пароль к базе SQL</b></td>
	  <td class='pformright'><input type='text' id='textinput' name='sql_pass' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Префикс таблиц SQL</b>(Это поле заполнять необязательно)</td>
	  <td class='pformright'><input type='text' id='textinput' name='sql_tbl_prefix' value=''></td>
	</tr>
	</table>
	</div>
	<div class='fade'>&nbsp;</div>
	
	<br />
	
	<div class='tableborder'>
	<div class='maintitle'>Ваш аккаунт Администратора</div>
	<div class='pformstrip'>В этой секции необходимо ввести информацию, для создания аккаунта Администратора. Вводите данные внимательно!</div>
	<table width='100%' cellspacing='1'>
	<tr>
	  <td class='pformleftw'><b>Имя пользователя</b></td>
	  <td class='pformright'><input type='text' id='textinput' name='adminname' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Пароль</b></td>
	  <td class='pformright'><input type='password' id='textinput' name='adminpassword' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>Повторите пароль</b></td>
	  <td class='pformright'><input type='password' id='textinput' name='adminpassword2' value=''></td>
	</tr>
	
	<tr>
	  <td class='pformleftw'><b>E-mail адрес</b></td>
	  <td class='pformright'><input type='text' id='textinput' name='email' value=''></td>
	</tr>
	</table>
	<div align='center' class='pformstrip'  style='text-align:center'><input type='image' src='html/sys-img/install_proceed.gif'></div>
	</div>
	<div class='fade'>&nbsp;</div>
	</div>
	</form>";
						 
	$template->output();
						 
}

//+---------------------------------------

function do_intro()
{
	global $std, $template, $root;
	
	$template->print_top('Welcome');
	
	$template->contents .= "<table width='80%' border='0' cellpadding='0' cellspacing='0' align='center'>
							<tr>
							 <td valign='top'><img src='html/sys-img/install_face.jpg' border='0' alt='Вступление'></td>
							 <td><img src='html/sys-img/install_text.gif' border='0' alt='Welcome to IPB'>
							  <br /><br />
							   Перед продолжением, тщательно проверьте, что все файлы загружены и что файл 
							   'conf_global.php' имеет необходимый CHMOD атрибут для записи в него ( достаточно установить атрибут 0666 ).
							   <br><br>
							   Вам также, необходимо уточнить следующую информацию у Вашего хостинг-провайдера:
							   <ul>
							   <li>Название Вашей базы MySQL</li>
							   <li>Имя пользователя базы MySQL</li>
							   <li>Пароль базы MySQL</li>
							   <li>Хост MySQL (обычно используется localhost)</li>
							   </ul>
							   <br />
							   На следующей странице установки, Вы должны будете ввести всю необходимую информацию для установки форума.
							   <br><br>
							   <b>ВАЖНОЕ ПРИМЕЧАНИЕ: ИМЕЙТЕ ВВИДУ, ЧТО ЕСЛИ У ВАС УЖЕ УСТАНОВЛЕН ФОРУМ IPB С ТЕМ ЖЕ ПРЕФИКСОМ НАЗВАНИЙ ТАБЛИЦ, В ТОЙ ЖЕ БАЗЕ ДАННЫХ, ТО ЭТОТ СУЩЕСТВУЮЩИЙ ФОРУМ БУДЕТ УДАЛЁН.</b>
							   ";
						 
	// Check to make sure that the config file is there and it's got suitable permissions to write to:
	
	$file = $root."conf_global.php";
	
	$style_file = $root."install_templates.txt";
	
	$warnings = array();
	
	if ( ! file_exists($style_file) )
	{
		$warnings[] = "Невозможно определить местонахождение файла 'install_templates.txt'. Этот файл должен находиться в той же директории, где и данный скрипт установки!";
	}
	
	if ( ! file_exists($file) )
	{
		$warnings[] = "Невозможно определить местонахождение файла 'conf_global.php'. Этот файл должен находиться в той же директории, где и данный скрипт установки!";
	}
	
	if ( ! is_writeable($file) )
	{
		$warnings[] = "Невозможно произвести запись в файл 'conf_global.php'. Проверьте, чтобы атрибут у этого файла был установлен на запись. Если Вы не уверены, установите CHMOD через FTP на 0777";
	}
	
	$phpversion = phpversion();
	
	if ($phpversion < '4.0.0') {
		$warnings[] = "Установка форума Invision Power Board невозможна. Для установки Invision Power Board требуется PHP версии 4.0.0 или выше.";
	}
	
	if ( count($warnings) > 0 )
	{
	
		$err_string = "<ul><li>".implode( "<li>", $warnings )."</ul>";
	
		$template->contents .= "<br /><br />
							    <div class='warnbox'>
							     <strong>Внимание!</strong>
							     <b>Перед продолжением, необходимо устранить следующие ошибки!</b>
								 <br><br>
								 $err_string
							    </div>";
	}
	else
	{
		$template->contents .= "<br /><br /><div align='center'><a href='sm_install.php?a=1'><img src='html/sys-img/install_proceed.gif' border='0' alt='proceed'></a></div>";
	}
	
	$template->contents .= " </td>
							  </tr>
							 </table>";
	
	$template->output();
}



function install_error($msg="")
{
	global $std, $template, $root;
	
	$template->print_top('Внимание!');
	

	
	$template->contents .= "<div class='warnbox'>
						     <strong style='font-size:16px;color:#F00'>Внимание!</strong>
						     <br /><br />
						     <b>Перед продолжением, необходимо устранить следующие ошибки!</b><br>Вернитесь назад и повторите попытку!
						     <br><br>
						     $msg
						    </div>";
	
	
	
	$template->output();
}

//+--------------------------------------------------------------------------
// CLASSES
//+--------------------------------------------------------------------------



class template
{
	var $contents = "";
	
	function output()
	{
		echo $this->contents;
		echo "   
				 
				 <br><br><br><br><center><span id='copy'><a href='http://www.invisionboard.com'>Invision Power Board</a> &copy; 2010 <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a></span></center>
				 
				 </body>
				 </html>";
		exit();
	}
	
	//--------------------------------------

	function print_top($title="")
	{
	
		$this->contents = "<html>
		          <head><title>Установка форума Invision Power Board :: $title </title>
		          <style type='text/css'>
		          	
		          	BODY		          	
		          	{
		          		font-size: 11px;
		          		font-family: Verdana, Arial;
		          		color: #000;
		          		margin: 0px;
		          		padding: 0px;
		          		background-image: url(html/sys-img/fadebg.jpg);
		          		background-repeat: no-repeat;
		          		background-position: right bottom;
		          	}
		          	
		          	TABLE, TR, TD     { font-family:Verdana, Arial;font-size: 11px; color:#000 }
					
					a:link, a:visited, a:active  { color:#000055 }
					a:hover                      { color:#333377;text-decoration:underline }
					
					.centerbox { margin-right:10%;margin-left:10%;text-align:left }
					
					.warnbox {
							   border:1px solid #F00;
							   background: #FFE0E0;
							   padding:6px;
							   margin-right:10%;margin-left:10%;text-align:left;
							 }
					
					.tablepad    { background-color:#F5F9FD;padding:6px }

					.pformstrip { background-color: #D1DCEB; color:#3A4F6C;font-weight:bold;padding:7px;margin-top:1px;text-align:left }
					.pformleftw { background-color: #F5F9FD; padding:6px; margin-top:1px;width:40%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }
					.pformright { background-color: #F5F9FD; padding:6px; margin-top:1px;border-top:1px solid #C2CFDF; }

					.tableborder { border:1px solid #345487;background-color:#FFF; padding:0px; margin:0px; width:100% }

					.maintitle { text-align:left;vertical-align:middle;font-weight:bold; color:#FFF; letter-spacing:1px; padding:8px 0px 8px 5px; background-image: url(html/sys-img/tile_back.gif) }
					.maintitle a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFF }
					.maintitle a:hover { text-decoration: underline }
					
					#copy { font-size:10px }
										
					#button   { background-color: #4C77B6; color: #FFFFFF; font-family:Verdana, Arial; font-size:11px }
					
					#textinput { background-color: #EEEEEE; color:К#000000; font-family:Verdana, Arial; font-size:10px; width:100% }
					
					#dropdown { background-color: #EEEEEE; color:К#000000; font-family:Verdana, Arial; font-size:10px }
					
					#multitext { background-color: #EEEEEE; color:К#000000; font-family:Courier, Verdana, Arial; font-size:10px }
					
					#logostrip {
								 padding: 0px;
								 margin: 0px;
								 background: #7AA3D0;
							   }
							   
					.fade					
					{
						background-image: url(html/sys-img/fade.jpg);
						background-repeat: repeat-x;
					}
					
				  </style>
				  </head>
				 <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>
				 
				 <div id='logostrip'><img src='html/sys-img/title.gif' border='0' alt='Установщик Invision Power Board' /></div>
				 <div class='fade'>&nbsp;</div>
				 <br />
				 ";
				  	   
	}


}


class installer
{

	function parse_incoming()
    {
    	global $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_CLIENT_IP, $REQUEST_METHOD, $REMOTE_ADDR, $HTTP_PROXY_USER, $HTTP_X_FORWARDED_FOR;
    	$return = array();
    	
		if( is_array($HTTP_GET_VARS) )
		{
			while( list($k, $v) = each($HTTP_GET_VARS) )
			{
				//$k = $this->clean_key($k);
				if( is_array($HTTP_GET_VARS[$k]) )
				{
					while( list($k2, $v2) = each($HTTP_GET_VARS[$k]) )
					{
						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
					}
				}
				else
				{
					$return[$k] = $this->clean_value($v);
				}
			}
		}
		
		// Overwrite GET data with post data
		
		if( is_array($HTTP_POST_VARS) )
		{
			while( list($k, $v) = each($HTTP_POST_VARS) )
			{
				//$k = $this->clean_key($k);
				if ( is_array($HTTP_POST_VARS[$k]) )
				{
					while( list($k2, $v2) = each($HTTP_POST_VARS[$k]) )
					{
						$return[$k][ $this->clean_key($k2) ] = $this->clean_value($v2);
					}
				}
				else
				{
					$return[$k] = $this->clean_value($v);
				}
			}
		}
		
		return $return;
	}
    
    function clean_key($key) {
    
    	if ($key == "")
    	{
    		return "";
    	}
    	
    	$key = preg_replace( "/\.\./"           , ""  , $key );
    	$key = preg_replace( "/\_\_(.+?)\_\_/"  , ""  , $key );
    	$key = preg_replace( "/^([\w\.\-\_]+)$/", "$1", $key );
    	return $key;
    }
    
    function clean_value($val) {
    
    	if ($val == "")
    	{
    		return "";
    	}
    	
    	$val = preg_replace( "/&/"         , "&amp;"         , $val );
    	$val = preg_replace( "/<!--/"      , "&#60;&#33;--"  , $val );
    	$val = preg_replace( "/-->/"       , "--&#62;"       , $val );
    	$val = preg_replace( "/<script/i"  , "&#60;script"   , $val );
    	$val = preg_replace( "/>/"         , "&gt;"          , $val );
    	$val = preg_replace( "/</"         , "&lt;"          , $val );
    	$val = preg_replace( "/\"/"        , "&quot;"        , $val );
    	$val = preg_replace( "/\|/"        , "&#124;"        , $val );
    	$val = preg_replace( "/\n/"        , "<br>"          , $val ); // Convert literal newlines
    	$val = preg_replace( "/\\\$/"      , "&#036;"        , $val );
    	$val = preg_replace( "/\r/"        , ""              , $val ); // Remove literal carriage returns
    	$val = preg_replace( "/!/"         , "&#33;"         , $val );
    	$val = preg_replace( "/'/"         , "&#39;"         , $val ); // IMPORTANT: It helps to increase sql query safety.
    	$val = stripslashes($val);                                     // Swop PHP added backslashes
    	$val = preg_replace( "/\\\/"       , "&#092;"        , $val ); // Swop user inputted backslashes
    	return $val;
    }
   
}



// DATA AND STUFF, ETC

function get_sql()
{

$SQL = array();

$SQL[] = "CREATE TABLE ibf_admin_logs (
  id bigint(20) NOT NULL auto_increment,
  act varchar(255) default NULL,
  code varchar(255) default NULL,
  member_id int(10) default NULL,
  ctime int(10) default NULL,
  note text,
  ip_address varchar(255) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_admin_sessions (
  ID varchar(32) NOT NULL default '',
  IP_ADDRESS varchar(32) NOT NULL default '',
  MEMBER_NAME varchar(32) NOT NULL default '',
  MEMBER_ID varchar(32) NOT NULL default '',
  SESSION_KEY varchar(32) NOT NULL default '',
  LOCATION varchar(64) default 'index',
  LOG_IN_TIME int(10) NOT NULL default '0',
  RUNNING_TIME int(10) NOT NULL default '0',
  PRIMARY KEY  (ID)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_badwords (
  wid int(3) NOT NULL auto_increment,
  type varchar(250) NOT NULL default '',
  swop varchar(250) default NULL,
  m_exact tinyint(1) default '0',
  PRIMARY KEY  (wid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_cache_store (
  cs_key varchar(255) NOT NULL default '',
  cs_value text NOT NULL,
  cs_extra varchar(255) NOT NULL default '',
  PRIMARY KEY  (cs_key)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_calendar_events (
  eventid mediumint(8) NOT NULL auto_increment,
  userid mediumint(8) NOT NULL default '0',
  year int(4) NOT NULL default '2002',
  month int(2) NOT NULL default '1',
  mday int(2) NOT NULL default '1',
  title varchar(254) NOT NULL default 'no title',
  event_text text NOT NULL,
  read_perms varchar(254) NOT NULL default '*',
  unix_stamp int(10) NOT NULL default '0',
  priv_event tinyint(1) NOT NULL default '0',
  show_emoticons tinyint(1) NOT NULL default '1',
  rating smallint(2) NOT NULL default '1',
  event_ranged tinyint(1) NOT NULL default '0',
  event_repeat tinyint(1) NOT NULL default '0',
  repeat_unit char(2) NOT NULL default '',
  end_day int(2) default NULL,
  end_month int(2) default NULL,
  end_year int(4) default NULL,
  end_unix_stamp int(10) default NULL,
  event_bgcolor varchar(32) NOT NULL default '',
  event_color varchar(32) NOT NULL default '',
  PRIMARY KEY  (eventid),
  KEY unix_stamp (unix_stamp)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_categories (
  id smallint(5) NOT NULL default '0',
  position tinyint(3) default NULL,
  state varchar(10) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  image varchar(128) default NULL,
  url varchar(128) default NULL,
  PRIMARY KEY  (id),
  KEY id (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_contacts (
  id mediumint(8) NOT NULL auto_increment,
  contact_id mediumint(8) NOT NULL default '0',
  member_id mediumint(8) NOT NULL default '0',
  contact_name varchar(32) NOT NULL default '',
  allow_msg tinyint(1) default NULL,
  contact_desc varchar(50) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_css (
  cssid int(10) NOT NULL auto_increment,
  css_name varchar(128) NOT NULL default '',
  css_text text,
  css_comments text,
  updated int(10) default '0',
  PRIMARY KEY  (cssid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_email_logs (
  email_id int(10) NOT NULL auto_increment,
  email_subject varchar(255) NOT NULL default '',
  email_content text NOT NULL,
  email_date int(10) NOT NULL default '0',
  from_member_id mediumint(8) NOT NULL default '0',
  from_email_address varchar(250) NOT NULL default '',
  from_ip_address varchar(16) NOT NULL default '127.0.0.1',
  to_member_id mediumint(8) NOT NULL default '0',
  to_email_address varchar(250) NOT NULL default '',
  topic_id int(10) NOT NULL default '0',
  PRIMARY KEY  (email_id),
  KEY from_member_id (from_member_id),
  KEY email_date (email_date)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_emoticons (
  id smallint(3) NOT NULL auto_increment,
  typed varchar(32) NOT NULL default '',
  image varchar(128) NOT NULL default '',
  clickable smallint(2) NOT NULL default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_faq (
  id mediumint(8) NOT NULL auto_increment,
  title varchar(128) NOT NULL default '',
  text text,
  description text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_files (
  id mediumint(8) unsigned NOT NULL auto_increment,
  cat tinyint(3) unsigned NOT NULL default '0',
  sname varchar(70) NOT NULL default '',
  sdesc text NOT NULL,
  author varchar(75) NOT NULL default '',
  poster varchar(75) NOT NULL default '',
  mid mediumint(8) unsigned NOT NULL default '0',
  date varchar(13) NOT NULL default '',
  updated varchar(13) NOT NULL default '',
  views smallint(5) unsigned NOT NULL default '0',
  downloads smallint(6) NOT NULL default '0',
  topic mediumint(8) NOT NULL default '0',
  open tinyint(1) NOT NULL default '0',
  screenshot varchar(250) NOT NULL default '',
  votes tinyint(4) NOT NULL default '0',
  total tinyint(4) NOT NULL default '0',
  rating tinyint(4) NOT NULL default '0',
  url varchar(250) NOT NULL default '',
  link varchar(250) NOT NULL default '',
  current tinyint(1) NOT NULL default '1',
  ipaddress varchar(32) NOT NULL default '0',
  UNIQUE KEY id (id)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_cats (
  cid tinyint(3) unsigned NOT NULL default '0',
  sub tinyint(3) NOT NULL default '0',
  cname varchar(50) NOT NULL default '',
  cdesc tinytext NOT NULL,
  copen tinyint(1) unsigned NOT NULL default '1',
  dis_screen tinyint(1) unsigned NOT NULL default '0',
  dis_screen_cat tinyint(1) unsigned NOT NULL default '0',
  authorize tinyint(1) unsigned NOT NULL default '0',
  fordaforum smallint(5) unsigned NOT NULL default '0',
  position tinyint(4) NOT NULL auto_increment,
  show_notes tinyint(1) NOT NULL default '0',
  cnotes tinytext NOT NULL,
  UNIQUE KEY id (cid),
  KEY position (position),
  KEY sub (sub)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_comments (
  file_id mediumint(8) unsigned NOT NULL default '0',
  mem_id mediumint(8) unsigned NOT NULL default '0',
  name varchar(75) NOT NULL default '',
  date varchar(13) NOT NULL default '0',
  comment mediumtext NOT NULL
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_custentered (
  file_id mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (file_id)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_custfields (
  fid smallint(5) NOT NULL auto_increment,
  ftitle varchar(200) NOT NULL default '',
  fcontent text,
  ftype varchar(250) default 'text',
  freq tinyint(1) NOT NULL default '0',
  fshow tinyint(1) NOT NULL default '0',
  fmaxinput smallint(6) default '250',
  ftopic tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (fid)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_downloads (
  m_id mediumint(8) unsigned NOT NULL default '0',
  member_name varchar(75) NOT NULL default '',
  downloaded varchar(13) NOT NULL default '0',
  file_id mediumint(8) NOT NULL default '0',
  file_name varchar(70) NOT NULL default '',
  KEY id (m_id),
  KEY script_id (file_id)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_favorites (
  id mediumint(8) NOT NULL default '0',
  mid mediumint(8) NOT NULL default '0',
  mname varchar(50) NOT NULL default '',
  fid mediumint(8) NOT NULL default '0',
  fname varchar(75) NOT NULL default '',
  date varchar(13) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_files_votes (
  mid mediumint(8) NOT NULL default '0',
  rating tinyint(1) NOT NULL default '0',
  did mediumint(8) NOT NULL default '0'
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_forum_perms (
  perm_id int(10) NOT NULL auto_increment,
  perm_name varchar(250) NOT NULL default '',
  PRIMARY KEY  (perm_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_forum_tracker (
  frid mediumint(8) NOT NULL auto_increment,
  member_id varchar(32) NOT NULL default '',
  forum_id smallint(5) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (frid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_forums (
  id smallint(5) NOT NULL default '0',
  topics mediumint(6) default NULL,
  posts mediumint(6) default NULL,
  last_post int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default '0',
  last_poster_name varchar(32) default NULL,
  name varchar(128) NOT NULL default '',
  description text,
  position tinyint(2) default NULL,
  use_ibc tinyint(1) default NULL,
  use_html tinyint(1) default NULL,
  status varchar(10) default NULL,
  start_perms varchar(255) NOT NULL default '',
  reply_perms varchar(255) NOT NULL default '',
  read_perms varchar(255) NOT NULL default '',
  password varchar(32) default NULL,
  category tinyint(2) NOT NULL default '0',
  last_title varchar(128) default NULL,
  last_id int(10) default NULL,
  sort_key varchar(32) default NULL,
  sort_order varchar(32) default NULL,
  prune tinyint(3) default NULL,
  show_rules tinyint(1) default NULL,
  upload_perms varchar(255) default NULL,
  preview_posts tinyint(1) default NULL,
  allow_poll tinyint(1) NOT NULL default '1',
  allow_pollbump tinyint(1) NOT NULL default '0',
  inc_postcount tinyint(1) NOT NULL default '1',
  skin_id int(10) default NULL,
  parent_id mediumint(5) default '-1',
  subwrap tinyint(1) default '0',
  sub_can_post tinyint(1) default '1',
  quick_reply tinyint(1) default '1',
  redirect_url varchar(250) default '',
  redirect_on tinyint(1) NOT NULL default '0',
  redirect_hits int(10) NOT NULL default '0',
  redirect_loc varchar(250) default '',
  rules_title varchar(255) NOT NULL default '',
  rules_text text NOT NULL,
  has_mod_posts tinyint(1) NOT NULL default '0',
  topic_mm_id varchar(250) NOT NULL default '',
  notify_modq_emails text,
  PRIMARY KEY  (id),
  KEY category (category),
  KEY id (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_groups (
  g_id int(3) unsigned NOT NULL auto_increment,
  g_view_board tinyint(1) default NULL,
  g_mem_info tinyint(1) default NULL,
  g_other_topics tinyint(1) default NULL,
  g_use_search tinyint(1) default NULL,
  g_email_friend tinyint(1) default NULL,
  g_invite_friend tinyint(1) default NULL,
  g_edit_profile tinyint(1) default NULL,
  g_post_new_topics tinyint(1) default NULL,
  g_reply_own_topics tinyint(1) default NULL,
  g_reply_other_topics tinyint(1) default NULL,
  g_edit_posts tinyint(1) default NULL,
  g_delete_own_posts tinyint(1) default NULL,
  g_open_close_posts tinyint(1) default NULL,
  g_delete_own_topics tinyint(1) default NULL,
  g_post_polls tinyint(1) default NULL,
  g_vote_polls tinyint(1) default NULL,
  g_use_pm tinyint(1) default NULL,
  g_is_supmod tinyint(1) default NULL,
  g_access_cp tinyint(1) default NULL,
  g_title varchar(32) NOT NULL default '',
  g_can_remove tinyint(1) default NULL,
  g_append_edit tinyint(1) default NULL,
  g_access_offline tinyint(1) default NULL,
  g_avoid_q tinyint(1) default NULL,
  g_avoid_flood tinyint(1) default NULL,
  g_icon varchar(64) default NULL,
  g_attach_max bigint(20) default NULL,
  g_avatar_upload tinyint(1) default '0',
  g_calendar_post tinyint(1) default '0',
  g_d_add_files TINYINT (1) DEFAULT '0',
  g_d_ibcode_files TINYINT (1) DEFAULT '0', 
  g_d_html_files TINYINT (1) DEFAULT '0', 
  g_do_download TINYINT (1) DEFAULT '0', 
  g_d_edit_files TINYINT (1) DEFAULT '0', 
  g_d_manage_files TINYINT (1) DEFAULT '0',
  g_d_allow_dl_offline TINYINT(1) UNSIGNED DEFAULT '0',
  g_d_post_comments TINYINT (1) DEFAULT '0', 
  g_d_approve_down TINYINT (1) DEFAULT '0', 
  g_d_eofs TINYINT (1) DEFAULT '0',
  g_d_optimize_db TINYINT(1) UNSIGNED DEFAULT '0',
  g_d_check_links TINYINT (1) DEFAULT '0', 
  g_d_check_topics TINYINT (1) DEFAULT '0',
  g_d_max_dls TINYINT (4) NOT NULL DEFAULT '0',
  prefix varchar(250) default NULL,
  suffix varchar(250) default NULL,
  g_max_messages int(5) default '50',
  g_max_mass_pm int(5) default '0',
  g_search_flood mediumint(6) default '20',
  g_edit_cutoff int(10) default '0',
  g_promotion varchar(10) default '-1&-1',
  g_hide_from_list tinyint(1) default '0',
  g_post_closed tinyint(1) default '0',
  g_perm_id varchar(255) NOT NULL default '',
  g_photo_max_vars varchar(200) default '',
  g_dohtml tinyint(1) NOT NULL default '0',
  g_edit_topic tinyint(1) NOT NULL default '0',
  g_email_limit varchar(15) NOT NULL default '10:15',
  g_display varchar(3) DEFAULT '0' NOT NULL,
  PRIMARY KEY  (g_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_languages (
  lid mediumint(8) NOT NULL auto_increment,
  ldir varchar(64) NOT NULL default '',
  lname varchar(250) NOT NULL default '',
  lauthor varchar(250) default NULL,
  lemail varchar(250) default NULL,
  PRIMARY KEY  (lid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_macro (
  macro_id smallint(3) NOT NULL auto_increment,
  macro_value varchar(200) default NULL,
  macro_replace text,
  can_remove tinyint(1) default '0',
  macro_set smallint(3) NOT NULL default 0,
  PRIMARY KEY  (macro_id),
  KEY macro_set (macro_set)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_macro_name (
  set_id smallint(3) NOT NULL default '0',
  set_name varchar(200) default NULL,
  PRIMARY KEY  (set_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_member_extra (
  id mediumint(8) NOT NULL default '0',
  notes text,
  links text,
  bio text,
  ta_size char(3) default NULL,
  photo_type varchar(10) default '',
  photo_location varchar(255) default '',
  photo_dimensions varchar(200) default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_members (
  id mediumint(8) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  mgroup smallint(3) NOT NULL default '0',
  password varchar(32) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  joined int(10) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  avatar varchar(128) default NULL,
  avatar_size varchar(9) default NULL,
  posts mediumint(7) default '0',
  aim_name varchar(40) default NULL,
  icq_number varchar(40) default NULL,
  location varchar(128) default NULL,
  signature text,
  website varchar(70) default NULL,
  yahoo varchar(32) default NULL,
  title varchar(64) default NULL,
  allow_admin_mails tinyint(1) default NULL,
  time_offset varchar(10) default NULL,
  interests text,
  hide_email varchar(8) default NULL,
  email_pm tinyint(1) default NULL,
  email_full tinyint(1) default NULL,
  skin smallint(5) default NULL,
  warn_level int(10) default NULL,
  warn_lastwarn int(10) NOT NULL default '0',
  language varchar(32) default NULL,
  msnname varchar(64) default NULL,
  last_post int(10) default NULL,
  restrict_post varchar(100) NOT NULL default '0',
  view_sigs tinyint(1) default '1',
  view_img tinyint(1) default '1',
  view_avs tinyint(1) default '1',
  view_pop tinyint(1) default '1',
  bday_day int(2) default NULL,
  bday_month int(2) default NULL,
  bday_year int(4) default NULL,
  new_msg tinyint(2) default NULL,
  msg_from_id mediumint(8) default NULL,
  msg_msg_id int(10) default NULL,
  msg_total smallint(5) default NULL,
  vdirs text,
  show_popup tinyint(1) default NULL,
  misc varchar(128) default NULL,
  last_visit int(10) default '0',
  last_activity int(10) default '0',
  dst_in_use tinyint(1) default '0',
  view_prefs varchar(64) default '-1&-1',
  coppa_user tinyint(1) default '0',
  mod_posts varchar(100) NOT NULL default '0',
  auto_track tinyint(1) default '0',
  org_perm_id varchar(255) default '',
  org_supmod tinyint(1) default '0',
  integ_msg varchar(250) default '',
  temp_ban varchar(100) default NULL,
  sub_end int(10) NOT NULL default '0',
  files tinyint (4) DEFAULT '0',
  downloads tinyint(4) DEFAULT '0',
  PRIMARY KEY  (id),
  KEY name (name),
  KEY mgroup (mgroup),
  KEY bday_day (bday_day),
  KEY bday_month (bday_month)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_messages (
  msg_id int(10) NOT NULL auto_increment,
  msg_date int(10) default NULL,
  read_state tinyint(1) default NULL,
  title varchar(128) default NULL,
  message text,
  from_id mediumint(8) NOT NULL default '0',
  vid varchar(32) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  recipient_id mediumint(8) NOT NULL default '0',
  attach_type tinyint(128) default NULL,
  attach_file tinyint(128) default NULL,
  cc_users text,
  tracking tinyint(1) default '0',
  read_date int(10) default NULL,
  PRIMARY KEY  (msg_id),
  KEY member_id (member_id),
  KEY vid (vid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_moderator_logs (
  id int(10) NOT NULL auto_increment,
  forum_id int(5) default '0',
  topic_id int(10) NOT NULL default '0',
  post_id int(10) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  ip_address varchar(16) NOT NULL default '0',
  http_referer varchar(255) default NULL,
  ctime int(10) default NULL,
  topic_title varchar(128) default NULL,
  action varchar(128) default NULL,
  query_string varchar(128) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_moderators (
  mid mediumint(8) NOT NULL auto_increment,
  forum_id int(5) NOT NULL default '0',
  member_name varchar(32) NOT NULL default '',
  member_id mediumint(8) NOT NULL default '0',
  edit_post tinyint(1) default NULL,
  edit_topic tinyint(1) default NULL,
  delete_post tinyint(1) default NULL,
  delete_topic tinyint(1) default NULL,
  view_ip tinyint(1) default NULL,
  open_topic tinyint(1) default NULL,
  close_topic tinyint(1) default NULL,
  mass_move tinyint(1) default NULL,
  mass_prune tinyint(1) default NULL,
  move_topic tinyint(1) default NULL,
  pin_topic tinyint(1) default NULL,
  unpin_topic tinyint(1) default NULL,
  post_q tinyint(1) default NULL,
  topic_q tinyint(1) default NULL,
  allow_warn tinyint(1) default NULL,
  edit_user tinyint(1) NOT NULL default '0',
  is_group tinyint(1) default '0',
  group_id smallint(3) default NULL,
  group_name varchar(200) default NULL,
  split_merge tinyint(1) default '0',
  can_mm tinyint(1) NOT NULL default '0',
  pin_first_post_topic tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mid),
  KEY forum_id (forum_id),
  KEY group_id (group_id),
  KEY member_id (member_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_pfields_content (
  member_id mediumint(8) NOT NULL default '0',
  updated int(10) default '0',
  PRIMARY KEY  (member_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_pfields_data (
  fid smallint(5) NOT NULL auto_increment,
  ftitle varchar(200) NOT NULL default '',
  fdesc varchar(250) default '',
  fcontent text,
  ftype varchar(250) default 'text',
  freq tinyint(1) default '0',
  fhide tinyint(1) default '0',
  fmaxinput smallint(6) default '250',
  fedit tinyint(1) default '1',
  forder smallint(6) default '1',
  fshowreg tinyint(1) default '0',
  PRIMARY KEY  (fid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_polls (
  pid mediumint(8) NOT NULL auto_increment,
  tid int(10) NOT NULL default '0',
  start_date int(10) default NULL,
  choices text,
  starter_id mediumint(8) NOT NULL default '0',
  votes smallint(5) NOT NULL default '0',
  forum_id smallint(5) NOT NULL default '0',
  poll_question varchar(255) default NULL,
  PRIMARY KEY  (pid)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_posts (
  pid int(10) NOT NULL auto_increment,
  append_edit tinyint(1) default '0',
  edit_time int(10) default NULL,
  author_id mediumint(8) NOT NULL default '0',
  author_name varchar(32) default NULL,
  use_sig tinyint(1) NOT NULL default '0',
  use_emo tinyint(1) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  post_date int(10) default NULL,
  icon_id smallint(3) default NULL,
  post text,
  queued tinyint(1) default NULL,
  topic_id int(10) NOT NULL default '0',
  forum_id smallint(5) NOT NULL default '0',
  attach_id varchar(64) default NULL,
  attach_hits int(10) default NULL,
  attach_type varchar(128) default NULL,
  attach_file varchar(255) default NULL,
  post_title varchar(255) default NULL,
  new_topic tinyint(1) default '0',
  edit_name varchar(255) default NULL,
  has_modcomment TINYINT(1) DEFAULT '0' NULL,
  PRIMARY KEY  (pid),
  KEY topic_id (topic_id,author_id),
  KEY author_id (author_id),
  KEY forum_id (forum_id,post_date),FULLTEXT KEY post (post)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_reg_antispam (
  regid varchar(32) NOT NULL default '',
  regcode varchar(8) NOT NULL default '',
  ip_address varchar(32) default NULL,
  ctime int(10) default NULL,
  PRIMARY KEY  (regid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_search_results (
  id varchar(32) NOT NULL default '',
  topic_id text NOT NULL,
  search_date int(12) NOT NULL default '0',
  topic_max int(3) NOT NULL default '0',
  sort_key varchar(32) NOT NULL default 'last_post',
  sort_order varchar(4) NOT NULL default 'desc',
  member_id mediumint(10) default '0',
  ip_address varchar(64) default NULL,
  post_id text,
  post_max int(10) NOT NULL default '0',
  query_cache text
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_sessions (
  id varchar(32) NOT NULL default '0',
  member_name varchar(64) default NULL,
  member_id mediumint(8) NOT NULL default '0',
  ip_address varchar(16) default NULL,
  browser varchar(64) default NULL,
  running_time int(10) default NULL,
  login_type tinyint(1) default NULL,
  location varchar(40) default NULL,
  member_group smallint(3) default NULL,
  in_forum smallint(5) NOT NULL default '0',
  in_topic int(10) default NULL,
  PRIMARY KEY  (id),
  KEY in_topic (in_topic),
  KEY in_forum (in_forum)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_skin_templates (
  suid int(10) NOT NULL auto_increment,
  set_id int(10) NOT NULL default '0',
  group_name varchar(255) NOT NULL default '',
  section_content mediumtext,
  func_name varchar(255) default NULL,
  func_data text,
  updated int(10) default NULL,
  can_remove tinyint(4) default '0',
  PRIMARY KEY  (suid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_skins (
  uid int(10) NOT NULL auto_increment,
  sname varchar(100) NOT NULL default '',
  sid int(10) NOT NULL default '0',
  set_id int(5) NOT NULL default '0',
  tmpl_id int(10) NOT NULL default '0',
  macro_id int(10) NOT NULL default '1',
  css_id int(10) NOT NULL default '1',
  img_dir varchar(200) default '1',
  tbl_width varchar(250) default NULL,
  tbl_border varchar(250) default NULL,
  hidden tinyint(1) NOT NULL default '0',
  default_set tinyint(1) NOT NULL default '0',
  css_method varchar(100) default 'inline',
  PRIMARY KEY  (uid),
  KEY tmpl_id (tmpl_id),
  KEY css_id (css_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_spider_logs (
  sid int(10) NOT NULL auto_increment,
  bot varchar(255) NOT NULL default '',
  query_string text NOT NULL,
  entry_date int(10) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_stats (
  TOTAL_REPLIES int(10) NOT NULL default '0',
  TOTAL_TOPICS int(10) NOT NULL default '0',
  LAST_MEM_NAME varchar(32) default NULL,
  LAST_MEM_ID mediumint(8) NOT NULL default '0',
  MOST_DATE int(10) default NULL,
  MOST_COUNT int(10) default '0',
  MEM_COUNT mediumint(8) NOT NULL default '0'
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_subscriptions (
 sub_id smallint(5) NOT NULL auto_increment,
 sub_title varchar(250) NOT NULL default '',
 sub_desc text,
 sub_new_group mediumint(8) NOT NULL default 0,
 sub_length smallint(5) NOT NULL default '1',
 sub_unit varchar(2) NOT NULL default 'm',
 sub_cost decimal(10,2) NOT NULL default '0.00',
 sub_run_module varchar(250) NOT NULL default '',
 PRIMARY KEY (sub_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_subscription_extra (
 subextra_id smallint(5) NOT NULL auto_increment,
 subextra_sub_id smallint(5) NOT NULL default '0',
 subextra_method_id smallint(5) NOT NULL default '0',
 subextra_product_id varchar(250) NOT NULL default '0',
 subextra_can_upgrade tinyint(1) NOT NULL default '0',
 subextra_recurring tinyint(1) NOT NULL default '0',
 subextra_custom_1 text,
 subextra_custom_2 text,
 subextra_custom_3 text,
 subextra_custom_4 text,
 subextra_custom_5 text,
 PRIMARY KEY(subextra_id)
) TYPE=MyISAM;";


$SQL[] = "CREATE TABLE ibf_subscription_trans (
 subtrans_id int(10) NOT NULL auto_increment,
 subtrans_sub_id smallint(5) NOT NULL default '0',
 subtrans_member_id mediumint(8) NOT NULL default '0',
 subtrans_old_group smallint(5) NOT NULL default '0',
 subtrans_paid decimal(10,2) NOT NULL default '0.00',
 subtrans_cumulative decimal(10,2) NOT NULL default '0.00',
 subtrans_method varchar(20) NOT NULL default '',
 subtrans_start_date int(11) NOT NULL default '0',
 subtrans_end_date int(11) NOT NULL default '0',
 subtrans_state varchar(200) NOT NULL default '',
 subtrans_trxid varchar(200) NOT NULL default '',
 subtrans_subscrid varchar(200) NOT NULL default '',
 subtrans_currency varchar(10) NOT NULL default 'USD',
 PRIMARY KEY (subtrans_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_subscription_logs (
 sublog_id int(10) NOT NULL auto_increment,
 sublog_date int(10) NOT NULL default 0,
 sublog_member_id mediumint(8) NOT NULL default '0',
 sublog_transid int(10) NOT NULL default 0,
 sublog_ipaddress varchar(16) NOT NULL default '',
 sublog_data text,
 sublog_postdata text,
 PRIMARY KEY (sublog_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_subscription_methods (
 submethod_id smallint(5) NOT NULL auto_increment,
 submethod_title varchar(250) NOT NULL default '',
 submethod_name varchar(20) NOT NULL default '',
 submethod_email varchar(250) NOT NULL default '',
 submethod_sid text,
 submethod_custom_1 text,
 submethod_custom_2 text,
 submethod_custom_3 text,
 submethod_custom_4 text,
 submethod_custom_5 text,
 submethod_is_cc tinyint(1) NOT NULL default '0',
 submethod_is_auto tinyint(1) NOT NULL default '0',
 submethod_desc text,
 submethod_logo text,
 submethod_active tinyint(1) NOT NULL default 0,
 submethod_use_currency varchar(10) NOT NULL default 'USD',
 PRIMARY KEY (submethod_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_subscription_currency (
 subcurrency_code varchar(10) NOT NULL,
 subcurrency_desc varchar(250) NOT NULL default '',
 subcurrency_exchange decimal(10, 8) NOT NULL,
 subcurrency_default tinyint(1) NOT NULL default '0',
 PRIMARY KEY(subcurrency_code)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_templates (
  tmid int(10) NOT NULL auto_increment,
  template mediumtext,
  name varchar(128) default NULL,
  PRIMARY KEY  (tmid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_titles (
  id smallint(5) NOT NULL auto_increment,
  posts int(10) default NULL,
  title varchar(128) default NULL,
  pips varchar(128) default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_tmpl_names (
  skid int(10) NOT NULL auto_increment,
  skname varchar(60) NOT NULL default 'Invision Board',
  author varchar(250) default '',
  email varchar(250) default '',
  url varchar(250) default '',
  PRIMARY KEY  (skid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_topic_mmod (
  mm_id smallint(5) NOT NULL auto_increment,
  mm_title varchar(250) NOT NULL default '',
  mm_enabled tinyint(1) NOT NULL default '0',
  topic_state varchar(10) NOT NULL default 'leave',
  topic_pin varchar(10) NOT NULL default 'leave',
  topic_move smallint(5) NOT NULL default '0',
  topic_move_link tinyint(1) NOT NULL default '0',
  topic_title_st varchar(250) NOT NULL default '',
  topic_title_end varchar(250) NOT NULL default '',
  topic_reply tinyint(1) NOT NULL default '0',
  topic_reply_content text NOT NULL,
  topic_reply_postcount tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (mm_id)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_topics (
  tid int(10) NOT NULL auto_increment,
  title varchar(250) NOT NULL default '',
  description varchar(70) default NULL,
  state varchar(8) default NULL,
  posts int(10) default NULL,
  starter_id mediumint(8) NOT NULL default '0',
  start_date int(10) default NULL,
  last_poster_id mediumint(8) NOT NULL default '0',
  last_post int(10) default NULL,
  icon_id tinyint(2) default NULL,
  starter_name varchar(32) default NULL,
  last_poster_name varchar(32) default NULL,
  poll_state varchar(8) default NULL,
  last_vote int(10) default NULL,
  views int(10) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  approved tinyint(1) default NULL,
  author_mode tinyint(1) default NULL,
  pinned tinyint(1) default NULL,
  moved_to varchar(64) default NULL,
  rating text,
  total_votes int(5) NOT NULL default '0',
  firstpost tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY last_post (last_post),
  KEY forum_id (forum_id,approved,pinned),FULLTEXT KEY title (title)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_tracker (
  trid mediumint(8) NOT NULL auto_increment,
  member_id mediumint(8) NOT NULL default '0',
  topic_id bigint(20) NOT NULL default '0',
  start_date int(10) default NULL,
  last_sent int(10) NOT NULL default '0',
  PRIMARY KEY  (trid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_validating (
  vid varchar(32) NOT NULL default '',
  member_id mediumint(8) NOT NULL default '0',
  real_group smallint(3) NOT NULL default '0',
  temp_group smallint(3) NOT NULL default '0',
  entry_date int(10) NOT NULL default '0',
  coppa_user tinyint(1) NOT NULL default '0',
  lost_pass tinyint(1) NOT NULL default '0',
  new_reg tinyint(1) NOT NULL default '0',
  email_chg tinyint(1) NOT NULL default '0',
  ip_address varchar(16) NOT NULL default '0',
  PRIMARY KEY  (vid),
  KEY new_reg (new_reg)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_voters (
  vid int(10) NOT NULL auto_increment,
  ip_address varchar(16) NOT NULL default '',
  vote_date int(10) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  member_id varchar(32) default NULL,
  forum_id smallint(5) NOT NULL default '0',
  PRIMARY KEY  (vid)
) TYPE=MyISAM;";

$SQL[] = "CREATE TABLE ibf_warn_logs (
  wlog_id int(10) NOT NULL auto_increment,
  wlog_mid mediumint(8) NOT NULL default '0',
  wlog_notes text NOT NULL,
  wlog_contact varchar(250) NOT NULL default 'none',
  wlog_contact_content text NOT NULL,
  wlog_date int(10) NOT NULL default '0',
  wlog_type varchar(6) NOT NULL default 'pos',
  wlog_addedby mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (wlog_id)
) TYPE=MyISAM;";

$SQL[] = "INSERT INTO ibf_categories (id, position, state, name, description, image, url) VALUES (-1, NULL, NULL, '-', NULL, NULL, NULL)";
$SQL[] = "INSERT INTO ibf_categories (id, position, state, name, description, image, url) VALUES (1, 1, '1', 'Тестовая категория', '', '', '')";

$SQL[] = "INSERT INTO ibf_css (cssid, css_name, css_text, css_comments, updated) VALUES (1, 'IPB Default CSS', '".get_main_css()."', NULL, ".time()." )";

$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (1, ':mellow:', 'mellow.gif', 0)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (2, ':huh:', 'huh.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (3, '^_^', 'happy.gif', 0)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (4, ':o', 'ohmy.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (5, ';)', 'wink.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (6, ':P', 'tongue.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (7, ':D', 'biggrin.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (8, ':lol:', 'laugh.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (9, 'B)', 'cool.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (10, ':rolleyes:', 'rolleyes.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (11, '-_-', 'sleep.gif', 0)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (12, '&lt;_&lt;', 'dry.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (13, ':)', 'smile.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (14, ':wub:', 'wub.gif', 0)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (15, ':angry:', 'mad.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (16, ':(', 'sad.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (17, ':unsure:', 'unsure.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (18, ':wacko:', 'wacko.gif', 0)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (19, ':blink:', 'blink.gif', 1)";
$SQL[] = "INSERT INTO ibf_emoticons (id, typed, image, clickable) VALUES (20, ':ph34r:', 'ph34r.gif', 1)";

$SQL[] = "INSERT INTO ibf_faq VALUES (1, 'Преимущества регистрации', 'Для полноценного использования всех функций форума, Администратор требует зарегистрироваться на форуме. Регистрация на форуме абсолютно бесплатна и отнимет у Вас всего лишь пару минут.\r<br>\r<br>При регистрации необходимо обязательно указать Ваш реальный e-mail address. Администратор может потребовать от Вас подтверждения Вашей регистрации по указанному Вами email адресу. Если данное требование администратора включено, Вы будете извещены об этом во время регистрации. Если Вы по какой-то причине не получили письма для активации, зайдите на форум и в правой верхней части форума, воспользуйтесь ссылкой \'Выслать повторно письмо для активации\'.\r<br>\r<br>Также возможно, что администратор сам должен будет одобрить Вашу регистрацию. Об этом Вы также будете извещены во время регистрации.\r<br>\r<br>После завершения регистрации и успешной авторизации, Вы получите доступ к Вашей панели управления и к личному ящику для форума, сокращённое название которого - PM.\r<br>\r<br>Более подробные сведения обо всех функциях и характеристиках форума, Вы сможете узнать в секции помощи по форуму', 'Как зарегистрироваться и дополнительные преимущества зарегистрированных пользователей.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (2, ' Администратору форума: поддержка этого скрипта', 'Уважаемые администраторы форума, будем рады ответить на Ваши вопросы на неофициальном сайте поддержки ipb 1.3: <a href=http://www.sysman.ru/index.php?showforum=209 target=_blank><b>по этой ссылке</b></a> (все, даже самые сложные вопросы) и <a href=http://forum.aceweb.ru/ target=_blank><b>здесь</b></a> (легкие вопросы).', 'Данная версия скрипта официально не поддерживается, в этом разделе представлены ссылки, к кому можно обратиться в случае возникновения вопросов');";
$SQL[] = "INSERT INTO ibf_faq VALUES (3, 'Восстановление утерянного или забытого пароля', 'Главной характеристикой форума, является безопасность и поэтому, все пароли пользователей кодируются при регистрации.\r<br>Это означает, что мы не в состоянии выслать Вам, Ваш забытый пароль, т.к. \'раскодировка\' пароля невозможна. Но, тем не менее, Вы можете восстановить Ваш свой пароль.\r<br>\r<br>Для восстановления пароля, зайдите по ссылке <a href=\'index.php?act=Reg&CODE=10\'>Восстановление забытого пароля</a>, которая находится на странице авторизации.\r<br>\r<br>С дальнейшей инструкцией по восстановлению, Вы можете ознакмиться уже там.', 'Инструкция по восстановлению забытого пароля.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (4, 'Ваша панель управления (Мой профиль)', 'Панель управления форума, является частной собственностью каждого пользователя форума. Здесь Вы можете изменить и настроить различные функции форума.\r<br>\r<br><b>Подписки</b>\r<br>\r<br>На этой странице, Вы можете настраивать Ваши подписки на темы и форумы. Для более подробного ознакомления, с инструкциями по подписке на темы, читайте раздел помощи \'Уведомление по e-mail о новых сообщениях\'.\r<br>\r<br><b>Редактирование профиля</b>\r<br>\r<br>В этой секции Вы можете добавить или отредактировать различную дополнительную информацию о Вас.\r<br>\r<br><b>Редактирование подписи</b>\r<br>\r<br>Использование \'подписи\' на форуме, аналогично тому, как Вы используете подпись в Ваших письмах, отправляемых Вами с Вашего e-mail адреса. Ваша подпись будет добавлена к концу каждого Вашего сообщения на форуме, но в некоторых сообщениях, Вы можете отменить добавление подписи, просто сняв галочку в поле добавления подписи под формой отправки сообщения. Вы даже можете использовать в подписи коды форума, а в некоторых случаях даже и чистый HTML (если только администратор разрешил данную функцию).\r<br>\r<br><b>Настройки аватара</b>\r<br>\r<br>Аватар - это небольшая картинка, которая будет отображена под Вашим именем, в каждом Вашем сообщении на форуме. В зависимости от установок форума, настроенных администратором, Вы можете выбрать аватар в галерее аватаров форума, либо ввести ссылку на аватар, находящийся на другом сайте/сервере, либо загрузить личный аватар с Вашего компьютера. Вы также можете установить длину и ширину аватара, если Ваш аватар отображается не так, как бы Вам хотелось.\r<br>\r<br><b>Изменение личной фотографии</b>\r<br>\r<br>В этой секции, Вы можете добавить фотографию в Ваш профиль. Ваша фотография, будет доступна при просмотре Вашего профиля, пользователями, настранице мини профиля, а также, в списке пользователей форума, будет присутствовать ссылка на Вашу фотографию.\r<br>\r<br><b>Настройки e-mail</b>\r<br>\r<br>При установке галочки в поле <u>Скрыть мой e-mail адрес</u>, пользователи форума не смогут отправлять Вам из форума письма на Ваш e-mail адрес.\r<br><u>Сообщать мне обо всех изменениях, проводимых администратором форума</u> Эта функция для добавления Вашего e-mail адреса в список рассылок. Каждый раз, при каких-либо важных изменениях, дополнениях на форуме, Администратор форума будет сразу извещать Вас об этом.\r<br><u>Добавлять копию сообщения из подписанных тем в отправляемое письмо-уведомление</u> При включении этого, текст нового сообщения из подписанной темы будет отправлен Вам на e-mail.\r<br><u>Уведомлять меня по e-mail при получении новых личных писем</u>Добавлять копию сообщения из подписанных тем в отправляемое письмо-уведомление</u> При включении этого, Вы будете уведомлены по e-mail о каждом отправленном Вам на Личый ящик форума, приватном письме.\r<br><u>Включить \'E-mail Уведомления\' по умолчанию?</u> При включении этого, Вам будут отправлять уведомления о всех новых темах и сообщениях на форуме. Вы можете отписаться от всего, зайдя по ссылке \'Подписки\' в Вашей панели управления.\r<br>\r<br><b>Настройки форума</b>\r<br>\r<br>В этой секции Вы можете настроить Ваш часовой пояс, запретить отображение подписи пользователей, отображение аватаров и изображений в сообщениях.\r<br>Здесь же Вы можете выбрать, чтобы при получении новых писем на личный ящик (PM) форума, у Вас выскакивало новое окно-оповещение, а также выбрать отображение или скрытие формы \'Быстрого ответа\', если она включена администратором.\r<br>Вы также можете сами установить кол-во отображаемых тем/сообщений, за страницу форума, темы соответственно.\r<br>\r<br><b>Скины и Языки</b>\r<br>\r<br>Здесь Вы можете выбрать другой стиль и язык интерфейса форума. Перед изменением стиля, Вы можете предварительно просмотреть вид этого стиля.\r<br>\r<br><b>Изменить e-mail адрес</b>\r<br>\r<br>Здесь Вы можете в любое время изменить Ваш e-mail адрес, указанный Вами при регистрации на форуме. В некоторых случаях, для изменения e-mail адреса, от Вас могут потребовать подтверждение изменения и перактивацию Вашего аккаунта. Если данное требование включено, то перед изменением Вы будете заранее извещены об этом.\r<br>\r<br><b>Изменить пароль</b>\r<br>\r<br>В этой секции Вы можете изменить Ваш пароль. Но учтите, что перед изменением Вашего пароля на новый, Вы должны будете ввести Ваш старый пароль.\r<br>\r<br><a href=http://www.script-php.ru/script_foruma/>Скачать этот скрипт форума</a>.', 'Редактирование контактной и персональной информации, аватаров, подписи, настроек форума, а также выбор стиля и языка интерфейса форума.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (5, 'Уведомление на e-mail о новых сообщениях', 'На форуме существует возможность уведомлять Вас о новых сообщениях в темах. Многие пользователи с успехом используют эту функцию для своевременного извещения о том, что в теме имеются новые сообщения.\r<br>\r<br>Существует 3 способа подписки на тему:\r<br>\r<br><li>Нажатием на ссылку \'Слежение за этой темой\', находящуюся в верхней части каждой темы.\r<br><li> С помощью установки галочки в окошко \'Уведомлять по e-mail о новых сообщениях?\', находящееся под формой ответа.\r<br><li> Через пункт настройки e-mail в Вашей панели управления (Мой профиль), установив галочку на опции \'Включить E-mail Уведомление по умолчанию?\', после чего Вы будете автоматически подписываться на все темы, в которые Вы ответите.\r<br>\r<br>Но имейте ввиду, что, во избежание повтора писем, отправляемых на Ваш e-mail адрес, Вы будете получать только одно письмо для каждой подписанной темы, независимо от кол-ва новых ответов в эту тему, до тех пор, пока не посетите данную тему.\r<br>\r<br>Вы также можете подписаться на любой из разделов форума и при появлении новой темы в этом разделе, Вы будете уведомлены об этом. Для подписки на раздел, нажмите на ссылку \'Подписка на этот форум\', находящуюся в нижней части каждого форума.\r<br>\r<br>Для отмены подписки на любой из разделов или тем, на которые Вы подписаны, зайдите в \'Мой профиль\' и в меню \'Мои подписки\', Вы можете отписаться от всего, что угодно.', 'Как подписаться на тему для уведомления по e-mail о новых ответах.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (6, 'Ваш личный ящик (PM)', 'Ваш личный ящик на форуме, действует аналогично e-mail ящику. Здесь Вы можете получать и отправлять письма пользователям форума, хранить Ваши письма в папках, создавать, редактировать и удалять папки.\r<br>\r<br><b>Отправка нового письма</b>\r<br>\r<br>Отсюда Вы можете отправить письмо пользователю форума. Если в Вашем списке контактов имеются имена пользователей, Вы можете выбрать имя оттуда или ввести имя получателя вручную. Если Вы нажмёте в каком-либо сообщении в темах или в списке пользователей на кнопку \'PM\' то имя пользователя будет автоматически добавлено в графу получателя Вашего письма. Если Администратор форума разрешил отправлять письма нескольким пользователям одновременно, Вы можете сделать ввести имена сразу нескольких пользователей, - по одному имени на строку, в окошке Копии пользователям.\r<br>В некоторых случаях, если это разрешено Администратором, Вы даже можете использовать коды форума и HTML в тексте Ваших писем. Вы также можете выбрать пункт \'Добавить копию этого письма в папку Исходящие\' для сохранения копии письма в папке отправленных писем. При установке галочки в окошке \'Проследить за этим письмом?\', Вы сможете прослеживать через пункт меню \'Слежение за письмами\' состояние того, что прочитано ли Ваше письмо адресатом.\r<br>\r<br><b>Переход в папку Входящие</b>\r<br>\r<br>Вы попадёте в папку Входящих писем, на которую поступают все отправленные Вам письма. Нажав на заголовок письма, Вы сможете прочесть пришедшее Вам письмо. После прочтения, Вы можете удалять или перемещать письма в другие папки.\r<br>\r<br><b>Очистка папок РМ</b>\r<br>\r<br>Через эту опцию, Вы можете быстро удалить все письма из любых Ваших папок.\r<br>\r<br><b>Редактировать папки</b>\r<br>\r<br>Здесь Вы можете переименовывать, удалять или создавать новые папки для сортировки Ваших писем так, как Вам будет более удобно. Вы не можете удалять только 2 папки, созданные самим форумом это папка \'Исходящие\' и \'Входящие\'.\r<br>\r<br><b>Список Друзей/Врагов</b>\r<br>\r<br>Вы можете добавить в Ваш контактный лист имена Ваших друзей, а также внести в чёрный список имена Ваших недругов, от которых Вы не желаете получать письма.\r<br>При отправке писем, Вы будете способны быстро выбрать имя получателя в выпадающем списке, если Вы до этого ввели его имя в Ваш списко друзей\r<br>\r<br><b>Архивация писем</b>\r<br>\r<br>Если Ваш Личный Ящик переполнен и в связи с этим Вы не в состоянии получать новые письма, то Вы сможете произвести архивацию писем. Все Ваши письма будут скомпилированы на одной странице в формате HTML или Microsoft © Excel, а затем высланы на Ваш e-mail адрес, указанный Вами при регистрации на форуме.\r<br>\r<br><b>Неотправленные письма</b>\r<br>\r<br>Здесь Вы можете вернуться к любому письму, которое Вы решили сохранить, для отправки его позже.\r<br>\r<br><b>Слежение за письмами</B>\r<br>\r<br>На этой странице, появляются все письма, за которыми Вы решили проследить. Все данные о том, прочитаны ли Ваши письма получателями и когда они прочитаны, - появляются здесь. Также, если Вы передумали, и если письмо пока не прочитано получателем, Вы можете удалить это письмо, .', 'Отправка личных сообщений, редактирование папок Личного Ящика, слежение за письмами, архивация сохранённых сообщений.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (7, 'Cookies и их использование', 'Использование cookies не является обязательным, но очено рекомендуется. Cookies необходимы для того, чтобы прослеживать за темами, подписываться на них, узнавать каждый раз о новых темах и сообщениях, их количестве и для автоматической авторизации на форуме при каждом посещении форума.\r<br>\r<br>Если в Вашем компьютере нет возможности использования системы cookie, для корректного использования форума, то, форум автоматически будет добавлять ко всем ссылкам ID Вашего сеанса посещения, для удобства использования форума\r<br>\r<br><b>Очистка Cookies</b>\r<br>\r<br>Вы можете удалять все Ваши cookies, если нажмёте на ссылку, находящуюся в нижней части главной страницы форума (это самая первая страница форума). Если у Вас это не сработает, то возможно Вам необходимо проделать это вручную.\r<br>\r<br><u>Удаление Cookies для пользователей Internet Explorer для Windows</u>\r<br>\r<br><ul>\r<br><li> Закройте все окна Internet Explorer\r<br><li> Нажмите на кнопку \'Пуск\r<br><li> Перейдите в меню \'Найти\' и выберите \'Файлы и папки\'\r<br><li> После открытия проводника, введите название домена форума в поле \'Искать текст\'. (Например, если адрес форума \'http://www.invisionboard.com/forums/index.php\' то Вы должны ввести \'invisionboard.com\' без кавычек)\r<br><li> В поле \'где искать\' выберите <b>C:WindowsCookies</b> и нажмите кнопку \'Найти\'\r<br><li> После завершения поиска, выделите все найденные файлы (для выделения всех файлов, можете использовать комбинацию клавиш CTRL+A) и удалите их.\r<br></ul>\r<br>\r<br><u>Удаление Cookies для пользователей Internet Explorer для Macintosh</u>\r<br>\r<br><ul>\r<br><li> Откройте окно Internet Explorer, зайдите в меню \'Правка\' и выберите \'Настройки\'\r<br><li> После открытия панели настроек, зайдите в меню \'Cookies\' в секции \'Принятые файлы\'.\r<br><li> После полной загрузки панели cookie, найдите там доменное имя форума (Если адрес форума \'http://www.invisionboard.com/forums/index.php\' ищите \'invisionboard.com\' или \'www.invisionboard.com\'\r<br><li> Войдите в каждый cookie и нажмите кнопку удаления.\r<br></ul>\r<br>\r<br>Теперь все Ваши cookies удалены. В некоторых случаях, для вступления изменений в силу, необходимо перезагрузить компьютер.', 'Преимущества использования cookies и удаление cookies , установленных форумом.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (8, 'Просмотр информации профиля пользователей', 'Для просмотра данных профиля любого пользователя, нажмите на имя этого пользователя, если имя подчёркнуто (в виде ссылки), либо нажмите на ссылку \'Профиль\', находящуюся под именем каждого пользователя, в темах.\r<br>\r<br>После нажатия, Вы попадёте на страницу профиля пользователя, в которой Вы увидите всю контактную информацию, все данные (если он ввёл их), а также \'Статистику активности на форуме\' данного пользователя.\r<br>\r<br>Вы также можете нажать на кнопку \'Мини профиль\', находящуюся в нижней части сообщения любого из пользователей и, тем самым Вы попадёте на страницу \'Мини профиля\', где также можете увидеть контактную информацию пользователя и, если он загрузил фотографию на форум, - его фотографию.', 'Где можно найти контактную информацию пользователя.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (9, 'Просмотр активных тем и новых сообщений', 'Вы можете быстро просмотреть списко сегодняшних активных тем (это темы, имеющие новые ответы), нажав на ссылку \'Активные темы\', находящуюся в нижней части Главной страницы форума. Там же, Вы можете установить свои критерии поиска по дате, для поиска активных тем, обновлённых в течении нескольких дней.\r<br>\r<br>Ссылка \'Новые сообщения\', находящаяся в верхней части каждой страницы форума, покажет Вам все новые сообщения, с момента Вашего последнего посещения форума.', 'Где можно просмотреть список сегодняшних активных тем и новые сообщения, появившиеся с момента Вашего последнего посещения форума.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (10, 'Поиск тем и сообщений', 'Функция поиска, предназначена для быстрого поиска тем и сообщений, по введённым Вами ключевым словам.\r<br>\r<br>Имеются 2 доступных типа поиска, простой поиск и расширенный поиск. Вы можете переключаться между ними, при помощи кнопок \'Расшиерный поиск\' и \'Простой поиск\'.\r<br>\r<br><b>Простой поиск</b>\r<br>\r<br>Здесь Вы должны ввести ключевое слово для поиска и выбрать форум(ы), в которых хотите найти что-то по этому слову, (для выбора нескольких форумов одновременно, необходимо кликать по их названиям, удерживая кнопку Ctrl, а пользователи Mac, должны удерживать кнопки Shift/Apple), затем выбрать порядок сортировки найденного и искать.\r<br>\r<br><b>Расширенный поиск</b>\r<br>\r<br>На странице расширенного поиска, у Вас  появится возможность использования дополнительных критериев поиска, значительно улучшающих функцию поиска. Дополнительно к поиску по ключевому слову, Вы сможете произвести поиск по имени пользователя, а также возможно использование сразу обоих кобинаций. Можно увеличить критерии поиска, посредством выбора диапазона дат, а также выбрать порядок сортировки найденного. Также имеется 2 вида отображения результатов поиска - в виде отображения полного текста сообщения и в виде ссылки на тему. Вид олтображения, Вы можете заранее указать, на странице поиска.\r<br>\r<br>Администратор может включить минимальное время, известное под названием - Флуд-контроль, которое Вы должны будете прождать, для повторного поиска.\r<br>\r<br>На форуме имеется также поисковая формочка, известная под названием - Фильтр поиска, находящаяся в нижней части каждого раздела, через которую Вы сможете произвести быстрый поиск в конкртеном разделе форума.', 'Как воспользоваться функцией поиска.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (11, 'Авторизация и Выход из форума', 'Во время авторизации на форуме, Вы можете установить функцию запонимания Ваших данных через cookies, а если Вы зашли на форум с чужого компьютера, Вам надо будет авторизоваться снова.\r<br>\r<br>Но не используйте функцию запоминания cookies, если заходите с чужого компьютера.\r<br>\r<br>Вы можете также выбрать функцию скрытия, поставив галочку в окошке Скрытность. И тогда Ваше имя не будет отображаться в списке Кто в онлайне.\r<br>\r<br>Выход из форума, производится простым нажатием на ссылку Выход. Если вдруг Вы обнаружите, что выход не сработал, возможно Вы должны будете удалить Ваши cookies. Читайте раздел помощи про \'Cookies\', для дополнительной информации.', 'Как авторизоваться, выйти из форума, а также как скрыть своё имя из списка пользователей, находящихся на форуме.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (13, 'Мой помощник', 'В верхней части форума, находится ссылка \'Мой помощник\', нажав ан которую, у Вас откроется страница, через которую Вы сможете узнать, сколько имеется новых сообщений с момента Вашего последнего посещения форума. На следующе строке, Вы увидите информацию о том, сколько из этих сообщений, являются ответами в тему, созданную Вами.\r<br>Нажатием на ссылку \'Просмотр\', в любой из этих двух строк, Вы сможете просмотреть эти сообщения.\r<br>\r<br>Следующая секция на этой странице - это пять полезных ссылок со следующими функциями:\r<br>\r<br><li>Ссылка Администрация, покажет Вам список Администраторов и модераторов форума.\r<br><li> Ссылка \'Активные темы\', покажет Вам списко тем, созданных за последние 24 часа.\r<br><li>Ссылка \'10 авторов сегодня\', покажет Вам, имена Активных пользователей за последний день и кол-во их сообщений за этот день, а также процент их сообщений за сегодня и общее кол-во их сообщений на форуме.\r<br><li>Ссылка \'Лучшие 10 авторов\', покажет Вам имена лучшей десятки пользователей, имеющих самое рекордное кол-во сообщений на форуме, со дня открытия форума.\r<br><li>Ссылка \'10 моих посл. сообщений\', покажет Вам 10 Ваших последних сообщений, с обрезанным текстом самих сообщений. Если Вы захотите увидеть полный текст какого-то из этих 10 сообщений, Вы можете нажать на ссылку, ведущую в тему, в которой находится данное сообщение.\r<br>\r<br>И псоледняя секция на этой странице, это секция поиска по форуму, через которое Вы сможете найти конкретное сообщение, имеющее ключевое слово, введённое Вами.\r<br>\r<br>Следующее окошко поиска, найдёт Вам конкретный раздел помощи по форуму, посредством ввода интересующего Вас ключевого слова.', 'Полный справочник по использованию этой маленькой, но удобной функции.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (12, 'Сообщения', 'На форуме доступны 3 разные формы создания сообщений. Кнопка \'Новая тема\', находящаяся в каждом разделе форума и на странице тем, предназначена для создания новой темы. Кнопка \'Новый опрос\', (если опросы допущены Администратором), также видимая в каждом разделе форума и на странице тем, предназначена для создания новой темы-голосования. При просмотре темы, Вы увидите кнопку \'Ответить\', которая предназначена для добавления Вашего сообщения в данную тему. \r\n<br>\r\n<br><b>Создание новой темы и ответ в тему</b>\r\n<br>\r\n<br>При написании сообщений, Вас вероятно заинтересуют Коды форума. Данные коды, предназначены для различного форматирования текстов Ваших сообщений. Для более подробной информации по использованию кодов форума, воспользуйтесь кнопкой \'Помощь по кодам\', находящася рядом с окошком для написания сообщения.\r\n<br>\r\n<br>С левой стороны от окна для воода текста сообщения, имеется таблица со смайликами. Для добавления любого смайлика к тексту Вашего сообщения, нажмите на этот смайлик и его код будет автоматически добавлен в нужное место текста.\r\n<br>\r\n<br>Имеются 3 опции сообщения, доступные при создании темы или ответе в тему. При отключении опции \'Включить смайлики?\', коды смайликов не будут преобразованы в изображения. Опция \'Включить подпись?\', предназначена для добавления текста Вашей подписи к каждому сообщению на форуме. При установке галочки в окошке опции \'Включить уведомление на e-mail при ответах?\', на Ваш e-mail адрес будет поступать информация, каждый раз, при новых ответах в данныую тему. Для более подробной информации по этой функции, читайте раздел помощи \'Уведомление на e-mail о новых сообщениях\'.\r\n<br>\r\n<br>Также для Вас доступен выбор иконки сообщения, при создании тем или ответах в темы. Выбранная Вами иконка будет отображаться слева от названия Вашей темы, в списке тем форума, а если выбрать иконку при ответе в тему, Ваша иконка будет отображаться над Вашим сообщением, слева от даты написания сообщения.\r\n<br>\r\n<br>Если Администратор форума включил функцию присоединений, Вы увидите формочку для присоединения файла к Вашему сообщению. Для присоединения файла, нажмите на кнопку Обзор и выберите файл на Вашем компьютере, для загрузки этот файла. Если Вы загружаете файл изображения, данное изображения будет отображено под текстом Вашего сообщения, а при загрузке всех других типов файлов, под текстом Вашего сообщения, будет прописана ссылка для скачивания этого файла.\r\n<br>\r\n<br><b>Установки опроса</b>\r\n<br>\r\n<br>При создании нового опроса, под полями для ввода названия и описания темы, Вы увидите 2 дополнительных поля. Первое поле, служит для ввода в нём вопроса Вашего голосования. Второе, текстовое поле, служит для ввода пунктов опроса. Необходимо вводить по одному пункту на строку. Максмимально допустимое кол-во пунктов, отображается слева от окна ввода пунктов опроса.\r\n<br>\r\n<br><b>Цитирование сообщений</b>\r\n<br>\r\n<br>Над каждым сообщением в теме, имеется кнопка \'Цитата\'. При нажатии на эту кнопку, Вы сможете ответить в тему, с цитированием того сообщения, над которым нажали данную кнопку. Таким образом, при нажатии на эту кнопку, Вы попадёте на страницу написания ответа в тему, где под формой ответа, Вы увидите дополнительное текстовое окно, с текстом процитированного сообщения, которое Вы можете отредактировать на Ваше усмотрение.\r\n<br>\r\n<br><b>Редактирование сообщений</b>\r\n<br>\r\n<br>Над каждым сообщением, написанным Вами, Вы увидите кнопку \'Редактирование\'. При нажатии на эту кнопку, Вы сможете отредактировать текст Вашего сообщения, независимо от того, когда Вы это сообщение писали. \r\n<br>\r\n<br>При редактировании своего сообщения, Вы можете увидеть опцию \'Добавить надпись Отредактировано в это сообщение?\'. При установке галочки в этой опции, под Вашим отредактированным сообщением появится надпись, информирующая о том, что Вы редактировали это сообщение, с датой редактирования. Если Вы не видите эту опцию, значит данная информаиця о редактировании будет добавлена автоматически.\r\n<br>\r\n<br>Если Вы не видите кнопку для редактирования Вашего сообщения, то возможно, что Администратор форума отключил данную функцию, либо установил лимит времени, по истечении которого, редактирование сообщения уже невозможно.\r\n<br>\r\n<br>Поддержка проекта: <a href=http://www.grigus.ru/>Григус</a>', 'Руководство по функциям, при написании сообщений.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (14, 'Список участников', 'Список участников, доступный через ссылку \'Участники\', находящуюся в верхней части каждой страницы форума, - это полный списко всех пользователей, зарегистрированных на форуме.\r\n<br>\r\n<br>Для поиска конкретного пользователя по части или по полному имени, в выпадающем меню, находящимся в нижней части списка пользователей, измените пункт \'Найти всех доступных\' на \'Имя начинается с\' или \'Имя содержит\', затем введите часть имени или полное имя в текстовое поле и нажмите на кнопку \'ОК!\'. \r\n<br>\r\n<br>Также доступны несколько опций сортировки найденного, которые Вы можете использовать, как Вам будет удобно. \r\n<br>\r\n<br>В последней колонке списка пользователей, напротив имён пользователей, которые загрузили фотографию в свой профиль, будет отображаться изображение в виде фотоаппарата\r\n<br>\r\n<br>Будем рады, если Вы найдете <a href=http://www.statusy.su/>статусы для форума</a>', 'Просмотр и поиск пользователей через список участников и возможность сортировки найденного.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (15, 'Опции темы', 'В нижней части каждой темы, имеется кнопка \'Опции темы\'. При нажатии на эту кнопку, раскроется меню с опциями темы. \r\n<br>\r\n<br>В этом меню, Вы можете использовать следующие опции: \r\n<br>\r\n<br><li>Подписаться на тему - эта опция служит для получения уведомлений на e-mail, об обновлении темы. Для более подробной информации по этой части, читайте раздел помощи \'Уведомление на e-mail о новых сообщениях\'. \r\n<br><li>Подписка на этот форум - эта опция служит для получения уведомлений на e-mail, о любых новых темах, созданных в этом форуме. Для более подробной информации по этой части, читайте раздел помощи \'Уведомление на e-mail о новых сообщениях\'. \r\n<br><li>Скачать/Распечатать тему - эта опция отобразит данную тему в других различных форматах. \'Версия для печати\' отобразит тему в том виде, в котором она будет распечатана на принтере. \'Скачать HTML версию\', позволит Вам скачать данную тему на Ваш жёсткий диск, для дальнейшего просмотра и чтения содержимого этой темы, без необходимости входа в интернет. \'Скачать версию в формате Microsoft Word\', позволит Вам скачать данную тему на Ваш жёсткий диск и затем открыть её в популярном, известном приложении Microsoft Word, для просмотра и чтения содержимого этой темы, без необходимости входа в интернет.', 'Руководство по доступным опциям, при просмотре тем.');";
$SQL[] = "INSERT INTO ibf_faq VALUES (16, 'Календарь', 'На форуме имеется функция календаря, ссылка на который находится в верхней части форума.\r\n<br>\r\n<br>Вы можете добавлять персональные события в календарь, которые будут видны только Вам. Для добавления нового события, используйте кнопку \'Добавить событие\'. Имеется возможность создания трёх видов событий:\r\n<br>\r\n<br><li>Однодневное событие - для добавления этого вида, используйте первое поле, указав в нём дату события.\r\n<br><li>Многодневное событие - это вид события, предусмотренный на несколько дней, для добавления этого вида, используйте второе поле, указав в нём дату начала и дату окончания события. Здесь же присутствуют две дополнительные опции, для выбора цвета фона и цвета текста отображаемого события.\r\n<br><li>Чередующееся событие - этот вид события будет начат с момента его добавления и Вы можете в нём указать еженедельный, ежемесячный или ежегодный интервал чередования. Для добавления этого вида, используйте третье поле.\r\n<br>\r\n<br>Если это допущено Администратором форума, Вы также можете добавлять общественное событие, которое будет видно не только Вам, а всем пользователям форума.\r\n<br>\r\n<br>Также, Вы можете указать дату Вашего рождения через страницу Вашего профиля и, если Администратор включил эту возможность, то дата Вашего рождения появится в виде ссылки, в календаре, в день Вашего рождения.', 'Информация по использованию функции календаря форума.');";

$SQL[] = "INSERT INTO ibf_forums (id, topics, posts, last_post, last_poster_id, last_poster_name, name, description, position, use_ibc, use_html, status, start_perms, reply_perms, read_perms, password, category, last_title, last_id, sort_key, sort_order, prune, show_rules, upload_perms, preview_posts, allow_poll, allow_pollbump, inc_postcount, skin_id, parent_id, subwrap, sub_can_post) VALUES (1, 1, 1, <%time%>, 1, 'Команда Invision Power Board', 'Тестовый форум', 'Тестовый форум можно удалить в любое время', 1, 1, 0, '1', '*', '*', '*', '', 1, 'Добро пожаловать', 1, 'last_post', 'Z-A', 30, 0, '', 0, 0, 1, 1, NULL, -1, 0, 1)";

$SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_post_closed, g_perm_id) VALUES (4, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Администраторы', 0, 1, 1, 1, 1, '', 50000, 1, 1, '<span style=\'color:red\'>', '</span>', 50, 6, 0, 5, '-1&-1', 0, 1, 4)";
$SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_perm_id) VALUES (2, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, 'Гости', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 0, 2)";
$SQL[] = "INSERT INTO ibf_macro VALUES (67, 'GZIP_CHECK', 'RFFvZ0lDQWdJQ0FnSUNScFltWnZjblZ0Y3kwK2MydHBibHQwWlcxd2JHRjBaVjBnTGowZ0lqd2hMUzBnUTI5d2VYSnBaMmgwSUVsdVptOXliV0YwYVc5dUlDMHRQbHh1WEc0OFluSStQR1JwZGlCaGJHbG5iajBuWTJWdWRHVnlKeUJqYkdGemN6MG5ZMjl3ZVhKcFoyaDBKejVRYjNkbGNtVmtJR0o1SUR4aElHaHlaV1k5WENKb2RIUndPaTh2ZDNkM0xtbHVkbWx6YVc5dVltOWhjbVF1WTI5dFhDSWdkR0Z5WjJWMFBTZGZZbXhoYm1zblBrbHVkbWx6YVc5dUlGQnZkMlZ5SUVKdllYSmtQQzloUGloVktTQjJNUzR6SUVacGJtRnNJQ1pqYjNCNU95QXlNREF6SUNadVluTndPenhoSUdoeVpXWTlKMmgwZEhBNkx5OTNkM2N1YVc1MmFYTnBiMjV3YjNkbGNpNWpiMjBuSUhSaGNtZGxkRDBuWDJKc1lXNXJKejVKVUZNc0lFbHVZeTQ4TDJFK1BHSnlQdER6OGZIcTZPa2d6TzdrNlBUbzl1anc3dUxnN2UzNzZTQkpVRUlnZGpFdU15QkdhVzVoYkNBbVkyOXdlU0F5TURBeklEeGhJR2h5WldZOUoyaDBkSEE2THk5M2QzY3VZbVZ6ZEdacGJHVjZMbTVsZENjZ2RHRnlaMlYwUFNkZllteGhibXNuUGtKbGMxUkdhV3hsV2k1T1pYUThMMkUrSUNaaGJYQTdJRHhoSUdoeVpXWTlKMmgwZEhBNkx5OTNkM2N1YVdKeVpYTnZkWEpqWlM1eWRTY2dkR0Z5WjJWMFBTZGZZbXhoYm1zblBrbENVaUJVWldGdFBDOWhQand2WkdsMlBqeGljajRpT3c9PQ', 0, 0)";
$SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_perm_id) VALUES (3, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 0, 0, 'Пользователи', 0, 1, 0, 0, 0, '', 0, 1, 0, '<span style=\'color:green\'>', '</span>', 50, 0, 20, 0, '-1&-1', 0, 3)";
$SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_perm_id) VALUES (1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Ожидающие', 0, 1, 0, 0, 0, NULL, 0, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 0, 1)";
$SQL[] = "INSERT INTO ibf_groups (g_id, g_view_board, g_mem_info, g_other_topics, g_use_search, g_email_friend, g_invite_friend, g_edit_profile, g_post_new_topics, g_reply_own_topics, g_reply_other_topics, g_edit_posts, g_delete_own_posts, g_open_close_posts, g_delete_own_topics, g_post_polls, g_vote_polls, g_use_pm, g_is_supmod, g_access_cp, g_title, g_can_remove, g_append_edit, g_access_offline, g_avoid_q, g_avoid_flood, g_icon, g_attach_max, g_avatar_upload, g_calendar_post, prefix, suffix, g_max_messages, g_max_mass_pm, g_search_flood, g_edit_cutoff, g_promotion, g_hide_from_list, g_perm_id) VALUES (5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Banned', 0, 0, 0, 0, 0, NULL, NULL, 0, 0, NULL, NULL, 50, 0, 20, 0, '-1&-1', 1, 5)";

$SQL[] = "INSERT INTO ibf_forum_perms SET perm_name='Маска - Ожидающие', perm_id=1";
$SQL[] = "INSERT INTO ibf_forum_perms SET perm_name='Маска - Пользователи', perm_id=3";
$SQL[] = "INSERT INTO ibf_forum_perms SET perm_name='Маска - Гости', perm_id=2";
$SQL[] = "INSERT INTO ibf_forum_perms SET perm_name='Маска - Администраторы', perm_id=4";
$SQL[] = "INSERT INTO ibf_forum_perms SET perm_name='Маска - Banned', perm_id=5";


$SQL[] = "INSERT INTO ibf_languages (lid, ldir, lname, lauthor, lemail) VALUES (1, 'en', 'English', 'Invision Power Board', 'languages@invisionboard.com')";
$SQL[] = "INSERT INTO ibf_languages (lid, ldir, lname, lauthor, lemail) VALUES (2, '2', 'Русский', 'aceweb.ru', 'admin@aceweb.ru')";

$SQL[] = "INSERT INTO ibf_macro VALUES (1, 'A_LOCKED_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'Закрытая тема\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (2, 'A_MOVED_B', '<img src=\'style_images/<#IMG_DIR#>/t_moved.gif\' border=\'0\'  alt=\'Перемещённая тема\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (3, 'A_POLLONLY_B', '<img src=\'style_images/<#IMG_DIR#>/t_closed.gif\' border=\'0\'  alt=\'Только для голосования\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (4, 'A_POST', '<img src=\'style_images/<#IMG_DIR#>/t_new.gif\' border=\'0\'  alt=\'Создание новой темы\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (5, 'A_REPLY', '<img src=\'style_images/<#IMG_DIR#>/t_reply.gif\' border=\'0\'  alt=\'Ответ в тему\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (6, 'A_POLL', '<img src=\'style_images/<#IMG_DIR#>/t_poll.gif\' border=\'0\'  alt=\'Создание опроса\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (7, 'A_STAR', '<img src=\'style_images/<#IMG_DIR#>/pip.gif\' border=\'0\'  alt=\'*\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (8, 'B_HOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot.gif\' border=\'0\'  alt=\'Горячая тема\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (9, 'B_HOT_NN', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no.gif\' border=\'0\'  alt=\'Нет новых\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (10, 'B_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/f_closed.gif\' border=\'0\'  alt=\'Закрыта\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (11, 'B_MOVED', '<img src=\'style_images/<#IMG_DIR#>/f_moved.gif\' border=\'0\'  alt=\'Перемещена\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (12, 'B_NEW', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'Новые сообщения\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (13, 'B_NORM', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'Нет новых сообщений\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (14, 'B_PIN', '<img src=\'style_images/<#IMG_DIR#>/f_pinned.gif\' border=\'0\'  alt=\'Зафиксирована\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (15, 'B_POLL', '<img src=\'style_images/<#IMG_DIR#>/f_poll.gif\' border=\'0\'  alt=\'Опрос\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (16, 'B_POLL_NN', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no.gif\' border=\'0\'  alt=\'Нет новых голосов\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (17, 'B_HOT_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_dot.gif\' border=\'0\' alt=\'Новые сообщения\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (18, 'B_NEW_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_dot.gif\' border=\'0\' alt=\'Нет новых сообщений\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (19, 'B_HOT_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_hot_no_dot.gif\' border=\'0\' alt=\'Нет новых сообщений*\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (20, 'B_NORM_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no_dot.gif\' border=\'0\' alt=\'Нет новых сообщений*\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (21, 'B_POLL_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_dot.gif\' border=\'0\' alt=\'Опрос*\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (22, 'B_POLL_NN_DOT', '<img src=\'style_images/<#IMG_DIR#>/f_poll_no_dot.gif\' border=\'0\' alt=\'Нет новых голосов*\'>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (23, 'C_LOCKED', '<img src=\'style_images/<#IMG_DIR#>/bf_readonly.gif\' border=\'0\'  alt=\'Форум только для чтения\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (24, 'C_OFF', '<img src=\'style_images/<#IMG_DIR#>/bf_nonew.gif\' border=\'0\'  alt=\'Нет новых сообщений\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (25, 'C_OFF_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_nonew.gif\' border=\'0\'  alt=\'Нет новых сообщений\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (26, 'C_OFF_RES', '<img src=\'style_images/<#IMG_DIR#>/br_nonew.gif\' border=\'0\'  alt=\'Нет новых сообщений\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (27, 'C_ON', '<img src=\'style_images/<#IMG_DIR#>/bf_new.gif\' border=\'0\'  alt=\'Новые сообщения\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (28, 'C_ON_CAT', '<img src=\'style_images/<#IMG_DIR#>/bc_new.gif\' border=\'0\'  alt=\'Новые сообщения\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (29, 'C_ON_RES', '<img src=\'style_images/<#IMG_DIR#>/br_new.gif\' border=\'0\'  alt=\'Новые сообщения\'  />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (30, 'F_ACTIVE', '<img src=\'style_images/<#IMG_DIR#>/user.gif\' border=\'0\'  alt=\'Пользователи на форуме\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (31, 'F_NAV_SEP', '&nbsp;-&gt;&nbsp;', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (32, 'F_NAV', '<img src=\'style_images/<#IMG_DIR#>/nav.gif\' border=\'0\'  alt=\'&gt;\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (33, 'F_STATS', '<img src=\'style_images/<#IMG_DIR#>/stats.gif\' border=\'0\'  alt=\'Статистика форума\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (34, 'NO_PHOTO', '<img src=\'style_images/<#IMG_DIR#>/nophoto.gif\' border=\'0\'  alt=\'Фотография не имеется\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (35, 'CAMERA', '<img src=\'style_images/<#IMG_DIR#>/camera.gif\' border=\'0\'  alt=\'Фотография\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (36, 'M_READ', '<img src=\'style_images/<#IMG_DIR#>/f_norm_no.gif\' border=\'0\'  alt=\'Прочитанные письма\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (37, 'M_UNREAD', '<img src=\'style_images/<#IMG_DIR#>/f_norm.gif\' border=\'0\'  alt=\'Непрочитанные письма\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (38, 'P_AOL', '<img src=\'style_images/<#IMG_DIR#>/p_aim.gif\' border=\'0\'  alt=\'AOL\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (39, 'P_DELETE', '<img src=\'style_images/<#IMG_DIR#>/p_delete.gif\' border=\'0\'  alt=\'Удалить сообщение\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (40, 'P_EDIT', '<img src=\'style_images/<#IMG_DIR#>/p_edit.gif\' border=\'0\'  alt=\'Редактировать сообщение\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (41, 'P_EMAIL', '<img src=\'style_images/<#IMG_DIR#>/p_email.gif\' border=\'0\'  alt=\'Письмо на e-mail пользователю\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (42, 'P_ICQ', '<img src=\'style_images/<#IMG_DIR#>/p_icq.gif\' border=\'0\'  alt=\'ICQ\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (43, 'P_MSG', '<img src=\'style_images/<#IMG_DIR#>/p_pm.gif\' border=\'0\'  alt=\'PM\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (44, 'P_QUOTE', '<img src=\'style_images/<#IMG_DIR#>/p_quote.gif\' border=\'0\'  alt=\'Цитировать сообщение\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (45, 'P_WEBSITE', '<img src=\'style_images/<#IMG_DIR#>/p_www.gif\' border=\'0\'  alt=\'Сайт пользователя\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (46, 'P_YIM', '<img src=\'style_images/<#IMG_DIR#>/p_yim.gif\' border=\'0\' alt=\'Yahoo\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (47, 'P_REPORT', '<img src=\'style_images/<#IMG_DIR#>/p_report.gif\' border=\'0\'  alt=\'Сообщить модератору\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (48, 'P_MSN', '<img src=\'style_images/<#IMG_DIR#>/p_msn.gif\' border=\'0\' alt=\'MSN\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (49, 'CAT_IMG', '<img src=\'style_images/<#IMG_DIR#>/nav_m.gif\' border=\'0\'  alt=\'&gt;\' width=\'8\' height=\'8\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (50, 'NEW_POST', '<img src=\'style_images/<#IMG_DIR#>/newpost.gif\' border=\'0\'  alt=\'К последнему непрочитанному\' title=\'К последнему непрочитанному\' hspace=2>', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (51, 'LAST_POST', '<img src=\'style_images/<#IMG_DIR#>/lastpost.gif\' border=\'0\'  alt=\'Последнее сообщение\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (52, 'BR_REDIRECT', '<img src=\'style_images/<#IMG_DIR#>/br_redirect.gif\' border=\'0\'  alt=\'Переадресация\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (53, 'INTEGRITY_MSGR', '<img src=\'style_images/<#IMG_DIR#>/p_im.gif\' border=\'0\'  alt=\'Integrity Messenger IM\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (54, 'ADDRESS_CARD', '<img src=\'style_images/<#IMG_DIR#>/addresscard.gif\' border=\'0\'  alt=\'Мини профиль\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (55, 'T_QREPLY', '<img src=\'style_images/<#IMG_DIR#>/t_qr.gif\' border=\'0\'  alt=\'Быстрый ответ\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (56, 'T_OPTS', '<img src=\'style_images/<#IMG_DIR#>/t_options.gif\' border=\'0\'  alt=\'Опции темы\' />', 1, 1);";
$SQL[] = "INSERT INTO ibf_macro VALUES (57, 'CAL_NEWEVENT', '<img src=\'style_images/<#IMG_DIR#>/cal_newevent.gif\' border=\'0\'  alt=\'Добавление нового события\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (58, 'F_RULES', '<img src=\'style_images/<#IMG_DIR#>/forum_rules.gif\' border=\'0\'  alt=\'Правила форума\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (59, 'WARN_0', '<img src=\'style_images/<#IMG_DIR#>/warn0.gif\' border=\'0\'  alt=\'-----\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (60, 'WARN_1', '<img src=\'style_images/<#IMG_DIR#>/warn1.gif\' border=\'0\'  alt=\'X----\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (61, 'WARN_2', '<img src=\'style_images/<#IMG_DIR#>/warn2.gif\' border=\'0\'  alt=\'XX---\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (62, 'WARN_3', '<img src=\'style_images/<#IMG_DIR#>/warn3.gif\' border=\'0\'  alt=\'XXX--\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (63, 'WARN_4', '<img src=\'style_images/<#IMG_DIR#>/warn4.gif\' border=\'0\'  alt=\'XXXX-\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (64, 'WARN_5', '<img src=\'style_images/<#IMG_DIR#>/warn5.gif\' border=\'0\'  alt=\'XXXXX\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (65, 'WARN_ADD', '<img src=\'style_images/<#IMG_DIR#>/warn_add.gif\' border=\'0\'  alt=\'Повышение рейтинга\' />', 1, 1)";
$SQL[] = "INSERT INTO ibf_macro VALUES (66, 'WARN_MINUS', '<img src=\'style_images/<#IMG_DIR#>/warn_minus.gif\' border=\'0\'  alt=\'Понижение рейтинга\' />', 1, 1)";


$SQL[] = "INSERT INTO ibf_macro_name (set_id, set_name) VALUES (1, 'IPB Default Macro Set')";

$SQL[] = "INSERT INTO ibf_members VALUES (0, 'Guest', 2, '', 'test@localhost.com', 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '0', 1, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', 1052787402, 1052787402, 0, '-1&-1', 0, '0', 0, '0', 0, '', '','0', 0, 0);";

$SQL[] = "INSERT INTO ibf_posts (append_edit, edit_time, pid, author_id, author_name, use_sig, use_emo, ip_address, post_date, icon_id, post, queued, topic_id, forum_id, attach_id, attach_hits, attach_type, attach_file, post_title, new_topic, edit_name) VALUES (0, NULL, 1, 1, 'Invision Power Board Team', '0', '1', '127.0.0.1', <%time%>, 0, 'Добро пожаловать в Ваш форум Invision Power Board!<br>Эта, тема создана автоматически и служит для показа того, что форум успешно установлен.<br>Вы в любое время можете удалить это сообщение, тему, форум или даже всю категорию.', 0, 1, 1, '', 0, '', '', NULL, 1, NULL)";


$SQL[] = "INSERT INTO ibf_skins (uid, sname, sid, set_id, tmpl_id, macro_id, css_id, img_dir, tbl_width, tbl_border, hidden, default_set) VALUES (1, 'Invision Power Board', 0, 1, 1, 1, 1, '1', '95%', '#999999', 0, 1)";

$SQL[] = "INSERT INTO ibf_stats (TOTAL_REPLIES, TOTAL_TOPICS, LAST_MEM_NAME, LAST_MEM_ID, MOST_DATE, MOST_COUNT, MEM_COUNT) VALUES (0, 1, '', '1', <%time%>, 1, 1)";

$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='USD', subcurrency_desc='Доллары США', subcurrency_exchange='1.00', subcurrency_default=1;";
$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='GBP', subcurrency_desc='Фунты Великобритании', subcurrency_exchange=' 0.630776', subcurrency_default=0;";
$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='CAD', subcurrency_desc='Канадские доллары', subcurrency_exchange='1.37080', subcurrency_default=0;";
$SQL[] = "insert into ibf_subscription_currency SET subcurrency_code='EUR', subcurrency_desc='Евро', subcurrency_exchange='0.901517', subcurrency_default=0;";

$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (1, 'PayPal', 'paypal', '', '', '', '', '', '', '', 0, 1, 'Принимаются любые крупные кредитные карты. Посетите <a href=\"https://www.paypal.com/affil/pal=9DJEWQQKVB6WL\" target=\"_blank\">PayPal</a> для более подробной информации.', '', 1, 'USD');";
$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (2, 'NOCHEX', 'nochex', '', '', '', '', '', '', '', 0, 1, 'Кредитные и дебетные карты UK, такие как Switch, Solo и VISA Delta. Все цены будут переконвертированы  при заказе в GBP (Фунты Великобритании).', NULL, 1, 'GBP');";
$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (3, 'Post Service', 'manual', '', '', '', '', '', '', '', 0, 0, 'Вы можете использовать этот метод, для перечисления денег в виде отправки чека, почтового перевода или международного денежного перевода.', NULL, 1, 'USD');";
$SQL[] = "INSERT INTO ibf_subscription_methods VALUES (4, '2CheckOut', '2checkout', '', '', '', '', '', '', '', 1, 1, 'Принимаются любые крупные кредитные карты. Посетите <a href=\'http://www.2checkout.com/cgi-bin/aff.2c?affid=28376\' target=\'_blank\'>2CheckOut</a> для более подробной информации.', NULL, 1, 'USD');";


$SQL[] = "INSERT INTO ibf_templates VALUES (1, '".get_main_wrapper()."', 'Invision Board Standard');";

$SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (1, 0, 'Новичок', '1')";
$SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (2, 50, 'Пользователь', '2')";
$SQL[] = "INSERT INTO ibf_titles (id, posts, title, pips) VALUES (4, 100, 'Старик', '3')";

$SQL[] = "INSERT INTO ibf_tmpl_names (skid, skname, author, email, url) VALUES (1, 'Invision Power Board Template Set', 'Invision Power Board', 'skins@invisionboard.com', 'http://www.invisionboard.com')";

$SQL[] = "INSERT INTO ibf_topics (tid, title, description, state, posts, starter_id, start_date, last_poster_id, last_post, icon_id, starter_name, last_poster_name, poll_state, last_vote, views, forum_id, approved, author_mode, pinned, moved_to, rating, total_votes) VALUES (1, 'Добро пожаловать', '', 'open', 0, '-1', <%time%>, '0', <%time%>, 0, 'Команда Invision Power Board', 'Команда Invision Power Board', '0', 0, 0, 1, 1, 0, 0, NULL, NULL, 0)";

return $SQL;
}


function get_main_css()
{
return "/* FIX IE6 Scrollbars bug - Leave this in! */
/* FIX IE6 Scrollbars bug - Leave this in! */
html { overflow-x: auto; } 

/* Body entry, change forum page background colour, default font, font size, etc. Leave text-align:center to center board content
   #ipwrapper will set text-align back to left for the forum. Any other tables / divs you use must use text-align:left to re-align
   the content properly. This is a work around to a known Internet Explorer bug */
BODY { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; margin:0px;padding:0px;background-color:#FFF; text-align:center }
TABLE, TR, TD { font-family: Verdana, Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }

/* MAIN WRAPPER: Adjust forum width here. Leave margins alone to auto-center content */
#ipbwrapper { text-align:left; width:95%; margin-left:auto;margin-right:auto }

a:link, a:visited, a:active { text-decoration: underline; color: #000 }
a:hover { color: #465584; text-decoration:underline }

fieldset.search { padding:6px; line-height:150% }
label      { cursor:pointer; }
form       { display:inline; }
img        { vertical-align:middle; border:0px }
img.attach { border:2px outset #EEF2F7;padding:2px }

.googleroot  { padding:6px; line-height:130% }
.googlechild { padding:6px; margin-left:30px; line-height:130% }
.googlebottom, .googlebottom a:link, .googlebottom a:visited, .googlebottom a:active { font-size:11px; color: #3A4F6C; }
.googlish, .googlish a:link, .googlish a:visited, .googlish a:active { font-size:14px; font-weight:bold; color:#00D; }
.googlepagelinks { font-size:1.1em; letter-spacing:1px }
.googlesmall, .googlesmall a:link, .googlesmall a:active, .googlesmall a:visited { font-size:10px; color:#434951 }

li.helprow { padding:0px; margin:0px 0px 10px 0px }
ul#help    { padding:0px 0px 0px 15px }

option.cat { font-weight:bold; }
option.sub { font-weight:bold;color:#555 }
.caldate   { text-align:right;font-weight:bold;font-size:11px;color:#777;background-color:#DFE6EF;padding:4px;margin:0px }

.warngood { color:green }
.warnbad  { color:red }

#padandcenter { margin-left:auto;margin-right:auto;text-align:center;padding:14px 0px 14px 0px }

#profilename { font-size:28px; font-weight:bold; }
#calendarname { font-size:22px; font-weight:bold; }

#photowrap { padding:6px; }
#phototitle { font-size:24px; border-bottom:1px solid black }
#photoimg   { text-align:center; margin-top:15px } 

#ucpmenu    { line-height:150%;width:22%; border:1px solid #345487;background-color: #F5F9FD }
#ucpmenu p  { padding:2px 5px 6px 9px;margin:0px; }
#ucpcontent { background-color: #F5F9FD; border:1px solid #345487;line-height:150%; width:auto }
#ucpcontent p  { padding:10px;margin:0px; }

#ipsbanner { position:absolute;top:1px;right:5%; }
#logostrip { border:1px solid #345487;background-color: #3860BB;background-image:url(style_images/<#IMG_DIR#>/tile_back.gif);padding:0px;margin:0px; }
#submenu   { border:1px solid #BCD0ED;background-color: #DFE6EF;font-size:10px;margin:3px 0px 3px 0px;color:#3A4F6C;font-weight:bold;}
#submenu a:link, #submenu  a:visited, #submenu a:active { font-weight:bold;font-size:10px;text-decoration: none; color: #3A4F6C; }
#userlinks { border:1px solid #C2CFDF; background-color: #F0F5FA }

#navstrip  { font-weight:bold;padding:6px 0px 6px 0px; }

.activeuserstrip { background-color:#BCD0ED; padding:6px }

/* Form stuff (post / profile / etc) */
.pformstrip { background-color: #D1DCEB; color:#3A4F6C;font-weight:bold;padding:7px;margin-top:1px }
.pformleft  { background-color: #F5F9FD; padding:6px; margin-top:1px;width:25%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }
.pformleftw { background-color: #F5F9FD; padding:6px; margin-top:1px;width:40%; border-top:1px solid #C2CFDF; border-right:1px solid #C2CFDF; }
.pformright { background-color: #F5F9FD; padding:6px; margin-top:1px;border-top:1px solid #C2CFDF; }

/* Topic View elements */
.signature   { font-size: 10px; color: #339; line-height:150% }
.postdetails { font-size: 10px }
.postcolor   { font-size: 12px; line-height: 160% }

.normalname { font-size: 12px; font-weight: bold; color: #003 }
.normalname a:link, .normalname a:visited, .normalname a:active { font-size: 12px }
.unreg { font-size: 11px; font-weight: bold; color: #900 }

.post1 { background-color: #F5F9FD }
.post2 { background-color: #EEF2F7 }
.postlinksbar { background-color:#D1DCEB;padding:7px;margin-top:1px;font-size:10px; background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif) }

/* Common elements */
.row1 { background-color: #F5F9FD }
.row2 { background-color: #DFE6EF }
.row3 { background-color: #EEF2F7 }
.row4 { background-color: #E4EAF2 }

.darkrow1 { background-color: #C2CFDF; color:#4C77B6; }
.darkrow2 { background-color: #BCD0ED; color:#3A4F6C; }
.darkrow3 { background-color: #D1DCEB; color:#3A4F6C; }

.hlight { background-color: #DFE6EF }
.dlight { background-color: #EEF2F7 }

.titlemedium { font-weight:bold; color:#3A4F6C; padding:7px; margin:0px; background-image: url(style_images/<#IMG_DIR#>/tile_sub.gif) }
.titlemedium  a:link, .titlemedium  a:visited, .titlemedium  a:active  { text-decoration: underline; color: #3A4F6C }

/* Main table top (dark blue gradient by default) */
.maintitle { vertical-align:middle;font-weight:bold; color:#FFF; padding:8px 0px 8px 5px; background-image: url(style_images/<#IMG_DIR#>/tile_back.gif) }
.maintitle a:link, .maintitle  a:visited, .maintitle  a:active { text-decoration: none; color: #FFF }
.maintitle a:hover { text-decoration: underline }

/* tableborders gives the white column / row lines effect */
.plainborder { border:1px solid #345487;background-color:#F5F9FD }
.tableborder { border:1px solid #345487;background-color:#FFF; padding:0; margin:0 }
.tablefill   { border:1px solid #345487;background-color:#F5F9FD;padding:6px;  }
.tablepad    { background-color:#F5F9FD;padding:6px }
.tablebasic  { width:100%; padding:0px 0px 0px 0px; margin:0px; border:0px }

.wrapmini    { float:left;line-height:1.5em;width:25% }
.pagelinks   { float:left;line-height:1.2em;width:35% }

.desc { font-size:10px; color:#434951 }
.edit { font-size: 9px }


.searchlite { font-weight:bold; color:#F00; background-color:#FF0 }

#QUOTE { white-space:normal; font-family: Verdana, Arial; font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }
#SPOILER { font-family: Verdana, Arial; font-size: 8pt; color: #FAFCFE; background-color: #FAFCFE; border: 1px solid Black; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }
#CODE  { white-space:normal; font-family: Courier, Courier New, Verdana, Arial;  font-size: 11px; color: #465584; background-color: #FAFCFE; border: 1px solid #000; padding-top: 2px; padding-right: 2px; padding-bottom: 2px; padding-left: 2px }

.copyright { font-family: Verdana, Tahoma, Arial, Sans-Serif; font-size: 9px; line-height: 12px }

.codebuttons  { font-size: 10px; font-family: verdana, helvetica, sans-serif; vertical-align: middle }
.forminput, .textinput, .radiobutton, .checkbox  { font-size: 11px; font-family: verdana, helvetica, sans-serif; vertical-align: middle }

.thin { padding:6px 0px 6px 0px;line-height:140%;margin:2px 0px 2px 0px;border-top:1px solid #FFF;border-bottom:1px solid #FFF }

.purple { color:purple;font-weight:bold }
.red    { color:red;font-weight:bold }
.green  { color:green;font-weight:bold }
.blue   { color:blue;font-weight:bold }
.orange { color:#F90;font-weight:bold }";
}

function get_main_wrapper()
{
return '<?xml version="1.0" encoding="windows-1251"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<title><% TITLE %></title> 
<meta http-equiv="content-type" content="text/html; charset=windows-1251" /> 
<% GENERATOR %> 
<% CSS %> 
<% JAVASCRIPT %> 
</head> 
<body>
<div id="ipbwrapper">
<% BOARD HEADER %> 
<% NAVIGATION %> 
<% BOARD %> 
<% STATS %> 
<% COPYRIGHT %>
</div>
</body>
</html>';
}

?>