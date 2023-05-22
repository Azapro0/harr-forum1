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
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
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
			$ADMIN->error("���������� ���������� ID ����� �������, ���������� �����");
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
			$ADMIN->error("���������� ������ ��������");
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
			$ADMIN->error("���������� ���������� ID ����� �������, ���������� �����");
		}
		
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $perms = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ID ����� �������, ���������� �����");
		}
		
		//+-------------------------------------------
		// What we doin'?
		//+-------------------------------------------
		
		switch( $IN['t'] )
		{
			case 'start':
				$human_type = '�������� ���';
				$code_word  = 'start_perms';
				break;
				
			case 'reply':
				$human_type = '����� � ����';
				$code_word  = 'reply_perms';
				break;
				
			default:
				$human_type = '�������� ������';
				$code_word  = 'read_perms';
				break;
		}
		
		//+-------------------------------------------
		// Get all members using that ID then!
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "$human_type" , "100%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������� �������������: " . $perms['perm_name'] );
		
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
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array(
													"����� ������������ $human_type",
													"<input type='text' readonly='readonly' style='border:1px solid black;background-color:black;size=30px' name='blah'>"
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array(
													"�� ����� ������������ $human_type",
													"<input type='text' readonly='readonly' style='border:1px solid gray;background-color:gray;size=30px' name='blah'>"
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array(
													"���� �...",
													$SKIN->form_dropdown( 't',
																		array( 0 => array( 'start', '��������� ���'    ),
																			   1 => array( 'reply', '������� � ����' ),
																			   2 => array( 'read' , '������� ������'      ),
																			  ), $IN['t'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form( "��������" );
		
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
			$ADMIN->error("���������� ���������� ID ������������, ���������� �����");
		}
		
		//+-------------------------------------------
		// Get, check and reset
		//+-------------------------------------------
		
		$DB->query("SELECT id, name, org_perm_id FROM ibf_members WHERE id=".intval($IN['id']));
		
		if ( ! $mem = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ID ������������, ���������� �����");
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
		
		$ADMIN->html .= $SKIN->start_table( "���������" );
		
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "�������� ����� ������� <b>{$mem['name']}</b>.",
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
			$ADMIN->error("���������� ���������� ID ����� �������, ���������� �����");
		}
		
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $perms = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ID ����� �������, ���������� �����");
		}
		
		//+-------------------------------------------
		// Get all members using that ID then!
		//+-------------------------------------------
		
		$SKIN->td_header[] = array( "������ ������������" , "50%" );
		$SKIN->td_header[] = array( "��������"       , "50%" );
		
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
		
		$ADMIN->html .= $SKIN->start_table( "������������� ������������: " . $perms['perm_name'] );
		
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
						$extra = "<li>����� ������������: <em style='color:red'>";
						
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
															  "&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=$pid' title='Remove this mask from the user (will not remove all if they have multimasks'>������� �����</a>
															   <br />&#149;&nbsp;<a href='{$SKIN->base_url}&amp;act=group&amp;code=remove_mask&amp;id={$r['id']}&amp;pid=all' title='������� ��� ���������������� �����'>������� ��� �����</a>
															   <br /><br />&#149;&nbsp;<a href='javascript:pop_close_and_stop(\"{$r['id']}\");'>������������� ������������</a>",
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
		
		$ADMIN->page_title = "�������� ����� ������� [ ������� ]";
		
		$ADMIN->page_detail = "��� ������ ��� ���������� ������� ������� ������.";
		
		$ADMIN->page_detail .= "<br /><b>������������ ��������</b> ��� ���������������� ������, ������������ ��� ����� �������
								<br /><b>���-�� �������������</b> ��� ���������� �������������, ������������ ������ ����� �������
							    <br /><b>������������</b> ��� ������� �������� ������� ������ � ������ �������
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
		
		$SKIN->td_header[] = array( "�������� �����"          , "20%" );
		$SKIN->td_header[] = array( "������������ ��������"   , "20%" );
		$SKIN->td_header[] = array( "���-�� �������������"     , "20%" );
		$SKIN->td_header[] = array( "������������"            , "10%" );
		$SKIN->td_header[] = array( "�������������"               , "15%" );
		$SKIN->td_header[] = array( "�������"             , "15%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ����� �������" );
		
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
				$groups_used = "<center><i>�� ����������</i></center>";
			}
			
			$mems_used = 0;
			
			if ( $mems[ $id ] > 0 )
			{
				$is_active = 1;
				$mems_used = $mems[ $id ] . " (<a href='javascript:pop_win(\"&amp;act=group&amp;code=view_perm_users&amp;id=$id\", \"User\", \"500\",\"350\");' title='�������� � ����� ���� �������������, ������������ ��� �����'>��������</a>)";
			}
			
			if ( $is_active > 0 )
			{
				$delete = "<i>����������, ��� �������������</i>";
			}
			else
			{
				$delete = "<a href='{$SKIN->base_url}&amp;act=group&amp;code=pdelete&amp;id=$id'>�������</a>";
			}
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>$name</b>" ,
													  "$groups_used",
													  "<center>$mems_used</center>",
													  "<center><a href='javascript:pop_win(\"&amp;act=group&amp;code=preview_forums&amp;id=$id&amp;t=read\", \"400\",\"350\");' title='�������� ������ ������� ������ � ���������..'>������������</a></center>",
													  "<center><a href='{$SKIN->base_url}&amp;act=group&amp;code=fedit&amp;id=$id'>�������������</a></center>",
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
		
		$ADMIN->html .= $SKIN->start_table( "�������� ����� ����� �������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����� �������</b>" ,
												  $SKIN->form_input( 'new_perm_name' ),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ������� ��...</b>" ,
												 $SKIN->form_dropdown( 'new_perm_copy', $dlist ),
										 )      );
		
		$ADMIN->html .= $SKIN->end_form("�������");
						     
		$ADMIN->html .= $SKIN->end_table();
		
		
		
		$ADMIN->output();
			
			
	}
	
	
	
	function forum_perms()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������, ���������� �����");
		}
		
		//+----------------------------------
		
		$ADMIN->page_title = "�������� ����� ������� [ �������������� ]";
		
		$ADMIN->page_detail = "��� ������ ��� ���������� ������� ������� ������.";
		
		$ADMIN->page_detail .= "<br />���������� ������� ��� ������� � ������� �������� ��� ������� ������� ��� ������� ������� ��������.
							   <br /><b>����� ������</b> ��������� �� ��, ��� ��� ������� � ������� ����� ����� ����� ������ � ����� ��������";
		
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
		
		$ADMIN->html .= $SKIN->start_table( "��������� �������� ������: ".$group['perm_name'] );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �����</b>" ,
												  $SKIN->form_input("perm_name", $gname )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("�������� ��������");
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+----------------------------------
		//| MAIN FORM
		//+----------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dofedit' ),
												  2 => array( 'act'   , 'group'   ),
												  3 => array( 'id'    , $gid      ),
									     )      );
		
		$SKIN->td_header[] = array( "�����"   , "40%" );
		$SKIN->td_header[] = array( "������"         , "15%" );
		$SKIN->td_header[] = array( "������"        , "15%" );
		$SKIN->td_header[] = array( "��������"        , "15%" );
		$SKIN->td_header[] = array( "��������"       , "15%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������� � ������ ��� ".$group['perm_name'] );
		
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
					$global = '<center id="mgblue"><i>����� ������</i></center>';
					
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
					
					$global = '<center id="mgred"><i>����� ������</i></center>';
					
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
					
					$global = '<center id="mggreen"><i>����� ������</i></center>';
					
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
					
					$global = '<center id="memgroup"><i>����� ������</i></center>';
					
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
							$global = "<center id='mgblue'><i>����� ������</i></center>";
							
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
							
							$global = "<center id='mgred'><i>����� ������</i></center>";
							
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
							
							$global = "<center id='mggreen'><i>����� ������</i></center>";
							
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
							
							$global = "<center id='memgroup'><i>����� ������</i></center>";
							
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
		
		$ADMIN->html .= $SKIN->end_form("�������� ��������� �������");
		
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
			$ADMIN->error("���������� ���������� ID ������");
		}
		
		if ( $IN['perm_name'] == "" )
		{
			$ADMIN->error("���������� ������ ��������");
		}
		
		$gid = $IN['id'];
		
		//---------------------------
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $gr = $DB->fetch_row() )
		{
			$ADMIN->error("������������ ID ������");
		}
		
		$DB->query("UPDATE ibf_forum_perms SET perm_name='{$IN['perm_name']}' WHERE perm_id='".$IN['id']."'");
		
		$ADMIN->save_log("��������� �������� ����� �������: '{$gr['perm_name']}'");
		
		$ADMIN->done_screen("��������� ������� ������ ���������", "���������� ������� �������", "act=group&code=permsplash" );
		
		
	}
	
	
	
	function do_forum_perms()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------
		// Check for legal ID
		//---------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������");
		}
		
		$gid = $IN['id'];
		
		//---------------------------
		
		$DB->query("SELECT * FROM ibf_forum_perms WHERE perm_id='".$IN['id']."'");
		
		if ( ! $gr = $DB->fetch_row() )
		{
			$ADMIN->error("������������ ID ������");
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
				die ("������ ���������� �� �������� ��� ������ ".$row['id']);
			}
			
		}
		
		$ADMIN->save_log("�������������� ���������� ������� ��� �����: '{$gr['perm_name']}'");
		
		$ADMIN->done_screen("��������� ������� ���������", "���������� ������� �������", "act=group&code=permsplash" );
		
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
			$ADMIN->error("���������� ���������� ID ������, ���������� �����");
		}
		
		if ($IN['id'] < 5)
		{
			$ADMIN->error("�� �� ������ ����������� ���� ������ � ������. �������� ������ ��������� �������� ����� � �������������� ����������.");
		}
		
		$ADMIN->page_title = "�������� ������";
		
		$ADMIN->page_detail = "��������� ��� ��� � ���, ��� �� ����������� ������� �������� ������.";
		
		
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
		
		$ADMIN->html .= $SKIN->start_table( "������������� ��������: ".$group['g_title'] );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������� � ���� ������</b>" ,
												  "<b>".$black_adder['users']."</b>",
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ������������� ���� ������ � ������...</b>" ,
												  $SKIN->form_dropdown("to_id", $mem_groups )
									     )      );
		
		$ADMIN->html .= $SKIN->end_form("������� ������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
	}
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ������, ���������� �����");
		}
		
		if ($IN['to_id'] == "")
		{
			$ADMIN->error("���������� ����������� � ������������� ������.");
		}
		
		// Check to make sure that the relevant groups exist.
		
		$DB->query("SELECT g_id FROM ibf_groups WHERE g_id IN(".$IN['id'].",".$IN['to_id'].")");
		
		if ( $DB->get_num_rows() != 2 )
		{
			$ADMIN->error("���������� ���������� ID ��������� ������");
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
		
		$ADMIN->save_log("�������� ���������������� ������ '{$IN['name']}'");
		
		$ADMIN->done_screen("������ �������", "���������� ��������", "act=group" );
		
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
			$ADMIN->error("���������� ������ �������� ������.");
		}
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("���������� ���������� ID ������");
			}
			
			if ($IN['id'] == $INFO['admin_group'] and $IN['g_access_cp'] != 1)
			{
				$ADMIN->error("�� �� ������ ��������� ����������� ������� � �����������, ��� ���� ������");
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
			$ADMIN->error("�� ������� ����� �������");
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
			
			$ADMIN->save_log("�������������� ������ '{$IN['g_title']}'");
			
			$ADMIN->done_screen("������ ���������������", "���������� ��������", "act=group" );
			
		}
		else
		{
			$rstring = $DB->compile_db_insert_string( $db_string );
			
			$DB->query("INSERT INTO ibf_groups (" .$rstring['FIELD_NAMES']. ") VALUES (". $rstring['FIELD_VALUES'] .")");
			
			$ADMIN->save_log("�������� ������ '{$IN['g_title']}'");
			
			$ADMIN->done_screen("������ �������", "���������� ��������", "act=group" );
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
		
		$all_groups = array( 0 => array ('none', '�� ����������') );
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("������ � ���� ������ �� �������, ���������� �����.");
			}
			
			if ( $INFO['admin_group'] == $IN['id'] )
			{
				if ( $MEMBER['mgroup'] != $INFO['admin_group'] )
				{
					$ADMIN->error("�� �� ������ ������������� ��� ������, �.�. ��� ������ �������� �������� ���������������� �������");
				}
			}
			
			$form_code = 'doedit';
			$button    = '��������� ���������';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = '������� ������';
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
			$ADMIN->page_title = "�������������� ������ ".$group['g_title'];
		}
		else
		{
			$ADMIN->page_title = '�������� ����� ������';
			$group['g_title'] = '����� ������';
		}
		
		$guest_legend = "";
		
		if ($group['g_id'] == $INFO['guest_group'])
		{
			$guest_legend = "</b><br><i>(� ������ �� ���������)</i>";
		}
		
		$ADMIN->page_detail = "������ ��������� ��� �������� ������, ����� �����������.";
		
		
		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript'>
						 <!--
						  function checkform() {
						  
						  	isAdmin = document.forms[0].g_access_cp;
						  	isMod   = document.forms[0].g_is_supmod;
						  	
						  	msg = '';
						  	
						  	if (isAdmin[0].checked == true)
						  	{
						  		msg += '������������ ���� ������ ����� ����� ������ � �����������\\n\\n';
						  	}
						  	
						  	if (isMod[0].checked == true)
						  	{
						  		msg += '������������ ���� ������ ����� ����� ����� ����������������.\\n\\n';
						  	}
						  	
						  	if (msg != '')
						  	{
						  		msg = '�������� ������������\\n--------------\\n�������� ������: ' + document.forms[0].g_title.value + '\\n--------------\\n\\n' + msg + '�� �������������?';
						  		
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
		
		$ADMIN->html .= $SKIN->start_table( "����� ���������", "�������� ��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
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
					show = '��������� �� ����������\\n������� �� �����������, ��� ���������';
				}
				
				alert('��������� ����� �������\\n---------------------------------\\n' + show);
			}
			
		</script>";
		
		$arr = explode( ",", $group['g_perm_id'] );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ��������� ������� ������...</b><br>����� ������� ���������, ��������� ������� <b>Ctrl</b>" ,
												  $SKIN->form_multiselect("permid[]", $perm_masks, $arr, 5, 'onfocus="saveit(this)"; onchange="saveit(this)";' )."<a href='javascript:show_me();'>�������� ��������� �����</a>"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������</b><br>(����� ����������)" ,
												  $SKIN->form_input("g_icon", $group['g_icon'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������: ������������ ������ ����������� ������ (� ��)</b>".$SKIN->js_help_link('mg_upload')."<br>(�������� ��� ���� ������ ��� ������� ��������)" ,
												  $SKIN->form_input("g_attach_max", $group['g_attach_max'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����: ������������ ������ ����������� ���������� (� ��)</b><br>(�������� ��� ���� ������ ��� ������� ��������)" ,
												  $SKIN->form_input("p_max", $p_max )."<br />"
												  ."����. ������ (px): <input type='text' size='3' name='p_width' value='{$p_width}'> "
												  ."����. ������ (px): <input type='text' size='3' name='p_height' value='{$p_height}'>"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ����������� � ���������� ������ [���������]</b><br>(����� �� ���������)<br>(������:&lt;span style='color:red'&gt;)" ,
												  $SKIN->form_input("prefix", $prefix )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ����������� � ���������� ������ [���������]</b><br>(����� �� ���������)<br>(������:&lt;/span&gt;)" ,
												  $SKIN->form_input("suffix", $suffix )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ��� ������ �� ������ ����������?</b>" ,
												  $SKIN->form_yes_no("g_hide_from_list", $group['g_hide_from_list'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+***********************************************************************----------

		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );

		$ADMIN->html .= $SKIN->start_table( "������� � ��������� ������" );


		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� ������ � ��������� ������?</b>" ,
												  $SKIN->form_yes_no("g_do_download", $group['g_do_download'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���-�� ������, ����������� �� 1 ���?</b><br />��� ���������� ������, ������� 0 ��� �������� ���� ������" ,
												  $SKIN->form_input("g_d_max_dls", $group['g_d_max_dls'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ������ � �������� �����?</b>" ,
												  $SKIN->form_yes_no("g_d_add_files", $group['g_d_add_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������������/�������� ����������� ������, ����������� � �������� �����?</b>" ,
												  $SKIN->form_yes_no("g_d_edit_files", $group['g_d_edit_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ����� ������ � �������� ������?</b>" ,
												  $SKIN->form_yes_no("g_d_ibcode_files", $group['g_d_ibcode_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� HTML ����� � �������� ������?</b>" ,
												  $SKIN->form_yes_no("g_d_html_files", $group['g_d_html_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ����������� � ������?</b>" ,
												  $SKIN->form_yes_no("g_d_post_comments", $group['g_d_post_comments'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� ������� � �������� ������?</b><br />���� ���������� �������������� ����������..." ,
												  $SKIN->form_yes_no("g_d_manage_files", $group['g_d_manage_files'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������� �����, ���� �� ���������� �������� ����������� ������?</b><br />���������� � ���� ���������� �������, ������� ��" ,
												  $SKIN->form_yes_no("g_d_approve_down", $group['g_d_approve_down'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������������/������� �����, ����������� ������� ��������������?</b><br />���������� � ���� ���������� �������, ������� ��" ,
												  $SKIN->form_yes_no("g_d_eofs", $group['g_d_eofs'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ������� ����������� ����?</b><br />���������� � ���� ���������� �������, ������� ��" ,
												  $SKIN->form_yes_no("g_d_optimize_db", $group['g_d_optimize_db'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ������� \"�������� ������\"?</b><br />���������� � ���� ���������� �������, ������� ��" ,
												  $SKIN->form_yes_no("g_d_check_links", $group['g_d_check_links'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ������� \"�������� ���\"?</b><br />���������� � ���� ���������� �������, ������� ��" ,
												  $SKIN->form_yes_no("g_d_check_topics", $group['g_d_check_topics'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ��������� ������, ����� ����� ��������?</b>" ,
												  $SKIN->form_yes_no("g_d_allow_dl_offline", $group['g_d_allow_dl_offline'] )
									     )      );

		$ADMIN->html .= $SKIN->end_table();
		//+***********************************************************************----------
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ��������", "����������� �������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� �����?</b>" ,
												  $SKIN->form_yes_no("g_view_board", $group['g_view_board'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� �����, ����� �� ��������?</b>" ,
												  $SKIN->form_yes_no("g_access_offline", $group['g_access_offline'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ������� ������������� � ������ ����������?</b>" ,
												  $SKIN->form_yes_no("g_mem_info", $group['g_mem_info'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ���� ������ �������������?</b>" ,
												  $SKIN->form_yes_no("g_other_topics", $group['g_other_topics'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������������� �������?</b>" ,
												  $SKIN->form_yes_no("g_use_search", $group['g_use_search'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������ ��� ���� �������� ��� ������</b><br>��� ������ ���� ��������, �������� ������ ��� ������� 0" ,
												  $SKIN->form_input("g_search_flood", $group['g_search_flood'] )
									     )      );
									     
		list( $limit, $flood ) = explode( ":", $group['g_email_limit'] );					     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���������� ������ �� e-mail �� ������?</b><br />��� ������ ������, �� ���������� ��������� ���� $guest_legend</b>" ,
												  $SKIN->form_yes_no("g_email_friend", $group['g_email_friend'] )
												 ."<br />��������� �� ������ ". $SKIN->form_simple_input("join_limit", $limit, 2 )." ����� �� 24 ����"
												 ."<br />...� �� ������ 1 ������ � ������� ".$SKIN->form_simple_input("join_flood", $flood, 2 )." ������"
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� ���� �������?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_profile", $group['g_edit_profile'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������ PM ������?$guest_legend" ,
												  $SKIN->form_yes_no("g_use_pm", $group['g_use_pm'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� �������������, ��� ������������� �������� ������ �� PM?$guest_legend<br>(������� 0 ��� �������� ������, ��� ������������� �������� �������� ����� �� PM)" ,
												  $SKIN->form_input("g_max_mass_pm", $group['g_max_mass_pm'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� ����������� ���������?$guest_legend" ,
												  $SKIN->form_input("g_max_messages", $group['g_max_messages'] )
									     )      );
									     						     							     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����������� ��������?$guest_legend" ,
												  $SKIN->form_yes_no("g_avatar_upload", $group['g_avatar_upload'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������� � ����������", "����������� �������� ������ � ����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����� ��� (��� ��������)?</b>" ,
												  $SKIN->form_yes_no("g_post_new_topics", $group['g_post_new_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������ � ���� ����?</b>" ,
												  $SKIN->form_yes_no("g_reply_own_topics", $group['g_reply_own_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������ � ���� ������ ���������� (��� ��������)?</b>" ,
												  $SKIN->form_yes_no("g_reply_other_topics", $group['g_reply_other_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������������� ����� ���������?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_posts", $group['g_edit_posts'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� �� ������� �������������� (� �������)?$guest_legend<br>�� ���������� ���� �����, ������������ ��� �� ������ ��������������� ��� ���������. �������� ������ ��� ������� 0, ��� ������ ����� �����������." ,
												  $SKIN->form_input("g_edit_cutoff", $group['g_edit_cutoff'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����� ������� ������� '���������������' � ����������?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_append_edit", $group['g_append_edit'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����� ���������?$guest_legend" ,
												  $SKIN->form_yes_no("g_delete_own_posts", $group['g_delete_own_posts'] )
									     )      );
									     						     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���������/����������� ���� ����?$guest_legend" ,
												  $SKIN->form_yes_no("g_open_close_posts", $group['g_open_close_posts'] )
									     )      );							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������������� �������� � �������� ����� ���?$guest_legend" ,
												  $SKIN->form_yes_no("g_edit_topic", $group['g_edit_topic'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����� ���?$guest_legend" ,
												  $SKIN->form_yes_no("g_delete_own_topics", $group['g_delete_own_topics'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����������� (��� ��������)?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_post_polls", $group['g_post_polls'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� � ������� (��� ��������)?$guest_legend" ,
												  $SKIN->form_yes_no("g_vote_polls", $group['g_vote_polls'] )
									     )      );							     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���� �������� ��� ���� ������?</b>" ,
												  $SKIN->form_yes_no("g_avoid_flood", $group['g_avoid_flood'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������� ��� ������������, ��� ���� ������?</b>" ,
												  $SKIN->form_yes_no("g_avoid_q", $group['g_avoid_q'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��������� ������� � ���������?$guest_legend</b>" ,
												  $SKIN->form_yes_no("g_calendar_post", $group['g_calendar_post'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� [doHTML] �����?$guest_legend</b><br />".$SKIN->js_help_link('mg_dohtml') ,
												  $SKIN->form_yes_no("g_dohtml", $group['g_dohtml'] )
									     )      );
									     					     							     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������� ������", "������ ��� ������ ���� ������, � ����������� �������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������������� (����� ������������ �����)?$guest_legend" ,
												  $SKIN->form_yes_no("g_is_supmod", $group['g_is_supmod'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� ������ � ����������?$guest_legend" ,
												  $SKIN->form_yes_no("g_access_cp", $group['g_access_cp'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� ������ �������� � '��������' �����?" ,
												  $SKIN->form_yes_no("g_post_closed", $group['g_post_closed'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������� �� �������" );
		
		if ($group['g_id'] == $INFO['admin_group'])
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� '�� ����������' ��� ���������� ���������������</b><br>".$SKIN->js_help_link('mg_promote') ,
													  "��������������� �� ��������� � ���������������, ��� ��� ������ ���� ����������������� ���."
											 )      );
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� '�� ����������' ��� ���������� ���������������</b>$guest_legend<br>".$SKIN->js_help_link('mg_promote') ,
													  '���������� ������������� ���� ������ � ������: '.$SKIN->form_dropdown("g_promotion_id", $all_groups, $group['g_promotion_id'] )
													 .'<br>��� ������ '.$SKIN->form_simple_input('g_promotion_posts', $group['g_promotion_posts'] ).' ���������'
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
		
		$ADMIN->page_title = "������ �������������";
		
		$ADMIN->page_detail = "���������� ������������� �� ������� �������� �������� �������� ��� ����������� ����� �������������. �� ��������� � ������ ������� 4 ������ (���������, �����, ������������ � ��������������) , ������� ���������� �������, �� ����� ��������������� �� �������� � ���������. �������� ��� ������� ������ � ��������� '����������' � ���������� ��� ���� ������ ��������� ����������, ������� ���������� ������� �������������.<br>� ���������� ������� �� ������ ���������� ������� ��������� �������� � �������� ���, ������� � ������ �������. ��������� ������� ����� ����� ��������������� � ����� ���� '���������� ��������'.";
		
		$g_array = array();
		
		$SKIN->td_header[] = array( "�������� ������"    , "30%" );
		$SKIN->td_header[] = array( "������ � ��?"    , "15%" );
		$SKIN->td_header[] = array( "���-�����?"     , "15%" );
		$SKIN->td_header[] = array( "�����-��"        , "10%" );
		$SKIN->td_header[] = array( "������. ������"     , "20%" );
		$SKIN->td_header[] = array( "�������"         , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ����������������� ��������" );
		
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
				$del = "<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=".$r['g_id']."'>�������</a></center>";
			}
			//-----------------------------------
			if ($r['g_access_cp'] == 1)
			{
				$adm = '<center><span style="color:red">��</span></center>';
			}
			//-----------------------------------
			if ($r['g_is_supmod'] == 1)
			{
				$mod = '<center><span style="color:red">��</span></center>';
			}
			
			if ($r['g_id'] != 1 and $r['g_id'] != 2)
			{
				$total_linkage = "<a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Members&max_results=30&filter={$r['g_id']}&sort_order=asc&sort_key=name&st=0' target='_blank' title='������ ����������'>".$r['prefix'].$r['g_title'].$r['suffix']."</a>";
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
												      "<center><a href='{$ADMIN->base_url}&act=group&code=edit&id=".$r['g_id']."'>�������������</a></center>",
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
		
		$ADMIN->html .= $SKIN->start_table( "�������� ����� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ������ �� ������ ������...</b>" ,
												  $SKIN->form_dropdown("id", $g_array, 3 )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("������� ������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
		
}


?>