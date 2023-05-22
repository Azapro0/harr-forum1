<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.3 Final
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Time: Thu, 20 Nov 2003 01:15:27 GMT
|   Release: 322f4d4bcd09dcb3058f62ae41ab3e8b
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Language functions
|   > Module written by Matt Mecham
|   > Date started: 22nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}

$idx = new ad_langs();


class ad_langs {

	var $base_url;

	function ad_langs() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//---------------------------------------

		switch($IN['code'])
		{
			
			case 'add':
				$this->add_language();
				break;
				
			case 'edit':
				$this->do_form('edit');
				break;
				
			case 'edit2':
				$this->show_file();
				break;
				
			case 'doadd':
				$this->save_wrapper('add');
				break;
				
			case 'doedit':
				$this->save_langfile();
				break;
				
			case 'remove':
				$this->remove();
				break;
				
			case 'editinfo':
				$this->edit_info();
				break;
				
			case 'export':
				$this->export();
				break;
				
			case 'import':
				$this->import();
				break;
				
			case 'doimport':
				$this->doimport();
				break;
				
			case 'makedefault':
				$this->make_default();
				break;
				
			//-------------------------
			default:
				$this->list_current();
				break;
		}
		
	}
	
	
	//--------------------------------------------
	
	function make_default()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_GET_VARS;
	
		$new_dir = stripslashes(urldecode(trim($HTTP_GET_VARS['id'])));
		
		if ($new_dir == "")
		{
			$ADMIN->error("Невозможно назначить новый ID для языкового пакета по умолчанию");
		}
		
		// Update conf file 
		
		$ADMIN->rebuild_config( array( 'default_language' => $new_dir ) );
		
		// Bring it all back to yoooo!
		
		$std->boink_it($SKIN->base_url."&act=lang");
	
	}
	
	
	//--------------------------------------------
	
	
	
	
	function doimport()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['tarball'] == "")
		{
			$ADMIN->error("Вы не выбрали tar-chive для распаковки!");
		}
		
		require ROOT_PATH."sources/lib/tar.php";
		
		$to_dir = $INFO['base_dir']."lang";
		
		$tarname = $IN['tarball'];
		
		$from_dir = $INFO['base_dir']."archive_in";
		
		$tar = new tar();
		
		//------------------------------
		
		$real_name = preg_replace( "/^lang-(\S+)\.tar$/", "\\1", $IN['tarball'] );
		
		$real_name = preg_replace( "/_/", " ", $real_name );
		
		//------------------------------
		
		$tar->new_tar( $from_dir, $tarname );
		
		$files = $tar->list_files();
		
		if (count($files) > 0)
		{
			foreach($files as $giles)
			{
				if ( ! preg_match( "/^(?:[\.\w\d\+\-\_\/]+)$/", $giles) )
				{
					$ADMIN->error("$tarname оказался битым архивом. Перезагрузите его в режиме binary");
				}
			}
		}
		else
		{
			$ADMIN->error("$tarname является неправильным архивом");
		}
		
		$DB->query("INSERT INTO ibf_languages (ldir, lname) VALUES('temp', '$real_name (Import)')");
		
		$new_id = $DB->get_insert_id();
		
		//-------------------------
		// attempt to make new dir
		//-------------------------
		
		$dest = $to_dir."/".$new_id;
		
		if ( ! mkdir($dest, 0777) )
		{
			$DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");
			
			$ADMIN->error("Невозможно создать новую директорию в $to_dir, установите правильный атрибут CHMOD для этой директории.");
		}
		
		//------------------------------
		// Extract the tarball
		//------------------------------
		
		$tar->extract_files($dest);
		
		if ($tar->error != "")
		{
			$DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");
			
			$ADMIN->error( $tar->error );
		}
		
		$extra = array( 'lauthor' => "", 'lemail' => "" );
		
		if (file_exists($dest."/conf.inc"))
		{
			require $dest."/conf.inc";
		
			$extra['lauthor'] = stripslashes($config['lauthor']);
			$extra['lemail']  = stripslashes($config['lemail']);
			
		}
		
		$extra['ldir'] = $new_id;
		
		$db_string = $DB->compile_db_update_string($extra);
		
		$DB->query("UPDATE ibf_languages SET $db_string WHERE lid='$new_id'");
		
		$ADMIN->done_screen("Языковый пакет создан", "Настройка языков", "act=lang" );
		
	}
	
	//--------------------------------------------
	
	function import()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "Tar-архив, который Вы хотите импортировать, должен быть загружен в директорию 'archive_in' и должен быть правильным tar-архивом, загруженным в режиме binary.";
		$ADMIN->page_title  = "Импортирование языкового пакета";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'      , 'doimport'    ),
												  2 => array( 'act'       , 'lang'      ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;" , "50%" );
		$SKIN->td_header[] = array( "&nbsp;" , "50%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Выберите tar-архив для распаковки" );
		
		$files = $ADMIN->get_tar_names("lang-");
		
		$form_array = array();
		
		if (count($files) > 0)
		{
			foreach($files as $piles)
			{
				$form_array[] = array( $piles, $piles );
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Импортируемый tar-архив...</b>" ,
										  $SKIN->form_dropdown( "tarball", $form_array  )
								 )      );
	
		$ADMIN->html .= $SKIN->end_form("Распаковать!");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	
	}
	
	
	//--------------------------------------------
	
	
	function export()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных");
		}
		
		//+-------------------------------
		
		$archive_dir = $INFO['base_dir']."/archive_out";
		$lang_dir    = $INFO['base_dir']."/lang/".$row['ldir'];
		
		require ROOT_PATH."sources/lib/tar.php";
		
		if (!is_dir($archive_dir))
		{
			$ADMIN->error("Невозможно определить местоположение директории $archive_dir");
		}
		
		if (!is_writeable($archive_dir))
		{
			$ADMIN->error("Невозможно произвести запись в директорию $archive_dir . Установите через FTP атрибут на запись - CHMOD 0755 или 0777. Скрипт форума не в состоянии сделать это самостоятельно.");
		}
		
		if (!is_dir($lang_dir))
		{
			$ADMIN->error("Невозможно определить местоположение директории $lang_dir");
		}
		
		//+-------------------------------
		// Attempt to copy the files to the
		// working directory...
		//+-------------------------------
		
		$l_name = preg_replace( "/\s{1,}/", "_", $row['lname'] );
		
		$new_dir = "lang-".$l_name;
		
		if ( ! $ADMIN->copy_dir($lang_dir, $archive_dir."/".$new_dir) )
		{
			$ADMIN->error( $ADMIN->errors );
		}
		
		// Generate the config file..
		
		$file_content = "<?php\n\n\$config=array('lauthor' => \"".addslashes($row['lauthor'])."\", 'lemail'=>\"".addslashes($row['lemail'])."\")\n\n?".">";
		
		$FH = fopen($archive_dir."/".$new_dir."/"."conf.inc", 'w');
		fwrite($FH, $file_content, strlen($file_content));
		fclose($FH);
		
		// Add files and write tarball
		
		$tar = new tar();
		
		$tar->new_tar( $archive_dir, $new_dir.".tar" );
		$tar->add_directory( $archive_dir."/".$new_dir );
		$tar->write_tar();
		
		// Check for errors.
		
		if ($tar->error != "")
		{
			$ADMIN->error($tar->error);
		}
		
		// remove original unarchived directory
		
		$ADMIN->rm_dir($archive_dir."/".$new_dir);
		
		$ADMIN->done_screen("Языковый пакет экспортирован<br><br>Вы можете скачать tar-архив <a href='archive_out/{$new_dir}.tar'>здесь</a>", "Настройка языков", "act=lang" );
		
		
	}
	
	
	
	
	
	function show_file()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных");
		}
		
		//+-------------------------------
		
		$lang_dir = $INFO['base_dir']."lang/".$row['ldir'];
		
		$form_array = array();
		
		$lang_file = $lang_dir."/".$IN['lang_file'];
	
		
		if ( ! is_writeable($lang_dir) )
		{
			$ADMIN->error("Невозможно произвести запись в директорию '$lang_dir'. Установите через FTP атрибут на запись - CHMOD 0777. Скрипт форума не в состоянии сделать это самостоятельно.");
		}
		
		if (! file_exists($lang_file) )
		{
			$ADMIN->error("Невозможно найти {$IN['lang_file']} в '$lang_dir', вернитесь назад и проверьте данные.");
		}
		else
		{
			require $lang_file;
		}
		
		if ($IN['lang_file'] == 'email_content.php')
		{
			$lang     = $EMAIL;
			$is_email = 1;
		}
		
		if ( ! is_writeable($lang_file) )
		{
			$ADMIN->error("Невозможно произвести запись в файл '$lang_file'. Установите через FTP атрибут на запись - CHMOD 0777. Скрипт форума не в состоянии сделать это самостоятельно.");
		}
	
	
		$ADMIN->page_detail = "Ниже Вы можете отредактировать любые языки..";
		$ADMIN->page_title  = "Редактирование языка: ".$row['lname'];
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'      , 'doedit'    ),
												  2 => array( 'act'       , 'lang'      ),
												  3 => array( 'id'        , $IN['id']   ),
												  4 => array( 'lang_file' , $IN['lang_file']   ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Название блока" , "20%" );
		$SKIN->td_header[] = array( "Содержимое"    , "80%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Текст файла: ".$IN['lang_file'] );
									     
		foreach($lang as $k => $v)
		{
			
				
			//+-------------------------------
			// Swop < and > into ascii entities
			// to prevent textarea breaking html
			//+-------------------------------
			
			$v = stripslashes($v);
			
			$v = preg_replace("/&/", "&#38;", $v );
			$v = preg_replace("/</", "&#60;", $v );
			$v = preg_replace("/>/", "&#62;", $v );
			$v = preg_replace("/'/", "&#39;", $v );
			
			if ($IN['lang_file'] == 'email_content.php')
			{
				$rows = 15;
			
				$cols = 70;
				
				if ( isset($SUBJECT[ $k ]) )
				{
					$subj = "Subject: &nbsp;".$SKIN->form_input('SS_'.$k, $SUBJECT[ $k ] )."<br />";
				}
				else
				{
					$subj = "";
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( 
													"&lt;ibf.lang.<b>".$k."</b>&gt;",
													$subj.$SKIN->form_textarea('XX_'.$k, $v, $cols, $rows),
										 )      );
			}
			else
			{
				$ADMIN->html .= $SKIN->add_td_row( array( 
													"&lt;ibf.lang.<b>".$k."</b>&gt;",
													$SKIN->form_input('XX_'.$k, $v),
										 )      );
			}
				
		}
									     

												 
		$ADMIN->html .= $SKIN->end_form("Сохранить изменения");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
		
	}
	
	//-------------------------------------------------------------
	// Edit language pack information
	//-------------------------------------------------------------
	
	function edit_info()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных");
		}
		
		$final['lname'] = stripslashes($HTTP_POST_VARS['lname']);
		
		if (isset($HTTP_POST_VARS['lname']))
		{
			$final['lauthor'] = stripslashes($HTTP_POST_VARS['lauthor']);
			$final['lemail']  = stripslashes($HTTP_POST_VARS['lemail']);
		}
		
		$db_string = $DB->compile_db_update_string( $final );
		
		$DB->query("UPDATE ibf_languages SET $db_string WHERE lid='".$IN['id']."'");
		
		$ADMIN->done_screen("Языковый пакет обновлён", "Настройка языков", "act=lang" );
		
	}
	
	//-------------------------------------------------------------
	// Add language pack
	//-------------------------------------------------------------
	
	
	function add_language()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		
		$DB->query("SELECT * FROM ibf_languages WHERE lid='".$IN['id']."'");
		
		//-------------------------------------
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос об этом языковом пакете из базы данных");
		}
		
		//-------------------------------------
		
		//-------------------------------------
		
		if ( ! is_writeable(ROOT_PATH.'lang') )
		{
			$ADMIN->error("Скрипт не может произвести запись в директорию 'lang'. Проверьте и установите атрибут для этой директории на запись - CHMOD 0777 и повторите попытку.");
		}
		
		//-------------------------------------
		
		if ( ! is_dir(ROOT_PATH.'lang/'.$row['ldir']) )
		{
			$ADMIN->error("Невозможно определить местоположение оригинального языкового пакета для копирования. Проверьте данные и попробуйте снова.");
		}
		
		//-------------------------------------
		
		$row['lname'] = $row['lname'].".2";
		
		// Insert a new row into the DB...
		
		$final = array();
		
		foreach($row as $k => $v)
		{
			if ($k == 'lid')
			{
				continue;
			}
			else
			{
				$final[ $k ] = $v;
			}
		}
		
		$db_string = $DB->compile_db_insert_string( $final );
		
		$DB->query("INSERT INTO ibf_languages (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		$new_id = $DB->get_insert_id();
		
		//-------------------------------------
		
		if ( ! $ADMIN->copy_dir( $INFO['base_dir'].'lang/'.$row['ldir'] , $INFO['base_dir'].'lang/'.$new_id ) )
		{
			$DB->query("DELETE FROM ibf_languages WHERE lid='$new_id'");
			
			$ADMIN->error( $ADMIN->errors );
		}
		else
		{
			$DB->query("UPDATE ibf_languages SET ldir='$new_id' WHERE lid='$new_id'");
		}
		
		//-------------------------------------
		// Pass to edit / add form...
		//-------------------------------------
		
		$this->do_form('add', $new_id);
	
	}
	
	//-------------------------------------------------------------
	// REMOVE WRAPPERS
	//-------------------------------------------------------------
	
	function remove()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//+-------------------------------
		
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего языка. Вернитесь назад и повторите попытку.");
		}
		
		if ($IN['id'] == 1)
		{
			$ADMIN->error("Вы не можете удалить этот языковый пакет.");
		}
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации об этом языковом пакете из базы данных.");
		}
		
		// Is it default??????????????? ok enuff
		
		if ($INFO['default_language'] == "")
		{
			$INFO['default_language'] = '2';
		}
		
		if ($row['ldir'] == $INFO['default_language'])
		{
			$ADMIN->error("Вы не можете удалить языковый пакет, установленный по умолчанию. Для удаления этого, установите по умолчанию другой язык.");
		}
		
		$DB->query("UPDATE ibf_members SET language='{$INFO['default_language']}' WHERE language='{$row['ldir']}'");
		
		if ( $ADMIN->rm_dir( $INFO['base_dir']."lang/".$row['ldir'] ) )
		{
		
			$DB->query("DELETE FROM ibf_languages WHERE lid='".$IN['id']."'");
			
			$std->boink_it($SKIN->base_url."&act=lang");
			exit();
		}
		else
		{
			$ADMIN->error("Невозможно удалить файлы языкового пакета. Установите необходимый атрибут CHMOD для этого.");
		}
	}
	
	
	
	//-------------------------------------------------------------
	// ADD / EDIT IMAGE SETS
	//-------------------------------------------------------------
	
	function save_langfile()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		if ($IN['lang_file'] == "")
		{
			$ADMIN->error("Вы должны определить существующее название языкового файла. Вернитесь назад и повторите попытку.");
		}
	
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации о языке из базы данных.");
		}
		
		$lang_file = ROOT_PATH."lang/".$row['ldir']."/".$IN['lang_file'];
		
		if (! file_exists( $lang_file ) )
		{
			$ADMIN->error("Невозможно определить местоположение $lang_file");
		}
		
		if (! is_writeable( $lang_file ) )
		{
			$ADMIN->error("Невозможно произвести запись в файл $lang_file, установите атрибут на запись - CHMOD 0666 и повторите попытку.");
		}
		
		$barney = array();
		
		foreach ($IN as $k => $v)
		{
			if ( preg_match( "/^XX_(\S+)$/", $k, $match ) )
			{
				if ( isset($IN[ $match[0] ]) )
				{
					$v = preg_replace("/&#39;/", "'", stripslashes($HTTP_POST_VARS[ $match[0] ]) );
					$v = preg_replace("/&#60;/", "<",  $v );
					$v = preg_replace("/&#62;/", ">", $v );
					$v = preg_replace("/&#38;/", "&", $v );
					$v = preg_replace("/\r/", "", $v );
				
					$barney[ $match[1] ] = $v;
				}
			}
		}
		
		if ( count($barney) < 1 )
		{
			$ADMIN->error("Произошла ошибка. Вы не заполнили ни одно из полей");
		}
		
		$start = "<?php\n\n";
		
		if ($IN['lang_file'] == 'email_content.php')
		{
			foreach($barney as $key => $text)
			{
				$text = preg_replace("/n{1,}$/", "", $text);
				
				if ( $IN['SS_'.$key] != "" )
				{
					$start .= "\n".'$SUBJECT['."'$key'".'] = "'.str_replace( '"', "&quot;", stripslashes($HTTP_POST_VARS['SS_'.$key]) )."\";\n";
				}
				$start .= "\n".'$EMAIL['."'$key'".'] = <<<EOF'."\n".$text."\nEOF;\n";
			}
		}
		else
		{
			foreach($barney as $key => $text)
			{
				$start .= "\n".'$lang['."'$key'".']  = "'.addslashes($text).'";';
			}
		}
		
		$start .= "\n\n?".">";
		
		if ($fh = fopen( $lang_file, 'w') )
		{
			fwrite($fh, $start, strlen($start) );
			fclose($fh);
		}
		else
		{
			$ADMIN->error("Невозможно перезаписать файл $lang_file");
		}
		
		$ADMIN->done_screen("Пакет обновлён", "Настройка языков", "act=lang" );
		
		
		
	}
	
	//-------------------------------------------------------------
	// EDIT SPLASH
	//-------------------------------------------------------------
	
	function do_form( $method='add', $id="" )
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		
		if ($id != "")
		{
			$IN['id'] = $id;
		}
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Необходимо определить ID существующего языка. Вернитесь назад и повторите попытку");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных");
		}
		
		//+-------------------------------
		
		$lang_dir = $INFO['base_dir']."lang/".$row['ldir'];
		
		$form_array = array();
	
		if ($method != 'add')
		{
			if ( ! is_writeable($lang_dir) )
			{
				$ADMIN->error("Невозможно произвести запись в директорию '$lang_dir'. Установите через FTP атрибут на запись - CHMOD 0777. Скрипт форума не в состоянии сделать это самостоятельно.");
			}
		}
		
		//+-------------------------------
		
		if ( is_dir($lang_dir) )
		{
			$handle = opendir($lang_dir);
			
			while (($filename = readdir($handle)) !== false)
			{
				if (($filename != ".") && ($filename != ".."))
				{
					if (preg_match("/^index/", $filename))
					{
						continue;
					}
					
					if (preg_match("/\.php$/", $filename))
					{
						$form_array[] = array( $filename, preg_replace( "/\.php$/", "", $filename ) );
					}
				}
			}
				
			closedir($handle);
		}
		
		if ($row['lauthor'] and $row['lemail'])
		{
			$author = "<br><br>Автором этого языкового пакета <b>'{$row['lname']}'</b> является <a href='mailto:{$row['lemail']}' target='_blank'>{$row['lauthor']}</a>";
		}
		else if ($row['lauthor'])
		{
			$author = "<br><br>Автором языкового пакета <b>'{$row['lname']}'</b> является {$row['lauthor']}";
		}
		
		//+-------------------------------
	
		$ADMIN->page_detail = "Выберите язык для редактирования, ниже .$author $url";
		$ADMIN->page_title  = "Редактирование языка";
		
		//+-------------------------------
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'editinfo'    ),
												  2 => array( 'act'   , 'lang'       ),
												  3 => array( 'id'    , $IN['id']     ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Редактирование информации языкового пакета" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Название языка</b>",
													$SKIN->form_input('lname', $row['lname']),
									     )      );
									     
		if ($method == 'add')
		{
									     
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>Имя автора языкового пакета:</b>",
														$SKIN->form_input('lauthor', $row['lauthor']),
											 )      );
											 
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>E-mail автора языкового пакета:</b>",
														$SKIN->form_input('lemail', $row['lemail']),
											 )      );
											 
		}
									     
		$ADMIN->html .= $SKIN->end_form("Сохранить изменения");
									     
		$ADMIN->html .= $SKIN->end_table();
									     
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'edit2'    ),
												  2 => array( 'act'   , 'lang'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Редактирование файла языка '".$row['lname']."'" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>Выберите файл для редактирования</b>",
													$SKIN->form_dropdown('lang_file', $form_array),
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Отредактировать файл");
									     
		$ADMIN->html .= $SKIN->end_table();
									     
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
	}
	
	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------
	
	function list_current()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($INFO['default_language'] == "")
		{
			$INFO['default_language'] = '2';
		}
		
		$form_array = array();
	
		$ADMIN->page_detail = "Здесь Вы можете редактировать, удалять или создавать новые языковые пакеты";
		$ADMIN->page_title  = "Настройка языков";
		
		//+-------------------------------
		
		$DB->query("select ibf_languages.*, count(ibf_members.id) as mcount from ibf_languages left join ibf_members on(ibf_members.language=ibf_languages.ldir) where (ibf_members.language is not null or ibf_members.language = 'en') group by ibf_languages.ldir order by ibf_languages.lname");
		
		
		$used_ids = array();
		$show_array = array();
		
		$ADMIN->html .= $SKIN->js_checkdelete();
		
		if ( $DB->get_num_rows() )
		{
		
			$SKIN->td_header[] = array( "Название"        , "40%" );
			$SKIN->td_header[] = array( "Пользователей"      , "30%" );
			$SKIN->td_header[] = array( "Экспорт"       , "10%" );
			$SKIN->td_header[] = array( "Редактировать"         , "10%" );
			$SKIN->td_header[] = array( "Удалить"       , "10%" );
		
			$ADMIN->html .= $SKIN->start_table( "Текущие используемые языки" );
			
			while ( $r = $DB->fetch_row() )
			{
			
				if ($INFO['default_language'] == $r['ldir'])
				{
					$root = "<span style='color:red;font-weight:bold'> (По умолчанию)</span>";
				}
				else
				{
					$root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=".urlencode($r['ldir'])."'>Назначить по умолчанию</a> )";
				}
			
				$show_array[ $r['lid'] ] .= stripslashes($r['lname'])."<br>";
			
				if ( in_array( $r['lid'], $used_ids ) )
				{
					continue;
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['lname'])."</b> $root",
														  "<center>{$r['mcount']}</center>",
														  "<center><a href='".$SKIN->base_url."&act=lang&code=export&id={$r['lid']}'>Экспорт</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=lang&code=edit&id={$r['lid']}'>Редактировать</a></center>",
														  "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>Удалить</a></center>",
												 )      );
												   
				$used_ids[] = $r['lid'];
				
				$form_array[] = array( $r['lid'], $r['lname'] );
				
			}
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		if ( count($used_ids) < 1 )
		{
			$used_ids[] = '0';
		}
			$DB->query("SELECT lid, ldir, lname FROM ibf_languages WHERE lid NOT IN(".implode(",",$used_ids).")");
		
			if ( $DB->get_num_rows() )
			{
			
				$SKIN->td_header[] = array( "Название"  , "40%" );
				$SKIN->td_header[] = array( "Экспорт" , "10%" );
				$SKIN->td_header[] = array( "Редактировать"   , "30%" );
				$SKIN->td_header[] = array( "Удалить" , "20%" );
			
				$ADMIN->html .= $SKIN->start_table( "Текущие неиспользуемые языки" );
				
				
				
				while ( $r = $DB->fetch_row() )
				{
					
					if ($INFO['default_language'] == $r['ldir'])
					{
						$root = "<span style='color:red;font-weight:bold'> (По умолчанию)</span>";
					}
					else
					{
						$root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=".urlencode($r['ldir'])."'>Назначить по умолчанию</a> )";
					}
				
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['lname'])."</b> $root",
					 										  "<center><a href='".$SKIN->base_url."&act=lang&code=export&id={$r['lid']}'>Экспорт</a></center>",
															  "<center><a href='".$SKIN->base_url."&act=lang&code=edit&id={$r['lid']}'>Редактировать</a></center>",
															  "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>Удалить</a></center>",
													 )      );
													 
					$form_array[] = array( $r['lid'], $r['lname'] );
													   
				}
				
				$ADMIN->html .= $SKIN->end_table();
			}
		//}
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add'     ),
												  2 => array( 'act'   , 'lang'    ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Создание языка" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Создать новый языковый пакет на основе...</b>" ,
										  		  $SKIN->form_dropdown( "id", $form_array)
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("Создать новый пакет");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
}


?>