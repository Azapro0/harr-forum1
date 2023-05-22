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
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
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
			$ADMIN->error("���������� ��������� ����� ID ��� ��������� ������ �� ���������");
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
			$ADMIN->error("�� �� ������� tar-chive ��� ����������!");
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
					$ADMIN->error("$tarname �������� ����� �������. ������������� ��� � ������ binary");
				}
			}
		}
		else
		{
			$ADMIN->error("$tarname �������� ������������ �������");
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
			
			$ADMIN->error("���������� ������� ����� ���������� � $to_dir, ���������� ���������� ������� CHMOD ��� ���� ����������.");
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
		
		$ADMIN->done_screen("�������� ����� ������", "��������� ������", "act=lang" );
		
	}
	
	//--------------------------------------------
	
	function import()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "Tar-�����, ������� �� ������ �������������, ������ ���� �������� � ���������� 'archive_in' � ������ ���� ���������� tar-�������, ����������� � ������ binary.";
		$ADMIN->page_title  = "�������������� ��������� ������";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'      , 'doimport'    ),
												  2 => array( 'act'       , 'lang'      ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;" , "50%" );
		$SKIN->td_header[] = array( "&nbsp;" , "50%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� tar-����� ��� ����������" );
		
		$files = $ADMIN->get_tar_names("lang-");
		
		$form_array = array();
		
		if (count($files) > 0)
		{
			foreach($files as $piles)
			{
				$form_array[] = array( $piles, $piles );
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� tar-�����...</b>" ,
										  $SKIN->form_dropdown( "tarball", $form_array  )
								 )      );
	
		$ADMIN->html .= $SKIN->end_form("�����������!");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	
	}
	
	
	//--------------------------------------------
	
	
	function export()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� �� ���� ������");
		}
		
		//+-------------------------------
		
		$archive_dir = $INFO['base_dir']."/archive_out";
		$lang_dir    = $INFO['base_dir']."/lang/".$row['ldir'];
		
		require ROOT_PATH."sources/lib/tar.php";
		
		if (!is_dir($archive_dir))
		{
			$ADMIN->error("���������� ���������� �������������� ���������� $archive_dir");
		}
		
		if (!is_writeable($archive_dir))
		{
			$ADMIN->error("���������� ���������� ������ � ���������� $archive_dir . ���������� ����� FTP ������� �� ������ - CHMOD 0755 ��� 0777. ������ ������ �� � ��������� ������� ��� ��������������.");
		}
		
		if (!is_dir($lang_dir))
		{
			$ADMIN->error("���������� ���������� �������������� ���������� $lang_dir");
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
		
		$ADMIN->done_screen("�������� ����� �������������<br><br>�� ������ ������� tar-����� <a href='archive_out/{$new_dir}.tar'>�����</a>", "��������� ������", "act=lang" );
		
		
	}
	
	
	
	
	
	function show_file()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� �� ���� ������");
		}
		
		//+-------------------------------
		
		$lang_dir = $INFO['base_dir']."lang/".$row['ldir'];
		
		$form_array = array();
		
		$lang_file = $lang_dir."/".$IN['lang_file'];
	
		
		if ( ! is_writeable($lang_dir) )
		{
			$ADMIN->error("���������� ���������� ������ � ���������� '$lang_dir'. ���������� ����� FTP ������� �� ������ - CHMOD 0777. ������ ������ �� � ��������� ������� ��� ��������������.");
		}
		
		if (! file_exists($lang_file) )
		{
			$ADMIN->error("���������� ����� {$IN['lang_file']} � '$lang_dir', ��������� ����� � ��������� ������.");
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
			$ADMIN->error("���������� ���������� ������ � ���� '$lang_file'. ���������� ����� FTP ������� �� ������ - CHMOD 0777. ������ ������ �� � ��������� ������� ��� ��������������.");
		}
	
	
		$ADMIN->page_detail = "���� �� ������ ��������������� ����� �����..";
		$ADMIN->page_title  = "�������������� �����: ".$row['lname'];
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'      , 'doedit'    ),
												  2 => array( 'act'       , 'lang'      ),
												  3 => array( 'id'        , $IN['id']   ),
												  4 => array( 'lang_file' , $IN['lang_file']   ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "�������� �����" , "20%" );
		$SKIN->td_header[] = array( "����������"    , "80%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����� �����: ".$IN['lang_file'] );
									     
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
									     

												 
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
										 
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
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� �� ���� ������");
		}
		
		$final['lname'] = stripslashes($HTTP_POST_VARS['lname']);
		
		if (isset($HTTP_POST_VARS['lname']))
		{
			$final['lauthor'] = stripslashes($HTTP_POST_VARS['lauthor']);
			$final['lemail']  = stripslashes($HTTP_POST_VARS['lemail']);
		}
		
		$db_string = $DB->compile_db_update_string( $final );
		
		$DB->query("UPDATE ibf_languages SET $db_string WHERE lid='".$IN['id']."'");
		
		$ADMIN->done_screen("�������� ����� �������", "��������� ������", "act=lang" );
		
	}
	
	//-------------------------------------------------------------
	// Add language pack
	//-------------------------------------------------------------
	
	
	function add_language()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		
		$DB->query("SELECT * FROM ibf_languages WHERE lid='".$IN['id']."'");
		
		//-------------------------------------
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ �� ���� �������� ������ �� ���� ������");
		}
		
		//-------------------------------------
		
		//-------------------------------------
		
		if ( ! is_writeable(ROOT_PATH.'lang') )
		{
			$ADMIN->error("������ �� ����� ���������� ������ � ���������� 'lang'. ��������� � ���������� ������� ��� ���� ���������� �� ������ - CHMOD 0777 � ��������� �������.");
		}
		
		//-------------------------------------
		
		if ( ! is_dir(ROOT_PATH.'lang/'.$row['ldir']) )
		{
			$ADMIN->error("���������� ���������� �������������� ������������� ��������� ������ ��� �����������. ��������� ������ � ���������� �����.");
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
			$ADMIN->error("�� ������ ���������� ID ������������� �����. ��������� ����� � ��������� �������.");
		}
		
		if ($IN['id'] == 1)
		{
			$ADMIN->error("�� �� ������ ������� ���� �������� �����.");
		}
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� �� ���� �������� ������ �� ���� ������.");
		}
		
		// Is it default??????????????? ok enuff
		
		if ($INFO['default_language'] == "")
		{
			$INFO['default_language'] = '2';
		}
		
		if ($row['ldir'] == $INFO['default_language'])
		{
			$ADMIN->error("�� �� ������ ������� �������� �����, ������������� �� ���������. ��� �������� �����, ���������� �� ��������� ������ ����.");
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
			$ADMIN->error("���������� ������� ����� ��������� ������. ���������� ����������� ������� CHMOD ��� �����.");
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
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		if ($IN['lang_file'] == "")
		{
			$ADMIN->error("�� ������ ���������� ������������ �������� ��������� �����. ��������� ����� � ��������� �������.");
		}
	
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� � ����� �� ���� ������.");
		}
		
		$lang_file = ROOT_PATH."lang/".$row['ldir']."/".$IN['lang_file'];
		
		if (! file_exists( $lang_file ) )
		{
			$ADMIN->error("���������� ���������� �������������� $lang_file");
		}
		
		if (! is_writeable( $lang_file ) )
		{
			$ADMIN->error("���������� ���������� ������ � ���� $lang_file, ���������� ������� �� ������ - CHMOD 0666 � ��������� �������.");
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
			$ADMIN->error("��������� ������. �� �� ��������� �� ���� �� �����");
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
			$ADMIN->error("���������� ������������ ���� $lang_file");
		}
		
		$ADMIN->done_screen("����� �������", "��������� ������", "act=lang" );
		
		
		
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
			$ADMIN->error("���������� ���������� ID ������������� �����. ��������� ����� � ��������� �������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_languages WHERE lid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� �� ���� ������");
		}
		
		//+-------------------------------
		
		$lang_dir = $INFO['base_dir']."lang/".$row['ldir'];
		
		$form_array = array();
	
		if ($method != 'add')
		{
			if ( ! is_writeable($lang_dir) )
			{
				$ADMIN->error("���������� ���������� ������ � ���������� '$lang_dir'. ���������� ����� FTP ������� �� ������ - CHMOD 0777. ������ ������ �� � ��������� ������� ��� ��������������.");
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
			$author = "<br><br>������� ����� ��������� ������ <b>'{$row['lname']}'</b> �������� <a href='mailto:{$row['lemail']}' target='_blank'>{$row['lauthor']}</a>";
		}
		else if ($row['lauthor'])
		{
			$author = "<br><br>������� ��������� ������ <b>'{$row['lname']}'</b> �������� {$row['lauthor']}";
		}
		
		//+-------------------------------
	
		$ADMIN->page_detail = "�������� ���� ��� ��������������, ���� .$author $url";
		$ADMIN->page_title  = "�������������� �����";
		
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
		
		$ADMIN->html .= $SKIN->start_table( "�������������� ���������� ��������� ������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>�������� �����</b>",
													$SKIN->form_input('lname', $row['lname']),
									     )      );
									     
		if ($method == 'add')
		{
									     
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>��� ������ ��������� ������:</b>",
														$SKIN->form_input('lauthor', $row['lauthor']),
											 )      );
											 
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>E-mail ������ ��������� ������:</b>",
														$SKIN->form_input('lemail', $row['lemail']),
											 )      );
											 
		}
									     
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
									     
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
		
		$ADMIN->html .= $SKIN->start_table( "�������������� ����� ����� '".$row['lname']."'" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<b>�������� ���� ��� ��������������</b>",
													$SKIN->form_dropdown('lang_file', $form_array),
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("��������������� ����");
									     
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
	
		$ADMIN->page_detail = "����� �� ������ �������������, ������� ��� ��������� ����� �������� ������";
		$ADMIN->page_title  = "��������� ������";
		
		//+-------------------------------
		
		$DB->query("select ibf_languages.*, count(ibf_members.id) as mcount from ibf_languages left join ibf_members on(ibf_members.language=ibf_languages.ldir) where (ibf_members.language is not null or ibf_members.language = 'en') group by ibf_languages.ldir order by ibf_languages.lname");
		
		
		$used_ids = array();
		$show_array = array();
		
		$ADMIN->html .= $SKIN->js_checkdelete();
		
		if ( $DB->get_num_rows() )
		{
		
			$SKIN->td_header[] = array( "��������"        , "40%" );
			$SKIN->td_header[] = array( "�������������"      , "30%" );
			$SKIN->td_header[] = array( "�������"       , "10%" );
			$SKIN->td_header[] = array( "�������������"         , "10%" );
			$SKIN->td_header[] = array( "�������"       , "10%" );
		
			$ADMIN->html .= $SKIN->start_table( "������� ������������ �����" );
			
			while ( $r = $DB->fetch_row() )
			{
			
				if ($INFO['default_language'] == $r['ldir'])
				{
					$root = "<span style='color:red;font-weight:bold'> (�� ���������)</span>";
				}
				else
				{
					$root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=".urlencode($r['ldir'])."'>��������� �� ���������</a> )";
				}
			
				$show_array[ $r['lid'] ] .= stripslashes($r['lname'])."<br>";
			
				if ( in_array( $r['lid'], $used_ids ) )
				{
					continue;
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['lname'])."</b> $root",
														  "<center>{$r['mcount']}</center>",
														  "<center><a href='".$SKIN->base_url."&act=lang&code=export&id={$r['lid']}'>�������</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=lang&code=edit&id={$r['lid']}'>�������������</a></center>",
														  "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>�������</a></center>",
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
			
				$SKIN->td_header[] = array( "��������"  , "40%" );
				$SKIN->td_header[] = array( "�������" , "10%" );
				$SKIN->td_header[] = array( "�������������"   , "30%" );
				$SKIN->td_header[] = array( "�������" , "20%" );
			
				$ADMIN->html .= $SKIN->start_table( "������� �������������� �����" );
				
				
				
				while ( $r = $DB->fetch_row() )
				{
					
					if ($INFO['default_language'] == $r['ldir'])
					{
						$root = "<span style='color:red;font-weight:bold'> (�� ���������)</span>";
					}
					else
					{
						$root = " ( <a href='{$SKIN->base_url}&act=lang&code=makedefault&id=".urlencode($r['ldir'])."'>��������� �� ���������</a> )";
					}
				
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['lname'])."</b> $root",
					 										  "<center><a href='".$SKIN->base_url."&act=lang&code=export&id={$r['lid']}'>�������</a></center>",
															  "<center><a href='".$SKIN->base_url."&act=lang&code=edit&id={$r['lid']}'>�������������</a></center>",
															  "<center><a href='javascript:checkdelete(\"act=lang&code=remove&id={$r['lid']}\")'>�������</a></center>",
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
		
		$ADMIN->html .= $SKIN->start_table( "�������� �����" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� �������� ����� �� ������...</b>" ,
										  		  $SKIN->form_dropdown( "id", $form_array)
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("������� ����� �����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
}


?>