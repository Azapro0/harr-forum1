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
|   > Admin Forum functions
|   > Module written by Matt Mecham
|   > Date started: 17th March 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}


$idx = new ad_groups();


class ad_groups {

	var $base_url;

	function ad_groups() {
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
			case 'doadd':
				$this->save_group('add');
				break;
				
			case 'add':
				$this->group_form('add');
				break;
				
			case 'edit':
				$this->group_form('edit');
				break;
			
			case 'doedit':
				$this->save_group('edit');
				break;
			
			case 'delete':
				$this->delete_form();
				break;
			
			case 'dodelete':
				$this->do_delete();
				break;
				
			//-------------------------	
				
			case 'fedit':
				$this->forum_perms();
				break;
				
			case 'pdelete':
				$this->delete_mask();
				break;
				
			case 'dofedit':
				$this->do_forum_perms();
				break;
				
			case 'permsplash':
				$this->permsplash();
				break;
				
			case 'view_perm_users':
				$this->view_perm_users();
				break;
					
			case 'remove_mask':
				$this->remove_mask();
				break;
				
			case 'preview_forums':
				$this->preview_forums();
				break;
				
			case 'dopermadd':
				$this->add_new_perm();
				break;
				
			case 'donameedit':
				$this->edit_name_perm();
				break;
			//-------------------------			
					
					
						
			default:
				$this->main_screen();
				break;
		}
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Member group /forum mask permission form thingy doodle do yes. Viewing Perm users
	//
	//+---------------------------------------------------------------------------------
	
	function delete_mask()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------------------
		// Check for a valid ID
		//+-------------------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID маски доступа, попробуйте снова");
		}
		
		$DB->query("DELETE FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		$old_id = intval($IN['id']);
		
		//+-------------------------------------------
		// Remove from forums...
		//+-------------------------------------------
		
		$get = $DB->query("SELECT id, read_perms, reply_perms, start_perms, upload_perms FROM ibf_forums");
				
		while( $f = $DB->fetch_row($get) )
		{
			$d_str = "";
			$d_arr = array();
			
			foreach( array( 'read_perms', 'reply_perms', 'start_perms', 'upload_perms' ) as $perm_bit )
			{
				if ($f[ $perm_bit ] != '*')
				{
					if ( preg_match( "/(^|,)".$old_id."(,|$)/", $f[ $perm_bit ]) )
					{
						$f[ $perm_bit ] = preg_replace( "/(^|,)".$old_id."(,|$)/", "\\1\\2", $f[ $perm_bit ] );
						
						$d_arr[ $perm_bit ] = $this->clean_perms( $f[ $perm_bit ] );
					}
				}
			}
			
			// Do we have anything to save?
				
			if ( count($d_arr) > 0 )
			{
				$d_str = $DB->compile_db_update_string( $d_arr );
				
				// Sure?..
				
				if ( strlen($d_str) > 5)
				{
					$save = $DB->query("UPDATE ibf_forums SET $d_str WHERE id={$f['id']}");
				}
			}
		}
		
		$this->permsplash();
	}
	
	//+---------------------------------------------------------------------------------
	
	
	function add_new_perm()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$IN['new_perm_name'] = trim($IN['new_perm_name']);
		
		if ($IN['new_perm_name'] == "")
		{
			$ADMIN->error("Необходимо ввести название");
		}
		
		$copy_id = $IN['new_perm_copy'];
		
		//+-------------------------------------------
		// UPDATE DB
		//+-------------------------------------------
		
		$DB->query("INSERT INTO ibf_forum_perms SET perm_name='".$IN['new_perm_name']."'");
		
		$new_id = $DB->get_insert_id();
		
		if ( $copy_id != 'none' )
		{
			//+-------------------------------------------
			// Add new mask to forum accesses
			//+-------------------------------------------
		
			$old_id = intval($copy_id);
			
			if ( ($new_id > 0) and ($old_id > 0) )
			{
				$get = $DB->query("SELECT id, read_perms, reply_perms, start_perms, upload_perms FROM ibf_forums");
				
				while( $f = $DB->fetch_row($get) )
				{
					$d_str = "";
					$d_arr = array();
					
					foreach( array( 'read_perms', 'reply_perms', 'start_perms', 'upload_perms' ) as $perm_bit )
					{
						if ($f[ $perm_bit ] != '*')
						{
							if ( preg_match( "/(^|,)".$old_id."(,|$)/", $f[ $perm_bit ]) )
							{
								$d_arr[ $perm_bit ] = $this->clean_perms( $f[ $perm_bit ] ) . ",".$new_id;
							}
						}
					}
					
					// Do we have anything to save?
						
					if ( count($d_arr) > 0 )
					{
						$d_str = $DB->compile_db_update_string( $d_arr );
						
						// Sure?..
						
						if ( strlen($d_str) > 5)
						{
							$save = $DB->query("UPDATE ibf_forums SET $d_str WHERE id={$f['id']}");
						}
					}
				}
			}
			
		
		}
		
		$this->permsplash();
			
		
	}
	
	//-_-_-_-_-_-_-_-_-
	//_-_-_-_-_-_-_-_-_
	//Now that's pretty
	
	function preview_forums()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------------------
		// Check for a valid ID
		//+-------------------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID маски доступа, попробуйте снова");
		}
		
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $perms = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить ID маски доступа, попробуйте снова");
		}
		
		//+-------------------------------------------
		// What we doin'?
		//+-------------------------------------------
		
		switch( $IN['t'] )
		{
			case 'start':
				$human_type = 'Создание тем';
				$code_word  = 'start_perms';
				break;
				
			case 'reply':
				$human_type = 'Ответ в темы';
				$code_word  = 'reply_perms';
				break;
				
			default:
				$human_type = 'Просмотр форума';
				$code_word  = 'read_perms';
				break;
		}
		
		//+-------------------------------------------
		// Get all members using that ID then!
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "$human_type" , "100%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Использование предпросмотра: " . $perms['perm_name'] );
		
		$last_cat_id = -1;
		
		$DB->query("SELECT f.id as forum_id, f.parent_id, f.subwrap, f.sub_can_post, f.name as forum_name, f.position, f.read_perms, f.start_perms, f.reply_perms, c.id as cat_id, c.name
				    FROM ibf_forums f
				     LEFT JOIN ibf_categories c ON (c.id=f.category)
				    ORDER BY c.position, f.position");
		
		
		$forum_keys = array();
		$cat_keys   = array();
		$children   = array();
		$subs       = array();
		$the_html   = "";
		
		$perm_id    = intval($IN['id']);
		
		while ( $i = $DB->fetch_row() )
		{
			
			if ($i['subwrap'] == 1 and $i['sub_can_post'] != 1)
			{
				$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "&nbsp;&nbsp;- {$i['forum_name']}\n";
			}
			else
			{
				if ($i[ $code_word ] == '*')
				{
					if ($i['parent_id'] > 0)
					{
						$children[ $i['parent_id'] ][] = "&nbsp;&nbsp;---- {$i['forum_name']}\n";
					}
					else
					{
						$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "&nbsp;&nbsp;- {$i['forum_name']}\n";
					}
				}
				else if (preg_match( "/(^|,)".$perm_id."(,|$)/", $i[ $code_word ]) )
				{
					if ($i['parent_id'] > 0)
					{
						$children[ $i['parent_id'] ][] = "&nbsp;&nbsp;---- {$i['forum_name']}\n";
					}
					else
					{
						$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "&nbsp;&nbsp;- {$i['forum_name']}\n";
					}
				}
				else
				{
					//-------------------------------------
					// CAN'T ACCESS
					//-------------------------------------
					
					if ($i['parent_id'] > 0)
					{
						$children[ $i['parent_id'] ][] = "<span style='color:gray'>&nbsp;&nbsp;---- {$i['forum_name']}</span>\n";
					}
					else
					{
						$forum_keys[ $i['cat_id'] ][$i['forum_id']] = "<span style='color:gray'>&nbsp;&nbsp;- {$i['forum_name']}</span>\n";
					}
				}
			}
			
			if ($last_cat_id != $i['cat_id'])
			{
				
				// Make sure cats with hidden forums are not shown in forum jump
				
				$cat_keys[ $i['cat_id'] ] = "<b>{$i['name']}</b>\n";
							              
				$last_cat_id = $i['cat_id'];
				
			}
		}
		
		foreach($cat_keys as $cat_id => $cat_text)
		{
			if ( is_array( $forum_keys[$cat_id] ) && count( $forum_keys[$cat_id] ) > 0 )
			{
				$the_html .= $cat_text;
				
				foreach($forum_keys[$cat_id] as $idx => $forum_text)
				{
					$the_html .= $forum_text;
					
					if (count($children[$idx]) > 0)
					{
						foreach($children[$idx] as $ii => $tt)
						{
							$the_html .= $tt;
						}
					}
				}
			}
		}
		
		$the_html = str_replace( "\n", "<br />\n", $the_html );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $the_html )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		//----------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'preview_forums' ),
												  2 => array( 'act'   , 'group'   ),
												  3 => array( 'id'    , $IN['id']      ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;" , "60%" );
		$SKIN->td_header[] = array( "&nbsp;" , "40%" );
		
		$ADMIN->html .= $SKIN->start_table( "Информация" );
		
		$ADMIN->html .= $SKIN->add_td_row( array(
													"Может использовать $human_type",
													"<input type='text' readonly='readonly' style='border:1px solid black;background-color:black;size=30px' name='blah'>"
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array(
													"НЕ МОЖЕТ использовать $human_type",
													"<input type='text' readonly='readonly' style='border:1px solid gray;background-color:gray;size=30px' name='blah'>"
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array(
													"Тест с...",
													$SKIN->form_dropdown( 't',
																		array( 0 => array( 'start', 'Созданием тем'    ),
																			   1 => array( 'reply', 'Ответом в темы' ),
																			   2 => array( 'read' , 'Чтением форума'      ),
																			  ), $IN['t'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form( "Обновить" );
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
							   
	}
	
	//===========================================================================
	
	function remove_mask()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------------------
		// Check for a valid ID
		//+-------------------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID пользователя, попробуйте снова");
		}
		
		//+-------------------------------------------
		// Get, check and reset
		//+-------------------------------------------
		
		$DB->query("SELECT id, name, org_perm_id FROM ibf_members WHERE id=".intval($IN['id']));
		
		if ( ! $mem = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить ID пользователя, попробуйте снова");
		}
		
		if ( $IN['pid'] == 'all' )
		{
			$DB->query("UPDATE ibf_members SET org_perm_id=0 WHERE id=".intval($IN['id']));
		}
		else
		{
			$IN['pid'] = intval($IN['pid']);
			
			$pid_array = explode( ",", $mem['org_perm_id'] );
			
			if ( count($pid_array) < 2 )
			{
				$DB->query("UPDATE ibf_members SET org_perm_id=0 WHERE id=".intval($IN['id']));
			}
			else
			{
				$new_arr = array();
				
				foreach( $pid_array as $sid )
				{
					if ( $sid != $IN['pid'] )
					{
						$new_arr[] = $sid;
					}
				}
				
				$DB->query("UPDATE ibf_members SET org_perm_id='".implode(",",$new_arr)."' WHERE id=".intval($IN['id']));
			}	
			
		}
			
		//+-------------------------------------------
		// Get all members using that ID then!
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;" , "100%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Результат" );
		
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "Удаление маски доступа <b>{$mem['name']}</b>.",
										 )      );
	
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
							   
	}
	
	//===========================================================================
	
	
	function view_perm_users()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------------------
		// Check for a valid ID
		//+-------------------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID маски доступа, попробуйте снова");
		}
		
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $perms = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить ID маски доступа, попробуйте снова");
		}
		
		//+-------------------------------------------
		// Get all members using that ID then!
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "Данные пользователя" , "50%" );
		$SKIN->td_header[] = array( "Действие"       , "50%" );
		
		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript' type='text/javascript'>
						 <!--
						  function pop_close_and_stop( id )
						  {
						  	opener.location = \"{$SKIN->base_url}&act=mem&code=doform&MEMBER_ID=\" + id;
						  	self.close();
						  }
						  //-->
						  </script>";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Пользователей использующих: " . $perms['perm_name'] );
		
		$outer = $DB->query("SELECT id, name, email, posts, org_perm_id FROM ibf_members WHERE (org_perm_id IS NOT NULL AND org_perm_id <> 0) ORDER BY name");
		
		while( $r = $DB->fetch_row($outer) )
		{
			$exp_pid = explode( ",", $r['org_perm_id'] );
			
			foreach( explode( ",", $r['org_perm_id'] ) as $pid )
			{
				if ( $pid == $IN['id'] )
				{
					if ( count($exp_pid) > 1 )
					{
						$extra = "<li>Также используется: <em style='color:red'>";
						
						$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id IN ({$r['org_perm_id']}) AND perm_id <> {$IN['id']}");
						
						while ( $mr = $DB->fetch_row() )
						{
							$extra .= $mr['perm_name'].",";
						}
						
						$extra = preg_replace( "/,$/", "", $extra );
						
						$extra .= "</em>";
					}
					else
					{
						$extra = "";
					}
					
					$ADMIN->html .= $SKIN->add_td_row( array( "<div style='font-weight:bold;font-size:11px;padding-bottom:6px;margin-bottom:3px;border-bottom:1px solid #000'>{$r['name']}</div>
															   <li>Posts: {$r['posts']}
															   <li>Email: {$r['email']}
															   $extra" ,
															  "&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=$pid' title='Remove this mask from the user (will not remove all if they have multimasks'>Удалить маску</a>
															   <br />&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=all' title='Удалить все пользовательские маски'>Удалить все маски</a>
															   <br /><br />&#149;&nbsp;<a href='javascript:pop_close_and_stop(\"{$r['id']}\");'>Редактировать пользователя</a>",
													 )      );
				}
			}
		}
		
						     
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
							   
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Member group /forum mask permission form thingy doodle do yes.
	//
	//+---------------------------------------------------------------------------------
	
	
	function permsplash()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Форумные маски доступа [ ГЛАВНАЯ ]";
		
		$ADMIN->page_detail = "Эта секция для управления масками доступа форума.";
		
		$ADMIN->page_detail .= "<br /><b>Используется группами</b> это пользовательские группы, использующие эту маску доступа
								<br /><b>Кол-во пользователей</b> это количество пользователей, использующих данную маску доступа
							    <br /><b>Предпросмотр</b> это удобный просмотр доступа группы к разным форумам
							   ";
								
		
		//+-------------------------------------------
		// Get the names for the perm masks w/id
		//+-------------------------------------------
		
		$perms = array();
		
		$DB->query("SELECT * FROM ibf_forum_perms");
		
		while( $r = $DB->fetch_row() )
		{
			$perms[ $r['perm_id'] ] = $r['perm_name'];
		}
		
		//+-------------------------------------------
		// Get the number of members using this mask
		// as an over ride
		//+-------------------------------------------
		
		$mems = array();
		
		$DB->query("SELECT COUNT(id) as count, org_perm_id FROM ibf_members WHERE (org_perm_id IS NOT NULL AND org_perm_id <> 0) GROUP by org_perm_id");
		
		while( $r = $DB->fetch_row() )
		{
			if ( strstr($r['org_perm_id'] , "," ) )
			{
				foreach( explode( ",", $r['org_perm_id'] ) as $pid )
				{
					$mems[ $pid ] += $r['count'];
				}
			}
			else
			{
				$mems[ $r['org_perm_id'] ] += $r['count'];
			}
		}
		
		//+-------------------------------------------
		// Get the member group names and the mask
		// they use
		//+-------------------------------------------
		
		$groups = array();
		
		$DB->query("SELECT g_id, g_title, g_perm_id FROM ibf_groups");
		
		while( $r = $DB->fetch_row() )
		{
			if ( strstr($r['g_perm_id'] , "," ) )
			{
				foreach( explode( ",", $r['g_perm_id'] ) as $pid )
				{
					$groups[ $pid ][] = $r['g_title'];
				}
			}
			else
			{
				$groups[ $r['g_perm_id'] ][] = $r['g_title'];
			}
		}
		
		//+-------------------------------------------
		// Print the splash screen
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "Название маски"          , "20%" );
		$SKIN->td_header[] = array( "Используется группами"   , "20%" );
		$SKIN->td_header[] = array( "Кол-во пользователей"     , "20%" );
		$SKIN->td_header[] = array( "Предпросмотр"            , "10%" );
		$SKIN->td_header[] = array( "Редактировать"               , "15%" );
		$SKIN->td_header[] = array( "Удалить"             , "15%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Форумные маски доступа" );
		
		foreach( $perms as $id => $name )
		{
			$groups_used = "";
			
			$is_active = 0;
			
			if ( is_array( $groups[ $id ] ) )
			{
				foreach( $groups[ $id ] as $bleh => $g_title )
				{
					$groups_used .= $g_title . "<br />";
				}
				
				$is_active = 1;
				
			}
			else
			{
				$groups_used = "<center><i>Не определено</i></center>";
			}
			
			$mems_used = 0;
			
			if ( $mems[ $id ] > 0 )
			{
				$is_active = 1;
				$mems_used = $mems[ $id ] . " (<a href='javascript:pop_win(\"&amp;act=group&amp;code=view_perm_users&amp;id=$id\", \"User\", \"500\",\"350\");' title='Просмотр в новом окне пользователей, использующих эту маску'>Показать</a>)";
			}
			
			if ( $is_active > 0 )
			{
				$delete = "<i>Невозможно, при использовании</i>";
			}
			else
			{
				$delete = "<a href='{$SKIN->base_url}&amp;act=group&amp;code=pdelete&amp;id=$id'>Удалить</a>";
			}
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>$name</b>" ,
													  "$groups_used",
													  "<center>$mems_used</center>",
													  "<center><a href='javascript:pop_win(\"&amp;act=group&amp;code=preview_forums&amp;id=$id&amp;t=read\", \"400\",\"350\");' title='Показать группы имеющие доступ к просмотру..'>Предпросмотр</a></center>",
													  "<center><a href='{$SKIN->base_url}&amp;act=group&amp;code=fedit&amp;id=$id'>Редактировать</a></center>",
													  "<center>$delete</center>",
											 )      );
		
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$dlist = array();
		
		$dlist[] = array( 'none', 'Do not inherit' );
		
		foreach( $perms as $id => $name )
		{
			$dlist[] = array( $id, $name );
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dopermadd' ),
												  2 => array( 'act'   , 'group'   ),
									     )      );
									     
		
		$SKIN->td_header[] = array( "{none}" , "60%" );
		$SKIN->td_header[] = array( "{none}" , "40%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Создание новой маски доступа" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название маски доступа</b>" ,
												  $SKIN->form_input( 'new_perm_name' ),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Применить маску доступа из...</b>" ,
												 $SKIN->form_dropdown( 'new_perm_copy', $dlist ),
										 )      );
		
		$ADMIN->html .= $SKIN->end_form("Создать");
						     
		$ADMIN->html .= $SKIN->end_table();
		
		
		
		$ADMIN->output();
			
			
	}
	
	
	
	function forum_perms()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы, попробуйте снова");
		}
		
		//+----------------------------------
		
		$ADMIN->page_title = "Форумные маски доступа [ РЕДАКТИРОВАНИЕ ]";
		
		$ADMIN->page_detail = "Эта секция для управления масками доступа форума.";
		
		$ADMIN->page_detail .= "<br />Установите галочку для доступа к данному действию или снимите галочку для запрета данного действия.
							   <br /><b>Общий доступ</b> указывает на то, что все текущие и будущие маски будут иметь доступ к этому действию";
		
		//+----------------------------------
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		$group = $DB->fetch_row();
		
		$gid   = $group['perm_id'];
		$gname = $group['perm_name'];
		
		//+-------------------------------
		
		$cats     = array();
		$forums   = array();
		$children = array();
		
		$DB->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");
		
		while ($r = $DB->fetch_row())
		{
			$cats[$r['id']] = $r;
		}
		
		$DB->query("SELECT * from ibf_forums ORDER BY position ASC");
		
		while ($r = $DB->fetch_row())
		{
			
			if ($r['parent_id'] > 0)
			{
				$children[ $r['parent_id'] ][] = $r;
			}
			else
			{
				$forums[] = $r;
			}
			
		}
		
		//+----------------------------------
		//| EDIT NAME
		//+----------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'donameedit' ),
												  2 => array( 'act'   , 'group'   ),
												  3 => array( 'id'    , $gid      ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Изменение названия группы: ".$group['perm_name'] );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название маски</b>" ,
												  $SKIN->form_input("perm_name", $gname )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Изменить название");
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+----------------------------------
		//| MAIN FORM
		//+----------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dofedit' ),
												  2 => array( 'act'   , 'group'   ),
												  3 => array( 'id'    , $gid      ),
									     )      );
		
		$SKIN->td_header[] = array( "Форум"   , "40%" );
		$SKIN->td_header[] = array( "Чтение"         , "15%" );
		$SKIN->td_header[] = array( "Ответы"        , "15%" );
		$SKIN->td_header[] = array( "Создание"        , "15%" );
		$SKIN->td_header[] = array( "Загрузка"       , "15%" );
		
		$ADMIN->html .= $SKIN->start_table( "Параметры доступа к форуму для ".$group['perm_name'] );
		
		$last_cat_id = -1;
		
		foreach ($cats as $c)
		{
			
			$ADMIN->html .= $SKIN->add_td_basic( $c['name'], 'left', 'catrow' );
													   
			$last_cat_id = $c['id'];
			
			
			foreach($forums as $r)
			{	
			
				if ($r['category'] == $last_cat_id)
				{
				
					$read   = "";
					$start  = "";
					$reply  = "";
					$upload = "";
					$global = '<center id="mgblue"><i>Общий доступ</i></center>';
					
					if ($r['read_perms'] == '*')
					{
						$read = $global;
					}
					else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['read_perms'] ) )
					{
						$read = "<center id='mgblue'><input type='checkbox' name='read_".$r['id']."' value='1' checked></center>";
					}
					else
					{
						$read = "<center id='mgblue'><input type='checkbox' name='read_".$r['id']."' value='1'></center>";
					}
					
					//---------------------------
					
					$global = '<center id="mgred"><i>Общий доступ</i></center>';
					
					if ($r['start_perms'] == '*')
					{
						$start = $global;
					}
					else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['start_perms'] ) )
					{
						$start = "<center id='mgred'><input type='checkbox' name='start_".$r['id']."' value='1' checked></center>";
					}
					else
					{
						$start = "<center id='mgred'><input type='checkbox' name='start_".$r['id']."' value='1'></center>";
					}
					
					//---------------------------
					
					$global = '<center id="mggreen"><i>Общий доступ</i></center>';
					
					if ($r['reply_perms'] == '*')
					{
						$reply = $global;
					}
					else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['reply_perms'] ) )
					{
						$reply = "<center id='mggreen'><input type='checkbox' name='reply_".$r['id']."' value='1' checked></center>";
					}
					else
					{
						$reply = "<center id='mggreen'><input type='checkbox' name='reply_".$r['id']."' value='1'></center>";
					}
					
					//---------------------------
					
					$global = '<center id="memgroup"><i>Общий доступ</i></center>';
					
					if ($r['upload_perms'] == '*')
					{
						$upload = $global;
					}
					else if ( preg_match( "/(^|,)".$gid."(,|$)/", $r['upload_perms'] ) )
					{
						$upload = "<center id='memgroup'><input type='checkbox' name='upload_".$r['id']."' value='1' checked></center>";
					}
					else
					{
						$upload = "<center id='memgroup'><input type='checkbox' name='upload_".$r['id']."' value='1'></center>";
					}
					
					//---------------------------
					
					if ($r['subwrap'] == 1 and $r['sub_can_post'] != 1)
					{
						$ADMIN->html .= $SKIN->add_td_basic( "&gt; ".$r['name'], 'left', 'catrow2' );
					}
					else
					{
						$css = $r['subwrap'] == 1 ? 'catrow2' : '';
						
						$ADMIN->html .= $SKIN->add_td_row( array(
															   "<b> - ".$r['name']."</b>",
															   $read,
															   $reply,
															   $start,
															   $upload
													 )   , $css   );
					}
													 
					if ( ( isset($children[ $r['id'] ]) ) and ( count ($children[ $r['id'] ]) > 0 ) )
					{
						foreach($children[ $r['id'] ] as $idx => $rd)
						{
							$read   = "";
							$start  = "";
							$reply  = "";
							$upload = "";
							$global = "<center id='mgblue'><i>Общий доступ</i></center>";
							
							if ($rd['read_perms'] == '*')
							{
								$read = $global;
							}
							else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['read_perms'] ) )
							{
								$read = "<center id='mgblue'><input type='checkbox' name='read_".$rd['id']."' value='1' checked></center>";
							}
							else
							{
								$read = "<center id='mgblue'><input type='checkbox' name='read_".$rd['id']."' value='1'></center>";
							}
							
							//---------------------------
							
							$global = "<center id='mgred'><i>Общий доступ</i></center>";
							
							if ($rd['start_perms'] == '*')
							{
								$start = $global;
							}
							else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['start_perms'] ) )
							{
								$start = "<center id='mgred'><input type='checkbox' name='start_".$rd['id']."' value='1' checked></center>";
							}
							else
							{
								$start = "<center id='mgred'><input type='checkbox' name='start_".$rd['id']."' value='1'></center>";
							}
							
							//---------------------------
							
							$global = "<center id='mggreen'><i>Общий доступ</i></center>";
							
							if ($rd['reply_perms'] == '*')
							{
								$reply = $global;
							}
							else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['reply_perms'] ) )
							{
								$reply = "<center id='mggreen'><input type='checkbox' name='reply_".$rd['id']."' value='1' checked></center>";
							}
							else
							{
								$reply = "<center id='mggreen'><input type='checkbox' name='reply_".$rd['id']."' value='1'></center>";
							}
							
							//---------------------------
							
							$global = "<center id='memgroup'><i>Общий доступ</i></center>";
							
							if ($rd['upload_perms'] == '*')
							{
								$upload = $global;
							}
							else if ( preg_match( "/(^|,)".$gid."(,|$)/", $rd['upload_perms'] ) )
							{
								$upload = "<center id='memgroup'><input type='checkbox' name='upload_".$rd['id']."' value='1' checked></center>";
							}
							else
							{
								$upload = "<center id='memgroup'><input type='checkbox' name='upload_".$rd['id']."' value='1'></center>";
							}
							
							//---------------------------
					
							$ADMIN->html .= $SKIN->add_td_row( array(
															   "<b> --- ".$rd['name']."</b>",
															   $read,
															   $reply,
															   $start,
															   $upload
													 ) , 'subforum'     );
						}
					}					 
				}
			}
		}
		
		$ADMIN->html .= $SKIN->end_form("Обновить параметры доступа");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	function edit_name_perm()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------
		// Check for legal ID
		//---------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы");
		}
		
		if ( $IN['perm_name'] == "" )
		{
			$ADMIN->error("Необходимо ввести название");
		}
		
		$gid = $IN['id'];
		
		//---------------------------
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $gr = $DB->fetch_row() )
		{
			$ADMIN->error("Неправильное ID группы");
		}
		
		$DB->query("UPDATE ibf_forum_perms SET perm_name='{$IN['perm_name']}' WHERE perm_id='".$IN['id']."'");
		
		$ADMIN->save_log("Изменение названия маски доступа: '{$gr['perm_name']}'");
		
		$ADMIN->done_screen("Параметры доступа форума обновлены", "Управление масками доступа", "act=group&code=permsplash" );
		
		
	}
	
	
	
	function do_forum_perms()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------
		// Check for legal ID
		//---------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы");
		}
		
		$gid = $IN['id'];
		
		//---------------------------
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $gr = $DB->fetch_row() )
		{
			$ADMIN->error("Неправильное ID группы");
		}
		
		//---------------------------
		// Pull the forum data..
		//---------------------------
		
		$forum_q = $DB->query("SELECT id, read_perms, start_perms, reply_perms, upload_perms FROM ibf_forums ORDER BY position ASC");
		
		while ( $row = $DB->fetch_row( $forum_q ) )
		{
		
			$read  = "";
			$reply = "";
			$start = "";
			$upload = "";
			//---------------------------
			// Is this global?
			//---------------------------
			
			if ($row['read_perms'] == '*')
			{
				$read = '*';
				
			}
			else
			{
				//---------------------------
				// Split the set IDs
				//---------------------------
				
				$read_ids = explode( ",", $row['read_perms'] );
				
				if ( is_array($read_ids) )
				{
				   foreach ($read_ids as $i)
				   {
					   //---------------------------
					   // If it's the current ID, skip
					   //---------------------------
					   
					   if ($gid == $i)
					   {
						   continue;
					   }
					   else
					   {
						   $read .= $i.",";
					   }
				   }
				}
				//---------------------------
				// Was the box checked?
				//---------------------------
				
				if ($IN[ 'read_'.$row['id'] ] == 1)
				{
					// Add our group ID...
					
					$read .= $gid.",";
				}
				
				// Tidy..
				
				$read = preg_replace( "/,$/", "", $read );
				$read = preg_replace( "/^,/", "", $read );
				
			}
			
			//---------------------------
			// Reply topics..
			//---------------------------
				
			if ($row['reply_perms'] == '*')
			{
				$reply = '*';
			}
			else
			{
				$reply_ids = explode( ",", $row['reply_perms'] );
				
				if ( is_array($reply_ids) )
				{
				
					foreach ($reply_ids as $i)
					{
						if ($gid == $i)
						{
							continue;
						}
						else
						{
							$reply .= $i.",";
						}
					}
				
				}
				
				if ($IN[ 'reply_'.$row['id'] ] == 1)
				{
					$reply .= $gid.",";
				}
				
				$reply = preg_replace( "/,$/", "", $reply );
				$reply = preg_replace( "/^,/", "", $reply );
			}
			
			//---------------------------
			// Start topics..
			//---------------------------
				
			if ($row['start_perms'] == '*')
			{
				$start = '*';
			}
			else
			{
				$start_ids = explode( ",", $row['start_perms'] );
				
				if ( is_array($start_ids) )
				{
				
					foreach ($start_ids as $i)
					{
						if ($gid == $i)
						{
							continue;
						}
						else
						{
							$start .= $i.",";
						}
					}
				
				}
				
				if ($IN[ 'start_'.$row['id'] ] == 1)
				{
					$start .= $gid.",";
				}
				
				$start = preg_replace( "/,$/", "", $start );
				$start = preg_replace( "/^,/", "", $start );
			}
			
			//---------------------------
			// Upload topics..
			//---------------------------
				
			if ($row['upload_perms'] == '*')
			{
				$upload = '*';
			}
			else
			{
				$upload_ids = explode( ",", $row['upload_perms'] );
				
				if ( is_array($upload_ids) )
				{
				
					foreach ($upload_ids as $i)
					{
						if ($gid == $i)
						{
							continue;
						}
						else
						{
							$upload .= $i.",";
						}
					}
				
				}
				
				if ($IN[ 'upload_'.$row['id'] ] == 1)
				{
					$upload .= $gid.",";
				}
				
				$upload = preg_replace( "/,$/", "", $upload );
				$upload = preg_replace( "/^,/", "", $upload );
			}
			
			//---------------------------
			// Update the DB...
			//---------------------------
			
			if (! $new_q = $DB->query("UPDATE ibf_forums SET read_perms='$read', reply_perms='$reply', start_perms='$start', upload_perms='$upload' WHERE id='".$row['id']."'") )
			{
				die ("Запрос обновления не выполнен для форума ".$row['id']);
			}
			
		}
		
		$ADMIN->save_log("Редактирование параметров доступа для маски: '{$gr['perm_name']}'");
		
		$ADMIN->done_screen("Параметры доступа обновлены", "Управление масками доступа", "act=group&code=permsplash" );
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Delete a group
	//
	//+---------------------------------------------------------------------------------
	
	function delete_form()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы, попробуйте снова");
		}
		
		if ($IN['id'] < 5)
		{
			$ADMIN->error("Вы не можете переместить одну группу в другую. Возможно только изменение названия групп и редактирование параметров.");
		}
		
		$ADMIN->page_title = "Удаление группы";
		
		$ADMIN->page_detail = "Убедитесь ещё раз в том, что Вы собираетесь удалить ненужную группу.";
		
		
		//+-------------------------------
		
		$DB->query("SELECT COUNT(id) as users FROM ibf_members WHERE mgroup='".$IN['id']."'");
		$black_adder = $DB->fetch_row();
		
		if ($black_adder['users'] < 1)
		{
			$black_adder['users'] = 0;
		}
		
		$DB->query("SELECT g_title FROM ibf_groups WHERE g_id='".$IN['id']."'");
		$group = $DB->fetch_row();
		
		//+-------------------------------
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id <> '".$IN['id']."'");
		
		$mem_groups = array();
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_groups[] = array( $r['g_id'], $r['g_title'] );
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
												  2 => array( 'act'   , 'group'     ),
												  3 => array( 'id'    , $IN['id']   ),
												  4 => array( 'name'  , $group['g_title'] ),
									     )      );
									     
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Подтверждение удаления: ".$group['g_title'] );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во пользователей в этой группе</b>" ,
												  "<b>".$black_adder['users']."</b>",
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переместить пользователей этой группы в группу...</b>" ,
												  $SKIN->form_dropdown("to_id", $mem_groups )
									     )      );
		
		$ADMIN->html .= $SKIN->end_form("Удалить группу");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
	}
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы, попробуйте снова");
		}
		
		if ($IN['to_id'] == "")
		{
			$ADMIN->error("Невозможно переместить в неопределённую группу.");
		}
		
		// Check to make sure that the relevant groups exist.
		
		$DB->query("SELECT g_id FROM ibf_groups WHERE g_id IN(".$IN['id'].",".$IN['to_id'].")");
		
		if ( $DB->get_num_rows() != 2 )
		{
			$ADMIN->error("Невозможно определить ID удаляемой группы");
		}
		
		$DB->query("UPDATE ibf_members SET mgroup='".$IN['to_id']."' WHERE mgroup='".$IN['id']."'");
		
		$DB->query("DELETE FROM ibf_groups WHERE g_id='".$IN['id']."'");
		
		// Look for promotions in case we have members to be promoted to this group...
		
		$prq = $DB->query("SELECT g_id FROM ibf_groups WHERE g_promotion LIKE '{$IN['id']}&%'");
		
		while ( $row = $DB->fetch_row($prq) )
		{
			$nq = $DB->query("UPDATE ibf_groups SET g_promotion='-1&-1' WHERE g_id='".$row['g_id']."'");
		}
		
		// Remove from moderators table
		
		$DB->query("DELETE FROM ibf_moderators WHERE is_group=1 AND group_id=".$IN['id']);
		
		$ADMIN->save_log("Удаление пользовательской группы '{$IN['name']}'");
		
		$ADMIN->done_screen("Группа удалена", "Управление группами", "act=group" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Save changes to DB
	//
	//+---------------------------------------------------------------------------------
	
	function save_group($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['g_title'] == "")
		{
			$ADMIN->error("Необходимо ввести название группы.");
		}
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Невозможно определить ID группы");
			}
			
			if ($IN['id'] == $INFO['admin_group'] and $IN['g_access_cp'] != 1)
			{
				$ADMIN->error("Вы не можете запретить возможность доступа к админцентру, для этой группы");
			}
		}
		
		//------------------------------------
		// Sort out the perm mask id things
		//------------------------------------
		
		if ( is_array( $HTTP_POST_VARS['permid'] ) )
		{
			$perm_id = implode( ",", $HTTP_POST_VARS['permid'] );
		}
		else
		{
			$ADMIN->error("Не выбрана маска доступа");
		}
		
		// Build up the hashy washy for the database ..er.. wase.
		
		$prefix = preg_replace( "/&#39;/", "'" , $std->txt_safeslashes($HTTP_POST_VARS['prefix']) );
		$prefix = preg_replace( "/&lt;/" , "<" , $prefix          );
		$suffix = preg_replace( "/&#39;/", "'" , $std->txt_safeslashes($HTTP_POST_VARS['suffix']) );
		$suffix = preg_replace( "/&lt;/" , "<" , $suffix          );
		
		$promotion_a = '-1'; //id
		$promotion_b = '-1'; // posts
		
		if ($IN['g_promotion_id'] > 0)
		{
			$promotion_a = $IN['g_promotion_id'];
			$promotion_b = $IN['g_promotion_posts'];
		}
		
		//if ($IN['g_max_messages'] == 0)
		//{
			//$IN['g_max_messages'] = -1;
		//}
		
		//list($p_max, $p_width, $p_height) = explode( ":", $group['g_photo_max_vars'] );
		
		$IN['p_max']    = str_replace( ":", "", $IN['p_max'] );
		$IN['p_width']  = str_replace( ":", "", $IN['p_width'] );
		$IN['p_height'] = str_replace( ":", "", $IN['p_height'] );
		
		$db_string = array(
							 'g_view_board'         => $IN['g_view_board'],
							 'g_mem_info'           => $IN['g_mem_info'],
							 'g_other_topics'       => $IN['g_other_topics'],
							 'g_use_search'         => $IN['g_use_search'],
							 'g_email_friend'       => $IN['g_email_friend'],
							 'g_invite_friend'      => $IN['g_invite_friend'],
							 'g_edit_profile'       => $IN['g_edit_profile'],
							 'g_post_new_topics'    => $IN['g_post_new_topics'],
							 'g_reply_own_topics'   => $IN['g_reply_own_topics'],
							 'g_reply_other_topics' => $IN['g_reply_other_topics'],
							 'g_edit_posts'         => $IN['g_edit_posts'],
							 'g_edit_cutoff'        => $IN['g_edit_cutoff'],
							 'g_delete_own_posts'   => $IN['g_delete_own_posts'],
							 'g_open_close_posts'   => $IN['g_open_close_posts'],
							 'g_delete_own_topics'  => $IN['g_delete_own_topics'],
							 'g_post_polls'         => $IN['g_post_polls'],
							 'g_vote_polls'         => $IN['g_vote_polls'],
							 'g_use_pm'             => $IN['g_use_pm'],
							 'g_is_supmod'          => $IN['g_is_supmod'],
							 'g_access_cp'          => $IN['g_access_cp'],
							 'g_title'              => trim($IN['g_title']),
							 'g_can_remove'         => $IN['g_can_remove'],
							 'g_append_edit'        => $IN['g_append_edit'],
							 'g_access_offline'     => $IN['g_access_offline'],
							 'g_avoid_q'            => $IN['g_avoid_q'],
							 'g_avoid_flood'        => $IN['g_avoid_flood'],
							 'g_icon'               => trim($IN['g_icon']),
							 'g_attach_max'         => $IN['g_attach_max'],
							 'g_avatar_upload'      => $IN['g_avatar_upload'],
							 'g_calendar_post'      => $IN['g_calendar_post'],
							 'g_d_max_dls'		=> $IN['g_d_max_dls'],
							 'g_do_download'		=> $IN['g_do_download'],
							 'g_d_add_files'		=> $IN['g_d_add_files'],
							 'g_d_ibcode_files'		=> $IN['g_d_ibcode_files'],
							 'g_d_html_files'		=> $IN['g_d_html_files'],
							 'g_d_manage_files'	=> $IN['g_d_manage_files'],
							 'g_d_edit_files'		=> $IN['g_d_edit_files'],
							 'g_d_allow_dl_offline'	=> $IN['g_d_allow_dl_offline'],
							 'g_d_post_comments'		=> $IN['g_d_post_comments'],
							 'g_d_approve_down'		=> $IN['g_d_approve_down'],
							 'g_d_eofs'		=> $IN['g_d_eofs'],
							 'g_d_optimize_db'		=> $IN['g_d_optimize_db'],
							 'g_d_check_links'	=> $IN['g_d_check_links'],
							 'g_d_check_topics'		=> $IN['g_d_check_topics'],
							 'g_max_messages'       => $IN['g_max_messages'],
							 'g_max_mass_pm'        => $IN['g_max_mass_pm'],
							 'g_search_flood'       => $IN['g_search_flood'],
							 'prefix'               => $prefix,
							 'suffix'               => $suffix,
							 'g_promotion'          => $promotion_a.'&'.$promotion_b,
							 'g_hide_from_list'     => $IN['g_hide_from_list'],
							 'g_post_closed'        => $IN['g_post_closed'],
							 'g_perm_id'			=> $perm_id,
							 'g_photo_max_vars'	    => $IN['p_max'].':'.$IN['p_width'].':'.$IN['p_height'],
							 'g_dohtml'			    => $IN['g_dohtml'],
							 'g_edit_topic'			=> $IN['g_edit_topic'],
							 'g_email_limit'		=> intval($IN['join_limit']).':'.intval($IN['join_flood']),
							 
						  );
						  
		if ($type == 'edit')
		{
			$rstring = $DB->compile_db_update_string( $db_string );
			
			$DB->query("UPDATE ibf_groups SET $rstring WHERE g_id='".$IN['id']."'");
			
			// Update the title of the group held in the mod table incase it changed.
			
			$DB->query("UPDATE ibf_moderators SET group_name='".trim($IN['g_title'])."' WHERE group_id='".$IN['id']."'");
			
			$ADMIN->save_log("Редактирование группы '{$IN['g_title']}'");
			
			$ADMIN->done_screen("Группа отредактирована", "Управление группами", "act=group" );
			
		}
		else
		{
			$rstring = $DB->compile_db_insert_string( $db_string );
			
			$DB->query("INSERT INTO ibf_groups (" .$rstring['FIELD_NAMES']. ") VALUES (". $rstring['FIELD_VALUES'] .")");
			
			$ADMIN->save_log("Создание группы '{$IN['g_title']}'");
			
			$ADMIN->done_screen("Группа создана", "Управление группами", "act=group" );
		}
	}
	
	function clean_perms($str)
	{
		$str = preg_replace( "/,$/", "", $str );
		$str = str_replace(  ",,", ",", $str );
		
		return $str;
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Add / edit group
	//
	//+---------------------------------------------------------------------------------
	
	function group_form($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$all_groups = array( 0 => array ('none', 'Не перемещать') );
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Группа в базе данных не найдена, попробуйте снова.");
			}
			
			if ( $INFO['admin_group'] == $IN['id'] )
			{
				if ( $MEMBER['mgroup'] != $INFO['admin_group'] )
				{
					$ADMIN->error("Вы не можете редактировать эту группу, т.к. эта группа является корневой административной группой");
				}
			}
			
			$form_code = 'doedit';
			$button    = 'Сохранить изменения';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = 'Создать группу';
		}
		
		if ($IN['id'] != "")
		{
			$DB->query("SELECT * FROM ibf_groups WHERE g_id='".$IN['id']."'");
			$group = $DB->fetch_row();
			
			$query = "SELECT g_id, g_title FROM ibf_groups WHERE g_id <> {$IN['id']} ORDER BY g_title";
		}
		else
		{
			$group = array();
			
			$query = "SELECT g_id, g_title FROM ibf_groups ORDER BY g_title";
		}
		
		//-------------------------------------------
		// sort out the promotion stuff
		//-------------------------------------------
		
		list($group['g_promotion_id'], $group['g_promotion_posts']) = explode( '&', $group['g_promotion'] );
		
		if ($group['g_promotion_posts'] < 1)
		{
			$group['g_promotion_posts'] = '';
		}
		
		$DB->query($query);
		
		while ( $r = $DB->fetch_row() )
		{
			if ( $r['g_id'] == $INFO['admin_group'] )
			{
				continue;
			}
			
			$all_groups[] = array( $r['g_id'], $r['g_title'] );
		}
		
		//-------------------------------------------
		
		$perm_masks = array();
		
		$DB->query("SELECT * FROM ibf_forum_perms");
		
		while ( $r = $DB->fetch_row() )
		{
			$perm_masks[] = array( $r['perm_id'], $r['perm_name'] );
		}
		
		//-------------------------------------------
		
		if ($type == 'edit')
		{
			$ADMIN->page_title = "Редактирование группы ".$group['g_title'];
		}
		else
		{
			$ADMIN->page_title = 'Создание новой группы';
			$group['g_title'] = 'Новая группа';
		}
		
		$guest_legend = "";
		
		if ($group['g_id'] == $INFO['guest_group'])
		{
			$guest_legend = "</b><br><i>(К гостям не относится)</i>";
		}
		
		$ADMIN->page_detail = "Дважды проверьте все введённые данные, перед сохранением.";
		
		
		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript'>
						 <!--
						  function checkform() {
						  
						  	isAdmin = document.forms[0].g_access_cp;
						  	isMod   = document.forms[0].g_is_supmod;
						  	
						  	msg = '';
						  	
						  	if (isAdmin[0].checked == true)
						  	{
						  		msg += 'Пользователи этой группы будут иметь доступ к админцентру\\n\\n';
						  	}
						  	
						  	if (isMod[0].checked == true)
						  	{
						  		msg += 'Пользователи этой группы будут иметь права супермодераторов.\\n\\n';
						  	}
						  	
						  	if (msg != '')
						  	{
						  		msg = 'Проверка безопасности\\n--------------\\nНазвание группы: ' + document.forms[0].g_title.value + '\\n--------------\\n\\n' + msg + 'Вы подтверждаете?';
						  		
						  		formCheck = confirm(msg);
						  		
						  		if (formCheck == true)
						  		{
						  			return true;
						  		}
						  		else
						  		{
						  			return false;
						  		}
						  	}
						  }
						 //-->
						 </script>\n";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $form_code  ),
												  2 => array( 'act'   , 'group'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     ) , 'adform', "onSubmit='return checkform()'" );
									     
		
		list($p_max, $p_width, $p_height) = explode( ":", $group['g_photo_max_vars'] );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$prefix = preg_replace( "/'/", "&#39;", $group['prefix'] );
		$prefix = preg_replace( "/</", "&lt;" , $prefix          );
		$suffix = preg_replace( "/'/", "&#39;", $group['suffix'] );
		$suffix = preg_replace( "/</", "&lt;" , $suffix          );
		
		$ADMIN->html .= $SKIN->start_table( "Общие настройки", "Основные настройки группы" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название группы</b>" ,
												  $SKIN->form_input("g_title", $group['g_title'] )
									     )      );
									     
		//+-------------------------------
		// Sort out default array
		//+-------------------------------
		
		$ADMIN->html .=
		"<script type='text/javascript'>
			
			var show   = '';
		";
		
		foreach ($perm_masks as $id => $d)
		{
			$ADMIN->html .= " 		perms_$d[0] = '$d[1]';\n";
		}
		
		$ADMIN->html .=
		"	
			var show = '';
			
		 	function saveit(f)
		 	{
		 		show = '';
		 		
		 		for (var i = 0 ; i < f.options.length; i++)
				{
					if (f.options[i].selected)
					{
						tid  = f.options[i].value;
						show += '\\n' + eval('perms_'+tid);
					}
				}
			}
			
			function show_me()
			{
				if (show == '')
				{
					show = 'Изменения не обнаружены\\nНажмите на необходимое, для активации';
				}
				
				alert('Выбранная маска доступа\\n---------------------------------\\n' + show);
			}
			
		</script>";
		
		$arr = explode( ",", $group['g_perm_id'] );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать параметры доступа группы...</b><br>Можно выбрать несколько, удерживая клавишу <b>Ctrl</b>" ,
												  $SKIN->form_multiselect("permid[]", $perm_masks, $arr, 5, 'onfocus="saveit(this)"; onchange="saveit(this)";' )."<a href='javascript:show_me();'>Показать выбранные маски</a>"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Иконка группы</b><br>(Можно пропустить)" ,
												  $SKIN->form_input("g_icon", $group['g_icon'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>СООБЩЕНИЕ: Максимальный размер загружаемых файлов (в кб)</b>".$SKIN->js_help_link('mg_upload')."<br>(Оставьте это поле пустым для запрета загрузки)" ,
												  $SKIN->form_input("g_attach_max", $group['g_attach_max'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>ПЕРСОНАЛЬНОЕ ФОТО: Максимальный размер загружаемой фотографии (в кб)</b><br>(Оставьте это поле пустым для запрета загрузки)" ,
												  $SKIN->form_input("p_max", $p_max )."<br />"
												  ."Макс. ширина (px): <input type='text' size='3' name='p_width' value='{$p_width}'> "
												  ."Макс. высота (px): <input type='text' size='3' name='p_height' value='{$p_height}'>"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Вид отображения в онлайновом списке [Приставка]</b><br>(Можно не заполнять)<br>(Пример:&lt;span style='color:red'&gt;)" ,
												  $SKIN->form_input("prefix", $prefix )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Вид отображения в онлайновом списке [Окончание]</b><br>(Можно не заполнять)<br>(Пример:&lt;/span&gt;)" ,
												  $SKIN->form_input("suffix", $suffix )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Скрыть эту группу из списка участников?</b>" ,
												  $SKIN->form_yes_no("g_hide_from_list", $group['g_hide_from_list'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+***********************************************************************----------

		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );

		$ADMIN->html .= $SKIN->start_table( "Доступы к файловому архиву" );


		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить скачивание файлов с файлового архива?</b>" ,
												  $SKIN->form_yes_no("g_do_download", $group['g_do_download'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальное кол-во файлов, скачиваемых за 1 раз?</b><br />Для отключения лимита, введите 0 или оставьте поле пустым" ,
												  $SKIN->form_input("g_d_max_dls", $group['g_d_max_dls'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить загрузку файлов в файловый архив?</b>" ,
												  $SKIN->form_yes_no("g_d_add_files", $group['g_d_add_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить редактирование/удаление собственных файлов, загруженных в файловый архив?</b>" ,
												  $SKIN->form_yes_no("g_d_edit_files", $group['g_d_edit_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование кодов форума в описании файлов?</b>" ,
												  $SKIN->form_yes_no("g_d_ibcode_files", $group['g_d_ibcode_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование HTML тэгов в описании файлов?</b>" ,
												  $SKIN->form_yes_no("g_d_html_files", $group['g_d_html_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут оставлять комментарии к файлам?</b>" ,
												  $SKIN->form_yes_no("g_d_post_comments", $group['g_d_post_comments'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить управление файлами в файловом архиве?</b><br />Ниже установите индивидуальные разрешения..." ,
												  $SKIN->form_yes_no("g_d_manage_files", $group['g_d_manage_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут одобрять файлы, если Вы установили проверку загружаемых файлов?</b><br />Необходимо в поле Управление файлами, выбрать Да" ,
												  $SKIN->form_yes_no("g_d_approve_down", $group['g_d_approve_down'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут редактировать/Удалять файлы, загруженные другими пользователями?</b><br />Необходимо в поле Управление файлами, выбрать Да" ,
												  $SKIN->form_yes_no("g_d_eofs", $group['g_d_eofs'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут запускать команду Оптимизации базы?</b><br />Необходимо в поле Управление файлами, выбрать Да" ,
												  $SKIN->form_yes_no("g_d_optimize_db", $group['g_d_optimize_db'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут запускать команду \"Проверка ссылок\"?</b><br />Необходимо в поле Управление файлами, выбрать Да" ,
												  $SKIN->form_yes_no("g_d_check_links", $group['g_d_check_links'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут запускать команду \"Проверка тем\"?</b><br />Необходимо в поле Управление файлами, выбрать Да" ,
												  $SKIN->form_yes_no("g_d_check_topics", $group['g_d_check_topics'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить просмотр файлового архива, когда архив выключен?</b>" ,
												  $SKIN->form_yes_no("g_d_allow_dl_offline", $group['g_d_allow_dl_offline'] )
									     )      );

		$ADMIN->html .= $SKIN->end_table();
		//+***********************************************************************----------
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Настройка доступов", "Ограничения действий группы" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут просматривать форум?</b>" ,
												  $SKIN->form_yes_no("g_view_board", $group['g_view_board'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут просматривать форум, когда он выключен?</b>" ,
												  $SKIN->form_yes_no("g_access_offline", $group['g_access_offline'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут просматривать профиль пользователей и список участников?</b>" ,
												  $SKIN->form_yes_no("g_mem_info", $group['g_mem_info'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут просматривать темы других пользователей?</b>" ,
												  $SKIN->form_yes_no("g_other_topics", $group['g_other_topics'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут воспользоваться поиском?</b>" ,
												  $SKIN->form_yes_no("g_use_search", $group['g_use_search'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во секунд для флуд контроля при поиске</b><br>Для отмены флуд контроля, оставьте пробел или введите 0" ,
												  $SKIN->form_input("g_search_flood", $group['g_search_flood'] )
									     )      );
									     
		list( $limit, $flood ) = explode( ":", $group['g_email_limit'] );					     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут отправлять письма на e-mail из форума?</b><br />Для отмены лимита, не заполняйте следующее поле $guest_legend</b>" ,
												  $SKIN->form_yes_no("g_email_friend", $group['g_email_friend'] )
												 ."<br />Разрешить не больше ". $SKIN->form_simple_input("join_limit", $limit, 2 )." писем за 24 часа"
												 ."<br />...и не больше 1 письма в течении ".$SKIN->form_simple_input("join_flood", $flood, 2 )." минуты"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут редактировать свой профиль?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_profile", $group['g_edit_profile'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут использовать PM форума?$guest_legend" ,
												  $SKIN->form_yes_no("g_use_pm", $group['g_use_pm'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. кол-во пользователей, для одновременной отправки письма на PM?$guest_legend<br>(Введите 0 или оставьте пробел, для невозможности массовой отправки писем на PM)" ,
												  $SKIN->form_input("g_max_mass_pm", $group['g_max_mass_pm'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. кол-во сохраняемых сообщений?$guest_legend" ,
												  $SKIN->form_input("g_max_messages", $group['g_max_messages'] )
									     )      );
									     						     							     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить загрузку собственных аватаров?$guest_legend" ,
												  $SKIN->form_yes_no("g_avatar_upload", $group['g_avatar_upload'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Доступы в сообщениях", "Ограничения действий группы в сообщениях" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить создание новых тем (где допущено)?</b>" ,
												  $SKIN->form_yes_no("g_post_new_topics", $group['g_post_new_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить ответы в свои темы?</b>" ,
												  $SKIN->form_yes_no("g_reply_own_topics", $group['g_reply_own_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить ответы в темы других участников (где допущено)?</b>" ,
												  $SKIN->form_yes_no("g_reply_other_topics", $group['g_reply_other_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить редактирование своих сообщений?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_posts", $group['g_edit_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ограничение во времени редактирования (в минутах)?$guest_legend<br>По прошествии этих минут, пользователь уже не сможет отредактировать своё сообщение. Оставьте пробел или введите 0, для отмены этого ограничения." ,
												  $SKIN->form_input("g_edit_cutoff", $group['g_edit_cutoff'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Пользователи могут удалять надпись 'Отредактировано' в сообщениях?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_append_edit", $group['g_append_edit'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление своих сообщений?$guest_legend" ,
												  $SKIN->form_yes_no("g_delete_own_posts", $group['g_delete_own_posts'] )
									     )      );
									     						     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут открывать/блокировать свои темы?$guest_legend" ,
												  $SKIN->form_yes_no("g_open_close_posts", $group['g_open_close_posts'] )
									     )      );							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут редактировать название и описание своих тем?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_topic", $group['g_edit_topic'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить удаление своих тем?$guest_legend" ,
												  $SKIN->form_yes_no("g_delete_own_topics", $group['g_delete_own_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить создание голосований (где допущено)?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_post_polls", $group['g_post_polls'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить голосовать в опросах (где допущено)?$guest_legend" ,
												  $SKIN->form_yes_no("g_vote_polls", $group['g_vote_polls'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отменить флуд контроль для этой группы?</b>" ,
												  $SKIN->form_yes_no("g_avoid_flood", $group['g_avoid_flood'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отменить проверку тем модераторами, для этой группы?</b>" ,
												  $SKIN->form_yes_no("g_avoid_q", $group['g_avoid_q'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Могут добавлять события в календарь?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_calendar_post", $group['g_calendar_post'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование [doHTML] тэгов?$guest_legend</b><br />".$SKIN->js_help_link('mg_dohtml') ,
												  $SKIN->form_yes_no("g_dohtml", $group['g_dohtml'] )
									     )      );
									     					     							     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Модераторский доступ", "Доступ или запрет этой группы, к возможности модерирования" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Назначить Супермодератором (могут модерировать везде)?$guest_legend" ,
												  $SKIN->form_yes_no("g_is_supmod", $group['g_is_supmod'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Дать доступ в Админцентр?$guest_legend" ,
												  $SKIN->form_yes_no("g_access_cp", $group['g_access_cp'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить этой группе отвечать в 'закрытых' темах?" ,
												  $SKIN->form_yes_no("g_post_closed", $group['g_post_closed'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Перемещение по группам" );
		
		if ($group['g_id'] == $INFO['admin_group'])
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите 'Не перемещать' для отключения автоперемещения</b><br>".$SKIN->js_help_link('mg_promote') ,
													  "Автоперемещение не относится к Администраторам, так как группы выше администраторской нет."
											 )      );
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите 'Не перемещать' для отключения автоперемещения</b>$guest_legend<br>".$SKIN->js_help_link('mg_promote') ,
													  'Перемещать пользователей этой группы в группу: '.$SKIN->form_dropdown("g_promotion_id", $all_groups, $group['g_promotion_id'] )
													 .'<br>при наборе '.$SKIN->form_simple_input('g_promotion_posts', $group['g_promotion_posts'] ).' сообщений'
											 )      );
		}
		
									     
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}

	//+---------------------------------------------------------------------------------
	//
	// Show "Management Screen
	//
	//+---------------------------------------------------------------------------------
	
	function main_screen()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Группы пользователей";
		
		$ADMIN->page_detail = "Разделение пользователей по группам является полезной функцией для организации Ваших пользователей. По умолчанию в форуме созданы 4 группы (Ожидающие, Гости, Пользователи и Администраторы) , которые невозможно удалить, но можно отредактировать их названия и параметры. Советуем Вам создать группу с названием 'Модераторы' и установить для этой группы отдельные привилегии, которые недоступны обычным пользователям.<br>В параметрах доступа Вы можете произвести быстрые настройки доступов к созданию тем, ответам и чтению форумов. Параметры доступа можно также отредактировать и через меню 'Управление форумами'.";
		
		$g_array = array();
		
		$SKIN->td_header[] = array( "Название группы"    , "30%" );
		$SKIN->td_header[] = array( "Доступ к АЦ?"    , "15%" );
		$SKIN->td_header[] = array( "Суп-модер?"     , "15%" );
		$SKIN->td_header[] = array( "Польз-ей"        , "10%" );
		$SKIN->td_header[] = array( "Редакт. группу"     , "20%" );
		$SKIN->td_header[] = array( "Удалить"         , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Управление пользовательскими группами" );
		
		$DB->query("SELECT ibf_groups.g_id, ibf_groups.g_access_cp, ibf_groups.g_is_supmod, ibf_groups.g_title,ibf_groups.prefix, ibf_groups.suffix, COUNT(ibf_members.id) as count FROM ibf_groups "
		          ."LEFT JOIN ibf_members ON (ibf_members.mgroup = ibf_groups.g_id) "
		          ."GROUP BY ibf_groups.g_id ORDER BY ibf_groups.g_title");
		
		while ( $r = $DB->fetch_row() )
		{
		
			$del  = "";
			$mod  = '&nbsp;';
			$adm  = '&nbsp;';
			
			if ($r['g_id'] > 4)
			{
				$del = "<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=".$r['g_id']."'>Удалить</a></center>";
			}
			//-----------------------------------
			if ($r['g_access_cp'] == 1)
			{
				$adm = '<center><span style="color:red">Да</span></center>';
			}
			//-----------------------------------
			if ($r['g_is_supmod'] == 1)
			{
				$mod = '<center><span style="color:red">Да</span></center>';
			}
			
			if ($r['g_id'] != 1 and $r['g_id'] != 2)
			{
				$total_linkage = "<a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Members&max_results=30&filter={$r['g_id']}&sort_order=asc&sort_key=name&st=0' target='_blank' title='Список участников'>".$r['prefix'].$r['g_title'].$r['suffix']."</a>";
			}
			else
			{
				$total_linkage = $r['prefix'].$r['g_title'].$r['suffix'];
			}
			
			if ( $INFO['admin_group'] == $r['g_id'] )
			{
				$is_root = " ( ROOT )";
			}
			else
			{
				$is_root = "";
			}
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>$total_linkage</b> $is_root" ,
												      $adm,
												      $mod,
												      "<center>".$r['count']."</center>",
												      "<center><a href='{$ADMIN->base_url}&act=group&code=edit&id=".$r['g_id']."'>Редактировать</a></center>",
												      $del
												      
									     )      );
									     
			$g_array[] = array( $r['g_id'], $r['g_title'] );
		}
		
		$ADMIN->html .= $SKIN->add_td_basic("&nbsp;", "center", "title");

		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add' ),
												  2 => array( 'act'   , 'group'     ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Создание новой группы" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Создать новую группу на основе группы...</b>" ,
												  $SKIN->form_dropdown("id", $g_array, 3 )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Создать группу");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
		
}


?>