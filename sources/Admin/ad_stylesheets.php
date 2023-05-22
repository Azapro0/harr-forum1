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
|   > CSS management functions
|   > Module written by Matt Mecham
|   > Date started: 4th April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}

$idx = new ad_settings();


class ad_settings {

	var $base_url;

	function ad_settings() {
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
			case 'wrapper':
				$this->list_sheets();
				break;
				
			case 'add':
				$this->do_form('add');
				break;
				
			case 'edit2':
				$this->do_form('edit');
				break;
				
			case 'edit':
				//$this->edit_splash();
				$this->do_form('edit');
				break;
				//break;
				
			case 'doadd':
				$this->save_wrapper('add');
				break;
				
			case 'doedit':
				$this->save_wrapper('edit');
				break;
				
			case 'remove':
				$this->remove();
				break;
				
			case 'export':
				$this->export();
				break;
				
			case 'optimize':
				$this->optimize();
				break;
				
			case 'css_upload':
				$this->css_upload('new');
				break;
				
			case 'easyedit':
				$this->easy_edit();
				break;
				
			case 'doresync':
				$this->do_resynch();
				break;
			
			case 'colouredit':
				$this->colouredit();
				break;
				
			case 'docolour':
				$this->do_colouredit();
				break;
			
			//-------------------------
			default:
				$this->list_sheets();
				break;
		}
		
	}
	
	//+-------------------------------
	
	function do_resynch()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего шаблона. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT cssid, css_text, css_name, css_comments FROM ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $cssinfo = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно запросить данные CSS из базы данных.");
		}
		
		if ( $IN['favour'] == 'cache' )
		{
			$cache_file = ROOT_PATH."cache/css_".$IN['id'].".css";
			
			if ( file_exists( $cache_file ) )
			{
				$FH = fopen( $cache_file, 'r' );
				$cache_data = fread( $FH, filesize($cache_file) );
				fclose($FH);
			}
			else
			{
				$ADMIN->error("Невоможно определить местоположение кэша CSS файла @ $cache_file");
			}
			
			$dbr = $DB->compile_db_update_string( array( 'css_text' => $cache_data ) );
			
			$DB->query("UPDATE ibf_css SET $dbr WHERE cssid='".$IN['id']."'");
		}
		else
		{
			$cache_file = ROOT_PATH."cache/css_".$IN['id'].".css";
			
			$FH = fopen( $cache_file, 'w' );
			fputs( $FH, $cssinfo['css_text'], strlen($cssinfo['css_text']) );
			fclose($FH);
		}
		
		if ( $IN['return'] != 'colouredit' )
		{
			$this->do_form('edit');
		}
		else
		{
			$this->colouredit();
		}
	}
	
	
	
	//+-------------------------------
	
	function resync_splash($db_length, $cache_length, $cache_mtime, $db_mtime, $id, $return="")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
	
		$ADMIN->page_detail = "Обнаружены несовпадения между кэшем стиля и стилем, загруженным в базу данных";
		$ADMIN->page_title  = "Ресинхронизировать стиль";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "50%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "50%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doresync'  ),
												  2 => array( 'act'   , 'style'     ),
												  3 => array( 'id'    , $id         ),
												  4 => array( 'return', $return     ),
									     )    );
									     
		$favour = 'db';
		
		$ADMIN->html .= $SKIN->start_table( "Ресинхронизация CSS перед редактированием..." );
		
		if ( intval($cache_mtime) > intval($db_mtime) )
		{
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>Последнее обновление CSS в базе данных:</b> ".$ADMIN->get_date($db_mtime, 'LONG'),
														"<b>Кол-во символов CSS в базе данных:</b> $db_length",
											 )      );
											 
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<span style='color:red'><b>Последнее обновление CSS в кэше:</b> ".$ADMIN->get_date($cache_mtime, 'LONG')."</span>",
														"<span style='color:red'><b>Кол-во символов CSS в кэше:</b> $cache_length</span>",
											 )      );
			$favour = 'cache';
											 
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<span style='color:red'><b>Последнее обновление CSS в базе данных:</b> ".$ADMIN->get_date($db_mtime, 'LONG')."</span>",
														"<span style='color:red'><b>Кол-во символов CSS в базе данных:</b> $db_length</span>",
											 )      );
											 
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>Последнее обновление CSS в кэше:</b> ".$ADMIN->get_date($cache_mtime, 'LONG'),
														"<b>Кол-во символов CSS в кэше:</b> $cache_length",
											 )      );
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( 
														"<b>Использование ресинхронизации...</b>",
														$SKIN->form_dropdown( 'favour', array(
																							    0 => array( 'cache', 'Перезаписать версию базы данных с версии кэша'),
																							    1 => array( 'db'   , 'Обновить кэш версию из базы данных' ),
																							 ), $favour ),
											 )      );
		
		$ADMIN->html .= $SKIN->end_form("Ресинхронизировать");
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
	}
	
	//-------------------------------------------------------------
	// ADD / EDIT WRAPPERS
	//-------------------------------------------------------------
	
	
	
	
	function css_upload($type='new')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_FILES;
		
		$FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];
		$FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];
		$FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];
		
		// Naughty Opera adds the filename on the end of the
		// mime type - we don't want this.
		
		$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
		
		if (! is_dir($INFO['upload_dir']) )
		{
			$ADMIN->error("Невозможно определить местоположение директории для загрузок. Проверьте правильность указания пути к директории 'uploads'");
		}
							
		// Naughty Mozilla likes to use "none" to indicate an empty upload field.
		// I love universal languages that aren't universal.
		
		if ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "" or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "none") )
		{
			$ADMIN->error("Вы не выбрали файл для загрузки!");
		}
		
		//-------------------------------------------------
		// Move the uploaded file to somewhere we can
		// manipulate it in safe mode
		//-------------------------------------------------
		
		if (! @move_uploaded_file( $HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['upload_dir']."/".$FILE_NAME) )
		{
			$ADMIN->error("Неудачная загрузка");
		}
		
		// Open the file and copy to the DB
		
		$real_name = str_replace( "_", " ", preg_replace( "/^(.*),\d+\.css$/", "\\1", $FILE_NAME ) );
		$real_name .= ' [UPLOAD]';
		
		if ( $FH = @fopen( $INFO['upload_dir']."/".$FILE_NAME, "r" ) )
		{
			$data = @fread( $FH, @filesize($INFO['upload_dir']."/".$FILE_NAME) );
			@fclose($FH);
			@unlink($INFO['upload_dir']."/".$FILE_NAME);
		}
		else
		{
			@unlink($INFO['upload_dir']."/".$FILE_NAME);
			$ADMIN->error("Невозможно открыть загруженный файл для чтения. Процесс приостановлен.");
		}
		
		list($css, $comments) = explode( "<|COMMENTS|>", $data );
		
		$css      = trim($css);
		$comments = trim($css);
		
		if ($type == 'new')
		{
			$dbs = $DB->compile_db_insert_string( array (
														  'css_name'     => $real_name,
														  'css_text'     => $css,
														  'css_comments' => $comments,
														  'updated'      => time(),
												)       );
											
												
			$DB->query("INSERT INTO ibf_css (".$dbs['FIELD_NAMES'].") VALUES(".$dbs['FIELD_VALUES'].")");
			
			$new_id = $DB->get_insert_id();
			
			if ( file_exists( ROOT_PATH."cache" ) )
			{
				if ( is_writeable( ROOT_PATH."cache" ) )
				{
					$FH = fopen( ROOT_PATH."cache/css_".$new_id.".css", 'w' );
					fputs( $FH, $css, strlen($css) );
					fclose($FH);
				}
			}
			
			$ADMIN->done_screen("Стиль загружен", "Настройка стиля", "act=style" );
		}
		
		
	}
	
	
	//----------------------------------------------------
	
	function optimize()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего CSS. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных.");
		}
		
		//+-------------------------------
		
		$orig_size = strlen($row['css_text']);
		
		$orig_text = str_replace( "\r\n", "\n", $row['css_text']);
		$orig_text = str_replace( "\r"  , "\n", $orig_text);
		$orig_text = str_replace( "\n\n", "\n", $orig_text);
		
		$parsed = array();
		
		// Remove comments
		
		$orig_text = preg_replace( "#/\*(.+?)\*/#s", "", $orig_text );
		
		// Grab all the definitions
		
		preg_match_all( "/(.+?)\{(.+?)\}/s", $orig_text, $match, PREG_PATTERN_ORDER );
		
		for ( $i = 0 ; $i < count($match[0]); $i++ )
		{
			$match[1][$i] = trim($match[1][$i]);
			$parsed[ $match[1][$i] ] = trim($match[2][$i]);
		}
		
		//------------------
		
		if ( count($parsed) < 1)
		{
			$ADMIN->error("Этот стиль в неправильном формате, который не может определить система Invision Power Board. Оптимизация не выполнена.");
		}
		
		// Clean them up
		
		$final = "";
		
		foreach( $parsed as $name => $p )
		{
			// Ignore comments
			
			if ( preg_match( "#^//#", $name) )
			{
				continue;
			}
			
			// Split up the components
			
			$parts = explode( ";", $p);
			$defs  = array();
			
			foreach( $parts as $part )
			{
				if ($part != "")
				{
					list($definition, $data) = explode( ":", $part );
					$defs[]   = trim($definition).": ".trim($data);
				}
			}
			
			$final .= $name . " { ".implode("; ", $defs). " }\n";
		}
		
		$final_size = strlen($final);
		
		if ($final_size < 1000)
		{
			$ADMIN->error("Этот стиль в неправильном формате, который не может определить система Invision Power Board. Оптимизация не выполнена.");
		}
		
		// Update the DB
		
		$dbs = $DB->compile_db_update_string( array( 'css_text' => $final ) );
		
		$DB->query("UPDATE ibf_css SET $dbs WHERE cssid='".$IN['id']."'");
		
		$saved    = $orig_size - $final_size;
		$pc_saved = 0;
		
		if ($saved > 0)
		{
			$pc_saved = sprintf( "%.2f", ($saved / $orig_size) * 100);
		}
		
		$ADMIN->done_screen("Стиль обновлён: Сохранено символов: $saved ($pc_saved %)", "Настройка стиля", "act=style" );
				    
		
		
	}
	
	
	//----------------------------------------------------
	
	function export()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего CSS. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * from ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно произвести запрос информации из базы данных.");
		}
		
		//+-------------------------------
		
		$name = str_replace( " ", "_", $row['css_name'] );
		
		@header("Content-type: unknown/unknown");
		@header("Content-Disposition: attachment; filename=$name,{$row['cssid']}.css");
		
		print $row['css_text'];
		
		exit();
		
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
			$ADMIN->error("Вы должны определить ID существующего стиля. Вернитесь назад и повторите попытку.");
		}
		
		$DB->query("DELETE FROM ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( file_exists( ROOT_PATH."cache/css_".$IN['id'].".css" ) )
		{
			@unlink( ROOT_PATH."cache/css_".$IN['id'].".css" );
		}
		
		$std->boink_it($SKIN->base_url."&act=style");
			
		exit();
		
		
	}
	
	
	
	//-------------------------------------------------------------
	// ADD / EDIT WRAPPERS
	//-------------------------------------------------------------
	
	function save_wrapper( $type='add' )
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//+-------------------------------
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Вы должны определить ID существующего CSS. Вернитесь назад и повторите попытку.");
			}
			
		}
		
		if ($IN['name'] == "")
		{
			$ADMIN->error("Вы должны определить название для этого стиля");
		}
		
		if ($IN['css'] == "")
		{
			$ADMIN->error("Нельзя использовать пустой стиль");
		}
		
		$css = stripslashes($HTTP_POST_VARS['css']);
		
		$barney = array( 'css_name'     => stripslashes($HTTP_POST_VARS['name']),
						 'css_text'     => $css,
						 'updated'      => time(),
					   );
					   
		if ($type == 'add')
		{
			$db_string = $DB->compile_db_insert_string( $barney );
			
			$DB->query("INSERT INTO ibf_css (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
			
			$new_id = $DB->get_insert_id();
			
			//--------------------------------------------
			// Update cache?
			//--------------------------------------------
			
			if ( file_exists( ROOT_PATH."cache" ) )
			{
				if ( is_writeable( ROOT_PATH."cache" ) )
				{
					$FH = fopen( ROOT_PATH."cache/css_".$new_id.".css", 'w' );
					fputs( $FH, $css, strlen($css) );
					fclose($FH);
				}
			}
			
			
			$std->boink_it($SKIN->base_url."&act=style");
			
			exit();
			
		}
		else
		{
			$db_string = $DB->compile_db_update_string( $barney );
			
			$DB->query("UPDATE ibf_css SET $db_string WHERE cssid='".$IN['id']."'");
			
			//--------------------------------------------
			// Update cache?
			//--------------------------------------------
			
			$extra = "<b>Кэш файл обновлён</b>";
			
			if ( file_exists( ROOT_PATH."cache" ) )
			{
				if ( is_writeable( ROOT_PATH."cache" ) )
				{
					if ( $FH = @fopen( ROOT_PATH."cache/css_".$IN['id'].".css", 'w' ) )
					{
						@fputs( $FH, $css, strlen($css) );
						@fclose($FH);
					}
					else
					{
						$extra = "<b>Кэш файл не обновлён. Проверьте CHMOD атрибуты для ./cache и ./cache/css_".$IN['id'].".css</b>";
					}
				}
				else
				{
					$extra = "<b>Кэш файл не обновлён. Проверьте CHMOD атрибуты для ./cache и ./cache/css_".$IN['id'].".css</b>";
				}
			}
			else
			{
				$extra = "<b>Кэш файл не обновлён. Кэш папка отсутствует</b>";
			}
			
			$ADMIN->nav[] = array( 'act=style' ,'Главная страница настройки стиля' );
			$ADMIN->nav[] = array( "act=style&code=edit2&id={$IN['id']}" ,"Повторное редактирование стиля" );
			
			$ADMIN->done_screen("Стиль обновлён : $extra", "Настройка стиля", "act=style" );
			
			
		}
		
		
	}
	
	
	function do_form( $type='add' )
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего шаблона. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT cssid, css_text, css_name, updated FROM ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $cssinfo = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно запросить данные CSS из базы данных.");
		}
		
		//+-------------------------------
		
		$css = $cssinfo['css_text'];
		
		if ($type == 'add')
		{
			$code = 'doadd';
			$button = 'Создать стиль';
			$cssinfo['css_name'] = $cssinfo['css_name'].".2";
		}
		else
		{
			$code = 'doedit';
			$button = 'Редактировать стиль';
			
			//+-------------------------------
			// DB same as cache version?
			//+-------------------------------
			
			$cache_file = ROOT_PATH."cache/css_".$IN['id'].".css";
			
			if ( file_exists( $cache_file ) )
			{
				$FH = fopen( $cache_file, 'r' );
				$cache_data = fread( $FH, filesize($cache_file) );
				fclose($FH);
			
				$db_length    = strlen( trim($css) );
				$cache_length = strlen(trim($cache_data));
				
				if ($db_length != $cache_length)
				{
					// We've got ourselves a mismatch!
					// Get mtime of cache file
					
					$stat = stat( $cache_file );
					
					$cache_mtime = $stat[9];
					$db_mtime    = $cssinfo['updated'];
					
					$this->resync_splash($db_length, $cache_length, $cache_mtime, $db_mtime, $IN['id']);
					
				}
			}
		}
		
		//+-------------------------------
		// COLURS!ooO!
		//+-------------------------------
		
		//.class { definitions }
		//#id { definitions }
		
		$css_elements = array();
		
		preg_match_all( "/(\.|\#)(\S+?)\s{0,}\{.+?\}/s", $css, $match );
		
		for ($i=0; $i < count($match[0]); $i++)
		{
			$type = trim($match[1][$i]);
			
			$name = trim($match[2][$i]);
			
			if ($type == '.')
			{
				$css_elements[] = array( 'class|'.$name, $type.$name );
			}
			else
			{
				$css_elements[] = array( 'id|'.$name, $type.$name );
			}
		}
			
		//+-------------------------------
	
		$ADMIN->page_detail = "Вы можете полноценно использовать существующие CSS, при добавлении или редактировании стиля.";
		$ADMIN->page_title  = "Настройка стиля";
		
		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript'>
		                 <!--
		                 function cssSearch(theID)
		                 {
		                 	cssChosen = document.cssForm.csschoice.options[document.cssForm.csschoice.selectedIndex].value;
		                 	
		                 	window.open('{$SKIN->base_url}&act=rtempl&code=css_search&id='+theID+'&element='+cssChosen,'Поиск CSS','width=400,height=500,resizable=yes,scrollbars=yes');
		                 }
		                 
		                 function cssPreview(theID)
		                 {
		                 	cssChosen = document.cssForm.csschoice.options[document.cssForm.csschoice.selectedIndex].value;
		                 	
		                 	window.open('{$SKIN->base_url}&act=rtempl&code=css_preview&id='+theID+'&element='+cssChosen,'Поиск CSS','width=400,height=500,resizable=yes,scrollbars=yes');
		                 }
		                 
		                 //-->
		                 </script>";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'css_search' ),
												  2 => array( 'act'   , 'style'      ),
												  3 => array( 'id'    , $IN['id']    ),
									     ), "cssForm"      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "20%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "80%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Поиск CSS" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"Найти строку...",
													$SKIN->form_dropdown('csschoice', $css_elements).' ... используемую в пределах шаблонов &nbsp;'
												   .'<input type="button" value="ОК!" onClick="cssSearch(\''.$IN['id'].'\');" id="editbutton">'
												   .'&nbsp;<input type="button" value="Предварительный просмотр CSS" onClick="cssPreview(\''.$IN['id'].'\');" id="editbutton">'
									     )      );
									     
		
												 
		$ADMIN->html .= $SKIN->end_form();
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_no_specialchars();
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $code      ),
												  2 => array( 'act'   , 'style'      ),
												  3 => array( 'id'    , $IN['id']   ),
									     ), "theAdminForm", "onSubmit=\"return no_specialchars('csssheet')\""      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "20%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "80%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( $button );
		
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"Название стиля",
													$SKIN->form_input('name', $cssinfo['css_name']),
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		$SKIN->td_header[] = array( "{none}"  , "100%" );

		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Содержимое" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( 
													"<center>".$SKIN->form_textarea('css', $css, $INFO['tx'], $INFO['ty'])."<br /><a href='html/sys-img/css.html' target='_blank'>Открыть редактор стиля</a></center>",
									     )      );
												 
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
		
	}
	
	//-------------------------------------------------------------
	// EDIT COLOURS START
	//-------------------------------------------------------------
	
	function colouredit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего шаблона. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT cssid, css_text, css_name, updated FROM ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $cssinfo = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно запросить данные CSS из базы данных.");
		}
		
		$css = $cssinfo['css_text'];
		
		//+-------------------------------
		// DB same as cache version?
		//+-------------------------------
		
		$cache_file = ROOT_PATH."cache/css_".$IN['id'].".css";
		
		if ( file_exists( $cache_file ) )
		{
			$FH = fopen( $cache_file, 'r' );
			$cache_data = fread( $FH, filesize($cache_file) );
			fclose($FH);
		
			$db_length    = strlen( $css );
			$cache_length = strlen($cache_data);
			
			if ($db_length != $cache_length)
			{
				// We've got ourselves a mismatch!
				// Get mtime of cache file
				
				$stat = stat( $cache_file );
				
				$cache_mtime = $stat[9];
				$db_mtime    = $cssinfo['updated'];
				
				if ( $cache_mtime != $db_mtime and ( $db_length - $cache_length > 3 ))
				{
					$this->resync_splash($db_length, $cache_length, $cache_mtime, $db_mtime, $IN['id'], 'colouredit');
				}
			}
		}
		
		
		//+-------------------------------
		// Start the CSS matcher thingy
		//+-------------------------------
		
		//.class { definitions }
		//#id { definitions }
		
		$colours = array();
		
		// Make http:// safe..
		
		//
		
		preg_match_all( "/([\:\.\#\w\s,]+)\{(.+?)\}/s", $css, $match );
		
		for ($i=0; $i < count($match[0]); $i++)
		{
			
			$name    = trim($match[1][$i]);
			$content = trim($match[2][$i]);
			
			$defs    = explode( ';', $content );
			
			if ( count( $defs ) > 0 )
			{
				foreach( $defs as $a )
				{
					$a = trim($a);
					
					if ( $a != "" )
					{
						list( $property, $value ) = explode( ":", $a, 2 );
						
						$property = trim($property);
						$value    = trim($value);
						
						if ( $property and $value )
						{
							if ( $property == 'color' or $property == 'background-color' or $property == 'border' or $property == 'background-image' )
							{
								$colours[ $name ][$property] = $value;
							}
						}
					}
				}
			}
		}
		
		if ( count($colours) < 1 )
		{
			$ADMIN->error("Нет цветов для редактирования");
		}
		
		//+-------------------------------
		
		// Get $skin_names stuff
		
		require './sources/Admin/skin_info.php';
	
		$ADMIN->page_detail = "Ниже, Вы можете редактировать существующие цвета. <strong><a href='html/sys-img/colours.html' target='_blank'>Открыть указатель цветов</a></center></strong>";
		$ADMIN->page_title  = "Настройка стиля [ Цвета ]";
		
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'docolour'   ),
												  2 => array( 'act'   , 'style'      ),
												  3 => array( 'id'    , $IN['id']    ),
									     )    );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Свойство"     , "25%" );
		$SKIN->td_header[] = array( "&nbsp;"       , "75%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Цвета CSS" );
		
		foreach ( $colours as $prop => $val )
		{
		
			$tbl_colour = "";
			$tbl_bg     = "";
			$tbl_html   = "";
			
			$desc = $css_names[ $prop ];
			
			if ( $desc == "" )
			{
				$desc = 'Нет доступных';
			}
			
			$name = $prop;
			
			$md5 = md5($name);
			
			if ( strlen($name) > 30 )
			{
				$name = substr( $name, 0, 30 ) .'...';
			}
			
			
			
			$ADMIN->html .= $SKIN->add_td_row( array( 
														"<strong>".$name."</strong><br />($desc)",
														"<table width='100%' border='0' cellpadding='4' cellspacing='0'>
														 <tr>
														  <td width='20%'>Цвет шрифта</td><td width='30%'>".
														     $SKIN->form_simple_input('frm_'.$md5.'_color'           , $val['color'], "8")."&nbsp;&nbsp;<input type='text' size='6' style='border:1px solid black;background-color:{$val['color']}' readonly='readonly'>"
														."</td>
														  <td width='20%'>Цвет фона</td><td width='30%'>".
														     $SKIN->form_simple_input('frm_'.$md5.'_background-color', $val['background-color'], "8")."&nbsp;&nbsp;<input type='text' size='6' style='border:1px solid black;background-color:{$val['background-color']}' readonly='readonly'>"
											 			."</td>
											 			 </tr>
											 			 <tr>
											 			 <td>Граница</td><td width='30%'>".
											 			   $SKIN->form_simple_input('frm_'.$md5.'_border'          , $val['border'], "20")
											 			."</td>
											 			  <td>Изображение границы</td><td width='30%'>".
											 			   $SKIN->form_simple_input('frm_'.$md5.'_background-image', $val['background-image'], "30")
											 			."</td></tr></table>"
											 )      );
									     
		}
												 
		$ADMIN->html .= $SKIN->end_form("Сохранить изменения");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
		
	}
	
	//-------------------------------------------------------------
	// EDIT COLOURS START
	//-------------------------------------------------------------
	
	function do_colouredit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны определить ID существующего шаблона. Вернитесь назад и повторите попытку.");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT cssid, css_text, css_name, updated FROM ibf_css WHERE cssid='".$IN['id']."'");
		
		if ( ! $cssinfo = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно запросить данные CSS из базы данных.");
		}
		
		$css = $cssinfo['css_text'];
		
		//+-------------------------------
		// Start the CSS matcher thingy
		//+-------------------------------
		
		$colours = array();
		
		preg_match_all( "/([\:\.\#\w\s,]+)\{(.+?)\}/s", $css, $match );
		
		for ($i=0; $i < count($match[0]); $i++)
		{
			
			$name    = trim($match[1][$i]);
			$content = trim($match[2][$i]);
			
			$md5     = md5($name);
			
			$defs    = explode( ';', $content );
			
			if ( count( $defs ) > 0 )
			{
				foreach( $defs as $a )
				{
					$a = trim($a);
					
					if ( $a != "" )
					{
						list( $property, $value ) = explode( ":", $a, 2 );
						
						$property = trim($property);
						$value    = trim($value);
						
						if ( $property and $value )
						{
							if ( $property != 'color' and $property != 'background-color' and $property != 'border' and $property != 'background-image' )
							{
								$colours[ $name ][$property] = $value;
							}
						}
					}
				}
			}
			
			foreach( array( 'color', 'background-color', 'border', 'background-image' ) as $prop )
			{
				if ( isset($HTTP_POST_VARS['frm_'.$md5.'_'.$prop]) )
				{
					$colours[ $name ][$prop] = stripslashes($HTTP_POST_VARS['frm_'.$md5.'_'.$prop]);
				}
			}
		}
		
		if ( count($colours) < 1 )
		{
			$ADMIN->error("Нет цветов для редактирования");
		}
		
		//+-------------------------------
		
		unset($name);
		unset($property);
		
		$final = "";
		
		foreach( $colours as $name => $property )
		{
			$final .= $name." { ";
			
			if ( is_array($property) and count($property) > 0 )
			{
				foreach( $property as $key => $value )
				{
					if ( $key AND $value )
					{
						$final .= $key.": ".$value.";";
					}
				}
			}
			
			$final .= " }\n";
		
		}
		
		$barney = array( 
						 'css_text'     => $final,
						 'updated'      => time(),
					   );
					   
		$db_string = $DB->compile_db_update_string( $barney );
		
		$DB->query("UPDATE ibf_css SET $db_string WHERE cssid='".$IN['id']."'");
		
		//--------------------------------------------
		// Update cache?
		//--------------------------------------------
		
		$extra = "<b>Кэш файл обновлён</b>";
			
			if ( file_exists( ROOT_PATH."cache" ) )
			{
				if ( is_writeable( ROOT_PATH."cache" ) )
				{
					if ( $FH = @fopen( ROOT_PATH."cache/css_".$IN['id'].".css", 'w' ) )
					{
						@fputs( $FH, $css, strlen($css) );
						@fclose($FH);
					}
					else
					{
						$extra = "<b>Кэш файл не обновлён. Проверьте CHMOD атрибуты для ./cache и ./cache/css_".$IN['id'].".css</b>";
					}
				}
				else
				{
					$extra = "<b>Кэш файл не обновлён. Проверьте CHMOD атрибуты для ./cache и ./cache/css_".$IN['id'].".css</b>";
				}
			}
			else
			{
				$extra = "<b>Кэш файл не обновлён. Кэш папка отсутствует.</b>";
			}
		
		$ADMIN->nav[] = array( 'act=style' ,'Главная страница настройки стиля' );
		$ADMIN->nav[] = array( "act=style&code=edit2&id={$IN['id']}" ,"Повторное редактирование стиля (Расширенное)" );
		$ADMIN->nav[] = array( "act=style&code=colouredit&id={$IN['id']}" ,"Редактировать цвета повторно?" );
		
		$ADMIN->done_screen("Стиль обновлён : $extra", "Настройка стиля", "act=style" );
			
		
		
		
	}
	
	//-------------------------------------------------------------
	// SHOW STYLE SHEETS
	//-------------------------------------------------------------
	
	function list_sheets()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
		$show_array = array();
	
		$ADMIN->page_detail = "Здесь Вы можете добавлять, редактировать или удалять стили.<br><br>Стили являются файлами CSS. Это те файлы, в которых Вы можете изменять цвета, шрифты, размеры шрифтов и другие параметры форума.";
		$ADMIN->page_title  = "Настройка стиля";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Название"  , "40%" );
		$SKIN->td_header[] = array( "Использование"   , "20%" );
		$SKIN->td_header[] = array( "Оптимизация" , "10%" );
		$SKIN->td_header[] = array( "Скачать" , "10%" );
		$SKIN->td_header[] = array( "Редактировать"   , "10%" );
		$SKIN->td_header[] = array( "Удалить" , "10%" );
		
		//+-------------------------------
		
		$DB->query("SELECT DISTINCT(c.cssid), c.css_name, s.sname from ibf_css c, ibf_skins s WHERE s.css_id=c.cssid ORDER BY c.css_name ASC");
		
		$used_ids = array();
		
		if ( $DB->get_num_rows() )
		{
		
			$ADMIN->html .= $SKIN->start_table( "Текущие используемые стили" );
			
			while ( $r = $DB->fetch_row() )
			{
			
				$show_array[ $r['cssid'] ] .= stripslashes($r['sname'])."<br>";
			
				if ( in_array( $r['cssid'], $used_ids ) )
				{
					continue;
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['css_name'])."</b>",
														  "<#X-{$r['cssid']}#>",
														  "<center><a href='".$SKIN->base_url."&act=style&code=optimize&id={$r['cssid']}'>Оптимизировать</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=style&code=export&id={$r['cssid']}'>Скачать</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=style&code=edit&id={$r['cssid']}'>Редактировать</a></center>",
														  "<i>Освободить перед удалением</i>",
												 )      );
												   
				$used_ids[] = $r['cssid'];
				
				$form_array[] = array( $r['cssid'], $r['css_name'] );
				
			}
			
			foreach( $show_array as $idx => $string )
			{
				$string = preg_replace( "/<br>$/", "", $string );
				
				$ADMIN->html = preg_replace( "/<#X-$idx#>/", "$string", $ADMIN->html );
			}
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		if ( count($used_ids) > 0 )
		{
		
			$DB->query("SELECT cssid, css_name FROM ibf_css WHERE cssid NOT IN(".implode(",",$used_ids).")");
		
			if ( $DB->get_num_rows() )
			{
			
				$SKIN->td_header[] = array( "Название"  , "60%" );
				$SKIN->td_header[] = array( "Оптимизация" , "10%" );
				$SKIN->td_header[] = array( "Скачать" , "10%" );
				$SKIN->td_header[] = array( "Редактировать"   , "10%" );
				$SKIN->td_header[] = array( "Удалить" , "10%" );
			
				$ADMIN->html .= $SKIN->start_table( "Текущие неиспользуемые стили" );
				
				$ADMIN->html .= $SKIN->js_checkdelete();
				
				
				while ( $r = $DB->fetch_row() )
				{
					
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['css_name'])."</b>",
					 										  "<center><a href='".$SKIN->base_url."&act=style&code=optimize&id={$r['cssid']}'>Оптимизировать</a></center>",
					 										  "<center><a href='".$SKIN->base_url."&act=style&code=export&id={$r['cssid']}'>Скачать</a></center>",
															  "<center><a href='".$SKIN->base_url."&act=style&code=edit&id={$r['cssid']}'>Редактировать</a></center>",
															  "<center><a href='javascript:checkdelete(\"act=style&code=remove&id={$r['cssid']}\")'>Удалить</a></center>",
													 )      );
													 
					$form_array[] = array( $r['cssid'], $r['css_name'] );
													   
				}
				
				$ADMIN->html .= $SKIN->end_table();
			}
		}
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add'      ),
												  2 => array( 'act'   , 'style'    ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Создание нового стиля (Копия)" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Создать новый стиль на основе...</b>" ,
										  		  $SKIN->form_dropdown( "id", $form_array)
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("Создать копию");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'css_upload' ),
												  2 => array( 'act'   , 'style'     ),
												  3 => array( 'MAX_FILE_SIZE', '10000000000' ),
									     ) , "uploadform", " enctype='multipart/form-data'"     );
									     
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Загрузка нового стиля" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите файл с Вашего диска</b>" ,
										  		  $SKIN->form_upload()
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("Загрузить новый стиль");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	
}


?>