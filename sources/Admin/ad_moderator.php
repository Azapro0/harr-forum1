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
|   > Admin Category functions
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

$idx = new ad_mod();


class ad_mod {

	var $base_url;

	function ad_mod() {
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
				$this->add_one();
				break;
			case 'add_two':
				$this->add_two();
				break;
			case 'add_final':
				$this->mod_form('add');
				break;
			case 'doadd':
				$this->add_mod();
				break;
				
			case 'edit':
				$this->mod_form('edit');
				break;
				
			case 'doedit':
				$this->do_edit();
				break;
				
			case 'remove':
				$this->do_delete();
				break;
				
			default:
				$this->show_list();
				break;
		}
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// DELETE MODERATOR
	//
	//+---------------------------------------------------------------------------------
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("�� ������� ������� ID ����������");
		}
		
		$DB->query("SELECT * FROM ibf_moderators WHERE mid='".$IN['mid']."'");
		$mod = $DB->fetch_row();
		
		if ( $mod['is_group'] )
		{
			$name = 'Group: '.$mod['group_name'];
		}
		else
		{
			$name = $mod['member_name'];
		}
			
		$DB->query("DELETE FROM ibf_moderators WHERE mid='".$IN['mid']."'");
		
		$ADMIN->save_log("�������� ���������� '{$name}'");
		
		$ADMIN->done_screen("��������� �����", "���������� ������������", "act=mod" );
		
	}	
	
	
	//+---------------------------------------------------------------------------------
	//
	// EDIT MODERATOR
	//
	//+---------------------------------------------------------------------------------
	
	function do_edit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("�� ������� ������� ID ����������");
		}
		
		$DB->query("SELECT member_name FROM ibf_moderators WHERE mid='".$IN['mid']."'");
		$mod = $DB->fetch_row();
		
		//--------------------------------------
		// Build Mr Hash
		//--------------------------------------
		
		$mr_hash = array( 
							'forum_id'     => $IN['forum_id'],
							'edit_post'    => $IN['edit_post'],
							'edit_topic'   => $IN['edit_topic'],
							'delete_post'  => $IN['delete_post'],
							'delete_topic' => $IN['delete_topic'],
							'view_ip'      => $IN['view_ip'],
							'open_topic'   => $IN['open_topic'],
							'close_topic'  => $IN['close_topic'],
							'mass_move'    => $IN['mass_move'],
							'mass_prune'   => $IN['mass_prune'],
							'move_topic'   => $IN['move_topic'],
							'pin_topic'    => $IN['pin_topic'],
							'unpin_topic'  => $IN['unpin_topic'],
							'post_q'       => $IN['post_q'],
							'topic_q'      => $IN['topic_q'],
							'allow_warn'   => $IN['allow_warn'],
							'split_merge'  => $IN['split_merge'],
							// start oska pin hack modidifed
							'pin_first_post_topic'  => $IN['pin_first_post_topic'],
							// end oska pin hack modified
							'edit_user'    => $IN['edit_user'],
							'can_mm'	   => $IN['can_mm'],
						);
						
		
			
		$db_string = $DB->compile_db_update_string( $mr_hash );
			
		$DB->query("UPDATE ibf_moderators SET $db_string WHERE mid='".$IN['mid']."'");
		
		$ADMIN->save_log("�������������� ���������� '{$mod['member_name']}'");
		
		$ADMIN->done_screen("��������� ��������������", "���������� ������������", "act=mod" );
		
	}	
	
	//+---------------------------------------------------------------------------------
	//
	// ADD MODERATOR
	//
	//+---------------------------------------------------------------------------------
	
	function add_mod()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['fid'] == "")
		{
			$ADMIN->error("�� �� ������� �����, ��� ���������� ����� ������������");
		}
		
		//--------------------------------------
		// Build Mr Hash
		//--------------------------------------
		
		$mr_hash = array( 
							'edit_post'    => $IN['edit_post'],
							'edit_topic'   => $IN['edit_topic'],
							'delete_post'  => $IN['delete_post'],
							'delete_topic' => $IN['delete_topic'],
							'view_ip'      => $IN['view_ip'],
							'open_topic'   => $IN['open_topic'],
							'close_topic'  => $IN['close_topic'],
							'mass_move'    => $IN['mass_move'],
							'mass_prune'   => $IN['mass_prune'],
							'move_topic'   => $IN['move_topic'],
							'pin_topic'    => $IN['pin_topic'],
							'unpin_topic'  => $IN['unpin_topic'],
							'post_q'       => $IN['post_q'],
							'topic_q'      => $IN['topic_q'],
							'allow_warn'   => $IN['allow_warn'],
							'split_merge'  => $IN['split_merge'],
							// start oska pin hack modidifed
							'pin_first_post_topic'  => $IN['pin_first_post_topic'],
							// end oska pin hack modified
							'edit_user'    => $IN['edit_user'],
							'can_mm'	   => $IN['can_mm'],
						);
						
		$forum_ids = array();
		
		$DB->query("SELECT id FROM ibf_forums WHERE id IN(".$IN['fid'].")");
		
		while( $i = $DB->fetch_row() )
		{
			$forum_ids[ $i['id'] ] = $i['id'];
		}
		
		//---------------------------------------
						
		if ($IN['mod_type'] == 'group')
		{
		
			if ($IN['gid'] == "")
			{
				$ADMIN->error("ID ������ �� �������");
			}
			
			$DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id='".$IN['gid']."'");
			
			if ( ! $group = $DB->fetch_row() )
			{
				$ADMIN->error("ID ������ �� �������");
			}
			
			//---------------------------------------
			// Already using this group on this forum?
			//---------------------------------------
			
			$DB->query("SELECT * FROM ibf_moderators WHERE forum_id IN(".$IN['fid'].") and group_id={$IN['gid']}");
			
			while( $f = $DB->fetch_row() )
			{
				unset($forum_ids[ $f['forum_id'] ]);
			}
			
			$mr_hash['member_name'] = '-1';
			$mr_hash['member_id']   = '-1';
			$mr_hash['group_id']    = $group['g_id'];
			$mr_hash['group_name']  = $group['g_title'];
			$mr_hash['is_group']    = 1;
			
			$ad_log = "���������� ������ '{$group['g_title']}', ������� �����������";
			
		}
		else
		{
		
			if ($IN['mem'] == "")
			{
				$ADMIN->error("�� �� ������� ������������ ��� ���������� ��� �����������");
			}
			
			$DB->query("SELECT id, name from ibf_members WHERE id='".$IN['mem']."'");
			
			if ( ! $mem = $DB->fetch_row() )
			{
				$ADMIN->error("��� ������������ �� �������.");
			}
			
			//---------------------------------------
			// Already using this member on this forum?
			//---------------------------------------
			
			$DB->query("SELECT * FROM ibf_moderators WHERE forum_id IN(".$IN['fid'].") and member_id={$IN['mem']}");
			
			while( $f = $DB->fetch_row() )
			{
				unset($forum_ids[ $f['forum_id'] ]);
			}
			
			$mr_hash['member_name'] = $mem['name'];
			$mr_hash['member_id']   = $mem['id'];
			$mr_hash['is_group']    = 0;
			
			$ad_log = "���������� ������������ '{$mem['name']}', �����������";
		
		}
		
		//--------------------------------------
		// Check for legal forums
		//--------------------------------------
		
		if ( count($forum_ids) == 0)
		{
			$ADMIN->error("�� �� ������� �� ������ ������, ��� ������������� ��� ����������� ����������� ��� ������� �����������");
		}
		
		//--------------------------------------
		// Loopy loopy
		//--------------------------------------
		
		foreach ($forum_ids as $cartman)
		{
			$mr_hash['forum_id'] = $cartman;
			
			$kenny = $DB->compile_db_insert_string( $mr_hash );
			
			$DB->query("INSERT INTO ibf_moderators (" .$kenny['FIELD_NAMES']. ") VALUES (". $kenny['FIELD_VALUES'] .")");
		}
		
		$ADMIN->save_log($ad_log);
		
		$ADMIN->done_screen("��������� ��������", "���������� ������������", "act=mod" );
		
	}	
	
	//+---------------------------------------------------------------------------------
	//
	// ADD FINAL, display the add / edit form
	//
	//+---------------------------------------------------------------------------------
	
	function mod_form( $type='add' ) {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$group = array();
		
		if ($type == 'add')
		{
			if ($IN['fid'] == "")
			{
				$ADMIN->error("�� �� ������� �����, ��� ���������� ����� ������������");
			}	
				
			$mod   = array();
			$names = array();
			
			//--------------------------------------
			
			$DB->query("SELECT name FROM ibf_forums WHERE id IN(".$IN['fid'].")");
			
			while ( $r = $DB->fetch_row() )
			{
				$names[] = $r['name'];
			}
			
			$thenames = implode( ", ", $names );
			
			//--------------------------------------
			
			$button = "�������� ����������";
			
			$form_code = 'doadd';
			
			if ($IN['mod_type'] == 'group')
			{
				$DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id='".$IN['mod_group']."'");
				
				if (! $group = $DB->fetch_row() )
				{
					$ADMIN->error("�� ������� ���� ������, ��� ���������� ������� �����������");
				}
				
				$ADMIN->page_detail = "���������� ������: <b>{$group['g_title']}</b>, ������������� ������� �: $thenames";
				$ADMIN->page_title = "���������� ������ �����������";
			}
			else
			{
			
				if ($IN['MEMBER_ID'] == "")
				{
					$ADMIN->error("���������� ���������� id ������������");
				}
				else
				{
					$DB->query("SELECT name, id FROM ibf_members WHERE id='".$IN['MEMBER_ID']."'");
					
					if ( ! $mem = $DB->fetch_row() )
					{
						$ADMIN->error("ID ������������ �� �������������");
					}
					
					$member_id   = $mem['id'];
					$member_name = $mem['name'];
				}
				
				$ADMIN->page_detail = "���������� ������������ <b>$member_name</b> ����������� �: $thenames";
				$ADMIN->page_title = "���������� ����������";
			
			}
			
		}
		else
		{
			if ($IN['mid'] == "")
			{
				$ADMIN->error("��� ��������������, ���������� ������� ����������.");
			}
			
			$button    = "��������� ���������";
			
			$form_code = "doedit";
			
			$ADMIN->page_title  = "�������������� ����������";
			$ADMIN->page_detail = "��������� ��������� ��� ������ ����� ��������������";
			
			$DB->query("SELECT * from ibf_moderators WHERE mid='".$IN['mid']."'");
			
			if ( ! $mod = $DB->fetch_row() )
			{
				$ADMIN->error("���������� ������� ������ ����� ����������");
			}
			
			$member_id   = $mod['member_id'];
			$member_name = $mod['member_name'];
		}
		
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'     , $form_code ),
												  2 => array( 'act'      , 'mod'      ),
												  3 => array( 'mid'      , $mod['mid']),
												  4 => array( 'fid'      , $IN['fid'] ),
												  5 => array( 'mem'      , $member_id ),
												  6 => array( 'mod_type' , $IN['mod_type'] ),
												  7 => array( 'gid'      , $group['g_id'] ),
												  8 => array( 'gname'    , $group['g_name'] ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ���������" );
		
		//+-------------------------------
		
		if ($type == 'edit')
		{
			$forums = array();
			
			$DB->query("SELECT id, name FROM ibf_forums ORDER BY position");
			
			while ( $r = $DB->fetch_row() )
			{
				$forums[] = array( $r['id'], $r['name'] );
			}
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� ������...</b>" ,
												  $SKIN->form_dropdown( "forum_id", $forums, $mod['forum_id'] )
									     )      );
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ����� ���������/������?</b>" ,
												  $SKIN->form_yes_no("edit_post", $mod['edit_post'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ��������� ����� ���?</b>" ,
												  $SKIN->form_yes_no("edit_topic", $mod['edit_topic'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������� ����� ���������?</b>" ,
												  $SKIN->form_yes_no("delete_post", $mod['delete_post'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������� ����� ����/������?</b>" ,
												  $SKIN->form_yes_no("delete_topic", $mod['delete_topic'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������ IP ������ �������������?</b>" ,
												  $SKIN->form_yes_no("view_ip", $mod['view_ip'] )
									     )      );		
				
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� �������� ����?</b>" ,
												  $SKIN->form_yes_no("open_topic", $mod['open_topic'] )
									     )      );		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� �������� ����?</b>" ,
												  $SKIN->form_yes_no("close_topic", $mod['close_topic'] )
									     )      );	
									     	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���������� ����?</b>" ,
												  $SKIN->form_yes_no("move_topic", $mod['move_topic'] )
									     )      );							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����������� ����?</b>" ,
												  $SKIN->form_yes_no("pin_topic", $mod['pin_topic'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������� ������������� ����?</b>" ,
												  $SKIN->form_yes_no("unpin_topic", $mod['unpin_topic'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� / ��������� ����?</b>" ,
												  $SKIN->form_yes_no("split_merge", $mod['split_merge'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �����������/�������� ������ ��������� � �����?</b>" ,
						                          $SKIN->form_yes_no("pin_first_post_topic", $mod['pin_first_post_topic'] )
					                     )      );
					     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������ ���������� ����������" );
		
		//+-------------------------------
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����������� �������� ����������� ���?</b>" ,
												  $SKIN->form_yes_no("mass_move", $mod['mass_move'] )
									     )      );	
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����������� �������� ���������� ���?</b>" ,
												  $SKIN->form_yes_no("mass_prune", $mod['mass_prune'] )
									     )      );
									     						     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ���������� ������?</b>" ,
												  $SKIN->form_yes_no("topic_q", $mod['topic_q'] )
									     )      );							     
									     	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ���������� �����������?</b>" ,
												  $SKIN->form_yes_no("post_q", $mod['post_q'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������������� ���������" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������� ������� �������������?</b>" ,
												  $SKIN->form_yes_no("allow_warn", $mod['allow_warn'] )
									     )      );							     
									     	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ������� � ������� �������������?</b>" ,
												  $SKIN->form_yes_no("edit_user", $mod['edit_user'] )
									     )      );
									   
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������ ������-��������� ���?</b><br>".$SKIN->js_help_link('mod_mmod', '������ ����������' ) ,
												  $SKIN->form_yes_no("can_mm", $mod['can_mm'] )
									     )      );						     
									     
		//+-------------------------------
									     
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();							     						     
									     
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// ADD step one: Look up a member
	//
	//+---------------------------------------------------------------------------------
	
	function add_one() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//-----------------------------
		// Grab and serialize the input
		//-----------------------------
		
		$fid      = "";
		$fidarray = array();
		
		foreach ($IN as $k => $v)
		{
			if ( preg_match( "/^add_(\d+)$/", $k, $match ) )
			{
				if ($IN[ $match[0] ])
				{
					$fidarray[] = $match[1];
				}
			}
		}
		
		if ( count($fidarray) < 1 )
		{
			$ADMIN->error("�� ������ ������� ���� ��� ��������� �������, ��� ���������� ����������. ������ �������� ������, ����� �� �������� ������");
		}
		
		$fid = implode( "," ,$fidarray );
		
		$ADMIN->page_title = "���������� ����������";
		
		$ADMIN->page_detail = "���������� ����� ������������ ��� ������, ��� ���������� �����������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add_two' ),
												  2 => array( 'act'   , 'mod'     ),
												  3 => array( 'fid'   , $fid      ),
												  4 => array( 'mod_type' , $IN['mod_type'] ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		if ($IN['mod_type'] == 'member')
		{
		
			$ADMIN->html .= $SKIN->start_table( "����� ������������" );
			
											 
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ��� ������ ��� ������������</b>" ,
													  $SKIN->form_input( "USER_NAME" )
											 )      );
											 
			$ADMIN->html .= $SKIN->end_form("����� ������������");
											 
			$ADMIN->html .= $SKIN->end_table();
		
		}
		else
		{
			// Get the group ID's and names
			
			$mem_group = array();
			
			$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
			while ( $r = $DB->fetch_row() )
			{
				$mem_group[] = array( $r['g_id'] , $r['g_title'] );
			}
			
			$ADMIN->html .= $SKIN->start_table( "���������� ������ �����������" );
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
													  $SKIN->form_dropdown( "mod_group", $mem_group )
											 )      );
											 
			$ADMIN->html .= $SKIN->end_form("�������� ������");
											 
			$ADMIN->html .= $SKIN->end_table();
			
		}
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// REFINE MEMBER SEARCH
	//
	//+---------------------------------------------------------------------------------
	
	function add_two() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		// Are we adding a group as a mod? If so, bounce straight to the mod perms form
		
		if ($IN['mod_type'] == 'group')
		{
			$this->mod_form();
			exit();
		}
		
		// Else continue as normal.
		
		if ($IN['USER_NAME'] == "")
		{
			$ADMIN->error("�� �� ����� ��� ������������ ��� ������!");
		}
		
		$DB->query("SELECT id, name FROM ibf_members WHERE name LIKE '".$IN['USER_NAME']."%'");
		
		if (! $DB->get_num_rows() )
		{
			$ADMIN->error("�� ������� �� ������ ������������, ���������������� �������� ������.");
		}
		
		$form_array = array();
		
		while ( $r = $DB->fetch_row() )
		{
			$form_array[] = array( $r['id'] , $r['name'] );
		}
		
		
		
		$ADMIN->page_title = "���������� ����������";
		
		$ADMIN->page_detail = "���� �������� ��� ������������, ��� ���������� ��� ����������� � ��������� ���� ������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add_final' ),
												  2 => array( 'act'   , 'mod'    ),
												  3 => array( 'fid'   , $IN['fid']),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����� ������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������������...</b>" ,
												  $SKIN->form_dropdown( "MEMBER_ID", $form_array )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("�������� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// SHOW LIST
	// Renders a complete listing of all the forums and categories w/mods.
	//
	//+---------------------------------------------------------------------------------
	
	function show_list() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "���������� ������������";
		$ADMIN->page_detail  = "����� �� ������ �������������, ������� ��� ��������� ����������� � ���� ������";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'add' ),
												  2 => array( 'act'   , 'mod'   ),
									     )      );
		
		$SKIN->td_header[] = array( "�������"                , "5%" );
		$SKIN->td_header[] = array( "�������� ������"         , "40%" );
		$SKIN->td_header[] = array( "������� ����������" , "55%" );
		
		$ADMIN->html .= $SKIN->start_table( "���� ��������� � ������" );
		
		//------------------------------------
		
		$cats   = array();
		$forums = array();
		$mods   = array();
		$children = array();
		
		$DB->query("SELECT * from ibf_categories where id > 0 ORDER BY position ASC");
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
		
		$DB->query("SELECT * from ibf_moderators");
		while ($r = $DB->fetch_row())
		{
			$mods[] = $r;
		}
		
		//------------------------------------
		
		$last_cat_id = -1;
		
		foreach ($cats as $c)
		{
			
			$ADMIN->html .= $SKIN->add_td_row( array(
													   '&nbsp;',
													   "<a href='{$ADMIN->base_url}&act=cat&code=doeditform&c={$c['id']}'>".$c['name']."</a>",
													   '&nbsp;',
											 ), 'pformstrip'     );
			$last_cat_id = $c['id'];
			
			
			foreach($forums as $r)
			{	
			
				if ($r['category'] == $last_cat_id)
				{
				
					$mod_string = "";
					
					foreach( $mods as $phpid => $data )
					{
						if ($data['forum_id'] == $r['id'])
						{
							if ($data['is_group'] == 1)
							{
								$mod_string .= "<tr>
											 <td width='60%'>������: {$data['group_name']}</td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>�������</a></td>
											 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>�������������</a></td>
											</tr>";
							}
							else
							{
								$mod_string .= "<tr>
												 <td width='60%'>{$data['member_name']}</td>
												 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>�������</a></td>
												 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>�������������</a></td>
												</tr>";
							}
						}
					}
					
					if ($mod_string != "")
					{
						$these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>".$mod_string."</table>";
					}
					else
					{
						$these_mods = "<center><i>�� ������������</i></center>";
					}
				
					if ($r['subwrap'] == 1 and $r['sub_can_post'] != 1)
					{
					
						$ADMIN->html .= $SKIN->add_td_row( array(
																   '&nbsp;',
																    " - <b>".$r['name']."</b>",
																   '&nbsp;',
														 ), 'subforum'     );
					}
					else
					{
						$css = $r['subwrap'] == 1 ? 'subforum' : '';
						
						$ADMIN->html .= $SKIN->add_td_row( array(
															   "<center><input type='checkbox' name='add_{$r['id']}' value='1'></center>",
															   "<b>".$r['name']."</b>",
															   $these_mods
													 ) , $css     );
					}
													 
					if ( ( isset($children[ $r['id'] ]) ) and ( count ($children[ $r['id'] ]) > 0 ) )
					{
						foreach($children[ $r['id'] ] as $idx => $rd)
						{
						
							$mod_string = "";
					
							foreach( $mods as $phpid => $data )
							{
								if ($data['forum_id'] == $rd['id'])
								{
									if ($data['is_group'] == 1)
									{
										$mod_string .= "<tr>
													 <td width='60%'>������: {$data['group_name']}</td>
													 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>�������</a></td>
													 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>�������������</a></td>
													</tr>";
									}
									else
									{
										$mod_string .= "<tr>
														 <td width='60%'>{$data['member_name']}</td>
														 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=remove&mid={$data['mid']}'>�������</a></td>
														 <td width='20%'><a href='{$ADMIN->base_url}&act=mod&code=edit&mid={$data['mid']}'>�������������</a></td>
														</tr>";
									}
								}
							}
							
							if ($mod_string != "")
							{
								$these_mods = "<table cellpadding='3' cellspacing='0' width='100%' align='center'>".$mod_string."</table>";
							}
							else
							{
								$these_mods = "<center><i>�� ������������</i></center>";
							}
					
							$ADMIN->html .= $SKIN->add_td_row( array(
															   "<center><input type='checkbox' name='add_{$rd['id']}' value='1'></center>",
															   " +-- <b>".$rd['name']."</b>",
															   $these_mods
													 )  ,'subforum'    );
						}
					}					 
				}
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_basic( "<b>��� �������������:</b> &nbsp;" . $SKIN->form_dropdown( "mod_type",
																				  array(
																						 0 => array( 'member', '������������ ������������' ),
																						 1 => array( 'group', '������ �������������'   )
																					   )
																				  ) , "center" );
		
		$ADMIN->html .= $SKIN->end_form("�������� ���������� � ��������� ������");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	


	
}


?>