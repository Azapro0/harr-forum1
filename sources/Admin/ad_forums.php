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
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
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
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$DB->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("���������� ���������� ID ����� ������");
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
		
		$ADMIN->page_title = "����� ����� ������";
		$ADMIN->page_detail  = "����� �� ������ ���������� ��� ������� ������ ���� ��� ������� ������. ��������� �������������� ���� ����� ������.";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doskinedit'),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ����� ��� ������: {$forum['name']}" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���� ����������?</b>" ,
												  $SKIN->form_dropdown("fsid", $form_array, $forum['skin_id'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("���������� ����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	function do_skin_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$DB->query("SELECT id, name, skin_id FROM ibf_forums WHERE id='".$IN['f']."'");
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("���������� ���������� ID ����� ������");
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
		
		$ADMIN->save_log("��������� ����� ��� ������ '{$forum['name']}'");
		
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
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$cats = array();
		
		$name = "";
		
		$last_cat_id = -1;
		
		$DB->query("SELECT c.id, c.name, f.id as forum_id, f.subwrap, f.name as forum_name, f.parent_id, f.category FROM ibf_categories c, ibf_forums f WHERE c.id > 0 ORDER BY c.position, f.position");
		
		while ( $r = $DB->fetch_row() )
		{
		
			if ($last_cat_id != $r['id'])
			{
				$cats[] = array( "c_".$r['id'] , "���������: ".$r['name'] );
				
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
			
				$cats[] = array( "f_".$r['forum_id'], "������������ ������: ".$r['forum_name'] );
			}
			
		}
		
		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------
		
		if ($DB->get_num_rows() < 2)
		{
			$ADMIN->error("���������� ������� ���� �����. ����� ��������� ����� ������, �������� ����� ��������� ��� ������������.");
		}
		
		//+-------------------------------
		
		$ADMIN->page_title = "�������� ������������ '$name'";
		
		$ADMIN->page_detail = "����� ���������, ������� � ���, ��� �� �� ������ � ���� ������������ �� ������ ������.";
		
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
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �����: </b>" , $name )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��� <i>������ ���� ������������</i> �</b>" ,
												  $SKIN->form_dropdown( "MOVE_ID", $cats )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����������� ������ � ������� �����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	
	function dosubdelete() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("���������� ���������� ID ��������� ������.");
		}
		
		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������ ������������.");
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
		
		$ADMIN->save_log("�������� ������������ '{$IN['name']}'");
		
		$ADMIN->done_screen("����� �����", "���������� ��������", "act=cat" );
		
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
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$DB->query("SELECT id, name, show_rules, rules_title, rules_text FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("���������� ���������� ID ����� ������");
		}
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		
		$ADMIN->page_title = "������� ������";
		$ADMIN->page_detail  = "�� ������ ���������, �������������, ������� ������� ������ � �������� ������ ����������� ������.";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dorules'),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"   , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"   , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������ ������" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ �����������</b>" ,
												  $SKIN->form_dropdown( "show_rules",
																		array( 
																				0 => array( '0' , '�� ����������' ),
																				1 => array( '1' , '���������� ������ ������' ),
																				2 => array( '2' , '���������� � ���� ������� ������' )
																			 ),
												  						$forum['show_rules']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������</b>" ,
												  $SKIN->form_input("title", $std->txt_stripslashes(str_replace( "'", '&#039;', $forum['rules_title'])))
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b><br>(����� ������������ HTML)" ,
												  $SKIN->form_textarea( "body", $std->txt_stripslashes($forum['rules_text']), 65, 20 )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("��������� �������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	function do_rules() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$rules = array( 
						'rules_title'    => $ADMIN->make_safe($std->txt_stripslashes($HTTP_POST_VARS['title'])),
						'rules_text'     => $ADMIN->make_safe($std->txt_stripslashes($HTTP_POST_VARS['body'])),
						'show_rules'     => $IN['show_rules']
					  );
					  
		$dbs = $DB->compile_db_update_string($rules);
		
		$DB->query("UPDATE ibf_forums SET $dbs WHERE id='".$IN['f']."'");
		
		$ADMIN->done_screen("������� ������ ���������", "���������� ��������", "act=cat" );
		
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
			$ADMIN->error("���������� ���������� ID ������������������� ������.");
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
		
		$ADMIN->save_log("�������� ��������� ������ '{$forum['name']}'");
		
		$ADMIN->done_screen("����� �����������������", "���������� ��������", "act=cat" );
		
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
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$DB->query("SELECT id, name FROM ibf_forums WHERE id='".$IN['f']."'");
		
		//+-------------------------------
		// Make sure we have a legal forum
		//+-------------------------------
		
		if ( !$DB->get_num_rows() )
		{
			$ADMIN->error("���������� ���������� ID ����� ������");
		}
		
		$forum = $DB->fetch_row();
		
		//+-------------------------------
		
		$ADMIN->page_title = "������� ������ '{$forum['name']}'";
		
		$ADMIN->page_detail = "����� ������� ��� ����, ��������� � ������.<br>��� ����� �� ����� ����� - ��������� ��� ��� ����� ������������, - ������������� �� �� ������ �������� �����.";
		
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
		
		$ADMIN->html .= $SKIN->start_table( "������� ������ '{$forum['name']}" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �����: </b>" , $forum['name'] )      );
									     
		$ADMIN->html .= $SKIN->end_form("�������� �����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_empty() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("���������� ���������� ID ��������� ������.");
		}
		
		// Check to make sure its a valid forum.
		
		$DB->query("SELECT id, posts, topics FROM ibf_forums WHERE id='".$IN['f']."'");
		
		if ( ! $forum = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ������ ���������� ������");
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
		
		$ADMIN->save_log("������� ���� ��������� ������ '{$IN['name']}'");
		
		$ADMIN->done_screen("����� ������", "���������� ��������", "act=cat" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function reorder_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "�������������� �������";
		$ADMIN->page_detail  = "��� �������������� �������, ������ �������� ����� �������, � ���������� ����, ����� � ������ ������� � ������� ������ ��������� ���������";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doreorder'),
												  2 => array( 'act'   , 'forum'     ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"       , "10%" );
		$SKIN->td_header[] = array( "�������� ������"   , "60%" );
		$SKIN->td_header[] = array( "���������"        , "15%" );
		$SKIN->td_header[] = array( "���"       , "15%" );
		
		$ADMIN->html .= $SKIN->start_table( "���� ��������� � ������" );
		
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
		
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
		
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
		
		$ADMIN->save_log("�������������� �������");
		
		$ADMIN->done_screen("������ ���������������", "���������� ��������", "act=cat" );
		
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
			$ADMIN->error("���������� ���������� ID ���������� ������.");
		}
		
		$DB->query("SELECT id, name FROM ibf_forums ORDER BY position");
		
		//+-------------------------------
		// Make sure we have more than 1
		// forum..
		//+-------------------------------
		
		if ($DB->get_num_rows() < 2)
		{
			$ADMIN->error("���������� ������� �����. ����� ��������� ������, �� ������ ������� ����� �����");
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
		
		$ADMIN->page_title = "�������� ������ '$name'";
		
		$ADMIN->page_detail = "����� ��������� ������, ��������� � ���, ��� � ���� ������ �� �� �������� ����������� ��� ��� � ���������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'),
												  2 => array( 'act'   , 'forum'     ),
												  3 => array( 'f'     , $IN['f']  ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �����: </b>" , $name )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��� <i>������������ ���� � ��������� ����� ������</i> �</b>" ,
												  $SKIN->form_dropdown( "MOVE_ID", $form_array )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����������� ���� � ������� �����");
										 
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
			$ADMIN->error("���������� ���������� ID ��������� ������.");
		}
		
		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������ ������������.");
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
		
		$ADMIN->save_log("�������� ������ '{$forum['name']}'");
		
		$ADMIN->done_screen("����� �����", "���������� ��������", "act=cat" );
		
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
					$cats[] = array( "c_".$r['id'] , "���������: ".$r['name'] );
					
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
				$cats[] = array( "c_".$r['id'] , "���������: ".$r['name'] );
			}
			
		}
		
		$ADMIN->page_title = "�������� ������ ������";
		
		$ADMIN->page_detail = "��� ������ ������ ��� �������� ������ ������ � ���������� ��� � ������������ ���������. ��������� � ������������ ������ ��������� ��� 
							   ����� ������. ���� �� ��-���� ��������� ������, �� ������ � ����� ����� ����� �� ������ \"���������� ��������\" � ���������� ����������� ���������
							   � ��������� ������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'donew'  ),
												  2 => array( 'act'   , 'forum'  ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ���������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� � ���������</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, '��������' ),
																					1 => array( 0, '������ ��� ������ �������'  ),
																				 ),
												  						"1"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
												  $SKIN->form_input("FORUM_NAME", $f_name)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b><br>����� ������������ HTML - ������� ����� ����� ��������� �������������" ,
												  $SKIN->form_textarea("FORUM_DESC")
									     )      );
									     
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� ������: ��������� ��������� � ���� ������?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ���� � ��������� � ���� ������?</b><br>��� ������ �� ��� ��������� ����� ���������� ���� ������ ���, � ��� ���������� ����������, ������ ����� ����� ����������� ��� ������� �����.<br><b>��� ������ '���', ������ ���������� ��������� ���������, �.�. ��� �� ����� ����� �������� ������� � ������ ����� ����� ����������� ������� ���������.</b>" ,
												  $SKIN->form_yes_no("sub_can_post", 1)."<br><b>����������</b> ��� ����� �� ����� ����� �������� �������, ���� ������ ����� ��� ��� �������� ����������.",
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� �������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������� ����� ������</b>" ,
												  $SKIN->form_input("redirect_url")
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������������</b><br>�� ���������� ��� ����, ���� ����������� '_self', ��� �������� ������ � ��� �� ����" ,
												  $SKIN->form_input("redirect_loc")
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ �������������?</b><br>��� ������ '��', ������ ���������� ��������� ��������� � ����� ������� � ���������� �������, �.�. ������ ����� ����� ����������� ������ ��� ������ �������������. ������������ � ���� ������ ��������� �� ����� ��������." ,
												  $SKIN->form_yes_no("redirect_on", 0)
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� �� ������ ������</b>" ,
												  $SKIN->form_input("redirect_hits", 0)
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� ������" );
		
		//+-------------------------------
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� [doHTML] �����?</b><br />����� HTML ���� � ���������� ����� �����������." ,
												  $SKIN->form_yes_no("FORUM_HTML", 0 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� ������ � ����������?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", 1 )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����� �������� ������?</b>" ,
												  $SKIN->form_yes_no("quick_reply", 1 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("allow_poll", 1 )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� ��� ��� ����� �������?</b><br />��� ���������� ������ � �������, ���� � ������� ����� ����������� �����" ,
												  $SKIN->form_yes_no("allow_pollbump", 0 )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������� ��������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", 1 )
									     )      );
									     
		//-----------
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���������?</b><br>(���������� �������� ���������� � ���� �����, ��� �������� ���� ��� � ��������� ����� �� ��������������)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, '���' ),
												  									 1 => array( 1, '������������ ��� ����� ���� � ���������' ),
												  									 2 => array( 2, '������������ ������ ����� ����' ),
												  									 3 => array( 3, '������������ ������ ���������' ),
												  									   ),
												  							    0 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ������, �� ������� ���������� ���������� ����������� � �����, ��������� �������������</b><br>(���� ��� ������������� � ����, �� ���������� ��� ����)<br />��� ����� ���������� �������, ���������� ������ ����� �������, �������� (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� � ���� �����?<br>������� ������</b><br>(�� ���������� ��� ����, ���� ������ ������� ���� � ���� ����� ���������)" ,
												  $SKIN->form_input("FORUM_PROTECT")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� �� ���������</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, '����' ),
																					1 => array( 5, '5 ����'  ),
																					2 => array( 7, '7 ����'  ),
																					3 => array( 10, '10 ����' ),
																					4 => array( 15, '15 ����' ),
																					5 => array( 20, '20 ����' ),
																					6 => array( 25, '25 ����' ),
																					7 => array( 30, '30 ����' ),
																					8 => array( 60, '60 ����' ),
																					9 => array( 90, '90 ����' ),
																					10=> array( 100,'���������� ���'     ),
																				 ),
												  						"30"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���� ��</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', '���� ���������� ���������' ),
																					1 => array( 'title'    , '��������� ���' ),
																					2 => array( 'starter_name', '������� ���' ),
																					3 => array( 'posts'    , '���-�� ��������� � �����' ),
																					4 => array( 'views'    , '���-�� ���������� ���' ),
																					5 => array( 'start_date', '���� �������� ���' ),
																					6 => array( 'last_poster_name'   , '��������� �������' ),
																				 ),
												  						"last_post"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����������</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', '�� �������� (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', '�� ����������� (A - Z, 10 - 0)' ),
																				 ),
												  						"Z-A"
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
											
		$SKIN->td_header[] = array( "��������"  , "40%" );
		$SKIN->td_header[] = array( "������"  , "15%" );
		$SKIN->td_header[] = array( "������" , "15%" );
		$SKIN->td_header[] = array( "��������" , "15%" );
		$SKIN->td_header[] = array( "��������", "15%" );
		
		$ADMIN->html .= $SKIN->start_table("��������� �������");
		
		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);
		
		$ADMIN->html .= $SKIN->end_form("������� �����");
										 
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
			$ADMIN->error("���������� ������ �������� ������");
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
		
		$ADMIN->save_log("�������� ������ '{$IN['FORUM_NAME']}'");
		
		$ADMIN->done_screen("����� {$IN['FORUM_NAME']} ������", "���������� ��������", "act=cat" );
		
		
		
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
			$ADMIN->error("�� �� ������� ����� ��� ��������������!");
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
				$cats[] = array( "c_".$r['id'] , "���������: ".$r['name'] );
				
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
			$ADMIN->error("���������� ���������� ID ������ {$IN['f']} � ���� ������");
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
		
		$ADMIN->page_title = "�������������� ������";
		
		$ADMIN->page_detail = "����� �� ������ ������������� ������������ ������. ��� ��������� ���������� ������� � ������� (����� ��� 
							   �������� ���, ������, ������) ������� �� '��������� �������' ��� ������� ������ � �����������.";
		
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
		
		$ADMIN->html .= $SKIN->start_table( "�������� ���������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� � ���������</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats, $real_parent)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, '��������' ),
																					1 => array( 0, '������ ��� ������ �������'  ),
																				 ),
												  						$forum['status']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
												  $SKIN->form_input("FORUM_NAME", $forum['name'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b><br>����� ������������ HTML - ������� ����� ����� ����������������� ������������� � &lt;br&gt;" ,
												  $SKIN->form_textarea("FORUM_DESC", $std->my_br2nl( $forum['description']) )
									     )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� �������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������� ����� ������</b>" ,
												  $SKIN->form_input("redirect_url", $forum['redirect_url'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������������</b><br>�� ���������� ��� ����, ���� ����������� '_self', ��� �������� ������ � ��� �� ����" ,
												  $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ �������������?</b><br>��� ������ '��', ������ ���������� ��������� ���������, �.�. ������ ����� ����� ����������� ������ ��� ������ �������������. ������������ � ���� ������ ��������� �� ����� ��������." ,
												  $SKIN->form_yes_no("redirect_on",
												 					 $forum['redirect_on'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
												  					  	   )
												  					 )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� �� ������ ������</b>" ,
												  $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		
		if ($forum['parent_id'] > 0)
		{
			$extra = "<span id='normal' style='color:red'><br><b>����������</b>: ������ ����� <b>��</b> �������� �������� ������� � ��� ��������� �� ����� ����� �������� �������, ���� �� �� �������� ��� �������.</span>";
		}
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$cp2_show = $forum['redirect_on'] == 1 ? 'show' : 'none';
		$cp_show  = $forum['redirect_on'] == 1 ? 'none' : 'show';
		
		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� ������: ��������� ��������� � ���� ������?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ���� � ��������� � ���� ������?</b><br>��� ������ �� ��� ��������� ����� ���������� ���� ������ ���, � ��� ���������� ����������, ������ ����� ����� ����������� ��� ������� �����.<br><b>��� ������ '���', ������ ���������� ��������� ���������, �.�. ��� �� ����� ����� �������� ������� � ������ ����� ����� ����������� ������� ���������.</b>" ,
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
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� ������" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� [doHTML] �����?</b><br />����� HTML ���� � ���������� ����� �����������." ,
												  $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� ������ � ����������?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����� �������� ������?</b>" ,
												  $SKIN->form_yes_no("quick_reply", $forum['quick_reply'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("allow_poll", $forum['allow_poll'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� ��� ��� ����� �������?</b><br />��� ���������� ������ � �������, ���� � ������� ����� ����������� �����" ,
												  $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������� ��������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���������?</b><br>(���������� �������� ���������� � ���� �����, ��� �������� ���� ��� � ��������� ����� �� ��������������)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, '���' ),
												  									 1 => array( 1, '������������ ��� ����� ���� � ���������' ),
												  									 2 => array( 2, '������������ ������ ����� ����' ),
												  									 3 => array( 3, '������������ ������ ���������' ),
												  									   ),
												  							    $forum['preview_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ������, �� ������� ���������� ���������� ����������� �� ��������� ������������� �����</b><br>(���� ��� ������������� � ����, �� ���������� ��� ����)<br />��� ����� ���������� �������, ���������� ������ ����� �������, �������� (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� � ���� �����?<br>������� ������</b><br>(�� ���������� ��� ����, ���� ������ ������� ���� � ���� ����� ���������)" ,
												  $SKIN->form_input("FORUM_PROTECT", $forum['password'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� �� ���������</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, '����' ),
																					1 => array( 5, '5 ����'  ),
																					2 => array( 7, '7 ����'  ),
																					3 => array( 10, '10 ����' ),
																					4 => array( 15, '15 ����' ),
																					5 => array( 20, '20 ����' ),
																					6 => array( 25, '25 ����' ),
																					7 => array( 30, '30 ����' ),
																					8 => array( 60, '60 ����' ),
																					9 => array( 90, '90 ����' ),
																					10=> array( 100,'���������� ���'     ),
																				 ),
												  						$forum['prune']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���� ��</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', '���� ���������� ���������' ),
																					1 => array( 'title'    , '��������� ���' ),
																					2 => array( 'starter_name', '������� ���' ),
																					3 => array( 'posts'    , '���-�� ��������� � �����' ),
																					4 => array( 'views'    , '���-�� ���������� ���' ),
																					5 => array( 'start_date', '���� �������� ���' ),
																					6 => array( 'last_poster_name'   , '��������� �������' ),
																				 ),
												  						$forum['sort_key']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����������</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', '�� �������� (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', '�� ����������� (A - Z, 10 - 0)' ),
																				 ),
												  						$forum['sort_order']
												  					  )
									     )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">��������� ��������� ������</a></div>
						     </div>
		                 </div><br />\n";
		                 
		                
		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">��������� ��������� ������</a></div>
						     </div>
		                 </div><br />\n";
		
		$ADMIN->html .= $SKIN->end_form_standalone("Edit this forum");
		
		$ADMIN->nav[] = array( 'act=cat', '���������� ��������' );
		
		$ADMIN->output();
			
			
	}


	//+---------------------------------------------------------------------------------
	
	function do_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['FORUM_NAME'] = trim($IN['FORUM_NAME']);
		
		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("���������� ������ �������� ������");
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
		
		$ADMIN->save_log("�������������� ������ '{$IN['name']}'");
		
		$ADMIN->done_screen("����� {$IN['name']} ��������������", "���������� ��������", "act=cat" );
		
		
		
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
			$cats[] = array( $r['id'] , "���������: ".$r['name'] );
		}
		
		$DB->query("SELECT * from ibf_forums WHERE subwrap='1' AND id='".$IN['f']."'");
		
		if (! $forum = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ����� ��� ������������ � ���� ������");
		}
		
		if ($forum['password'] == '-1')
		{
			$forum['password'] = "";
		}
		
		$ADMIN->page_title = "�������������� ������������";
		
		$ADMIN->page_detail = "��� ������ ��� �������������� ������������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doeditsub'  ),
												  2 => array( 'act'   , 'forum'  ),
												  3 => array( 'f'     , $IN['f'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ���������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� � ���������</b><br>" ,
												  $SKIN->form_dropdown("CATEGORY", $cats, $forum['category'])
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
												  $SKIN->form_input("name", $forum['name'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
												  $SKIN->form_textarea("desc", $forum['description'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������</b>" ,
												  $SKIN->form_dropdown( "FORUM_STATUS",
																			array( 
																					0 => array( 1, '��������' ),
																					1 => array( 0, '������ ��� ������ �������'  ),
																				 ),
												  						$forum['status']
												  					  )
									     )      );
									     
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� �������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������� ����� ������</b>" ,
												  $SKIN->form_input("redirect_url", $forum['redirect_url'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������������</b><br>�� ���������� ��� ����, ���� ����������� '_self', ��� �������� ������ � ��� �� ����" ,
												  $SKIN->form_input("redirect_loc", $forum['redirect_loc'])
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ �������������?</b><br>��� ������ '��', ������ ���������� ��������� ���������, �.�. ������ ����� ����� ����������� ������ ��� ������ �������������. ������������ � ���� ������ ��������� �� ����� ��������." ,
												  $SKIN->form_yes_no("redirect_on",
												 					 $forum['redirect_on'],
												  					  array(
												  					  		  'yes' => " onclick=\"ShowHide('canpost', 'canpostoff');\" ",
												  					  		  'no'  => " onclick=\"ShowHide('canpost', 'canpostoff');\" "
												  					  	   )
												  					 )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� �� ������ ������</b>" ,
												  $SKIN->form_input("redirect_hits", $forum['redirect_hits'])
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		$cp2_show = $forum['redirect_on'] == 1 ? 'show' : 'none';
		$cp_show  = $forum['redirect_on'] == 1 ? 'none' : 'show';
		
		$ADMIN->html .= "\n<div id='canpost' style='display:$cp_show'>\n";
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� � ���� ������?" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ���� � ��������� � ���� ������?</b><br>��� ������ ��, ��� ��������� ����� ���������� ���� ������ ���, � ��� ���������� ����������, ������ ����� ����� ����������� ��� ������� �����.<br><b>��� ������ '���', ������ ���������� ��������� ���������, �.�. ��� �� ����� ����� �������� ������� � ������ ����� ����� ����������� ������� ���������.</b>" ,
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
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� [doHTML] �����?</b><br />����� HTML ���� � ���������� ����� �����������." ,
												  $SKIN->form_yes_no("FORUM_HTML", $forum['use_html'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� ������ � ����������?</b>" ,
												  $SKIN->form_yes_no("FORUM_IBC", $forum['use_ibc'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����� �������� ������?</b>" ,
												  $SKIN->form_yes_no("quick_reply", $forum['quick_reply'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("allow_poll", $forum['allow_poll'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� ��� ��� ����� �������?</b><br />��� ���������� ������ � �������, ���� � ������� ����� ����������� �����" ,
												  $SKIN->form_yes_no("allow_pollbump", $forum['allow_pollbump'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������� ��������� � ���� ������?</b>" ,
												  $SKIN->form_yes_no("inc_postcount", $forum['inc_postcount'] )
									     )      );
									     
		//-----------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���������?</b><br>(���������� �������� ���������� � ���� �����, ��� �������� ���� ��� � ��������� ����� �� ��������������)" ,
												  $SKIN->form_dropdown("MODERATE", array(
												  									 0 => array( 0, '���' ),
												  									 1 => array( 1, '������������ ��� ����� ���� � ���������' ),
												  									 2 => array( 2, '������������ ������ ����� ����' ),
												  									 3 => array( 3, '������������ ������ ���������' ),
												  									   ),
												  							    $forum['preview_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ������, �� ������� ���������� ���������� ����������� �� ��������� ������������� �����</b><br>(���� ��� ������������� � ����, �� ���������� ��� ����)<br />��� ����� ���������� �������, ���������� ������ ����� �������, �������� (add@ress1.com,add@ress2.com)" ,
												  $SKIN->form_input("notify_modq_emails", $forum['notify_modq_emails'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� � ���� �����?<br>������� ������</b><br>(�� ���������� ��� ����, ���� ������ ������� ���� � ���� ����� ���������)" ,
												  $SKIN->form_input("FORUM_PROTECT", $forum['password'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� �� ���������</b>" ,
												  $SKIN->form_dropdown( "PRUNE_DAYS",
																			array( 
																					0 => array( 1, '����' ),
																					1 => array( 5, '5 ����'  ),
																					2 => array( 7, '7 ����'  ),
																					3 => array( 10, '10 ����' ),
																					4 => array( 15, '15 ����' ),
																					5 => array( 20, '20 ����' ),
																					6 => array( 25, '25 ����' ),
																					7 => array( 30, '30 ����' ),
																					8 => array( 60, '60 ����' ),
																					9 => array( 90, '90 ����' ),
																					10=> array( 100,'���������� ���'     ),
																				 ),
												  						$forum['prune']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���� ��</b>" ,
												  $SKIN->form_dropdown( "SORT_KEY",
																			array( 
																					0 => array( 'last_post', '���� ���������� ���������' ),
																					1 => array( 'title'    , '��������� ���' ),
																					2 => array( 'starter_name', '������� ���' ),
																					3 => array( 'posts'    , '���-�� ��������� � �����' ),
																					4 => array( 'views'    , '���-�� ���������� ���' ),
																					5 => array( 'start_date', '���� �������� ���' ),
																					6 => array( 'last_poster_name'   , '��������� �������' ),
																				 ),
												  						$forum['sort_key']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����������</b>" ,
												  $SKIN->form_dropdown( "SORT_ORDER",
																			array( 
																					0 => array( 'Z-A', '�� �������� (Z - A, 0 - 10)' ),
																					1 => array( 'A-Z', '�� ����������� (A - Z, 10 - 0)' ),
																				 ),
												  						$forum['sort_order']
												  					  )
									     )      );
									
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->html .= "\n<!--END MAIN DIV--></div>\n
		                   <div id='maindivoff' class='offdiv' style='display:$md2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('main_div', 'maindivoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">��������� ��������� ������</a></div>
						     </div>
						 <br />
		                 </div>\n";
		                 
		                
		$ADMIN->html .= "\n<!--END CAN POST DIV--></div>\n
		                   <div id='canpostoff' style='display:$cp2_show'>
		                     <div class='tableborder'>
						       <div class='maintitle'><a href=\"javascript:ShowHide('canpost', 'canpostoff');\"><img src='{$SKIN->img_url}/plus.gif'></a>&nbsp;<a href=\"javascript:ShowHide('main_div', 'maindivoff');\">��������� ��������� ������</a></div>
						     </div>
		                 </div><br />\n";
		
		$ADMIN->html .= $SKIN->end_form_standalone("��������������� �����");
		
		$ADMIN->nav[] = array( 'act=cat', '���������� ��������' );
		
		$ADMIN->output();
			
			
	}
	
	function doeditsub() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['FORUM_NAME'] = trim($IN['name']);
		
		if ($IN['FORUM_NAME'] == "")
		{
			$ADMIN->error("���������� ������ �������� ������");
		}
		
		if ($IN['f'] == "")
		{
			$ADMIN->error("�� �� ������� �� ������ ������. ��������� ����� � ��������� �������.");
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
		
		$ADMIN->save_log("�������������� ������������ '{$IN['FORUM_NAME']}'");
		
		$ADMIN->done_screen("����� ��������������", "���������� ��������", "act=cat" );
		
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
			$ADMIN->error("�� �� ������� ����� ��� ��������������!");
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
			$ADMIN->error("���������� ���������� ID ������ {$IN['f']} � ���� ������");
		}
		
		
		
		
		$ADMIN->page_title = "�������������� ���������� ������� ��� ".$forum['name'];
		
		$ADMIN->page_detail = "<b>��������� �������</b><br>(���������� ������� ��� ���������� ��� ������� ������� ��� �������)<br>���� �� ������� ������� � ������� '������' ��� �����-���� ������, ������ ������ ������ �� ������ ���� �����.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'pdoedit'  ),
												  2 => array( 'act'   , 'forum'   ),
												  3 => array( 'f'     , $IN['f']  ),
												  4 => array( 'name'  , $forum['name'] ),
											) );
											
		$SKIN->td_header[] = array( "��������"  , "40%" );
		$SKIN->td_header[] = array( "������"  , "15%" );
		$SKIN->td_header[] = array( "������" , "15%" );
		$SKIN->td_header[] = array( "��������" , "15%" );
		$SKIN->td_header[] = array( "��������", "15%" );
		
		$ADMIN->html .= $SKIN->start_table("��������� �������");
		
		$ADMIN->html .= $SKIN->build_group_perms($forum['read_perms'], $forum['start_perms'], $forum['reply_perms'], $forum['upload_perms']);
									 
		$ADMIN->html .= $SKIN->end_form("��������������� �����");
										 
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
		
		$ADMIN->save_log("�������������� ���������� ������� ��� '{$IN['name']}'");
		
		$ADMIN->done_screen("��������� ������� ���������������", "���������� ��������", "act=cat" );
		
		
		
	}
	
		
}


?>