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
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}



$idx = new ad_forums();


class ad_forums {

	var $base_url;

	function ad_forums() {
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
			case 'new':
				$this->new_form();
				break;
			case 'donew':
				$this->do_new();
				break;
			//+-------------------------
			case 'edit':
				$this->edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			//+-------------------------
			case 'pedit':
				$this->perm_edit_form();
				break;
			case 'pdoedit':
				$this->perm_do_edit();
				break;
			//+-------------------------
			case 'reorder':
				$this->reorder_form();
				break;
			case 'doreorder':
				$this->do_reorder();
				break;
			//+-------------------------
			case 'delete':
				$this->delete_form();
				break;
			case 'dodelete':
				$this->do_delete();
				break;
			//+-------------------------
			case 'recount':
				$this->recount();
				break;
			//+-------------------------
			case 'empty':
				$this->empty_form();
				break;
			case 'doempty':
				$this->do_empty();
				break;
			//+-------------------------
			case 'frules':
				$this->show_rules();
				break;
			case 'dorules':
				$this->do_rules();
				break;
			//+-------------------------
			case 'newsp':
				$this->new_form();
				break;
			case 'donewsplash':
				$this->donew_splash();
				break;
			case 'donewsub':
				$this->add_sub();
				break;
			//+-------------------------
			case 'subedit':
				$this->subedit();
				break;
			case 'doeditsub':
				$this->doeditsub();
				break;
				
			case 'subdelete':
				$this->subdeleteform();
				break;
			case 'dosubdelete':
				$this->dosubdelete();
				break;
			//+-------------------------
			case 'skinedit':
				$this->skin_edit();
				break;
			case 'doskinedit':
				$this->do_skin_edit();
				break;
			//+-------------------------	
			default:
				$this->new_form();
				break;
		}
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Edit forum skins
	//
	//+---------------------------------------------------------------------------------
	
	function skin_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID очищаемого форума.");
		}
		
		$DB->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("Невозможно определить ID этого форума");
		}
		
		$forum = $DB->fetch_row();
		
		if ( ($forum['skin_id'] == "") or ($forum['skin_id'] == -1) )
		{
			$forum['skin_id'] = 'n';
		}
		
		$form_array = array();
		
		$form_array[] = array( 'n', '-- NONE --' );
		
		$DB->query("SELECT sid, sname FROM ibf_skins");
		
		while ($r = $DB->fetch_row())
		{
			$form_array[] = array( $r['sid'], $r['sname'] );
		}
		
		
		//+-------------------------------
		
		$ADMIN->page_title = "Опции скина форума";
		$ADMIN->page_detail  = "Здесь Вы можете установить или удалить другой скин для данного форума. Выбранный пользователями скин будет отменён.";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doskinedit'),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Установка скина для форума: {$forum['name']}" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Какой скин установить?</b>" ,
												  $SKIN->form_dropdown("fsid", $form_array, $forum['skin_id'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Установить скин");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	function do_skin_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID очищаемого форума.");
		}
		
		$DB->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='".$IN['f']."'");
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("Невозможно определить ID этого форума");
		}
		
		if ($IN['fsid'] == 'n')
		{
			$DB->query("UPDATE ibf_forums SET skin_id='-1' WHERE id='".$IN['f']."'");
			$ADMIN->rebuild_config( array( 'forum_skin_'.$IN['f'] => "" ) );
		}
		else
		{
			$DB->query("UPDATE ibf_forums SET skin_id='".$IN['fsid']."' WHERE id='".$IN['f']."'");
			$ADMIN->rebuild_config( array( 'forum_skin_'.$IN['f'] => $IN['fsid'] ) );
		}
		
		$ADMIN->save_log("Изменение скина для форума '{$forum['name']}'");
		
		$std->boink_it($ADMIN->base_url."&act=cat" );
		exit();
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Sub Cat Delete Form
	//
	//+---------------------------------------------------------------------------------
	
	function subdeleteform() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID удаляемого форума.");
		}
		
		$cats = array();
		
		$name = "";
		
		$last_cat_id = -1;
		
		$DB->query("SELECT c.id, c.name, f.id as forum_id, f.subwrap, f.name as forum_name, f.parent_id, f.category FROM ibf_categories c, ibf_forums f WHERE c.id > 0 ORDER BY c.position, f.position");
		
		while ( $r = $DB->fetch_row() )
		{
		
			if ($last_cat_id != $r['id'])
			{
				$cats[] = array( "c_".$r['id'] , "Категория: ".$r['name'] );
				
				$last_cat_id = $r['id'];
			}
			
			if ($r['parent_id'] > 0)
			{
				continue;
			}
			
			if ($r['forum_id'] == $IN['f'])
			{
				$name = $r['forum_name'];
				continue;
			}
			
			if ($r['subwrap'] != 1)
			{
				continue;
			}
			
			if ($r['category'] == $r['id'])
			{
			
				$cats[] = array( "f_".$r['forum_id'], "Подкатегория форума: ".$r['forum_name'] );
			}
			
		}
		
		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------
		
		if ($DB->get_num_rows() < 2)
		{
			$ADMIN->error("Невозможно удалить этот форум. Перед удалением этого форума, создайте новую категорию или подкатегорию.");
		}
		
		//+-------------------------------
		
		$ADMIN->page_title = "Удаление подкатегории '$name'";
		
		$ADMIN->page_detail = "Перед удалением, убедить в том, что Вы не имеете в этой подкатегории ни одного форума.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dosubdelete'),
												  2 => array( 'act'   , 'forum'     ),
												  3 => array( 'f'     , $IN['f']  ),
												  4 => array( 'name'  , $name ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Требование" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удаляемый форум: </b>" , $name )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переместить все <i>форумы этой подкатегории</i> в</b>" ,
												  $SKIN->form_dropdown( "MOVE_ID", $cats )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Переместить форумы и удалить форум");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	
	function dosubdelete() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID исходного форума.");
		}
		
		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("Невозможно определить ID форума расположения.");
		}
		
		$cat    = -1;
		$parent = -1;
		
		if ( preg_match( "/^c_(\d+)$/", $IN['MOVE_ID'], $match ) )
		{
			$cat = $match[1];
		}
		else
		{
			$parent = preg_replace( "/^f_/", "", $IN['MOVE_ID'] );
		}
		
		// Move sub forums...
		
		$DB->query("UPDATE ibf_forums SET category='$cat', parent_id='$parent' WHERE parent_id='".$IN['f']."'");
		
		$DB->query("DELETE FROM ibf_forums WHERE id='".$IN['f']."'");
		
		// Delete any moderators, if any..
		
		$DB->query("DELETE FROM ibf_moderators WHERE forum_id='".$IN['f']."'");
		
		$ADMIN->save_log("Удаление подкатегории '{$IN['name']}'");
		
		$ADMIN->done_screen("Форум удалён", "Управление форумами", "act=cat" );
		
	}
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// Show forum rules
	//
	//+---------------------------------------------------------------------------------
	
	function show_rules() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID очищаемого форума.");
		}
		
		$DB->query("SELECT id, name, show_rules, rules_title, rules_text FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("Невозможно определить ID этого форума");
		}
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		
		$ADMIN->page_title = "Правила форума";
		$ADMIN->page_detail  = "Вы можете добавлять, редактировать, удалять правила форума и изменять способ отображения правил.";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dorules'),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Установка правил форума" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Способ отображения</b>" ,
												  $SKIN->form_dropdown( "show_rules",
																		array( 
																				0 => array( '0' , 'Не показывать' ),
																				1 => array( '1' , 'Показывать только ссылку' ),
																				2 => array( '2' , 'Показывать в виде полного текста' )
																			 ),
												  						$forum['show_rules']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Заголовок правил</b>" ,
												  $SKIN->form_input("title", $std->txt_stripslashes(str_replace( "'", '&#039;', $forum['rules_title'])))
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Текст правил</b><br>(Можно использовать HTML)" ,
												  $SKIN->form_textarea( "body", $std->txt_stripslashes($forum['rules_text']), 65, 20 )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Сохранить правила");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	function do_rules() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID очищаемого форума.");
		}
		
		$rules = array( 
						'rules_title'    => $ADMIN->make_safe($std->txt_stripslashes($HTTP_POST_VARS['title'])),
						'rules_text'     => $ADMIN->make_safe($std->txt_stripslashes($HTTP_POST_VARS['body'])),
						'show_rules'     => $IN['show_rules']
					  );
					  
		$dbs = $DB->compile_db_update_string($rules);
		
		$DB->query("UPDATE ibf_forums SET $dbs WHERE id='".$IN['f']."'");
		
		$ADMIN->done_screen("Правила форума обновлены", "Управление форумами", "act=cat" );
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// RECOUNT FORUM: Recounts topics and posts
	//
	//+---------------------------------------------------------------------------------
	
	function recount($f_override="") {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($f_override != "")
		{
			// Internal call, remap
			
			$IN['f'] = $f_override;
		}
		
		$DB->query("SELECT name FROM ibf_forums WHERE id='".$IN['f']."'");
		$forum = $DB->fetch_row();
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID ресинхронизируемого форума.");
		}
		
		// Get the topics..
		
		$DB->query("SELECT COUNT(tid) as count FROM ibf_topics WHERE approved=1 and forum_id='".$IN['f']."'");
		$topics = $DB->fetch_row();
		
		// Get the posts..
		
		$DB->query("SELECT COUNT(pid) as count FROM ibf_posts WHERE queued <> 1 and forum_id='".$IN['f']."'");
		$posts = $DB->fetch_row();
		
		// Get the forum last poster..
		
		$DB->query("SELECT tid, title, last_poster_id, last_poster_name, last_post FROM ibf_topics WHERE approved=1 and forum_id='".$IN['f']."' ORDER BY last_post DESC LIMIT 0,1");
		$last_post = $DB->fetch_row();
		
		// Reset this forums stats
		
		$postc = $posts['count'] - $topics['count'];
		
		if ($postc < 0)
		{
			$postc = 0;
		}
		
		$db_string = $DB->compile_db_update_string( array (
															 'last_poster_id'   => $last_post['last_poster_id'],
															 'last_poster_name' => $last_post['last_poster_name'],
															 'last_post'        => $last_post['last_post'],
															 'last_title'       => $last_post['title'],
															 'last_id'          => $last_post['tid'],
															 'topics'           => $topics['count'],
															 'posts'            => $postc
												 )        );
												 
		$DB->query("UPDATE ibf_forums SET $db_string WHERE id='".$IN['f']."'");
		
		// Override? then return..
		
		if ($f_override != "")
		{
			return TRUE;
		}
		
		$ADMIN->save_log("Пересчёт сообщений форума '{$forum['name']}'");
		
		$ADMIN->done_screen("Форум ресинхронизирован", "Управление форумами", "act=cat" );
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// EMPTY FORUM: Removes all topics and posts, etc.
	//
	//+---------------------------------------------------------------------------------
	
	function empty_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID очищаемого форума.");
		}
		
		$DB->query("SELECT id, name FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("Невозможно определить ID этого форума");
		}
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		
		$ADMIN->page_title = "Очистка форума '{$forum['name']}'";
		
		$ADMIN->page_detail = "Будут УДАЛЕНЫ ВСЕ ТЕМЫ, СООБЩЕНИЯ И ОПРОСЫ.<br>Сам форум не будет удалён - подумайте ещё раз перед продолжением, - действительно ли Вы хотите очистить форум.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doempty'),
												  2 => array( 'act'   , 'forum'     ),
												  3 => array( 'f'     , $IN['f']  ),
												  4 => array( 'name' , $forum['name'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Очистка форума '{$forum['name']}" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Очищаемый форум: </b>" , $forum['name'] )      );
									     
		$ADMIN->html .= $SKIN->end_form("Очистить форум");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_empty() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID исходного форума.");
		}
		
		// Check to make sure its a valid forum.
		
		$DB->query("SELECT id, posts, topics FROM ibf_forums WHERE id='".$IN['f']."'");
		
		if ( ! $forum = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить детали очищаемого форума");
		}
		
		// Delete topics...
		
		$DB->query("DELETE FROM ibf_topics WHERE forum_id='".$IN['f']."'");
		
		// Move posts...
		
		$DB->query("DELETE FROM ibf_posts WHERE forum_id='".$IN['f']."'");
		
		// Move polls...
		
		$DB->query("DELETE FROM ibf_polls WHERE forum_id='".$IN['f']."'");
		
		// Move voters...
		
		$DB->query("DELETE FROM ibf_voters WHERE forum_id='".$IN['f']."'");
		
		// Clean up the stats
		
		$DB->query("UPDATE ibf_forums SET posts='0', topics='0', last_post='', last_poster_id='', last_poster_name='', last_title='', last_id='' WHERE id='".$IN['f']."'");
		
		$DB->query("UPDATE ibf_stats SET TOTAL_TOPICS=TOTAL_TOPICS-".$forum['topics'].", TOTAL_REPLIES=TOTAL_REPLIES-".$forum['posts']);
		
		$ADMIN->save_log("Очистка всех сообщений форума '{$IN['name']}'");
		
		$ADMIN->done_screen("Форум очищен", "Управление форумами", "act=cat" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function reorder_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Пересортировка форумов";
		$ADMIN->page_detail  = "Для пересортировки форумов, просто выберите номер позиции, в выпадающем меню, рядом с каждым форумом и нажмите кнопку Сохранить изменения";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doreorder'),
												  2 => array( 'act'   , 'forum'     ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"       , "10%" );
		$SKIN->td_header[] = array( "Название форума"   , "60%" );
		$SKIN->td_header[] = array( "Сообщений"        , "15%" );
		$SKIN->td_header[] = array( "Тем"       , "15%" );
		
		$ADMIN->html .= $SKIN->start_table( "Ваши категории и форумы" );
		
		$cats        = array();
		$forums       = array();
		$forum_in_cat = array();
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
				$forum_in_cat[ $r['category'] ]++;
			}
			
		}
		
		$i = 1;
		
		$last_cat_id = -1;
		
		foreach ($cats as $c)
		{
			
			$ADMIN->html .= $SKIN->add_td_row( array(  '&nbsp;',
													   $c['name'],
													   '&nbsp;',
													   '&nbsp;',
											 ), 'pformstrip'     );
			$last_cat_id = $c['id'];
			
			
			foreach($forums as $r)
			{	
			
				if ($r['category'] == $last_cat_id)
				{
				
					
					$form_array = array();
				
					for ($c = 1 ; $c <= $forum_in_cat[ $r['category'] ] ; $c++ )
					{
						$i++;
						
						$form_array[] = array( $c, $c );
					}
					
					if ($r['subwrap'] == 1)
					{
					
						$ADMIN->html .= $SKIN->add_td_row( array(  $SKIN->form_dropdown( 'POS_'.$r['id'], $form_array, $r['position'] ),
																   $r['name'],
																   '&nbsp;',
																   '&nbsp;',
														 ), 'catrow2'     );
					}
					else
					{
					
				
						$ADMIN->html .= $SKIN->add_td_row( array(
																   $SKIN->form_dropdown( 'POS_'.$r['id'], $form_array, $r['position'] ),
																   "<b>".$r['name']."</b>",
																   $r['posts'],
																   $r['topics'],
														 )      );
					}
					
													 
					if ( ( isset($children[ $r['id'] ]) ) and ( count ($children[ $r['id'] ]) > 0 ) )
					{
						foreach($children[ $r['id'] ] as $idx => $rd)
						{
							$form_array = array();
					
							for ($c = 1 ; $c <= count($children[ $r['id'] ]) ; $c++ )
							{
								$i++;
								
								$form_array[] = array( $c, $c );
							}
							
						
							$ADMIN->html .= $SKIN->add_td_row( array(
																	   $SKIN->form_dropdown( 'POS_'.$rd['id'], $form_array, $rd['position'] ),
																	   "<b>".$rd['name']."</b>",
																	   $rd['posts'],
																	   $rd['topics'],
															 ), 'subforum'      );
						}
					}					 
				}
			}
		}
		
		$ADMIN->html .= $SKIN->end_form("Сохранить изменения");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_reorder() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$cat_query = $DB->query("SELECT id from ibf_forums");
		
		while ( $r = $DB->fetch_row($cat_query) )
		{
			$order_query = $DB->query("UPDATE ibf_forums SET position='".$IN[ 'POS_' . $r['id'] ]."' WHERE id='".$r['id']."'");
		}
		
		$ADMIN->save_log("Пересортировка форумов");
		
		$ADMIN->done_screen("Форумы пересортированы", "Управление форумами", "act=cat" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// REMOVE FORUM
	//
	//+---------------------------------------------------------------------------------
	
	function delete_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID удаляемого форума.");
		}
		
		$DB->query("SELECT id, name FROM ibf_forums ORDER BY position");
		
		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------
		
		if ($DB->get_num_rows() < 2)
		{
			$ADMIN->error("Невозможно удалить форум. Перед удалением форума, Вы должны создать новый форум");
		}
		
		while ( $r = $DB->fetch_row() )
		{
			if ($r['id'] == $IN['f'])
			{
				$name = $r['name'];
				continue;
			}
			
			$form_array[] = array( $r['id'] , $r['name'] );
		}
		
		//+-------------------------------
		
		$ADMIN->page_title = "Удаление форума '$name'";
		
		$ADMIN->page_detail = "Перед удалением форума, убедитесь в том, что в этом форуме Вы не оставили необходимых Вам тем и сообщений.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'),
												  2 => array( 'act'   , 'forum'     ),
												  3 => array( 'f'     , $IN['f']  ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Требование" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удаляемый форум: </b>" , $name )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переместить все <i>существующие темы и сообщения этого форума</i> в</b>" ,
												  $SKIN->form_dropdown( "MOVE_ID", $form_array )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Переместить темы и удалить форум");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_delete() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$DB->query("SELECT * FROM ibf_forums WHERE id='".$IN['f']."'");
		$forum = $DB->fetch_row();
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Невозможно определить ID исходного форума.");
		}
		
		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("Невозможно определить ID форума расположения.");
		}
		
		// Move topics...
		
		$DB->query("UPDATE ibf_topics SET forum_id='".$IN['MOVE_ID']."' WHERE forum_id='".$IN['f']."'");
		
		// Move posts...
		
		$DB->query("UPDATE ibf_posts SET forum_id='".$IN['MOVE_ID']."' WHERE forum_id='".$IN['f']."'");
		
		// Move polls...
		
		$DB->query("UPDATE ibf_polls SET forum_id='".$IN['MOVE_ID']."' WHERE forum_id='".$IN['f']."'");
		
		// Move voters...
		
		$DB->query("UPDATE ibf_voters SET forum_id='".$IN['MOVE_ID']."' WHERE forum_id='".$IN['f']."'");
		
		// Delete the forum
		
		$DB->query("DELETE FROM ibf_forums WHERE id='".$IN['f']."'");
		
		// Delete any moderators, if any..
		
		$DB->query("DELETE FROM ibf_moderators WHERE forum_id='".$IN['f']."'");
		
		
		$this->recount($IN['MOVE_ID']);
		
		// Have we moved this forum from a sub cat forum?
		// If so, are there any forums left in this sub cat forum?
		
		if ($forum['parent_id'] > 0)
		{
			$DB->query("SELECT id FROM ibf_forums WHERE parent_id='{$forum['parent_id']}'");
			
			if ( ! $DB->get_num_rows() )
			{
				// No, there are no more forums that have a parent id the same as the one we've just moved it from
				// So, make that forum a normal forum then!
				
				$DB->query("UPDATE ibf_forums SET subwrap=0 WHERE id='{$forum['parent_id']}'");
			}
		}
		
		$ADMIN->save_log("Удаление форума '{$forum['name']}'");
		
		$ADMIN->done_screen("Форум удалён", "Управление форумами", "act=cat" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// NEW FORUM
	//
	//+---------------------------------------------------------------------------------
	
	
	function new_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_GET_VARS;
		
		$f_name = "";
		
		if ($HTTP_GET_VARS['name'] != "")
		{
			$f_name = $std->txt_stripslashes(urldecode($HTTP_GET_VARS['name']));
		}
		
		$cats = array();
		$seen = array();
		
		$last_cat_id = -1;
		
		$DB->query("SELECT c.id, c.name, f.id as forum_id, f.subwrap, f.name as forum_name, f.subwrap, f.parent_id, f.category FROM ibf_categories c, ibf_forums f WHERE c.id > 0 ORDER BY c.position, f.position");
		
		if ( $DB->get_num_rows() )
		{
		
			while ( $r = $DB->fetch_row() )
			{
			
				if ($r['parent_id'] > 0)
				{
					continue;
				}
					
				if ($last_cat_id != $r['id'])
				{
					$cats[] = array( "c_".$r['id'] , "Категория: ".$r['name'] );
					
					$seen[$r['id']] = 1;
					
					$last_cat_id = $r['id'];
				}
				
				if ($r['category'] == $r['id'])
				{
					if ($r['forum_id'] != $IN['f'])
					{
						$cats[] = array( "f_".$r['forum_id'], "---- ".$r['forum_name'] );
					}
				}
				
			}
		
		}
		else
		{
			// No forums, get cats only..
			
			$DB->query("SELECT * from ibf_categories WHERE id > 0");
			
			while ($r = $DB->fetch_row())
			{
				$cats[] = array( "c_".$r['id'] , "Категория: ".$r['name'] );
			}
			
		}
		
		$ADMIN->page_title = "Создание нового форума";
		
		$ADMIN->page_detail = "Эта секция служит для создания нового форума и добавления его к существующей категории. Убедитесь в правильности выбора категории для 
							   этого форума. Если Вы всё-таки допустили ошибку, Вы можете в любое время зайти по ссылке \"Управление форумами\" и произвести необходимые изменения
							   в созданном форуме.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'donew'  ),
												  2 => array( 'act'   , 'forum'  ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Основные настройки" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Добавить в категорию</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Статус форума</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, 'Активный' ),
																					1 => array( 0, 'Только для чтения архивов'  ),
																				 ),
												  						"1"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки форума" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название форума</b>" ,
												  $SKIN->form_input("FORUM_NAME", $f_name)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Описание форума</b><br>Можно использовать HTML - переход строк будет произведён автоматически" ,
												  $SKIN->form_textarea("FORUM_DESC")
									     )      );
									     
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки корневого форума: разрешить сообщения в этом форуме?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить новые темы и сообщения в этом форуме?</b><br>При выборе да все подфорумы будут отображены выше списка тем, а при отсутствии подфорумов, данный форум будет действовать как обычный форум.<br><b>При выборе 'нет', можете пропустить остальные настройки, т.к. они не будут иметь никакого эффекта и данный форум будет действовать подобно категории.</b>" ,
												  $SKIN->form_yes_no("sub_can_post", 1)."<br><b>ПРИМЕЧАНИЕ</b> Эта опция не будет иметь никакого эффекта, если данный форум сам уже является подфорумом.",
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки переадресации" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка переадресации этого форума</b>" ,
												  $SKIN->form_input("redirect_url")
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Метод переадресации</b><br>Не заполняйте это поле, либо используйте '_self', для открытия ссылки в том же окне" ,
												  $SKIN->form_input("redirect_loc")
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить ссылку переадресации?</b><br>При выборе 'да', можете пропустить остальные настройки и сразу перейти к параметрам доступа, т.к. данный форум будет действовать просто как ссылка переадресации. Существующие в этом форуме сообщения не будут доступны." ,
												  $SKIN->form_yes_no("redirect_on", 0)
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во переходов на данный момент</b>" ,
												  $SKIN->form_input("redirect_hits", 0)
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки сообщений форума" );
		
		//+-------------------------------
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование [doHTML] тэгов?</b><br />Любые HTML тэги в сообщениях будут срабатывать." ,
												  $SKIN->form_yes_no("FORUM_HTML", 0 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить коды форума в сообщениях?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", 1 )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить форму быстрого ответа?</b>" ,
												  $SKIN->form_yes_no("quick_reply", 1 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить создание опросов в этом форуме?</b>" ,
												  $SKIN->form_yes_no("allow_poll", 1 )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить обновление тем при новых голосах?</b><br />При добавлении голоса в опросах, тема с опросом будет подниматься вверх" ,
												  $SKIN->form_yes_no("allow_pollbump", 0 )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить счётчик сообщений в этом форуме?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", 1 )
									     )      );
									     
		//-----------
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модерировать сообщения?</b><br>(Необходимо добавить модератора в этот форум, для проверки всех тем и сообщений перед их опубликованием)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, 'Нет' ),
												  									 1 => array( 1, 'Модерировать все новые темы и сообщения' ),
												  									 2 => array( 2, 'Модерировать только новые темы' ),
												  									 3 => array( 3, 'Модерировать только сообщения' ),
												  									   ),
												  							    0 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail адреса, на которые необходимо отправлять уведомления о темах, ожидающих подтверждения</b><br>(Если нет необходимости в этом, не заполняйте это поле)<br />При вводе нескольких адресов, разделяйте адреса через запятую, например (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запаролить вход в этот форум?<br>Введите пароль</b><br>(Не заполняйте это поле, если хотите сделать вход в этот форум свободным)" ,
												  $SKIN->form_input("FORUM_PROTECT")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать темы за последние</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, 'День' ),
																					1 => array( 5, '5 дней'  ),
																					2 => array( 7, '7 дней'  ),
																					3 => array( 10, '10 дней' ),
																					4 => array( 15, '15 дней' ),
																					5 => array( 20, '20 дней' ),
																					6 => array( 25, '25 дней' ),
																					7 => array( 30, '30 дней' ),
																					8 => array( 60, '60 дней' ),
																					9 => array( 90, '90 дней' ),
																					10=> array( 100,'Показывать все'     ),
																				 ),
												  						"30"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сортировать темы по</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', 'Дате последнего сообщения' ),
																					1 => array( 'title'    , 'Названиям тем' ),
																					2 => array( 'starter_name', 'Авторам тем' ),
																					3 => array( 'posts'    , 'Кол-ву сообщений в темах' ),
																					4 => array( 'views'    , 'Кол-ву просмотров тем' ),
																					5 => array( 'start_date', 'Дате создания тем' ),
																					6 => array( 'last_poster_name'   , 'Последним авторам' ),
																				 ),
												  						"last_post"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Порядок сортировки</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', 'По убыванию (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', 'По возрастанию (A - Z, 10 - 0)' ),
																				 ),
												  						"Z-A"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
											
		$SKIN->td_header[] = array( "Название"  , "40%" );
		$SKIN->td_header[] = array( "Чтение"  , "15%" );
		$SKIN->td_header[] = array( "Ответы" , "15%" );
		$SKIN->td_header[] = array( "Создание" , "15%" );
		$SKIN->td_header[] = array( "Загрузка", "15%" );
		
		$ADMIN->html .= $SKIN->start_table("Параметры доступа");
		
		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);
		
		$ADMIN->html .= $SKIN->end_form("Создать форум");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}


	//------------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------
	
	function do_new() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['FORUM_NAME'] = trim($IN['FORUM_NAME']);
		
		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("Необходимо ввести название форума");
		}
		
		// Get the new forum id. We could use auto_incrememnt, but we need the ID to use as the default
		// forum position...
		
		$DB->query("SELECT MAX(id) as top_forum FROM ibf_forums");
		$row = $DB->fetch_row();
		
		if ($row['top_forum'] < 1) $row['top_forum'] = 0;
		
		$row['top_forum']++;
		
		$perms = $ADMIN->compile_forum_perms();
		
		$cat    = -1;
		$parent = -1;
		
		if ( preg_match( "/^c_(\d+)$/", $IN['CATEGORY'], $match ) )
		{
			$cat = $match[1];
		}
		else
		{
			$parent = preg_replace( "/^f_/", "", $IN['CATEGORY'] );
			
			$DB->query("SELECT category FROM ibf_forums WHERE id='$parent'");
			
			if ($forum_result = $DB->fetch_row())
			{
				$cat = $forum_result['category'];
			}
		}
		
		$db_string = $DB->compile_db_insert_string( array (
															'id'               => $row['top_forum'],
															'position'         => $row['top_forum'],
															'topics'           => 0,
															'posts'            => 0,
															'last_post'        => "",
															'last_poster_name' => "",
															'name'             => $IN['FORUM_NAME'],
															'description'      => $std->my_nl2br( $std->txt_stripslashes($HTTP_POST_VARS['FORUM_DESC']) ),
															'use_ibc'          => $IN['FORUM_IBC'],
															'use_html'         => $IN['FORUM_HTML'],
															'status'           => $IN['FORUM_STATUS'],
															'start_perms'      => $perms['START'],
															'reply_perms'      => $perms['REPLY'],
															'read_perms'       => $perms['READ'],
															'upload_perms'     => $perms['UPLOAD'],
															'password'         => $IN['FORUM_PROTECT'],
															'category'         => $cat,
															'last_id'          => "",
															'last_title'       => "",
															'sort_key'         => $IN['SORT_KEY'],
															'sort_order'       => $IN['SORT_ORDER'],
															'prune'            => $IN['PRUNE_DAYS'],
															'show_rules'       => 0,
															'preview_posts'    => $IN['MODERATE'],
															'allow_poll'       => $IN['allow_poll'],
															'allow_pollbump'   => $IN['allow_pollbump'],
															'inc_postcount'    => $IN['inc_postcount'],
															'parent_id'        => $parent,
															'sub_can_post'     => $IN['sub_can_post'],
															'quick_reply'      => $IN['quick_reply'],
															'redirect_on'       => $IN['redirect_on'],
															'redirect_hits'     => $IN['redirect_hits'],
															'redirect_url'      => $IN['redirect_url'],
															'redirect_loc'		=> $IN['redirect_loc'],
															'notify_modq_emails'=> $IN['notify_modq_emails'],
															
												  )       );
												  
		$DB->query("INSERT INTO ibf_forums (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
		
		if ($parent != -1)
		{
			$DB->query("UPDATE ibf_forums SET subwrap=1 WHERE id='$parent'");
		}
		
		$ADMIN->save_log("Создание форума '{$IN['FORUM_NAME']}'");
		
		$ADMIN->done_screen("Форум {$IN['FORUM_NAME']} создан", "Управление форумами", "act=cat" );
		
		
		
	}
	
	//------------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------
	
	

	//+---------------------------------------------------------------------------------
	//
	// EDIT FORUM
	//
	//+---------------------------------------------------------------------------------
	
	function edit_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Вы не выбрали форум для редактирования!");
		}
		
		$cats = array();
		$seen = array();
		
		$last_cat_id = -1;
		
		$DB->query("SELECT c.id, c.name, f.subwrap, f.id as forum_id, f.name as forum_name, f.subwrap, f.parent_id, f.category FROM ibf_categories c, ibf_forums f WHERE c.id > 0 ORDER BY c.position, f.position");
		
		while ( $r = $DB->fetch_row() )
		{
			
			if ($r['parent_id'] > 0)
			{
				continue;
			}
				
			if ($last_cat_id != $r['id'])
			{
				$cats[] = array( "c_".$r['id'] , "Категория: ".$r['name'] );
				
				$seen[$r['id']] = 1;
				
				$last_cat_id = $r['id'];
			}
			
			if ($r['category'] == $r['id'])
			{
				if ($r['forum_id'] != $IN['f'])
				{
					$cats[] = array( "f_".$r['forum_id'], "---- ".$r['forum_name'] );
				}
			}
			
		}
		
		$DB->query("SELECT * FROM ibf_forums WHERE id='".$IN['f']."'");
		$forum = $DB->fetch_row();
		
		if ($forum['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID форума {$IN['f']} в базе данных");
		}
		
		//-------------------------------------
		
		$real_parent = "";
		
		if ($forum['parent_id'] < 1)
		{
			$real_parent = 'c_'.$forum['category'];
		}
		else
		{
			$real_parent = 'f_'.$forum['parent_id'];
		}
		
		//-------------------------------------
		
		$ADMIN->page_title = "Редактирование форума";
		
		$ADMIN->page_detail = "Здесь Вы можете редактировать существующие форумы. Для установки параметров доступа к форумам (такие как 
							   создание тем, чтение, ответы) нажмите на 'Параметры доступа' для каждого форума в отдельности.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doedit'  ),
												  2 => array( 'act'   , 'forum'   ),
												  3 => array( 'f'     , $IN['f']  ),
												  4 => array( 'name'  , $forum['name'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Основные настройки" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Добавить в категорию</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats, $real_parent)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Статус форума</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, 'Активный' ),
																					1 => array( 0, 'Только для чтения архивов'  ),
																				 ),
												  						$forum['status']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки форума" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название форума</b>" ,
												  $SKIN->form_input("FORUM_NAME", $forum['name'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Описание форума</b><br>Можно использовать HTML - переход строк будет переконвертирован автоматически в &lt;br&gt;" ,
												  $SKIN->form_textarea("FORUM_DESC", $std->my_br2nl( $forum['description']) )
									     )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки переадресации" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка переадресации этого форума</b>" ,
												  $SKIN->form_input("redirect_url", $forum['redirect_url'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Метод переадресации</b><br>Не заполняйте это поле, либо используйте '_self', для открытия ссылки в том же окне" ,
												  $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить ссылку переадресации?</b><br>При выборе 'да', можете пропустить остальные настройки, т.к. данный форум будет действовать просто как ссылка переадресации. Существующие в этом форуме сообщения не будут доступны." ,
												  $SKIN->form_yes_no("redirect_on",
												 					 $forum['redirect_on'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
												  					  	   )
												  					 )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во переходов на данный момент</b>" ,
												  $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		
		if ($forum['parent_id'] > 0)
		{
			$extra = "<span id='normal' style='color:red'><br><b>ПРИМЕЧАНИЕ</b>: Данный форум <b>НЕ</b> является корневым форумом и эта настройка не будет иметь никакого эффекта, пока Вы не сделаете его таковым.</span>";
		}
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$cp2_show = $forum['redirect_on'] == 1 ? 'show' : 'none';
		$cp_show  = $forum['redirect_on'] == 1 ? 'none' : 'show';
		
		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "Настройки корневого форума: разрешить сообщения в этом форуме?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить новые темы и сообщения в этом форуме?</b><br>При выборе да все подфорумы будут отображены выше списка тем, а при отсутствии подфорумов, данный форум будет действовать как обычный форум.<br><b>При выборе 'нет', можете пропустить остальные настройки, т.к. они не будут иметь никакого эффекта и данный форум будет действовать подобно категории.</b>" ,
												  $SKIN->form_yes_no(
												  					  "sub_can_post",
												  					  $forum['sub_can_post'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('main_div', 'maindivoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('main_div', 'maindivoff');\" "
												  					  	   )
												  					) . $extra
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
		
		$md_show  = $forum['sub_can_post'] == 1 ? 'show' : 'none';
		$md2_show = $forum['sub_can_post'] == 1 ? 'none' : 'show';
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= "\n<div id='main_div' style='display:$md_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "Настройки сообщений форума" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование [doHTML] тэгов?</b><br />Любые HTML тэги в сообщениях будут срабатывать." ,
												  $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить коды форума в сообщениях?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить форму быстрого ответа?</b>" ,
												  $SKIN->form_yes_no("quick_reply", $forum['quick_reply'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить создание опросов в этом форуме?</b>" ,
												  $SKIN->form_yes_no("allow_poll", $forum['allow_poll'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить обновление тем при новых голосах?</b><br />При добавлении голоса в опросах, тема с опросом будет подниматься вверх" ,
												  $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить счётчик сообщений в этом форуме?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модерировать сообщения?</b><br>(Необходимо добавить модератора в этот форум, для проверки всех тем и сообщений перед их опубликованием)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, 'Нет' ),
												  									 1 => array( 1, 'Модерировать все новые темы и сообщения' ),
												  									 2 => array( 2, 'Модерировать только новые темы' ),
												  									 3 => array( 3, 'Модерировать только сообщения' ),
												  									   ),
												  							    $forum['preview_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail адреса, на которые необходимо отправлять уведомления об ожидающих подтверждения темах</b><br>(Если нет необходимости в этом, не заполняйте это поле)<br />При вводе нескольких адресов, разделяйте адреса через запятую, например (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запаролить вход в этот форум?<br>Введите пароль</b><br>(Не заполняйте это поле, если хотите сделать вход в этот форум свободным)" ,
												  $SKIN->form_input("FORUM_PROTECT", $forum['password'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать темы за последние</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, 'День' ),
																					1 => array( 5, '5 дней'  ),
																					2 => array( 7, '7 дней'  ),
																					3 => array( 10, '10 дней' ),
																					4 => array( 15, '15 дней' ),
																					5 => array( 20, '20 дней' ),
																					6 => array( 25, '25 дней' ),
																					7 => array( 30, '30 дней' ),
																					8 => array( 60, '60 дней' ),
																					9 => array( 90, '90 дней' ),
																					10=> array( 100,'Показывать все'     ),
																				 ),
												  						$forum['prune']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сортировать темы по</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', 'Дате последнего сообщения' ),
																					1 => array( 'title'    , 'Названиям тем' ),
																					2 => array( 'starter_name', 'Авторам тем' ),
																					3 => array( 'posts'    , 'Кол-ву сообщений в темах' ),
																					4 => array( 'views'    , 'Кол-ву просмотров тем' ),
																					5 => array( 'start_date', 'Дате создания тем' ),
																					6 => array( 'last_poster_name'   , 'Последним авторам' ),
																				 ),
												  						$forum['sort_key']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Порядок сортировки</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', 'По убыванию (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', 'По возрастанию (A - Z, 10 - 0)' ),
																				 ),
												  						$forum['sort_order']
												  					  )
									     )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Настройки сообщений форума</a></div>
						     </div>
		                 </div><br />\n";
		                 
		                
		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Настройки сообщений форума</a></div>
						     </div>
		                 </div><br />\n";
		
		$ADMIN->html .= $SKIN->end_form_standalone("Edit this forum");
		
		$ADMIN->nav[] = array( 'act=cat', 'Управление форумами' );
		
		$ADMIN->output();
			
			
	}


	//+---------------------------------------------------------------------------------
	
	function do_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['FORUM_NAME'] = trim($IN['FORUM_NAME']);
		
		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("Необходимо ввести название форума");
		}
		
		$DB->query("SELECT * from ibf_forums WHERE id='".$IN['f']."'");
		
		$old_details = $DB->fetch_row();
		
		$cat    = -1;
		$parent = -1;
		
		if ( preg_match( "/^c_(\d+)$/", $IN['CATEGORY'], $match ) )
		{
			$cat = $match[1];
		}
		else
		{
			$parent = preg_replace( "/^f_/", "", $IN['CATEGORY'] );
			
			$DB->query("SELECT category FROM ibf_forums WHERE id='$parent'");
			
			if ($forum_result = $DB->fetch_row())
			{
				$cat = $forum_result['category'];
			}
		}
		
		$db_string = $DB->compile_db_update_string( array (
															
															'name'              => $IN['FORUM_NAME'],
															'description'       => $std->my_nl2br( $std->txt_stripslashes($HTTP_POST_VARS['FORUM_DESC']) ),
															'use_ibc'           => $IN['FORUM_IBC'],
															'use_html'          => $IN['FORUM_HTML'],
															'status'            => $IN['FORUM_STATUS'],
															'password'          => $IN['FORUM_PROTECT'],
															'category'          => $cat,
															'sort_key'          => $IN['SORT_KEY'],
															'sort_order'        => $IN['SORT_ORDER'],
															'prune'             => $IN['PRUNE_DAYS'],
															'preview_posts'     => $IN['MODERATE'],
															'allow_poll'        => $IN['allow_poll'],
															'allow_pollbump'    => $IN['allow_pollbump'],
															'inc_postcount'     => $IN['inc_postcount'],
															'parent_id'         => $parent,
															'sub_can_post'      => $IN['sub_can_post'],
															'quick_reply'       => $IN['quick_reply'],
															'redirect_on'       => $IN['redirect_on'],
															'redirect_hits'     => $IN['redirect_hits'],
															'redirect_url'      => $IN['redirect_url'],
															'redirect_loc'		=> $IN['redirect_loc'],
															'notify_modq_emails'=> $IN['notify_modq_emails'],
															
												  )       );
												  
		$DB->query("UPDATE ibf_forums SET $db_string WHERE id='".$IN['f']."'");
		
		// Update the parent if need be
		
		if ($parent != -1)
		{
			$DB->query("UPDATE ibf_forums SET subwrap=1 WHERE id='$parent'");
		}
		
		// Have we moved this forum from a sub cat forum?
		// If so, are there any forums left in this sub cat forum?
		
		if (($old_details['parent_id'] > 0) and ($old_details['parent_id'] != $parent))
		{
			$DB->query("SELECT id FROM ibf_forums WHERE parent_id='{$old_details['parent_id']}'");
			
			if ( ! $DB->get_num_rows() )
			{
				// No, there are no more forums that have a parent id the same as the one we've just moved it from
				// So, make that forum a normal forum then!
				
				$DB->query("UPDATE ibf_forums SET subwrap=0 WHERE id='{$old_details['parent_id']}'");
			}
		}
		
		$ADMIN->save_log("Редактирование форума '{$IN['name']}'");
		
		$ADMIN->done_screen("Форум {$IN['name']} отредактирован", "Управление форумами", "act=cat" );
		
		
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Sub Cat Edit Form
	//
	//+---------------------------------------------------------------------------------
	
	
	function subedit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_GET_VARS;
		
		
		$cats = array();
		
		$last_cat_id = -1;
		
		$DB->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position");
		
		while ( $r = $DB->fetch_row() )
		{
			$cats[] = array( $r['id'] , "Категория: ".$r['name'] );
		}
		
		$DB->query("SELECT * from ibf_forums WHERE subwrap='1' AND id='".$IN['f']."'");
		
		if (! $forum = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно найти эту подкатегорию в базе данных");
		}
		
		if ($forum['password'] == '-1')
		{
			$forum['password'] = "";
		}
		
		$ADMIN->page_title = "Редактирование подкатегории";
		
		$ADMIN->page_detail = "Эта секция для редактирования подкатегорий.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doeditsub'  ),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Основные настройки" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Добавить в категорию</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats, $forum['category'])
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки форума" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название форума</b>" ,
												  $SKIN->form_input("name", $forum['name'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Описание форума</b>" ,
												  $SKIN->form_textarea("desc", $forum['description'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Статус форума</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, 'Активный' ),
																					1 => array( 0, 'Только для чтения архивов'  ),
																				 ),
												  						$forum['status']
												  					  )
									     )      );
									     
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Настройки переадресации" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка переадресации этого форума</b>" ,
												  $SKIN->form_input("redirect_url", $forum['redirect_url'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Метод переадресации</b><br>Не заполняйте это поле, либо используйте '_self', для открытия ссылки в том же окне" ,
												  $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить ссылку переадресации?</b><br>При выборе 'да', можете пропустить остальные настройки, т.к. данный форум будет действовать просто как ссылка переадресации. Существующие в этом форуме сообщения не будут доступны." ,
												  $SKIN->form_yes_no("redirect_on",
												 					 $forum['redirect_on'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
												  					  	   )
												  					 )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во переходов на данный момент</b>" ,
												  $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$cp2_show = $forum['redirect_on'] == 1 ? 'show' : 'none';
		$cp_show  = $forum['redirect_on'] == 1 ? 'none' : 'show';
		
		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "Разрешить сообщения в этом форуме?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить новые темы и сообщения в этом форуме?</b><br>При выборе да, все подфорумы будут отображены выше списка тем, а при отсутствии подфорумов, данный форум будет действовать как обычный форум.<br><b>При выборе 'нет', можете пропустить остальные настройки, т.к. они не будут иметь никакого эффекта и данный форум будет действовать подобно категории.</b>" ,
												  $SKIN->form_yes_no(
												  					  "sub_can_post",
												  					  $forum['sub_can_post'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('main_div', 'maindivoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('main_div', 'maindivoff');\" "
												  					  	   )
												  					) . $extra
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
		
		$md_show  = $forum['sub_can_post'] == 1 ? 'show' : 'none';
		$md2_show = $forum['sub_can_post'] == 1 ? 'none' : 'show';
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= "\n<div id='main_div' style='display:$md_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "Настройки сообщений форума" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование [doHTML] тэгов?</b><br />Любые HTML тэги в сообщениях будут срабатывать." ,
												  $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить коды форума в сообщениях?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить форму быстрого ответа?</b>" ,
												  $SKIN->form_yes_no("quick_reply", $forum['quick_reply'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить создание опросов в этом форуме?</b>" ,
												  $SKIN->form_yes_no("allow_poll", $forum['allow_poll'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить обновление тем при новых голосах?</b><br />При добавлении голоса в опросах, тема с опросом будет подниматься вверх" ,
												  $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить счётчик сообщений в этом форуме?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модерировать сообщения?</b><br>(Необходимо добавить модератора в этот форум, для проверки всех тем и сообщений перед их опубликованием)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, 'Нет' ),
												  									 1 => array( 1, 'Модерировать все новые темы и сообщения' ),
												  									 2 => array( 2, 'Модерировать только новые темы' ),
												  									 3 => array( 3, 'Модерировать только сообщения' ),
												  									   ),
												  							    $forum['preview_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail адреса, на которые необходимо отправлять уведомления об ожидающих подтверждения темах</b><br>(Если нет необходимости в этом, не заполняйте это поле)<br />При вводе нескольких адресов, разделяйте адреса через запятую, например (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запаролить вход в этот форум?<br>Введите пароль</b><br>(Не заполняйте это поле, если хотите сделать вход в этот форум свободным)" ,
												  $SKIN->form_input("FORUM_PROTECT", $forum['password'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать темы за последние</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, 'День' ),
																					1 => array( 5, '5 дней'  ),
																					2 => array( 7, '7 дней'  ),
																					3 => array( 10, '10 дней' ),
																					4 => array( 15, '15 дней' ),
																					5 => array( 20, '20 дней' ),
																					6 => array( 25, '25 дней' ),
																					7 => array( 30, '30 дней' ),
																					8 => array( 60, '60 дней' ),
																					9 => array( 90, '90 дней' ),
																					10=> array( 100,'Показывать все'     ),
																				 ),
												  						$forum['prune']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сортировать темы по</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', 'Дате последнего сообщения' ),
																					1 => array( 'title'    , 'Названиям тем' ),
																					2 => array( 'starter_name', 'Авторам тем' ),
																					3 => array( 'posts'    , 'Кол-ву сообщений в темах' ),
																					4 => array( 'views'    , 'Кол-ву просмотров тем' ),
																					5 => array( 'start_date', 'Дате создания тем' ),
																					6 => array( 'last_poster_name'   , 'Последним авторам' ),
																				 ),
												  						$forum['sort_key']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Порядок сортировки</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', 'По убыванию (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', 'По возрастанию (A - Z, 10 - 0)' ),
																				 ),
												  						$forum['sort_order']
												  					  )
									     )      );
									
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Настройки сообщений форума</a></div>
						     </div>
						 <br />
		                 </div>\n";
		                 
		                
		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">Настройки сообщений форума</a></div>
						     </div>
		                 </div><br />\n";
		
		$ADMIN->html .= $SKIN->end_form_standalone("Отредактировать форум");
		
		$ADMIN->nav[] = array( 'act=cat', 'Управление форумами' );
		
		$ADMIN->output();
			
			
	}
	
	function doeditsub() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['FORUM_NAME'] = trim($IN['name']);
		
		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("Необходимо ввести название форума");
		}
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Вы не выбрали ни одного форума. Вернитесь назад и повторите попытку.");
		}
		
		// Get the new forum id. We could use auto_incrememnt, but we need the ID to use as the default
		// forum position...
		
		$db_string = $DB->compile_db_update_string( array (
															'name'             => $IN['FORUM_NAME'],
															'description'      => $std->my_nl2br( $std->txt_stripslashes($HTTP_POST_VARS['desc']) ),
															'category'         => $IN['CATEGORY'],
															'subwrap'          => 1,
															'sub_can_post'     => $IN['sub_can_post'],
															'use_ibc'           => $IN['FORUM_IBC'],
															'use_html'          => $IN['FORUM_HTML'],
															'status'            => $IN['FORUM_STATUS'],
															'password'          => $IN['FORUM_PROTECT'],
															'sort_key'          => $IN['SORT_KEY'],
															'sort_order'        => $IN['SORT_ORDER'],
															'prune'             => $IN['PRUNE_DAYS'],
															'preview_posts'     => $IN['MODERATE'],
															'allow_poll'        => $IN['allow_poll'],
															'allow_pollbump'    => $IN['allow_pollbump'],
															'inc_postcount'     => $IN['inc_postcount'],
															'quick_reply'       => $IN['quick_reply'],
															'redirect_on'       => $IN['redirect_on'],
															'redirect_hits'     => $IN['redirect_hits'],
															'redirect_url'      => $IN['redirect_url'],
															'redirect_loc'		=> $IN['redirect_loc'],
															'notify_modq_emails'=> $IN['notify_modq_emails'],
															
												  )       );
												  
		$DB->query("UPDATE ibf_forums SET $db_string WHERE id='".$IN['f']."'");
		
		$ADMIN->save_log("Редактирование подкатегории '{$IN['FORUM_NAME']}'");
		
		$ADMIN->done_screen("Форум отредактирован", "Управление форумами", "act=cat" );
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// EDIT FORUM
	//
	//+---------------------------------------------------------------------------------
	
	function perm_edit_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("Вы не выбрали форум для редактирования!");
		}
		
		$cats = array();
		
		$DB->query("SELECT id,name FROM ibf_categories ORDER BY position");
		
		while ( $r = $DB->fetch_row() )
		{
			$cats[] = array( $r['CAT_ID'] , $r['CAT_NAME'] );
		}
		
		$DB->query("SELECT * FROM ibf_forums WHERE id='".$IN['f']."'");
		$forum = $DB->fetch_row();
		
		if ($forum['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID форума {$IN['f']} в базе данных");
		}
		
		
		
		
		$ADMIN->page_title = "Редактирование параметров доступа для ".$forum['name'];
		
		$ADMIN->page_detail = "<b>Параметры доступа</b><br>(Установите галочку для разрешения или снимите галочку для запрета)<br>Если Вы снимете галочку в колонке 'Чтение' для какой-либо группы, данная группа вообще не увидит этот форум.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'pdoedit'  ),
												  2 => array( 'act'   , 'forum'   ),
												  3 => array( 'f'     , $IN['f']  ),
												  4 => array( 'name'  , $forum['name'] ),
											) );
											
		$SKIN->td_header[] = array( "Название"  , "40%" );
		$SKIN->td_header[] = array( "Чтение"  , "15%" );
		$SKIN->td_header[] = array( "Ответы" , "15%" );
		$SKIN->td_header[] = array( "Создание" , "15%" );
		$SKIN->td_header[] = array( "Загрузка", "15%" );
		
		$ADMIN->html .= $SKIN->start_table("Параметры доступа");
		
		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);
									 
		$ADMIN->html .= $SKIN->end_form("Отредактировать форум");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}


	function perm_do_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$perms = $ADMIN->compile_forum_perms();
		
		
		$db_string = $DB->compile_db_update_string( array (
															
															'start_perms' => $perms['START'],
															'reply_perms' => $perms['REPLY'],
															'read_perms'  => $perms['READ'],
															'upload_perms' => $perms['UPLOAD'],
															
												  )       );
												  
		$DB->query("UPDATE ibf_forums SET $db_string WHERE id='".$IN['f']."'");
		
		$ADMIN->save_log("Редактирование параметров доступа для '{$IN['name']}'");
		
		$ADMIN->done_screen("Параметры доступа отредактированы", "Управление форумами", "act=cat" );
		
		
		
	}
	
		
}


?>