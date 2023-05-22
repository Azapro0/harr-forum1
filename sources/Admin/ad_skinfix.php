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
|   > Fix Skin Functions
|   > Module written by Matt Mecham
|   > Date started: 21st September 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}

$idx = new ad_skinfix();


class ad_skinfix {

	var $base_url;
	var $db_html_files = "";
	var $ff_html_files = "";
	var $skin_id       = "";
	var $ff_fixes      = array();
	var $log           = array();

	function ad_skinfix()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_detail = "Здесь Вы можете откорректировать любые старые файлы скинов с самых последних файлов скинов, последнего выпуска IPB, для настройки их совместимости.<br /><br>
							   Просто скачайте самые последние списки скинов с нашего сервера и установите их. Затем запустите списки для начала процесса сравнения и обновления любых старых скин файлов.";
		$ADMIN->page_title  = "Проверка версии скина";

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
			
			case 'importurl':
				$this->import_ipb_file();
				break;
				
			case 'runtool':
				$this->run_tool();
				break;
			
			case 'delete':
				$this->delete_list();
				break;
			
			//-------------------------
			default:
				$this->list_current();
				break;
		}
		
	}
	
	//-------------------------------------------------------------
	// Delete local list-y-poooos
	//-------------------------------------------------------------
	
	function delete_list()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ( ! $IN['id'] )
		{
			$this->list_current("ID не передано, удаление невозможно");
		}
		
		if ( @unlink( ROOT_PATH . 'cache/'. $IN['id'] ) )
		{
			$this->list_current("Кэш файл удалён");
		}
		else
		{
			$this->list_current("Невозможно удалить кэш файл. Недостаточные атрибуты на запись.");
		}
	}
	
	
	
	//-------------------------------------------------------------
	// RUN THE TOOL:UPDATE MINOR: Update all bug fixes, etc
	//-------------------------------------------------------------
	
	function tool_update_updates()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//------------------------------
		// Find missing template bits
		//------------------------------
		
		$affected = array();
		
		foreach( $this->ff_fixes as $skin_file => $data )
		{
			foreach( $this->ff_fixes[ $skin_file ] as $section => $desc )
			{
				if ( $desc['isnew'] == 1 )
				{
					//------------------------------
					// Check to see if it's still missing...
					//------------------------------
					
					if ( $this->db_html_files[ $skin_file ][ $section ] )
					{
						//------------------------------
						// It's not, so fu.. continue
						//------------------------------
						
						continue;
					}
				}
				
				$data    = $this->ff_html_files[ $skin_file ][ $section ][ 'func_data' ];
				$content = $this->ff_html_files[ $skin_file ][ $section ][ 'section_content' ];
				
				$this->_update_section($skin_file, $section, $data, $content);
																	 
				$affected[$skin_file] = $skin_file;
			}
		}
		
		if ( count($affected) < 1 )
		{
			$this->log[] = "Невозможно обновить файлы шаблонов, обновление не произведено";
		}
		else
		{
			foreach( $affected as $sk )
			{
				$this->_update_php_file($sk);
			}
		}
		
		$this->write_log("Результаты обновления всех исправленных секций");
	}
	
	//-------------------------------------------------------------
	// RUN THE TOOL:UPDATE MAJOR: Update all missing templates
	//-------------------------------------------------------------
	
	function tool_updatemajor()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//------------------------------
		// Find missing template bits
		//------------------------------
		
		$affected = array();
		
		foreach( $this->ff_html_files as $skin_file => $data )
		{
			foreach( $this->ff_html_files[ $skin_file ] as $id => $ndata )
			{
				if ( ! $this->db_html_files[ $skin_file ][ $ndata['func_name'] ] )
				{
					$this->_update_section($skin_file, $ndata['func_name'], $ndata['func_data'], $ndata['section_content']);
																		 
					$affected[$skin_file] = $skin_file;
				}
			}
		}
		
		if ( count($affected) < 1 )
		{
			$this->log[] = "Невозможно обновить файлы шаблонов, обновление не произведено";
		}
		else
		{
			foreach( $affected as $sk )
			{
				$this->_update_php_file($sk);
			}
		}
		
		$this->write_log("Результаты обновления всех недостающих секций");
	}
	
	
	//-------------------------------------------------------------
	// RUN THE TOOL:VIEW CODE, View the code changes
	//-------------------------------------------------------------
	
	function tool_view_code()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$code = $this->ff_html_files[ $IN['skinfile'] ][ $IN['func'] ]['section_content'];
		
		if ( $code == "" )
		{
			$ADMIN->error("Невозможно отобразить код. Проверьте все скин файлы, названия функций и ID скинов");
		}
		
		$code = str_replace( "\n\n", "\n", str_replace( "\r", "\n", $code ) );
		
		if ( $IN['type'] == 'php' )
		{
			$code = 'function '.$IN['func'].'('.$this->ff_html_files[ $IN['skinfile'] ][ $IN['func'] ]['func_data'].') {'."\rglobal \$ibforums;\r".'return <<<EOF'."\r".
					$code .
					"\r".'EOF;'."\r}";
		}
		else
		{
			$code = $this->convert_tags($code);
		}
		
		if ( phpversion() >= '4.2.0' )
		{
			@error_reporting(0);
			
			$buffer = highlight_string( "<?php\r".$code."\r?>", TRUE );
			$buffer = preg_replace( "#(?:<|&lt;)\?php\r?(.*)\r?\?(?:>|&gt;)#s", "\\1", $buffer );
		}
		else
		{
			$buffer = htmlspecialchars($code);
		}
		
		
		$SKIN->td_header[] = array( "&nbsp;", "100%" );
		
		$ADMIN->html .= $SKIN->start_table( "{$IN['skinfile']} -&gt; {$IN['func']}" );
		
		$ADMIN->html .= $SKIN->add_td_basic( "<div style='font-family:monaco, courier;font-size:12px'><pre>".$buffer."</pre></div>", "left");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
	
	}
	
	//-------------------------------------------------------------
	// RUN THE TOOL:COMPARE, Show changes - do nothing
	//-------------------------------------------------------------
	
	function tool_compare()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_detail .= "<br /><br /><b>Имейте ввиду: При обновлении кодов шаблонов, будут полностью перезаписаны все существующие коды шаблонов. Если у Вас очень модифицированный скин, Вы можете вручную произвести 
							   изменения в кодах шаблонов для корректировки и интеграции с Вашим скином. Если у Вас имеется таковой модифицированный скин, рекомендуем Вам с целью безопасности произвести <a href='{$ADMIN->base_url}&act=templ&code=export&id={$this->skin_id}'>экспорт</a> скина, перед началом изменений.";
		
		$ADMIN->page_detail .= "<br /><br /><span style='color:darkorange'><b>База HTML шаблонов: {$this->db_skin['skname']}<br />Список скинов Invision Power Board: {$IN['list']}</b></span>";
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		$changes = array();
		
		//------------------------------
		// Find missing template bits
		//------------------------------
		
		$cnt = 0;
		
		foreach( $this->ff_html_files as $skin_file => $data )
		{
			foreach( $this->ff_html_files[ $skin_file ] as $id => $ndata )
			{
				if ( ! $this->db_html_files[ $skin_file ][ $ndata['func_name'] ] )
				{
					$changes[ $skin_file ][ $ndata['func_name'] ] = $ndata['func_name'];
					$cnt++;
				}
			}
		}
		
		//-----------------------------------------
		// Too many ch-ch-ch-changes?
		//-----------------------------------------
		
		if ( $cnt > 100 )
		{
			$ADMIN->page_detail .= "<br /><br /><span style='color:red;font-weight:bold'>Имеется $cnt недостающих секций, что означает, что Ваш шаблон сильно устаревший и возможно, обновление не увенчается успехом.</span>";
		}
		
		$SKIN->td_header[] = array( "Название файла скина"    , "25%" );
		$SKIN->td_header[] = array( "Функция"     , "30%" );
		$SKIN->td_header[] = array( "Отчёт"       , "20%" );
		$SKIN->td_header[] = array( "Просмотр кода"    , "25%" );
		
		$ADMIN->html .= $SKIN->start_table( "Недостающая секция в {$this->db_skin['skname']}" );
		
		if ( count($changes) > 0 )
		{
			foreach( $changes as $skin_file => $data )
			{
				foreach( $changes[ $skin_file ] as $fun )
				{
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$skin_file}</b>",
															  $fun,
															  "<i>Недостача в локальном шаблоне</i>",
															  "<center>Просмотр <a href='javascript:pop_win(\"&act=skinfix&code=runtool&tool=viewcode&list={$IN['list']}&skin={$IN['skin']}&skinfile={$skin_file}&func=$fun&type=php&m=db\", \"Просмотр кода\", \"700\",\"400\");'>PHP</a>
															  &middot; <a href='javascript:pop_win(\"&act=skinfix&code=runtool&tool=viewcode&list={$IN['list']}&skin={$IN['skin']}&skinfile={$skin_file}&func=$fun&type=html&m=db\", \"Просмотр кода\", \"700\",\"400\");'>HTML</a></center>"
													 )      );
				}
			}
			
			$ADMIN->html .= $SKIN->add_td_basic( "<a href='{$SKIN->base_url}&act=skinfix&code=runtool&tool=updatemajor&skin={$IN['skin']}&list={$IN['list']}' class='fauxbutton'>Обновление недостающих секций</a>", "center");
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic( "Недостающих секций не обнаружено", "center");
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//-----------------------------------------
		// Show bug fixes
		//-----------------------------------------
		
		$look_up_func  = array();
		$look_up_group = array();
		$look_up_final = array();
		
		$SKIN->td_header[] = array( "Название файла скина"    , "40%" );
		$SKIN->td_header[] = array( "Функция"     , "20%" );
		$SKIN->td_header[] = array( "Отчёт"       , "20%" );
		$SKIN->td_header[] = array( "Просмотр кода"    , "20%" );
		
		$ADMIN->html .= $SKIN->start_table( "Определены обновления для {$IN['list']}" );
		
		foreach( $this->ff_fixes as $skin_file => $data )
		{
			foreach( $this->ff_fixes[ $skin_file ] as $section => $desc )
			{
				if ( $desc['isnew'] == 1 )
				{
					// Check to see if it's still missing...
					
					if ( $this->db_html_files[ $skin_file ][ $section ] )
					{
						continue;
					}
				}
				
				$look_up_func[]  = $section;
				$look_up_group[] = $skin_file;
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$skin_file}</b><!--X:$skin_file,$section--><!--Y:$skin_file,$section-->",
														  $section,
														  $desc['desc'],
														  "<center>Просмотр <a href='javascript:pop_win(\"&act=skinfix&code=runtool&tool=viewcode&list={$IN['list']}&skin={$IN['skin']}&skinfile={$skin_file}&func=$section&type=php&m=ff\", \"Просмотр кода\", \"700\",\"400\");'>PHP</a>
														  &middot; <a href='javascript:pop_win(\"&act=skinfix&code=runtool&tool=viewcode&list={$IN['list']}&skin={$IN['skin']}&skinfile={$skin_file}&func=$section&type=html&m=ff\", \"Просмотр кода\", \"700\",\"400\");'>HTML</a></center>"
												 )      );
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_basic( "<a href='{$SKIN->base_url}&act=skinfix&code=runtool&tool=updateupdates&skin={$IN['skin']}&list={$IN['list']}' class='fauxbutton'>Обновление изменений нового выпуска</a>", "center");
		
		$ADMIN->html .= $SKIN->end_table();
		
		//-----------------------------------------
		// PARSE IN UPDATE TIME
		//-----------------------------------------
		
		if ( count($look_up_func) > 0 )
		{
			$DB->query("SELECT updated, suid, group_name, func_name, section_content FROM ibf_skin_templates WHERE group_name IN('".implode( "','", $look_up_group )."') AND
					    func_name IN('".implode( "','", $look_up_func )."') AND set_id={$this->skin_id}");
					    
			while ( $r = $DB->fetch_row() )
			{
				$ADMIN->html = str_replace( "<!--Y:{$r['group_name']},{$r['func_name']}-->", "<br /><span style='font-size:9px;color:#666'>Локальная версия соответствует отдалённой версии?: ".$this->_is_it_or_isnt_it($r['section_content'], $this->ff_html_files[$r['group_name']][$r['func_name']]['section_content'])."</span>", $ADMIN->html );
				$ADMIN->html = str_replace( "<!--X:{$r['group_name']},{$r['func_name']}-->", "<br /><span style='font-size:9px;color:#666'>Последнее обновление локальной версии: ".$std->get_date( $r['updated'], 'SHORT' )."</span>", $ADMIN->html );
			}
		}
		
		$ADMIN->output();
		
	}
	
	//-------------------------------------------------------------
	// RUN THE TOOL
	//-------------------------------------------------------------
	
	function run_tool()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$tool = trim($IN['tool']);
		$skin = intval(trim($IN['skin']));
		$list = trim($IN['list']);
		
		//-----------------------------------
		// GET DB STUFF
		//-----------------------------------
		
		$DB->query("SELECT * FROM ibf_skin_templates WHERE set_id=$skin");
		
		while ( $t = $DB->fetch_row() )
		{
			$this->db_html_files[ $t['group_name'] ][ $t['func_name'] ] = array( 'func_name'       => $t['func_name'],
																				 'func_data'       => $t['func_data'],
																				 'section_content' => $t['section_content']
																			   );
		}
		
		$DB->query("SELECT * FROM ibf_tmpl_names WHERE skid=$skin");
		
		$this->db_skin = $DB->fetch_row();
		
		//-----------------------------------
		// LOAD CACHE FILE
		//-----------------------------------
		
		$file = ROOT_PATH . 'cache/'.$list;
		
		if ( ! file_exists( $file ) )
		{
			$ADMIN->error("Невозможно найти $list в директории, 'cache'");
		}
		
		$fh = fopen( $file, 'r' );
		$raw = @fread( $fh, filesize( $file ) );
		@fclose($fh);
		
		//-----------------------------------
		// GET FIRST LINE CONF
		//-----------------------------------
		
		preg_match( "#<!--ST\@CONF-->(.+?)<!--END\@CONF-->#is", $raw, $match );
		
		$first_cut = explode( '|||', $match[1] );
		
		foreach( $first_cut as $d )
		{
			list( $skin_file, $section_desc ) = explode( '&', $d );
			list( $section, $desc2 ) = explode( '|~|', $section_desc );
			list( $desc, $isnew ) = explode( '|^|', $desc2 );
			
			$this->ff_fixes[ str_replace( '.php', '', $skin_file ) ][ $section ] = array( 'desc' => $desc, 'isnew' => $isnew );
		}
		
		//-----------------------------------
		// PARSE FLAT FILE
		//-----------------------------------
		
		preg_match_all( "/<!--IBF_GROUP_START:(\S+?)-->(.+?)<!--IBF_GROUP_END:\S+?-->/s", $raw, $match );
		
		for ($i=0; $i < count($match[0]); $i++)
		{
			$skin_file   = trim($match[1][$i]);
			
			$raw         = trim($match[2][$i]);
			
			//--------------------------------
			// Remove everything up until the
			// first <!--START tag...
			//--------------------------------
			
			$raw = preg_replace( "/^.*?(<!--IBF_START_FUNC)/s", "\\1", trim($raw));
			
			$raw = str_replace( "\r\n", "\n", $raw);
			
			//+-------------------------------
			// Convert the tags back to php native
			//+-------------------------------
			
			$raw = $this->unconvert_tags($raw);
			
			$master = array();
			$flag   = 0;
			
			$eachline = explode( "\n", $raw );
			
			foreach ($eachline as $line)
			{
				if ($flag == 0)
				{
					// We're not gathering HTML, lets see if we have a new
					// function start..
					
					if ( preg_match( "/\s*<!--IBF_START_FUNC\|(\S+?)\|(.*?)-->\s*/", $line, $matches) )
					{
						$func = trim($matches[1]);
						$data = trim($matches[2]);
						
						if ($func != "")
						{
							$flag = $func;
							
							$master[$func] = array( 'func_name'        => $func,
													'func_data'        => $data,
													'section_content'  => ""
												  );
						}
						continue;
					}
				}
				
				if ( preg_match("/\s*?<!--IBF_END_FUNC\|$flag-->\s*?/", $line) )
				{
					 // We have found the end of the subbie..
					 // Reset the flag and feed the next line.
					 
					 $flag = 0;
					 continue;
				}
				else
				{
					// Carry on feeding the HTML...
					
					if ( isset($master[$flag]['section_content']) )
					{
						$master[$flag]['section_content'] .= $line."\n";
						continue;
					}
				}
			}
			
			$this->ff_html_files[ $skin_file ] = $master;
		}
		
		$this->skin_id = $skin;
		
		//-----------------------------------
		// Do summink
		//-----------------------------------
		
		switch( $tool )
		{
			case 'updatemajor':
				$this->tool_updatemajor();
				break;
			case 'updateall':
				$this->tool_updateall();
				break;
			case 'updateupdates':
				$this->tool_update_updates();
				break;
			case 'viewcode':
				$this->tool_view_code();
				break;
			default:
				$this->tool_compare();
				break;
		}
	}
	
	
	//-------------------------------------------------------------
	// IMPORT IPB PLIST FILE
	//-------------------------------------------------------------
	
	function import_ipb_file()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$file = preg_replace( "/[^a-zA-Z0-9\-\_\.]/", "" , str_replace( "..", "", $IN['id'] ) );
		
		if ( ! preg_match( "/\.plist$/", $file ) )
		{
			$file = "";
		}
		
		if ( $file == "" )
		{
			$ADMIN->error("Файл не передан, либо содержит недопустимые символы и продолжение невозможно.");
		}
		
		//--------------------------------------
		// Grab it you pervert!
		//--------------------------------------
		
		$grab_meh_bits = "http://www.invisionboard.com/download/skinlist.php?dl=".$file;
		
		$data     = array();
		$contents = "";
		
		if ( ! $data = @file( $grab_meh_bits ) )
		{
			$ADMIN->error("Вероятно, происходит ошибка при выборе списка из основного сервера Invision Power Board. Причина этого, может быть в недоступности сервера на данный момент, 
			     либо в том, что Ваша версия PHP не поддерживает дистанционное чтение файлов.
			     <br />Попробуйте перейти непосредственно <a href='http://www.invisionboard.com/download/skingetlist.php'>на сервер </a> для самостоятельного скачивания этого файла оттуда.");
		}
		
		if ( count($data) < 1 )
		{
			$ADMIN->error("Вероятно, происходит ошибка при выборе списка из основного сервера Invision Power Board. Причина этого, может быть в недоступности сервера на данный момент, 
			     либо в том, что Ваша версия PHP не поддерживает дистанционное чтение файлов.
			     <br />Попробуйте перейти непосредственно <a href='http://www.invisionboard.com/download/skingetlist.php'>на сервер </a> для самостоятельного скачивания этого файла оттуда.");
		}
		
		$contents = implode( '', $data );
		
		//--------------------------------------
		// Write it to the local directory
		//--------------------------------------
		
		if ( $FH = fopen( ROOT_PATH . 'cache/' . $file, 'w' ) )
		{
			fwrite( $FH, $contents );
			fclose( $FH );
		}
		
		@chmod( ROOT_PATH . 'cache/' . $file, 0777 );
		
		//--------------------------------------
		// Pass back with thanks :D
		//--------------------------------------
		
		$this->list_current( "<li>$file успешно импортирован");
	
	}
	
	
	//-------------------------------------------------------------
	// SHOW MAIN SCREEN
	//-------------------------------------------------------------
	
	function list_current($msg="")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
	
				
		//--------------------------------------
		// Get PLIST from invisionboard.com server
		//--------------------------------------
		
		$file = 'http://www.invisionboard.com/download/skinlist.php';
		$emsg = "<li>Вероятно, происходит ошибка при выборе списка из основного сервера Invision Power Board. Причина этого, может быть в недоступности сервера на данный момент, 
			     либо в том, что Ваша версия PHP не поддерживает дистанционное чтение файлов.
			     <br />Попробуйте перейти непосредственно <a href='http://www.invisionboard.com/download/skingetlist.php'>на сервер </a> для самостоятельного скачивания этого файла оттуда.";
		
		$err     = 0;
		$nocache = 0;
		$data    = array();
		
		if ( $data = @file( $file ) )
		{
			if ( count($data) < 1 )
			{
				$err = 1;
			}
			else
			{
				$list = implode( '' , $data );
				
				//-------------------------------------------------
				// Divide the file up into different sections
				//-------------------------------------------------
				
				preg_match_all( "#<entry>(.+?)</entry>#is", $list, $match );
				
				$master = array();
				
				for ($i=0; $i < count($match[0]); $i++)
				{
					$main = trim($match[1][$i]);
					
					preg_match( "#<name>(.+?)</name>#is", $main, $m2 );
					
					$name = trim($m2[1]);
					
					preg_match( "#<updatefile>(.+?)</updatefile>#is", $main, $m2 );
					
					$updatefile = trim($m2[1]);
					
					preg_match( "#<time>(.+?)</time>#is", $main, $m2 );
					
					$time = trim($m2[1]);
					
					$master[ $updatefile ] = array(
											  'name'       => $name,
											  'updatefile' => $updatefile,
											  'time'	   => $time
											);
				}
				
				if ( count( $master ) < 1 )
				{
					$err = 1;
				}
			}
			
		}
		else
		{
			$err = 1;
		}
		
		//--------------------------------------
		// Cache directory present and writeable?
		//--------------------------------------
		
		if ( $err )
		{
			$msg .= $emsg;
		}
		
		if ( ! file_exists( ROOT_PATH . 'cache' ) )
		{
			$msg .= "<li>Невозможно найти ./cache. Проверьте наличие этой директории на Вашем сервере, в корневой директории форума.";
			$nocache = 1;
		}
		
		if ( ! is_writeable( ROOT_PATH . 'cache' ) )
		{
			$msg .= "<li>Невозможно произвести запись в ./cache. Проверьте CHMOD атрибуты этой директории и при необходимости исправьте их.";
		}
		
		if ( $msg )
		{
			$SKIN->td_header[] = array( "&nbsp;"  , "100%" );
			
			$ADMIN->html .= $SKIN->start_table( "Сообщение" );
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<ul>".$msg."</ul>" ) );
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		//--------------------------------------
		// Cache exists, read from it
		//--------------------------------------
		
		$local = array();
		
		if ( $nocache != 1 )
		{
			$dh = opendir( ROOT_PATH . 'cache' );
 		
			while ( $file = readdir( $dh ) )
			{
				if ( $file != "." && $file != ".." )
				{
					if ( preg_match( "#\.plist$#", $file ) )
					{
						$local[ $file ] = 1;
					}
				}
			}
			
 			closedir( $dh );
		}
		
		//--------------------------------------
		// if no error....
		//--------------------------------------
		
		if ( $err != 1 )
		{
			$SKIN->td_header[] = array( "Название"         , "40%" );
			$SKIN->td_header[] = array( "Обновлён"      , "30%" );
			$SKIN->td_header[] = array( "Скачать"     , "30%" );
			
			$ADMIN->html .= $SKIN->start_table( "ОТДАЛЁННЫЙ список из 'invisionboard.com'" );
			
			foreach( $master as $name => $data )
			{
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$data['name']}</b>",
														  "<center>{$data['time']}</center>",
														  "<center><a href='".$SKIN->base_url."&act=skinfix&code=importurl&id={$data['updatefile']}'>Скачать</a></center>",
												 )      );
			}
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		//--------------------------------------
		// Show local cache files...
		//--------------------------------------
		
		$tool_cache = array();
		
		$SKIN->td_header[] = array( "Выпуск"      , "30%" );
		$SKIN->td_header[] = array( "Название файла"    , "20%" );
		$SKIN->td_header[] = array( "Обновлён"      , "30%" );
		$SKIN->td_header[] = array( "Удалить"       , "20%" );
			
		$ADMIN->html .= $SKIN->start_table( "ЛОКАЛЬНЫЙ список из 'cache'" );
		
		if ( count($local) > 0 )
		{
			foreach( $local as $name => $data )
			{
				
				$tool_cache[] = array( $name, $master[ $name ]['name'] );
				
				@clearstatcache();
				
				$stat = @stat( ROOT_PATH . 'cache/'.$name );
				
				$mtime = gmdate( "j.m.Y - H:i",  $stat[9] );
			
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$master[ $name ]['name']}</b>",
														  "$name",
														  "<center>$mtime</center>",
														  "<center><a href='".$SKIN->base_url."&act=skinfix&code=delete&id=$name'>Удалить</a></center>",
												 )      );
			}
			
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic( "Невозможно обнаружить списки скин файлов в директори 'cache'", "center");
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//--------------------------------------
		// Show tool box
		//--------------------------------------
		
		$tools   = array();
		$skins   = array();
		$tools[] = array( 'compare'    , 'Сравнить и показать результат без изменений скина');
		$tools[] = array( 'updatemajor', 'Обновить, с добавлением ТОЛЬКО новых секций' );
		$tools[] = array( 'updateall'  , 'Обновить, с изменением ВСЕХ изменённых секций');
		
		$DB->query("SELECT t.skid, t.skname from ibf_tmpl_names t ORDER BY t.skname ASC");
		
		while ( $r = $DB->fetch_row() )
		{
			$skins[] = array( $r['skid'], $r['skname'] );
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'act'  , 'skinfix'  ),
												  2 => array( 'code' , 'runtool'  ),
												  3 => array( 'tool' , 'compare'  ),
									     )      );
									     
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		
		$ADMIN->html .= $SKIN->start_table( "Проверка версии скина" );
		
		if ( count($local) > 0 )
		{
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать список...</b>",
													  $SKIN->form_dropdown( 'list', $tool_cache )
											 )      );
											 
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сравнить изменения в HTML шаблонах...</b>",
													  $SKIN->form_dropdown( 'skin', $skins )
											 )      );
			
			
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic( "Невозможно обнаружить списки скин файлов в директори 'cache', эта секция не будет активна до тех пор, пока Вы не загрузите какие-нибудь файлы", "center");
		}
		
		$ADMIN->html .= $SKIN->end_form("Показать различия");
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
	
	
	
	
	function convert_tags($t="")
	{
		if ($t == "")
		{
			return "";
		}
		
		$t = preg_replace( "/{?\\\$ibforums->base_url}?/"            , "{ibf.script_url}"   , $t );
		$t = preg_replace( "/{?\\\$ibforums->session_id}?/"          , "{ibf.session_id}"   , $t );
		$t = preg_replace( "/{?\\\$ibforums->skin\['?(\w+)'?\]}?/"   , "{ibf.skin.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->lang\['?(\w+)'?\]}?/"   , "{ibf.lang.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->vars\['?(\w+)'?\]}?/"   , "{ibf.vars.\\1}"      , $t );
		$t = preg_replace( "/{?\\\$ibforums->member\['?(\w+)'?\]}?/" , "{ibf.member.\\1}"    , $t );
		
		// Make some tags safe..
		
		$t = preg_replace( "/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", "" , $t );
				
		return $t;
		
	}
	
	function unconvert_tags($t="")
	{
		if ($t == "")
		{
			return "";
		}
		
		// Make some tags safe..
		
		$t = preg_replace( "/\{ibf\.vars\.(sql_driver|sql_host|sql_database|sql_pass|sql_user|sql_port|sql_tbl_prefix|smtp_host|smtp_port|smtp_user|smtp_pass|html_dir|base_dir|upload_dir)\}/", "" , $t );
		
		$t = preg_replace( "/{ibf\.script_url}/i"   , '{$ibforums->base_url}'         , $t);
		$t = preg_replace( "/{ibf\.session_id}/i"   , '{$ibforums->session_id}'       , $t);
		$t = preg_replace( "/{ibf\.skin\.(\w+)}/"   , '{$ibforums->skin[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.lang\.(\w+)}/"   , '{$ibforums->lang[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.vars\.(\w+)}/"   , '{$ibforums->vars[\''."\\1".'\']}'   , $t);
		$t = preg_replace( "/{ibf\.member\.(\w+)}/" , '{$ibforums->member[\''."\\1".'\']}' , $t);
		
		return $t;
		
	}
	
	
	//-------------------------------------------------------------
	// WRITE OUT RESULTS
	//-------------------------------------------------------------
	
	function write_log($title="Результат")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$SKIN->td_header[] = array( "&nbsp;"  , "100%" );
			
		$ADMIN->html .= $SKIN->start_table( $title );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<ul><li>".implode( '<li>', $this->log )."</ul>" ) );
		
		$ADMIN->html .= $SKIN->add_td_basic( "<a href='{$SKIN->base_url}&act=skinfix' class='fauxbutton'>Главная страница проверки версии скинов</a>", "center");

		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// @ UPDATE DB WITH TEMPLATE CHANGES
	//-------------------------------------------------------------
	
	function _update_section($skin_file, $func_name, $func_data, $func_content)
	{
		global $IN, $DB, $std, $INFO;
		
		$skin_file = str_replace( ".php", "", $skin_file );
		
		if ( ! $this->skin_id )
		{
			$this->log[] = "Процесс прерван, ID скина не определен, невозможно обновить шаблон для $skin_file / $func_name";
			return;
		}
		
		//---------------------------------------
		// Can we write? YES WE CAN!
		// Can we get car credit? YES WE CAN!
		// Can we copy the 'Kings of Leon' CD to our computers? NO WE CAN'T
		// Shall we boycott the 'Kings of Leon' CD? YES WE WILL!
		//---------------------------------------
		
		$file = ROOT_PATH . 'Skin/s'. $this->skin_id.'/'.$skin_file.'.php';
		
		If ( $INFO['safe_mode_skins'] != 1 )
		{
			if ( ! is_writeable( $file ) )
			{
				if ( file_exists( $file ) )
				{
					$this->log[] = "Невозможно произвести запись в '$file', проверьте CHMOD атрибуты. Если у Вас в PHP включён режим safe mode, убедитесь в том, что Вы включили безопасный режим для скинов.";
					return;
				}
			}
		}
		
		//---------------------------------------
		// Delete old template from DB
		//---------------------------------------
		
		$DB->query("DELETE FROM ibf_skin_templates WHERE set_id={$this->skin_id} AND group_name='$skin_file' AND func_name='$func_name'");
		
		//---------------------------------------
		// Insert meh new record
		// I SAY BO! You Say SELECTA!
		// Can I get a reeeeeeewind?!?!
		//---------------------------------------
		
		$dbs = $DB->compile_db_insert_string( array(
													 'set_id'          => $this->skin_id,
													 'group_name'      => $skin_file,
													 'section_content' => str_replace( '\\n' , '\\\\\\n', $func_content ),
													 'func_name'	   => $func_name,
													 'func_data'	   => $func_data,
													 'updated'		   => time()
										   )       );
		
		$DB->query("INSERT INTO ibf_skin_templates ({$dbs['FIELD_NAMES']}) VALUES({$dbs['FIELD_VALUES']})");
		
		$this->log[] = "<span style='color:green'>$skin_file -&gt; $func_name успешно обновлён</span>";
		return;
	}
	
	//-------------------------------------------------------------
	// @ UPDATE A PHP FILE
	//-------------------------------------------------------------
	
	function _update_php_file($group)
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$group = str_replace( ".php", "", $group );
		
		$skin_dir  = ROOT_PATH."Skin/s".$this->skin_id;
		
		//-------------------------------------------
		// If we are not using safe mode skins, lets
		// run away!
		//-------------------------------------------
		
		if ( $INFO['safe_mode_skins'] == 1 OR SAFE_MODE_ON == 1 )
		{
			$this->log[] = "Установлен безопасный режим скинов. Обновление {$skin_file}.php не требуется...";
			return;
		}
		
		$final = "<"."?php\n\n".
				 "class $group {\n\n";
		
		
		$DB->query("SELECT * FROM ibf_skin_templates WHERE set_id={$this->skin_id} AND group_name='$group'");
		
		while( $data = $DB->fetch_row() )
		{
		
			$final .= "\n\nfunction ".trim($data['func_name'])."(".trim($data['func_data']).") {\n".
					  "global \$ibforums;\n".
					  "return <<<EOF\n";
					  
			$final .= trim($data['section_content']);
				   
			$final .= "\nEOF;\n}\n";
			
		}
		
		$final .= "\n\n}\n?".">";
		
		if ($fh = fopen( $skin_dir."/".$group.".php", 'w' ) )
		{
			fwrite($fh, $final, strlen($final) );
			fclose($fh);
			@chmod( $skin_dir."/".$group.".php", 0777 );
			
			$this->log[] = "<span style='color:darkgreen;font-weight:bold'>{$group}.php успешно обновлён</span>";
		}
		else
		{
			$this->log[] = "Невозможно сохранить информацию в {$group}.php, проверьте правильность CHMOD атрибутов на возможность создания файлов в директории.";
		}
				
		return TRUE;
	}
	
	//-------------------------------------------------------------
	// @ DETERMINE CHANGES
	//-------------------------------------------------------------
	
	function _is_it_or_isnt_it($remote, $local)
	{
		$remote = preg_replace( "/[\s\t\n\r]/s", "", strtolower($remote) );
		$local  = preg_replace( "/[\s\t\n\r]/s", "", strtolower($local) );
		
		$yes = "<span style='color:green'>Yes</span>";
		$no  = "<span style='color:red'>no</span>";
		
		return $remote == $local ? $yes : $no;
	
	}
	
	
	
	
}


?>