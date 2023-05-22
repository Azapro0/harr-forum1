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

$root_path = "";

class ad_forums {

	var $base_url;
	var $modules = "";

	function ad_forums()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;
		
		//--------------------------------------------
    	// Get the sync module
		//--------------------------------------------
		
		if ( USE_MODULES == 1 )
		{
			require ROOT_PATH."modules/ipb_member_sync.php";
			
			$this->modules = new ipb_member_sync();
		}
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		$ADMIN->nav[] = array( 'act=mem&code=edit', '����� ������ ������������' );
		
		//---------------------------------------

		switch($IN['code'])
		{
			case 'stepone':
				$this->do_advanced_search(1);
				break;
			case 'doform':
				$this->do_edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			case 'advancedsearch':
				$this->do_advanced_search();
				break;
			//---------------------
			case 'unsuspend':
				$this->unsuspend();
				break;
			//---------------------
			case 'add':
				$this->add_form();
				break;
			case 'doadd':
				$this->do_add();
				break;
			//---------------------
			case 'del':
				$this->delete_form();
				break;
			case 'delete2':
				$this->delete_lookup_form();
				break;
			case 'dodelete':
				$this->dodelete();
				break;
			case 'prune':
				$this->prune_confirm();
				break;
			case 'doprune':
				$this->doprune();
				break;
			//---------------------
			case 'title':
				$this->titles();
				break;
			case 'rank_edit':
				$this->rank_setup('edit');
				break;
			case 'rank_add':
				$this->rank_setup('add');
				break;
			case 'do_add_rank':
				$this->add_rank();
				break;
			case 'do_rank_edit':
				$this->edit_rank();
				break;
			case 'rank_delete':
				$this->delete_rank();
				break;
			//---------------------
			case 'ban':
				$this->ban_control();
				break;
			case 'doban':
				$this->update_ban();
				break;
			//---------------------
			case 'mod':
				$this->view_mod();
				break;
			case 'domod':
				$this->domod();
				break;
			//---------------------
			case 'changename':
				$this->change_name_start();
				break;
			case 'dochangename':
				$this->change_name_complete();
				break;
			//---------------------	
			case 'mail':
				$this->bulk_mail_form();
				break;
			case 'domail':
				$this->do_bulk_mail();
				break;
				
			case 'banmember':
				$this->temp_ban_start();
				break;
				
			case 'dobanmember':
				$this->temp_ban_complete();
				break;
			//---------------------
			default:
				$this->search_form();
				break;
		}
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// MASS EMAIL PEOPLE!
	//
	//+---------------------------------------------------------------------------------
	
	function do_bulk_mail() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		// Get the ID's of the groups we're emailing.
		
		$ids = array();
 		
 		foreach ($IN as $key => $value)
 		{
 			if ( preg_match( "/^sg_(\d+)$/", $key, $match ) )
 			{
 				if ($IN[$match[0]])
 				{
 					$ids[] = $match[1];
 				}
 			}
 		}
 		
 		if ( count($ids) < 1 )
 		{
 			$this->bulk_mail_form(1, 'Errors', '���������� ������� ���� �� ���� ������ ��� �������� ������');
 			exit();
 		}
 		
 		if ($IN['title'] == "")
 		{
 			$this->bulk_mail_form(1, 'Errors', '�� ������ ������ ��������� ������');
 			exit();
 		}
 		
 		if ($IN['email_contents'] == "")
 		{
 			$this->bulk_mail_form(1, 'Errors', '�� �� ����� ����� ������');
 			exit();
 		}
 		
 		$group_str = implode( ",", $ids);
 		
 		// Sort out the rest of the DB stuff
 		
 		$where = ""; // Where? who knows? who cares?
 		
 		if ($IN['posts'] > 0)
 		{
 			$where .= " AND posts < ".$IN['posts'];
 		}
 		
 		if ($IN['days'] > 0)
 		{
 			$time = time() - ($IN['days']*60*60*24);
 			$where .= " AND last_activity < '$time'";
 		}
 		
 		if ($IN["honour_user_setting"] == 1)
 		{
 			$where .= "AND allow_admin_mails=1";
 		}
 		
 		//+---------------------------------------
 		// Get a grip, er count
 		//+---------------------------------------
 		
 		$DB->query("SELECT COUNT(id) as total FROM ibf_members WHERE mgroup IN($group_str)".$where);
 		
 		$rows = $DB->fetch_row();
 		
 		if ($rows['total'] < 1)
 		{
 			$this->bulk_mail_form(1, 'Errors', '��������� ��������, �.�. � ���� ������ ��� �������������, ����������� �� �����������');
 			exit();
 		}
 		
 		//+---------------------------------------
 		// Regex up stuff
		//+---------------------------------------
		
		$DB->query("SELECT * FROM ibf_stats");
		$stats = $DB->fetch_row();
		
		$contents = $std->txt_stripslashes($HTTP_POST_VARS['email_contents']);
		
		$contents = str_replace( "{board_name}" , str_replace( "&#39;", "'", $INFO['board_name'] ) , $contents );
		$contents = str_replace( "{board_url}"  , $INFO['board_url']."/index.".$INFO['php_ext'] , $contents );
		$contents = str_replace( "{reg_total}"  , $stats['MEM_COUNT'] , $contents );
		$contents = str_replace( "{total_posts}", $stats['TOTAL_TOPICS'] + $stats['TOTAL_REPLIES'] , $contents );
		$contents = str_replace( "{busy_count}" , $stats['MOST_COUNT'] , $contents );
		$contents = str_replace( "{busy_time}"  , $std->get_date( $stats['MOST_DATE'], 'SHORT' ), $contents );

 		
 		//+---------------------------------------
 		// Are we previewing? Why am I asking you?
		//+---------------------------------------
		
		if ($IN['preview'] != "")
		{
			$this->bulk_mail_form(1, '��������������� ��������',
									 "<b>".$std->txt_stripslashes($IN['title'])."</b><br><br>"
									 .$contents."<br><br><b>�����������:</b> ".$rows['total']
								 );
 			exit();
 		}
 		
 		//+---------------------------------------
 		// We're still here? GROOVY, send da mail
		//+---------------------------------------
		
		@set_time_limit(1200);
		
		require "./sources/lib/emailer.php";
		
		$this->email = new emailer();
		
		$this->email->bcc = array();
		
		$DB->query("SELECT email FROM ibf_members WHERE mgroup IN($group_str)".$where);
		
		while ( $r = $DB->fetch_row() )
		{
			if ($r['email'] != "")
			{
				$this->email->bcc[] = $r['email'];
			}
		}
		
		$this->email->from    = $INFO['email_in'];
		$this->email->message = str_replace( "\r\n", "\n", $contents);
		$this->email->subject = $std->txt_stripslashes($IN['title']);
		
		if ($IN['email_admin'] == 1)
		{
			$this->email->to = $INFO['email_in'];
		}
		else
		{
			$this->email->to = "";
		}
		
		$this->email->send_mail();
		
		$ADMIN->save_log("�������� e-mail �������� ��� ($where)");
		
		$ADMIN->done_screen("�������� �������� ���������", "���������� ��������������", "act=mem" );
		
	}
	
	
	
	
	function bulk_mail_form($preview=0, $title='Preview', $content="") {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		
		$ADMIN->page_title = "�������� ��������";
		
		$ADMIN->page_detail = "����� �� ������ ���������� �������� e-mail �������� ����� �������������. ��������� ����� ����. �� ������ ������������ � ���� �������� ����� ��������, ����� �� ������ '������'.";
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'domail'  ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
									     
		if ($HTTP_POST_VARS['email_contents'] == "")
		{
			$HTTP_POST_VARS['email_contents'] = "\n\n\n-------------------------------------\n ���������� ������ {board_name}:\n"
								   ."-------------------------------------\n������������������ �������������: {reg_total}\n��������� �� ������: {total_posts}\n"
								   ."������ ������������: {busy_count} �������������, ��������������� {busy_time}\n\n"
								   ."-------------------------------------\n����������� ������\n"
								   ."-------------------------------------\n����� ������: {board_url}\n�����������: {board_url}?act=Login&CODE=00\n"
								   ."�������������� �������� ������: {board_url}?act=Reg&CODE=10\n\n"
								   ."-------------------------------------\n������� �� ��������\n"
								   ."-------------------------------------\n������� � ��� ������� �� ������ '��������� E-mail' ({board_url}?act=UserCP&CODE=02) � ��������� �������� � ���� "
								   ."�������� ��� ��� ���� ����������, ���������� ��������������� ������";
		}
		
		if ($preview == 1)
		{
			$SKIN->td_header[] = array( "&nbsp;"  , "100%" );
			
			$ADMIN->html .= $SKIN->start_table( $title );
			
			$ADMIN->html .= $SKIN->add_td_row( array( nl2br($content) ) );
			
			$ADMIN->html .= $SKIN->end_table();
		}
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ��������: ����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������ ���, ��� �������� �� '����������� �� ��������������'?</b><br>����������� ������� '��', ����� ��� ����� ��������� ������!!" ,
												  $SKIN->form_yes_no( "honour_user_setting", isset($IN["honour_user_setting"]) ? $IN["honour_user_setting"] : 1 )
									     	)      );
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������</b>" ,
												  $SKIN->form_input( "title", $std->txt_stripslashes($IN['title']) )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b><br>".$SKIN->js_help_link('m_bulkemail') ,
												  $SKIN->form_textarea( "email_contents", $std->txt_stripslashes($HTTP_POST_VARS['email_contents']), 60, 15 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� ������ �� e-mail ����� �������������� ��� �������� �����?</b><br>��������� ��� ������������� SMTP ������." ,
												  $SKIN->form_yes_no( "email_admin", isset($IN['email_admin']) ? $IN['email_admin'] : 1 )
									     )      );
									     							     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ��������: ���������" );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id <> ".$INFO['guest_group']." ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ������ <span style='color:red'>{$r['g_title']}</span>?</b>" ,
												  $SKIN->form_yes_no( "sg_{$r['g_id']}", isset($IN['sg_'.$r['g_id']]) ? $IN['sg_'.$r['g_id']] : 1 )
									     	)      );
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������, ������� ����� [x] ���������</b><br>�� ����������, ���� ������ ��������� ���� �������������, ���������� �� ���-�� ���������" ,
												  $SKIN->form_input( "post", $IN['post'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������, �� ���������� ����� ����� [x] ����</b><br>�� ����������, ���� ������ ��������� ���� �������������, ���������� �� ���-�� ���������" ,
												  $SKIN->form_input( "days", $IN['days'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_basic( '<input type="submit" name="preview" id="button" value="��������������� ��������">', 'center' );
		
		$ADMIN->html .= $SKIN->end_form("���������");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// TEMP BANNING
	//
	//+---------------------------------------------------------------------------------
	
	function temp_ban_start()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "���������� ��������";
		
		$ADMIN->page_detail = "�������������� ��������� ���������� ������������. ������ �������� ������������ ���������� � ����������� ��������, ����";
		
		$contents = "{membername},\n��� ���������������� ������� �� ������ {$INFO['board_name']}, �������� ������������.\n\n��� ������� ����� ������������ �� {date_end} (� ����������� �� ������ �������� �����). ������� �������������, ����� �������� �������������. �� ���������� ������ ������������� �������, ��� �������������.\n\n����� ������: {$INFO['board_url']}/index.php";
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("���������� ������ �������� id ������������. ��������� ����� � ��������� �������.");
		}
		
		$DB->query("SELECT * FROM ibf_members WHERE id='".$IN['mid']."'");
		
		if ( ! $member = $DB->fetch_row() )
		{
			$ADMIN->error("� ���������������� ���� ������, ��� ������������ � ����� ID");
		}
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dobanmember'  ),
												  2 => array( 'act'   , 'mem'       ),
												  3 => array( 'mid'   , $IN['mid']  ),
									     )      );
									     		
		$ban = $std->hdl_ban_line( $member['temp_ban'] );
		
		$units = array( 0 => array( 'h', '�����' ), 1 => array( 'd', '����' ) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ��������", "����������: ���� ���� ������������ ���, �������� ������������, �� ��� ����� ���������, ���������� ����� ������ ������." );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<strong>������������� ������������ {$member['name']} ��...</strong>" ,
												  $SKIN->form_input('timespan', $ban['timespan'], "text", "", '5' ) . '&nbsp;' . $SKIN->form_dropdown('units', $units, $ban['units'] ),
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ������������ �� e-mail?</b><br>(���� ��������, ������ ��������������� ����� ������, ����)" ,
												  $SKIN->form_yes_no( "send_email", 0 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b><br>(����: {membername} = ��� ������������, {date_end} = ���� ��������� ����������)" ,
												  $SKIN->form_textarea( "email_contents", $contents )
									     ), "", 'top'       );
									     									     
		$ADMIN->html .= $SKIN->end_form("������������� ���� �������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	}
	
	//---------------------------------------------------------------
	
	function unsuspend()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("���������� ������ �������� id ������������. ��������� ����� � ��������� �������.");
		}
		
		if ($IN['mid'] == 'all')
		{
			$DB->query("UPDATE ibf_members SET temp_ban=''");
			
			$ADMIN->save_log("������������� ���� �������������");
		
			$ADMIN->done_screen("��� ������������ ��������������", "���������� ��������������", "act=mem" );
		}
		else
		{
			$mid = intval($IN['mid']);
			
			$DB->query("UPDATE ibf_members SET temp_ban='' WHERE id=$mid");
			
			$DB->query("SELECT name FROM ibf_members WHERE id=$mid");
			
			$member = $DB->fetch_row();
			
			$ADMIN->save_log("������������� ������������ {$member['name']}");
		
			$ADMIN->done_screen("������������ {$member['name']} �������������", "���������� ��������������", "act=mem" );
		}	
			
		
	}
	
	//---------------------------------------------------------------
	
	function temp_ban_complete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$ADMIN->page_title = "���������� ��������";
		
		$ADMIN->page_detail = "�������������� ��������� ���������� ������������. ������������� � ����������";
		
		$IN['mid'] = intval($IN['mid']);
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("���������� ������ �������� id ������������. ��������� ����� � ��������� �������.");
		}
		
		$DB->query("SELECT * FROM ibf_members WHERE id='".$IN['mid']."'");
		
		if ( ! $member = $DB->fetch_row() )
		{
			$ADMIN->error("� ���������������� ���� ������, ��� ������������ � ����� ID");
		}
		
		//+-------------------------------
		// Work out end date
		//+-------------------------------
		
		$IN['timespan'] = intval($IN['timespan']);
		
		if ( $IN['timespan'] == "" )
		{
			$new_ban = "";
		}
		else
		{
			$new_ban = $std->hdl_ban_line( array( 'timespan' => intval($IN['timespan']), 'unit' => $IN['units']  ) );
		}
		
		$show_ban = $std->hdl_ban_line( $new_ban );
			
		//+-------------------------------
		// Update and show confirmation
		//+-------------------------------
									     
		$DB->query("UPDATE ibf_members SET temp_ban='$new_ban' WHERE id={$IN['mid']}");
		
		// I say, did we choose to email 'dis member?
		
		if ($IN['send_email'] == 1)
		{
			// By golly, we did!
			
			require "./sources/lib/emailer.php";
		
			$this->email = new emailer();
			
			$msg = trim($std->txt_stripslashes($HTTP_POST_VARS['email_contents']));
			
			$msg = str_replace( "{membername}", $member['name']       , $msg );
			$msg = str_replace( "{date_end}"  , $ADMIN->get_date( $show_ban['date_end'], 'LONG') , $msg );
			
			$this->email->message = $this->email->clean_message($msg);
			$this->email->subject = "����������� � ���������� ��������";
			$this->email->to      = $member['email'];
			$this->email->send_mail();
			
			$skin_extra = $SKIN->add_td_row( array( "<strong>������ � ��������</strong>",
												    "<strong>����������� � ���������� ��������</strong><br /><br />".str_replace( "\n", "<br />", $msg)
									    ), "", 'top'      );
		}
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ������������: ���������"  );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<strong>{$member['name']} ������������ ��...</strong>",
												  $ADMIN->get_date( $show_ban['date_end'], 'LONG'),
									    )      );
									    
		$ADMIN->html .= $skin_extra;
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->nav[] = array( "act=mem&code=stepone&USER_NAME={$member['name']}", "����� �������������� ��� {$member['name']}" );
		
		$ADMIN->save_log("���������� {$member['name']} �� ".$ADMIN->get_date( $show_ban['date_end'], 'SHORT'));
		
		$ADMIN->output();
	}
	
		
	//+---------------------------------------------------------------------------------
	//
	// CHANGE MEMBER NAME
	//
	//+---------------------------------------------------------------------------------
	
	function change_name_complete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$IN['new_name'] = str_replace( '|', '&#124;', $IN['new_name'] );
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("���������� ������ �������� id ������������. ��������� ����� � ��������� �������.");
		}
		
		if ($IN['new_name'] == "")
		{
			$this->change_name_start("�� �� ����� ����� ��� ��� ����� ������������");
			exit();
		}
		
		$DB->query("SELECT name, email FROM ibf_members WHERE id='".$IN['mid']."'");
		
		if ( ! $member = $DB->fetch_row() )
		{
			$ADMIN->error("� ���������������� ���� ������, ��� ������������ � ����� ID");
		}
		
		$mid = $IN['mid']; // Save me poor ol' carpels
		
		if ($IN['new_name'] == $member['name'])
		{
			$this->change_name_start("����� ���, ������������� �������, ��� �������� ���������� ���������. �������� ��� ������������ �� �������");
			exit();
		}
		
		// Check to ensure that his member name hasn't already been taken.
		
		$new_name = trim($IN['new_name']);
		
		$DB->query("SELECT id FROM ibf_members WHERE LOWER(name)='".strtolower($new_name)."'");
		
		if ( $DB->get_num_rows() )
		{
			$this->change_name_start("��� '$new_name' ��� ������������ �� ������. �������� ������ ���.");
			exit();
		}
		
		// If one gets here, one can assume that the new name is correct for one, er...one.
		// So, lets do the converteroo
		
		$DB->query("UPDATE ibf_members SET name='$new_name' WHERE id='$mid'");
		$DB->query("UPDATE ibf_contacts SET contact_name='$new_name' WHERE contact_id='$mid'");
		$DB->query("UPDATE ibf_forums SET last_poster_name='$new_name' WHERE last_poster_id='$mid'");
		$DB->query("UPDATE ibf_moderator_logs SET member_name='$new_name' WHERE member_id='$mid'");
		$DB->query("UPDATE ibf_moderators SET member_name='$new_name' WHERE member_id='$mid'");
		$DB->query("UPDATE ibf_posts SET author_name='$new_name' WHERE author_id='$mid'");
		$DB->query("UPDATE ibf_sessions SET member_name='$new_name' WHERE member_id='$mid'");
		$DB->query("UPDATE ibf_topics SET starter_name='$new_name' WHERE starter_id='$mid'");
		$DB->query("UPDATE ibf_topics SET last_poster_name='$new_name' WHERE last_poster_id='$mid'");
		
		// I say, did we choose to email 'dis member?
		
		if ($IN['send_email'] == 1)
		{
			// By golly, we did!
			
			require "./sources/lib/emailer.php";
		
			$this->email = new emailer();
			
			$msg = trim($HTTP_POST_VARS['email_contents']);
			
			$msg = str_replace( "{old_name}", $member['name'], $msg );
			$msg = str_replace( "{new_name}", $new_name      , $msg );
			
			$this->email->message = $this->email->clean_message($msg);
			$this->email->subject = "����������� �� ��������� ����� ������������";
			$this->email->to      = $member['email'];
			$this->email->send_mail();
		}
		
		$DB->query("SELECT id, name FROM ibf_members WHERE mgroup <> '".$INFO['auth_group']."' ORDER BY id DESC LIMIT 0,1");
		$r = $DB->fetch_row();
		$stats['LAST_MEM_NAME'] = $r['name'];
		$stats['LAST_MEM_ID']   = $r['id'];
		
		$db_string = $DB->compile_db_update_string( $stats );
		$DB->query("UPDATE ibf_stats SET $db_string");
		
		$ADMIN->save_log("��������� ����� ������������ '{$member['name']}' �� '$new_name'");
		
		if ( USE_MODULES == 1 )
		{
			$this->modules->register_class(&$this);
			$this->modules->on_name_change($mid, $new_name );
		}
		
		$ADMIN->done_screen("��� ������������ ��������", "���������� ��������������", "act=mem" );
	}
	
	
	
	//===========================================================================
	
	function change_name_start($message="") {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "��������� ����� ������������";
		
		$ADMIN->page_detail = "�� ������ �������� ��� ����� ������������.";
		
		if ($IN['mid'] == "")
		{
			$ADMIN->error("���������� ������ �������� id ������������. ��������� ����� � ��������� �������.");
		}
		
		$DB->query("SELECT name FROM ibf_members WHERE id='".$IN['mid']."'");
		
		if ( ! $member = $DB->fetch_row() )
		{
			$ADMIN->error("� ���������������� ���� ������, ��� ������������ � ����� ID");
		}
		
		$contents = "{old_name},\n������������� ������ {$INFO['board_name']}, ������� ���� ��� �� ������.\n\n���� ����� ��� ������������: {new_name}\n\n��� ��������� ��������� ������, �� ������ ������ ������������������ � ����� ����� ������.\n����� ������: {$INFO['board_url']}/index.php";
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dochangename'  ),
												  2 => array( 'act'   , 'mem'       ),
												  3 => array( 'mid'   , $IN['mid']  ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ����� ������������" );
		
		if ($message != "")
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �� ������:</b>" ,
												  	  "<b><span style='color:red'>$message</span></b>",
									     	 )      );
		}
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��� ������������</b>" ,
												  $member['name'],
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��� ������������</b>" ,
												  $SKIN->form_input( "new_name", $IN['new_name'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������ �� e-mail, �� ���������?</b><br>(���� ��������, ������ ��������������� ����� ������, ����)" ,
												  $SKIN->form_yes_no( "send_email", 1 )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b><br>(����: {old_name} = ������� ���, {new_name} = ����� ���)" ,
												  $SKIN->form_textarea( "email_contents", $contents )
									     )      );
									     									     
		$ADMIN->html .= $SKIN->end_form("�������� ��� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	}
	
	//+---------------------------------------------------------------------------------
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// Moderation control...
	//
	//+---------------------------------------------------------------------------------
	
	function domod() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ids = array();
		
		foreach ($IN as $k => $v)
		{
			if ( preg_match( "/^mid_(\d+)$/", $k, $match ) )
			{
				if ($IN[ $match[0] ])
				{
					$ids[] = $match[1];
				}
			}
		}
		
		//-------------------
		
		if ( count($ids) < 1 )
		{	
			$ADMIN->error("�� �� ������� �������������, ��� �������� ��� �������������");
		}
		
		//-------------------
		
		if ($IN['type'] == 'approve')
		{
		
			//-------------------------------------------
			
			require ROOT_PATH."sources/lib/emailer.php";
			
			$email = new emailer();
			
			$email->get_template("complete_reg");
			
			$email->build_message( "" );
			
			$email->subject = "������������� ����������� �� ".$INFO['board_name'];
			
			//-------------------------------------------
			
			$main = $DB->query("SELECT m.id, m.email, m.mgroup, v.* FROM ibf_validating v
								 LEFT JOIN ibf_members m ON (v.member_id=m.id)
							    WHERE m.id IN(".implode( ",",$ids ).")");
			
			while( $row = $DB->fetch_row( $main ) )
			{
				if ($row['mgroup'] != $INFO['auth_group'])
				{
					continue;
				}
				
				if ($row['real_group'] == "")
				{
					$row['real_group'] = $INFO['member_group'];
				}
				
				$update = $DB->query("UPDATE ibf_members SET mgroup=".$row['real_group']." WHERE id=".$row['id']);
				
				$email->to = $row['email'];
				
				$email->send_mail();
			}
			
			$DB->query("DELETE FROM ibf_validating WHERE member_id IN(".implode( ",",$ids ).")");
			
			$DB->query("SELECT id, name FROM ibf_members WHERE mgroup <> ".$INFO['auth_group']." ORDER BY id DESC LIMIT 0,1");
			$r = $DB->fetch_row();

			$DB->query("UPDATE ibf_stats SET MEM_COUNT=MEM_COUNT+".count($ids).", LAST_MEM_NAME='{$r['name']}', LAST_MEM_ID='{$r['id']}'");
			
			$ADMIN->save_log("������������� ����������� ��");
			
			$ADMIN->done_screen( count($ids)." ����������� ������������", "���������� �������������", "act=mem&code=mod" );	
			
		}
		else
		{
			$DB->query("DELETE FROM ibf_members WHERE id IN(".implode( ",",$ids ).")");
			
			$DB->query("DELETE FROM ibf_member_extra WHERE id IN(".implode( ",",$ids ).")");
			
			// Delete member messages...
		
			$DB->query("DELETE from ibf_messages WHERE member_id IN(".implode( ",",$ids ).")");
			$DB->query("DELETE from ibf_contacts WHERE member_id IN(".implode( ",",$ids ).") or contact_id IN(".implode( ",",$ids ).")");
			
			$DB->query("DELETE FROM ibf_validating WHERE member_id IN(".implode( ",",$ids ).")");
			
			$DB->query("DELETE from ibf_pfields_content WHERE member_id IN(".implode( ",",$ids ).")");
			
			$DB->query("DELETE from ibf_warn_logs WHERE wlog_mid IN(".implode( ",",$ids ).")");
			
			// Convert their posts and topics into guest postings..
		
			$DB->query("UPDATE ibf_posts SET author_id='0' WHERE author_id IN(".implode( ",",$ids ).")");
		
			$DB->query("UPDATE ibf_topics SET starter_id='0' WHERE starter_id IN(".implode( ",",$ids ).")");
			
			if ( USE_MODULES == 1 )
			{
				$this->modules->register_class(&$this);
				$this->modules->on_delete($ids);
			}
			
			$ADMIN->save_log("�������� ��������� �����������");
			
			$ADMIN->done_screen( count($ids)." ����������� �������", "���������� �������������", "act=mem&code=mod" );
		}
		
	}
	
	
	//---------------------------------------------
	
	function view_mod() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title  = "���������� ������������� � ��������������, ����������� e-mail";
		
		$ADMIN->page_detail = "� ���� ������ �� ������ ������������ ��� ������� ������������������ �������������, ��������� ������������� ��������������, � ����� ������������ ��� ������� �������������, ���������� e-mail �����.<br><br>�����, �� ������ ������������ ����������� ���, ��� �� �����-���� ������� �� ������� ������, ��� ��������� �����������.";
		
		$DB->query("SELECT COUNT(vid) as mcount FROM ibf_validating WHERE lost_pass <> 1");
		
		$row = $DB->fetch_row();
		
		$cnt = $row['mcount'] < 1 ? 0 : $row['mcount'];
		
		$st = intval($IN['st']);
		
		$ord = $IN['ord'] == 'asc' ? 'asc' : 'desc';
		
		$new_ord  = $ord  == 'asc' ? 'desc' : 'asc';
		
		switch ($IN['sort'])
		{
			case 'mem':
				$col = 'm.name';
				break;
			case 'email':
				$col = 'm.email';
				break;
			case 'sent':
				$col = 'v.entry_date';
				break;
			case 'posts':
				$col = 'm.posts';
				break;
			case 'reg':
				$col = 'm.joined';
				break;
			default:
				$col = 'v.entry_date';
				break;
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'domod'  ),
												  2 => array( 'act'   , 'mem'    ),
									     )      );
									     
		$SKIN->td_header[] = array( "<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=mem&ord=$new_ord'>��� ������������</a>"       , "20%" );
		$SKIN->td_header[] = array( "���������"            , "20%" );
		$SKIN->td_header[] = array( "<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=email&ord=$new_ord'>E-mail �����</a>"     , "15%" );
		$SKIN->td_header[] = array( "<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=sent&ord=$new_ord'>������ ����������</a>"        , "10%" );
		$SKIN->td_header[] = array( "<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=posts&ord=$new_ord'>���������</a>"             , "10%" );
		$SKIN->td_header[] = array( "<a href='{$SKIN->base_url}&act=mem&code=mod&st=$st&sort=reg&ord=$new_ord'>�����-���</a>"           , "10%" );
		$SKIN->td_header[] = array( "��������" , "10%" );
		$SKIN->td_header[] = array( "&nbsp;"            , "5%" );
		
		$ADMIN->html .= $SKIN->start_table( "������������, ��������� ���������" );
		
		$links = $std->build_pagelinks( array( 'TOTAL_POSS'  => $cnt,
											   'PER_PAGE'    => 75,
											   'CUR_ST_VAL'  => $st,
											   'L_SINGLE'    => "������������ ��������",
											   'L_MULTI'     => "��������� �������",
											   'BASE_URL'    => $SKIN->base_url."&act=mem&code=mod",
									  )      );
		
		$ADMIN->html .= $SKIN->add_td_basic( "<b>$cnt �������������, ��������� ������������� ����������� ��� ��������� e-mail ������.</b>", "center", "catrow2");
		
		if ($cnt > 0)
		{
			$DB->query("SELECT m.name, m.id, m.email, m.posts, m.joined, v.*
					      FROM ibf_validating v
					    LEFT JOIN ibf_members m ON (v.member_id=m.id)
					    WHERE v.lost_pass <> 1
					    ORDER BY $col $ord LIMIT $st,75");
			
			while ( $r = $DB->fetch_row() )
			{
			
				if ($r['coppa_user'] == 1)
				{
					$coppa = ' ( ������ COPPA )';
				}
				else
				{
					$coppa = "";
				}
				
				$where = ( $r['lost_pass'] ? '������� ������' : ( $r['new_reg'] ? "�����������" : ( $r['email_chg'] ? "��������� Email" : 'N/A' ) ) );
				
				//$age = floor( ( time() - $r['entry_date'] ) / 86400 );
				
				$hours  = floor( ( time() - $r['entry_date'] ) / 3600 );
				
				$days   = intval( $hours / 24 );
				
				$rhours = intval( $hours - ($days * 24) );
				
				if ( $r['name'] == "" )
				{
					$r['name'] = "<em>�������� ������������</em>";
				}
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".$r['name']."</b>$coppa" ,
				 										  "<center>$where</center>",
												  		  $r['email'],
												  		  "<center>".$std->get_date( $r['entry_date'], 'JOINED' )."</center>",
												  		  "<center>{$r['posts']}</center>",
												  		  "<center>".$std->get_date( $r['joined'], 'JOINED' )."</center>",
												  		  "<center><strong><span style='color:red'>$days d</span>, $rhours h</center>",
												  		  "<center><input type='checkbox' name='mid_{$r['member_id']}' value='1'></center>"
											 )      );
			}
			$ADMIN->html .= $SKIN->add_td_basic( "$links", "left", "catrow2");
	
			$ADMIN->html .= $SKIN->add_td_basic("<select name='type' id='dropdown'><option value='approve'>����������� �����������</option><option value='delete'>������� �����������</option></select>", "center", "catrow2" );
			
		}
		
		$ADMIN->html .= $SKIN->end_form("��");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// Ban control...
	//
	//+---------------------------------------------------------------------------------
	
	function ban_control() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "��� �������������";
		
		$ADMIN->page_detail = "� ���� ������ �� ������ ���������, ������������� ��� ������� IP ������, e-mail ������ � ����� �������������, ������� ���������� ��������.";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doban'  ),
												  2 => array( 'act'   , 'mem'       ),
									     )      );
									     
		$ip_list    = "";
		$name_list  = "";
		$email_list = "";
		
		if ($INFO['ban_ip'] != "")
		{
			$ip_list = preg_replace( "/\|/", "\n", $INFO['ban_ip'] );
		}
		
		//+-------------------------------
		
		if ($INFO['ban_email'] != "")
		{
			$email_list = preg_replace( "/\|/", "\n", $INFO['ban_email'] );
		}
		
		//+-------------------------------
		
		if ($INFO['ban_names'] != "")
		{
			$name_list = preg_replace( "/\|/", "\n", $INFO['ban_names'] );
		}

		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"     , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"     , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��� �������������" );
		
		$ADMIN->html .= $SKIN->add_td_basic("���������� IP ������ (���� ����� �� ������ - ����������� ���� * � �������� �����)", "center", "pformstrip");
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �� IP</b><br>(������: 212.45.45.23)<br>(������: 212.45.45.*)" ,
												  $SKIN->form_textarea( 'ban_ip', $ip_list )
											 )      );
											 
		$ADMIN->html .= $SKIN->add_td_basic("���������� e-mail ������ (���� ����� �� ������ - ����� ������������ ���� * � �������� �����)", "center", "pformstrip");
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �� e-mail</b><br>(������: name@domain.com)<br>(������: *@domain.com)" ,
												  $SKIN->form_textarea( 'ban_email', $email_list )
											 )      );
											 
		$ADMIN->html .= $SKIN->add_td_basic("���������� ��� ����������������� ����� (���� ��� �� ������)", "center", "pformstrip");
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �� �����</b>" ,
												  $SKIN->form_textarea( 'ban_names', $name_list )
											 )      );
											 
		$ADMIN->html .= $SKIN->end_form("�������� ��� ������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();

	}
	
	function update_ban()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		// Get the incoming..
		
		$new = array();
		
		$new['ban_ip']    = $this->_do_banline($HTTP_POST_VARS['ban_ip']);
		$new['ban_email'] = $this->_do_banline($HTTP_POST_VARS['ban_email']);
		$new['ban_names'] = $this->_do_banline($HTTP_POST_VARS['ban_names']);
		
		$ADMIN->rebuild_config( $new );
		
		$ADMIN->save_log("���������� ��� �������");
		
		$ADMIN->done_screen("��� ����� �������", "��� �������������", "act=mem&code=ban" );		
		
	}
	
	function _do_banline($raw)
	{
		global $std;
		
		$ban = trim($std->txt_stripslashes($raw));
		
		$ban = str_replace('|', "&#124;", $ban);
		
		$ban = preg_replace( "/\n/", '|', str_replace( "\n\n", "\n", str_replace( "\r", "\n", $ban ) ) );
		
		$ban = preg_replace( "/\|{1,}\s{1,}?/s", "|", $ban );
		
		$ban = preg_replace( "/^\|/", "", $ban );
		
		$ban = preg_replace( "/\|$/", "", $ban );
		
		$ban = str_replace( "'", '&#39;', $ban );
		
		return $ban;
	}
		
		
	//+---------------------------------------------------------------------------------
	//
	// MEMBER RANKS...
	//
	//+---------------------------------------------------------------------------------
	
	function titles() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "��������� ������� �������������";
		
		$ADMIN->page_detail = "����� �� ������ �������������, ������� ��� ��������� �������������� �������.<br>���� �� ������ ���������� ������ ������� ���� ����� ������������, ������� ���-�� ������� ��� ������� �������. ���� ������ ������� �� ������ ������������ �����-�� �����������, ������� �������� ����� ����������� � ���� ��� ����� ���-�� �������. <br><b>����������:</b> ����������� ������ ���������� � ���������� 'html/team_icons' ������ ������.";
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "������"      , "30%" );
		$SKIN->td_header[] = array( "���-�� ���������"  , "10%" );
		$SKIN->td_header[] = array( "�����������"       , "20%" );
		$SKIN->td_header[] = array( "�������������"     , "20%" );
		$SKIN->td_header[] = array( "�������"     , "20%" );
		
		//+-------------------------------
		
		$DB->query("SELECT macro_id, img_dir FROM ibf_skins WHERE default_set=1");
		
		$mid = $DB->fetch_row();
		
		$DB->query("SELECT macro_replace AS A_STAR FROM ibf_macro WHERE macro_set={$mid['macro_id']} AND macro_value='A_STAR'");
    	           
    	$row = $DB->fetch_row();
    	
    	$row['A_STAR'] = str_replace( "<#IMG_DIR#>", $mid['img_dir'], $row['A_STAR'] );
		
		$ADMIN->html .= $SKIN->start_table( "������� �������������" );
		
		$DB->query("SELECT * FROM ibf_titles ORDER BY posts");
		
		while ( $r = $DB->fetch_row() )
		{
			$img = "";
			
			if ( preg_match( "/^\d+$/", $r['pips'] ) )
			{
				for ($i = 1; $i <= $r['pips']; $i++)
				{
					$img .= $row['A_STAR'];
					
				}
			}
			else
			{
				$img = "<img src='html/team_icons/{$r['pips']}' border='0'>";
			}
				
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>".$r['title']."</b>" ,
													  $r['posts'],
													  $img,
													  "<a href='{$SKIN->base_url}&act=mem&code=rank_edit&id={$r['id']}'>�������������</a>",
													  "<a href='{$SKIN->base_url}&act=mem&code=rank_delete&id={$r['id']}'>�������</a>",
											 )      );
			
		}
									     
		
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'do_add_rank'  ),
												  2 => array( 'act'   , 'mem'       ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ������ �������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������</b>" ,
												  $SKIN->form_input( "title" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���-�� ��������� ��� ����� �������</b>" ,
												  $SKIN->form_input( "posts" )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� �������</b><br>(��� �������� �����������)" ,
												  $SKIN->form_input( "pips" )
									     )      );
									     									     
		$ADMIN->html .= $SKIN->end_form("�������� ������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	}
	
	//+---------------------------------------------------------------------------------
	
	function add_rank() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		// check for input
		//+-------------------------------
		
		foreach( array( 'posts', 'title', 'pips' ) as $field )
		{
			if ($IN[ $field ] == "")
			{
				$ADMIN->error("���������� ��������� ��� ���� �����");
			}
		}
		
		//+-------------------------------
		// Add it to the DB
		//+-------------------------------
		
		$db_string = $DB->compile_db_insert_string( array (
															 'posts'  => trim($IN['posts']),
															 'title'  => trim($IN['title']),
															 'pips'   => trim($IN['pips']),
												  )       );
												  
		$DB->query("INSERT INTO ibf_titles (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
		
		$ADMIN->done_screen("������ ��������", "��������� ��������", "act=mem&code=title" );					
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function delete_rank() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		// check for input
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("��� ID �� �������");
		}
		
		$DB->query("DELETE FROM ibf_titles WHERE id='".$IN['id']."'");
		
		$ADMIN->save_log("�������� �������");
		
		$ADMIN->done_screen("������ �����", "��������� ��������", "act=mem&code=title" );
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function edit_rank() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//+-------------------------------
		// check for input
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("��� ID �� �������");
		}
		
		//+-------------------------------
		
		foreach( array( 'posts', 'title', 'pips' ) as $field )
		{
			if ($IN[ $field ] == "")
			{
				$ADMIN->error("���������� ��������� ��� ���� �����");
			}
		}
		
		//+-------------------------------
		// Add it to the DB
		//+-------------------------------
		
		$db_string = $DB->compile_db_update_string( array (
															 'posts'  => trim($IN['posts']),
															 'title'  => trim($IN['title']),
															 'pips'   => trim($IN['pips']),
												  )       );
												  
		$DB->query("UPDATE ibf_titles SET $db_string WHERE id='".$IN['id']."'");
		
		$ADMIN->save_log("�������������� �������");
		
		$ADMIN->done_screen("������ ��������������", "��������� ��������", "act=mem&code=title" );					
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function rank_setup($mode='edit') {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "��������� ������� �������������";
		
		$ADMIN->page_detail = "���� �� ������, ����� ������������ ������ ������� ���� ����� ������������, ������� ���-�� ������� ��� ������� �������. ���� ������ ������� �� ������ ������������ �����-�� �����������, ������� �������� ����� ����������� � ���� ��� ����� ���-�� �������. <br><b>����������:</b> ����������� ������ ���������� � ���������� 'html/team_icons' ������ ������.";
		
		if ($mode == 'edit')
		{
			$form_code = 'do_rank_edit';
			
			if ($IN['id'] == "")
			{
				$ADMIN->error("������ �� ������. ���������� �����");
			}
			
			$DB->query("SELECT * from ibf_titles WHERE id='".$IN['id']."'");
			$rank = $DB->fetch_row();
			
			$button = "��������� ���������";
		}
		else
		{
			$form_code = 'do_add_rank';
			$rank = array( 'posts' => "", 'title' => "", 'pips' => "");
			$button = "�������� ������";
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $form_code  ),
												  2 => array( 'act'   , 'mem'       ),
												  3 => array( 'id'    , $rank['id'] ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������� �������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������</b>" ,
												  $SKIN->form_input( "title", $rank['title'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���-�� ���������</b>" ,
												  $SKIN->form_input( "posts", $rank['posts'] )
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� �������</b><br>(��� �������� �����������)" ,
												  $SKIN->form_input( "pips", $rank['pips'] )
									     )      );
									     									     
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	
	//+---------------------------------------------------------------------------------
	//
	// DELETE MEMBER SET UP
	//
	//+---------------------------------------------------------------------------------
	
	function delete_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "�������� ������������";
		
		$ADMIN->page_detail = "����������� ����� ������������, �������� ������ �������, ����� ������ ��� ��� ����� �����, ���� ��������� ������ ����� ��������.";
		
		
		$mem_group[0] = array( '0', '����� ������' );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'delete2' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����� ������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ����� ��� ������ ��� ������������</b>" ,
												  $SKIN->form_input( "USER_NAME" )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'prune' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "<u>���</u> ������� ��������� �������������..." );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������, ���������� ��������� ��������� [x] ���� �����.</b><br>([x] = ���-�� ����)<br>(�� ����������, ���� ������ ���������� ���� ���)" ,
												  $SKIN->form_input( "last_post", '60')
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><u>�</u> �������������, ������� ����� [x] ��������� �� ������</b><br>([x] = ���-�� ���������)<br>(�� ����������, ���� ������ ���������� ���� ���)" ,
												  $SKIN->form_input( "posts", '100')
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><u>�</u> �������������, ������������������ [x] ���� �����</b><br>([x] = ���-�� ����)<br>(�� ����������, ���� ������ ���������� ���� ���)" ,
												  $SKIN->form_input( "joined", '365')
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><u>�</u> ������������� ������...</b>" ,
												  $SKIN->form_dropdown( "mgroup",
																		$mem_group,
												  						0
												  					  )
									     )      );
									     							     
		$ADMIN->html .= $SKIN->end_form("����� ��� ��������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function prune_confirm() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//-----------------------------
		// Make sure we have *something*
		//------------------------------
		
		$blanks = 0;
		
		foreach( array( 'posts', 'last_post', 'joined' ) as $field )
		{
			if ($IN[ $field ] == "")
			{
				$blanks++;
			}
		}
		
		if ($blanks == 3)
		{
			$ADMIN->error("��� ��������, ���������� ��������� ���� �� ���� �� ����� �����");
		}
		
		$time_now = time();
		
		$query = "SELECT COUNT(id) as mcount FROM ibf_members WHERE";
		
		$add_query = array();
		
		if ($IN['joined'] > 0)
		{
			$j = $time_now - ($IN['joined'] *60*60*24);
			$add_query[] = " joined > $j ";
		}
		
		if ($IN['last_post'] > 0)
		{
			$l = $time_now - ($IN['last_post'] *60*60*24);
			$add_query[] = " (last_post < $l or last_post is null)";
		}
		
		if ($IN['posts'] > 0)
		{
			$add_query[] = " posts < ".$IN['posts']." ";
		}
		
		if ($IN['mgroup'] > 0)
		{
			$add_query[] = " mgroup='".$IN['mgroup']."' ";
		}
		
		$add_query[] = ' id > 0';
		
		$additional_query = implode( "AND", $add_query );
		
		$this_query = trim( $query.$additional_query );
		
		$pass_query = addslashes(urlencode($additional_query));
		
		//--------------------------------
		// Run the query
		//--------------------------------
		
		$DB->query($this_query);
		
		$count = $DB->fetch_row();
		
		if ($count['mcount'] < 1)
		{
			$ADMIN->error("�� ������� �� ������ ������������, ���������������� �������� ���������. ��������� ����� � �������� ��������.");
		}
		
		if ($count['mcount'] < 101)
		{
			$DB->query("SELECT id, name FROM ibf_members WHERE $additional_query");
			
			$member_arr = array();
			
			while ( $mem = $DB->fetch_row() )
			{
				$member_arr[] = $std->make_profile_link($mem['name'], $mem['id']);
			}
		}
		
		$ADMIN->page_title = "�������� �������������";
		
		$ADMIN->page_detail = "����������� �������� ��������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doprune' ),
												  2 => array( 'act'   , 'mem'     ),
												  3 => array( 'query' , $pass_query ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������� ��������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� �������������</b>" ,
												  $count['mcount']
									     )      );
									     
		if ( count($member_arr) > 0 )
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������</b>" ,
													  implode( ', ', $member_arr )
											 )      );
		}
									     
		$ADMIN->html .= $SKIN->end_form("������� �������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	
	//+---------------------------------------------------------------------------------
	
	
	function doprune() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//-----------------------------
		// Make sure we have *something*
		//------------------------------
		
		$query = trim(urldecode($std->txt_stripslashes($IN['query'])));
		
		$query = str_replace( "&lt;", "<", $query );
		$query = str_replace( "&gt;", ">", $query );
		
		if ($query == "")
		{
			$ADMIN->error("������ ������� ��������. ��� ������� �� ��������.");
		}
		
		//-----------------------------
		// Get the member ids...
		//------------------------------
		
		$ids = array();
		
		$DB->query("SELECT id FROM ibf_members WHERE ".$query);
		
		if ( $DB->get_num_rows() )
		{
			while ($i = $DB->fetch_row())
			{
				$ids[] = $i['id'];
			}
		}
		else
		{
			$ADMIN->error("�� ������� �� ������ ������������, ���������������� �������� ���������.");
		}
		
		$id_string = implode( "," , $ids );
		
		$id_count = count($ids);
		
		// Convert their posts and topics into guest postings..
		
		$DB->query("UPDATE ibf_posts SET author_id='0' WHERE author_id IN(".$id_string.")");
		
		$DB->query("UPDATE ibf_topics SET starter_id='0' WHERE starter_id IN(".$id_string.")");
		
		// Delete member...
		
		$DB->query("DELETE from ibf_members WHERE id IN(".$id_string.")");
		
		$DB->query("DELETE from ibf_pfields_content WHERE member_id IN(".$id_string.")");
		
		// Delete member messages...
		
		$DB->query("DELETE from ibf_messages WHERE member_id IN (".$id_string.")");
		
		// Delete member subscriptions.
		
		$DB->query("DELETE from ibf_tracker WHERE member_id IN (".$id_string.")");
		
		// Delete from validating..
		
		$DB->query("DELETE FROM ibf_validating WHERE member_id IN (".$id_string.")");
		
		// Set the stats DB straight.
		
		$DB->query("SELECT id, name FROM ibf_members WHERE mgroup <> '".$INFO['auth_group']."' ORDER BY joined DESC LIMIT 0,1");
		
		$memb = $DB->fetch_row();
		
		$DB->query("SELECT COUNT(id) as members from ibf_members WHERE mgroup <> '".$INFO['auth_group']."'");
		$r = $DB->fetch_row();
		// Remove "guest" account...
		$r['members']--;
		$r['members'] < 1 ? 0 : $r['members'];
		
		$DB->query("UPDATE ibf_stats SET ".
			             "MEM_COUNT={$r['members']}, ".
			             "LAST_MEM_NAME='" . $memb['name'] . "', ".
			             "LAST_MEM_ID='"   . $memb['id']   . "'");
			             
		// Blow me melon farmer
		
		$ADMIN->save_log("�������� $id_count �������������");
		
		if ( USE_MODULES == 1 )
		{
			$this->modules->register_class(&$this);
			$this->modules->on_delete($ids);
		}
		
		$ADMIN->done_screen("������������ �������", "���������� ��������������", "act=mem&code=edit" );
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function delete_lookup_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['USER_NAME'] == "")
		{
			$ADMIN->error("�� �� ����� ��� ������������ ��� ������!");
		}
		
		$DB->query("SELECT id, name FROM ibf_members WHERE name LIKE '".$IN['USER_NAME']."%'");
		
		if (! $DB->get_num_rows() )
		{
			$ADMIN->error("�� ������� �� ������ ������������, ���������������� �������� ��������� ������.");
		}
		
		$form_array = array();
		
		while ( $r = $DB->fetch_row() )
		{
			$form_array[] = array( $r['id'] , $r['name'] );
		}
		
		
		
		$ADMIN->page_title = "�������� ������������";
		
		$ADMIN->page_detail = "�������� ������������, ��� ��������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ������ ������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������������...</b>" ,
												  $SKIN->form_dropdown( "MEMBER_ID", $form_array )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("������� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	}
	
	//+---------------------------------------------------------------------------------
	
	//+---------------------------------------------------------------------------------
	//
	// DO DELETE
	//
	//+---------------------------------------------------------------------------------
	
	function dodelete() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['MEMBER_ID'] == "")
		{
			$ADMIN->error("���������� ���������� id ������������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_members WHERE id='".$IN['MEMBER_ID']."'");
		$mem = $DB->fetch_row();
		
		//+-------------------------------
		
		if ($mem['id'] == "")
		{
			$ADMIN->error("���������� ���������� id ������������");
		}
		
		// Convert their posts and topics into guest postings..
		
		$DB->query("UPDATE ibf_posts SET author_id='0' WHERE author_id='".$IN['MEMBER_ID']."'");
		
		$DB->query("UPDATE ibf_topics SET starter_id='0' WHERE starter_id='".$IN['MEMBER_ID']."'");
		
		// Delete member...
		
		$DB->query("DELETE from ibf_members WHERE id='".$IN['MEMBER_ID']."'");
		$DB->query("DELETE from ibf_pfields_content WHERE member_id='".$IN['MEMBER_ID']."'");
		$DB->query("DELETE from ibf_member_extra WHERE id='".$IN['MEMBER_ID']."'");
		
		// Delete member messages...
		
		$DB->query("DELETE from ibf_messages WHERE member_id='".$IN['MEMBER_ID']."'");
		$DB->query("DELETE from ibf_contacts WHERE member_id='".$IN['MEMBER_ID']."' or contact_id='".$IN['MEMBER_ID']."'");
		
		// Delete member subscriptions.
		
		$DB->query("DELETE from ibf_tracker WHERE member_id='".$IN['MEMBER_ID']."'");
		$DB->query("DELETE from ibf_forum_tracker WHERE member_id='".$IN['MEMBER_ID']."'");
		$DB->query("DELETE from ibf_warn_logs WHERE wlog_mid='".$IN['MEMBER_ID']."'");
		
		// Delete from validating..
		
		$DB->query("DELETE FROM ibf_validating WHERE member_id='".$IN['MEMBER_ID']."'");
		
		// Set the stats DB straight.
		
		$DB->query("SELECT id, name FROM ibf_members WHERE mgroup <> '".$INFO['auth_group']."' ORDER BY joined DESC LIMIT 0,1");
		
		$memb = $DB->fetch_row();
		
		$DB->query("SELECT COUNT(id) as members from ibf_members WHERE mgroup <> '".$INFO['auth_group']."'");
		$r = $DB->fetch_row();
		// Remove "guest" account...
		$r['members']--;
		$r['members'] < 1 ? 0 : $r['members'];
		
		$DB->query("UPDATE ibf_stats SET ".
			             "MEM_COUNT={$r['members']}, ".
			             "LAST_MEM_NAME='" . $memb['name'] . "', ".
			             "LAST_MEM_ID='"   . $memb['id']   . "'");
			             
		if ( USE_MODULES == 1 )
		{
			$this->modules->register_class(&$this);
			$this->modules->on_delete($IN['MEMBER_ID']);
		}
		
		// Blow me melon farmer
		
		$ADMIN->save_log("�������� ������������ '{$mem['name']}'");
		
		$ADMIN->done_screen("������������ �����", "���������� ��������������", "act=mem&code=edit" );
		
	}
		
	
	//+---------------------------------------------------------------------------------
	//
	// ADD MEMBER FORM
	//
	//+---------------------------------------------------------------------------------
	
	function add_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "����������� ������������";
		
		$ADMIN->page_detail = "����� �� ������ �������������� ����� �������������.";
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			if ($INFO['admin_group'] == $r['g_id'])
			{
				if ($MEMBER['mgroup'] != $INFO['admin_group'])
				{
					continue;
				}
			}
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		//+-------------------------------
		
		$custom_output = "";
		$field_data     = array();
		
		$DB->query("SELECT * from ibf_pfields_content WHERE member_id='".$IN['MEMBER_ID']."'");
		
		while ( $content = $DB->fetch_row() )
		{
			foreach($content as $k => $v)
			{
				if ( preg_match( "/^field_(\d+)$/", $k, $match) )
				{
					$field_data[ $match[1] ] = $v;
				}
			}
		}
		
		$DB->query("SELECT * from ibf_pfields_data WHERE fshowreg=1 ORDER BY forder");
		
		while( $row = $DB->fetch_row() )
		{
			$form_element = "";
			
			if ( $row['ftype'] == 'drop' )
			{
				$carray = explode( '|', trim($row['fcontent']) );
				
				$d_content = array();
				
				foreach( $carray as $entry )
				{
					$value = explode( '=', $entry );
					
					$ov = trim($value[0]);
					$td = trim($value[1]);
					
					if ($ov and $td)
					{
						$d_content[] = array( $ov, $td );
					}
				}
				
				$form_element = $SKIN->form_dropdown( 'field_'.$row['fid'], $d_content, "" );
				
			}
			else if ( $row['ftype'] == 'area' )
			{
				$form_element = $SKIN->form_textarea( 'field_'.$row['fid'], "" );
			}
			else
			{
				$form_element = $SKIN->form_input( 'field_'.$row['fid'], "" );
			}
			
			$custom_out .= $SKIN->add_td_row( array( "<b>{$row['ftitle']}</b><br>{$row['desc']}" , $form_element ) );
			
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doadd' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������� ������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������</b>" ,
												  $SKIN->form_input( "name" )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������</b>" ,
												  $SKIN->form_input( "password", "", 'password' )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail �����</b>" ,
												  $SKIN->form_input( "email" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������</b>" ,
												  $SKIN->form_dropdown( "mgroup",
																		$mem_group,
												  						$mem['mgroup']
												  					  )
									     )      );
									     
		if ($custom_out != "")
		{
			$ADMIN->html .= $custom_out;
		}
									     						     
		$ADMIN->html .= $SKIN->end_form("����������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	function do_add()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		foreach( array('name', 'password', 'email', 'mgroup') as $field )
		{
			if ($IN[ $field ] == "")
			{
				$ADMIN->error("���������� ��������� ��� ���� �����!");
			}
		}
		
		//----------------------------------
		// Do we already have such a member?
		//----------------------------------
		
		$DB->query("SELECT id FROM ibf_members WHERE LOWER(name)='".$IN['name']."'");
		
		if ( $DB->get_num_rows() )
		{
			$ADMIN->error("������������ � ����� ������ ����������. �������� ������ ���.");
		}
		
		//----------------------------------
		// Custom profile field stuff
		//----------------------------------
		
		$custom_fields = array();
		
		$DB->query("SELECT * from ibf_pfields_data");
		
		$have_custom = $DB->get_num_rows();
		
		while ( $row = $DB->fetch_row() )
		{
			$custom_fields[ 'field_'.$row['fid'] ] = $IN[ 'field_'.$row['fid'] ];
		}
		
		//+--------------------------------------------
		//| Find the highest member id, and increment it
		//| auto_increment not used for guest id 0 val.
		//+--------------------------------------------
		
		$DB->query("SELECT MAX(id) as new_id FROM ibf_members");
		$r = $DB->fetch_row();
		
		$member_id = $r['new_id'] + 1;
		
		$db_string = $DB->compile_db_insert_string( array (
															 'id'          => $member_id,
															 'name'        => trim($IN['name']),
															 'password'    => md5(trim($IN['password'])),
															 'email'       => trim(strtolower($IN['email'])),
															 'mgroup'      => $IN['mgroup'],
															 'joined'      => time(),
															 'posts'       => 0,
															 'ip_address'  => $IN['ip_address'],
															 'time_offset' => 0,
															 'view_sigs'   => 1,
															 'view_avs'    => 1,
															 'view_pop'    => 1,
															 'view_img'    => 1,
															 'vdirs'       => "in:��������|sent:������������",
												  )       );
												  
		$DB->query("INSERT INTO ibf_members (" .$db_string['FIELD_NAMES']. ") VALUES (". $db_string['FIELD_VALUES'] .")");
		
		//$member_id = $DB->get_insert_id();
		
		//+--------------------------------------------
		//| Insert into the custom profile fields DB
		//+--------------------------------------------
		
		if ( count($custom_fields) > 0 )
		{
		
			$custom_fields['member_id'] = $member_id;
				
			$db_string = $DB->compile_db_insert_string($custom_fields);
				
			$DB->query("INSERT INTO ibf_pfields_content (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		}
		
		unset($db_string);
		
		//+--------------------------------------------
		
		$DB->query("UPDATE ibf_stats SET ".
			             "MEM_COUNT=MEM_COUNT+1, ".
			             "LAST_MEM_NAME='" . trim($IN['name']) . "', ".
			             "LAST_MEM_ID='"   . $member_id   . "'");
			             
		$ADMIN->save_log("����������� ������������ � ������ '{$IN['name']}'");
		
		$ADMIN->done_screen("������������ ���������������", "���������� ��������������", "act=mem&code=edit" );												 
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// SEARCH FORM, SEARCH FOR MEMBER
	//
	//+---------------------------------------------------------------------------------
	
	function search_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "�������������� ������������";
		
		$ADMIN->page_detail = "����� ������������.";
		
		$mem_group = array( 0 => array( 'all', '����� ������') );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'stepone' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������� ����� ������������" );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ����� ��� ������ ��� ������������</b>" ,
												  $SKIN->form_input( "USER_NAME" )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'advancedsearch' ),
												  2 => array( 'act'   , 'mem'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������� ����� ������������", "���������� ��������� ���� �� ���� ���� ��� ������. �������� ���� ������ ���������� �������." );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������ ��������...</b>" ,
												  $SKIN->form_input( "name" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ����� ��������...</b>" ,
												  $SKIN->form_input( "email" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>IP ����� ��������...</b>" ,
												  $SKIN->form_input( "ip_address" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � AIM ��������...</b>" ,
												  $SKIN->form_input( "aim_name" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>ICQ ����� ��������...</b>" ,
												  $SKIN->form_input( "icq_number" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � Yahoo! ��������...</b>" ,
												  $SKIN->form_input( "yahoo" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��������...</b>" ,
												  $SKIN->form_input( "signature" )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������� ����������...</b>" ,
												  $SKIN->form_simple_input( "last_post" ). '... ���� ���� �����'
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������� ������...</b>" ,
												  $SKIN->form_simple_input( "last_activity" ). '... ���� ���� �����'
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ � ������...</b>" ,
												  $SKIN->form_dropdown( "mgroup", $mem_group )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����� ������������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_advanced_search($basic=0) {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$page_query = "";
		$un_all = "";
		
		if ( $IN['showsusp'] == 1 )
		{
			$rq  = "temp_ban <> '' and temp_ban is not null";
			$un_all = "<a href='{$SKIN->base_url}&act=mem&code=unsuspend&mid=all'>�������������� ����</a>&nbsp;&middot&nbsp;";
		}
		else
		{
			if ($basic == 0)
			{
				$query = array();
				
				foreach( array('name','email','ip_address','aim_name','icq_number','yahoo','signature','last_post','last_activity','mgroup') as $bit )
				{
					$IN[ $bit ] = urldecode(trim($IN[ $bit ]));
					
					$page_query .= '&'.$bit.'='.urlencode($IN[ $bit ]);
					
					if ($IN[ $bit ] != "")
					{
						if ($bit == 'last_post' or $bit == 'last_activity')
						{
							$dateline = time() - ($IN[ $bit ]*60*60*24);
							$query[] = 'm.'.$bit.' > '."'$dateline'";
						}
						else if ($bit == 'mgroup')
						{
							if ($IN['mgroup'] != 'all')
							{
								$query[] = "m.mgroup=".$IN['mgroup'];
							}
						}
						else
						{
							$query[] = 'm.'.$bit." LIKE '%".$IN[$bit]."%'";
						}
					}
				}
				
				if (count($query) < 1)
				{
					$ADMIN->error("���������� ��������� ���� �� ���� ���� ��� ������.");
				}
				
				$rq = implode( " AND ", $query );
			}
			else
			{
				// Basic username search
				
				if ( $IN['decode'] )
				{
					$IN['USER_NAME'] = trim(urldecode($IN['USER_NAME']));
				}
				else
				{
					$IN['USER_NAME'] = trim($IN['USER_NAME']);
				}
				
				if ($IN['USER_NAME'] == "")
				{
					$ADMIN->error("�� �� ����� ��� ��� ������!");
				}
				
				$page_query = "&decode=1&USER_NAME=".urlencode($IN['USER_NAME']);
			
				$rq = "name LIKE '".$IN['USER_NAME']."%'";
			}
		}
		
		$st = intval($IN['st']);
		
		if ($st < 1)
		{
			$st = 0;
		}
		
		$query = "SELECT m.id, m.email, m.name, m.mgroup, m.ip_address, m.posts, m.temp_ban, g.g_title
		          FROM ibf_members m
		           LEFT JOIN ibf_groups g ON (g.g_id=m.mgroup)
		          WHERE $rq ORDER BY m.name LIMIT $st,50";
		
		//+-------------------------------
		// Get the number of results
		//+-------------------------------
		
		$DB->query("SELECT COUNT(m.id) as count FROM ibf_members m WHERE $rq");
		
		$count = $DB->fetch_row();
		
		if ($count['count'] < 1)
		{
			if ($IN['showsusp'])
			{
				$ADMIN->error("��� �� ������ ���������������� ������������");
			}
			else
			{
				$ADMIN->error("�� ������� �� ������ ������������ ������ �������. ��������� ����� � ��������� �������.");
			}
		}
		
		$ADMIN->page_title = "���������� ������ ������������";
		
		$ADMIN->page_detail = "���������� ������.";
		
		//+-------------------------------
		
		$pages = $std->build_pagelinks( array( 'TOTAL_POSS'  => $count['count'],
											   'PER_PAGE'    => 50,
											   'CUR_ST_VAL'  => $IN['st'],
											   'L_SINGLE'    => $un_all."������������ ��������",
											   'L_MULTI'     => $un_all."��������� �������",
											   'BASE_URL'    => $SKIN->base_url."&act=mem&showsusp={$IN['showsusp']}&code={$IN['code']}".$page_query,
											 )
									  );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "IP ����� "  , "15%" );
		$SKIN->td_header[] = array( "������"        , "10%" );
		$SKIN->td_header[] = array( "���������"        , "10%" );
		$SKIN->td_header[] = array( "E-mail"        , "15%" );
		$SKIN->td_header[] = array( "�������������"         , "16%" );
		$SKIN->td_header[] = array( "�������� ���"       , "16%" );
		$SKIN->td_header[] = array( "���"          , "17%" );
		
		//+-------------------------------
		
		$title = $IN['showsusp'] == 1 ? '��������������� �������������' : '����������� ������';
		
		$ADMIN->html .= $SKIN->start_table( "{$count['count']} ".$title );
		
		//+-------------------------------
		// Run the query
		//+-------------------------------
		
		$DB->query($query);
		
		while ( $r = $DB->fetch_row() )
		{
			$tban = "";
			$sus_link = "";
			
			if ( $r['temp_ban'] != "" )
			{
				$s_ban = $std->hdl_ban_line( $r['temp_ban'] );
				
				$sus_link = " - <a href='{$SKIN->base_url}&act=mem&code=unsuspend&mid={$r['id']}'>��������������</a>";
				
				$tban = '&nbsp;&nbsp;<span style="font-size:10px">(������������ �� '.$ADMIN->get_date( $s_ban['date_end'], 'LONG') .$sus_link.')</span>';
			}
			
			$ADMIN->html .= $SKIN->add_td_basic( "<img src='html/sys-img/item.gif' border='0' alt='-'>&nbsp;<a style='font-size:12px' title='�������� ������� ����� ������������' href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$r['id']}' target='blank'>{$r['name']}</a> $tban", "left", "pformstrip" );
			$ADMIN->html .= $SKIN->add_td_row( array( "{$r['ip_address']}",
													  $r['g_title'],
													  "<center>".$r['posts']."</center>",
													  "<center>".$r['email']."</center>",
													  "<center><strong><a href='{$SKIN->base_url}&act=mem&code=doform&MEMBER_ID={$r['id']}' title='�������������� ����� ������������'>�������������</a></strong></center>",
													  "<center><a href='{$SKIN->base_url}&act=mem&code=changename&mid={$r['id']}' title='��������� ����� ����� ������������'>�������� ���</a></center>",
													  "<center><a href='{$SKIN->base_url}&act=mem&code=banmember&mid={$r['id']}' title='���������� ������������'>�������������</a></span></center>"
									     	 )      );
			
		}
		
		$ADMIN->html .= $SKIN->add_td_basic($pages, 'right', 'pformstrip');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// DO EDIT FORM
	//
	//+---------------------------------------------------------------------------------
	
	function do_edit_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;
		
		require ROOT_PATH."sources/lib/post_parser.php";
		
		$parser = new post_parser();
		
		if ($IN['MEMBER_ID'] == "")
		{
			$ADMIN->error("���������� ���������� id ������������");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_members WHERE id='".$IN['MEMBER_ID']."'");
		$mem = $DB->fetch_row();
		
		//+-------------------------------
		
		if ($mem['id'] == "")
		{
			$ADMIN->error("���������� ���������� id ������������");
		}
		
		//+-------------------------------
		
		$mem_group = array();
		$show_fixed = FALSE;
		
		$units = array( 0 => array( 'h', '�����' ), 1 => array( 'd', '����' ) );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			// Ensure only root admins can promote to root admin grou...
			// oh screw it, I can't be bothered explaining stuff tonight
			
			if ($INFO['admin_group'] == $r['g_id'])
			{
				if ($MEMBER['mgroup'] != $INFO['admin_group'])
				{
					continue;
				}
			}
			
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		// is this a non root editing a root?
		
		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			if ($mem['mgroup'] == $INFO['admin_group'])
			{
				$show_fixed = TRUE;
			}
		}
		
		//+-------------------------------
		
		$lang_array = array();
		
		$DB->query("SELECT ldir, lname FROM ibf_languages");
		
		while ( $l = $DB->fetch_row() )
		{
			$lang_array[] = array( $l['ldir'], $l['lname'] );
		}
 		
 		//+-------------------------------
 		
 		$DB->query("SELECT uid, sid, sname, default_set, hidden FROM ibf_skins");
 		
 		$skin_array = array();
 		
 		$def_skin = "";
 			
		if ( $DB->get_num_rows() )
		{
			while ( $s = $DB->fetch_row() )
			{
				if ($s['default_set'] == 1)
				{
					$def_skin = $s['sid'];
				}
				
				if ($s['hidden'] == 1)
				{
					$hidden = " *(Hidden)";
				}
				else
				{
					$hidden = "";
				}
				
				$skin_array[] = array( $s['sid'], $s['sname'].$hidden );
			   
			}
		}
			
		//+-------------------------------
		
		if ($INFO['default_language'] == "")
		{
			$INFO['default_language'] = '2';
		}
		
		//-----------------------------------------------
		// Custom profile fields stuff
		//-----------------------------------------------
		
		$custom_output = "";
		$field_data     = array();
		
		$DB->query("SELECT * from ibf_pfields_content WHERE member_id='".$IN['MEMBER_ID']."'");
		
		while ( $content = $DB->fetch_row() )
		{
			foreach($content as $k => $v)
			{
				if ( preg_match( "/^field_(\d+)$/", $k, $match) )
				{
					$field_data[ $match[1] ] = $v;
				}
			}
		}
		
		$DB->query("SELECT * from ibf_pfields_data ORDER BY forder");
		
		while( $row = $DB->fetch_row() )
		{
			$form_element = "";
			
			if ( $row['ftype'] == 'drop' )
			{
				$carray = explode( '|', trim($row['fcontent']) );
				
				$d_content = array();
				
				foreach( $carray as $entry )
				{
					$value = explode( '=', $entry );
					
					$ov = trim($value[0]);
					$td = trim($value[1]);
					
					if ($ov != "" and $td !="")
					{
						$d_content[] = array( $ov, $td );
					}
				}
				
				$form_element = $SKIN->form_dropdown( 'field_'.$row['fid'], $d_content, $field_data[$row['fid']] );
				
			}
			else if ( $row['ftype'] == 'area' )
			{
				$form_element = $SKIN->form_textarea( 'field_'.$row['fid'], $field_data[$row['fid']] );
			}
			else
			{
				$form_element = $SKIN->form_input( 'field_'.$row['fid'], $field_data[$row['fid']] );
			}
			
			$custom_out .= $SKIN->add_td_row( array( "<b>{$row['ftitle']}</b><br>{$row['desc']}" , $form_element ) );
			
		}
		
		//+-------------------------------
		//| Perms masks section
		//+-------------------------------
		
		$perm_masks = array();
		
		
		$DB->query("SELECT * FROM ibf_forum_perms");
		
		while ( $r = $DB->fetch_row() )
		{
			$perm_masks[] = array( $r['perm_id'], $r['perm_name'] );
		}
		
		//+-------------------------------
		
		
		$ADMIN->page_title = "�������������� ������������: ".$mem['name']." (ID: ".$mem['id'].")";
		
		$ADMIN->page_detail = "����� �� ������ ��������������� ������ ������������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doedit'  ),
												  2 => array( 'act'   , 'mem'     ),
												  3 => array( 'mid'   , $mem['id'] ),
												  4 => array( 'curpass', $mem['password'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>IP ����� ��� �����������</b>" ,$mem['ip_address'] )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ���������� ������������?</b>" ,
												  $SKIN->form_checkbox("remove_photo", 0)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��������</b>" ,
												  $SKIN->form_input("warn_level", $mem['warn_level'])
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������</b>" ,
												  $SKIN->form_input("title", $mem['title'])
									     )      );
									     
		if ($show_fixed != TRUE)
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������</b>" ,
													  $SKIN->form_dropdown( "mgroup",
																			$mem_group,
																			$mem['mgroup']
																		  )
											 )      );
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������</b>" ,
													  $SKIN->form_hidden( array( 1 => array( 'mgroup' , $mem['mgroup'] ) ) )."<b>�������� �������������</b> (���������� ��������)",
											 )      );
		}
		
		//+-------------------------------
		// Sort out perm id stuff
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
				
				if ( show != '' )
				{
					document.forms[0].override.checked = true;
				}
				else
				{
					document.forms[0].override.checked = false;
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
		
		$arr = explode( ",",$mem['org_perm_id'] );
		
		$ch_ch = ( $mem['org_perm_id'] ) ? 'checked' : '';
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ����� �������, ����������� ��� ������...</b><br>����� ������� ��������� �����, ��������� ������� <b>Ctrl</b>" ,
												  "<input type='checkbox' name='override' value='1' $ch_ch> <b>������������ ������ ��������...</b><br>".
												  $SKIN->form_multiselect( "permid[]",
																		$perm_masks,
																		$arr, 5, 'onfocus="saveit(this)" onchange="saveit(this)"'
																	  )."<br><input style='margin-top:5px' id='editbutton' type='button' onclick='show_me();' value='�������� ��������� �����'>"
										 ) , "subforum"   );
	
		//-----------------------------------------------------------------------------------------------
		// Mod posts bit
		//-----------------------------------------------------------------------------------------------
		
		$mod_tick = 0;
		$mod_arr  = array();
		
		if ( $mem['mod_posts'] == 1 )
		{
			$mod_tick = 'checked';
		}
		elseif ($mem['mod_posts'] > 0)
		{
			$mod_arr = $std->hdl_ban_line( $mem['mod_posts'] );
			
			$hours  = ceil( ( $mod_arr['date_end'] - time() ) / 3600 );
				
			if ( $hours > 24 and ( ($hours / 24) == ceil($hours / 24) ) )
			{
				$mod_arr['units']    = 'd';
				$mod_arr['timespan'] = $hours / 24;
			}
			else
			{
				$mod_arr['units']    = 'h';
				$mod_arr['timespan'] = $hours;
			}
			
			$mod_extra = "<br /><span style='color:red'>����������� ������ - ���������� ����� �����������</span>";
		}
						     
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� �������� ���� ��������� ����� ������������, ����������� ������?</b><br>��� ������ ����������, ��� ��������� ����� ������������, ����� ��������������, ����� ������� ������������� ����������. ���� ��� �� �����, ������� ������� � �������� ����." ,
												  "<input type='checkbox' name='mod_indef' value='1' $mod_tick> ���������� �������� �����������
												  <br /><b>��� ��</b> ".$SKIN->form_input('mod_timespan', $mod_arr['timespan'], "text", "", '5' ) . '&nbsp;' . $SKIN->form_dropdown('mod_units', $units, $mod_arr['units'] ).$mod_extra
									     )      );
									     
		
		$post_tick = 0;
		$post_arr  = array();
		
		if ( $mem['restrict_post'] == 1 )
		{
			$post_tick = 'checked';
		}
		else if( $mem['restrict_post'] > 0 )
		{
			$post_arr = $std->hdl_ban_line( $mem['restrict_post'] );
			
			$hours  = ceil( ( $post_arr['date_end'] - time() ) / 3600 );
				
			if ( $hours > 24 and ( ($hours / 24) == ceil($hours / 24) ) )
			{
				$post_arr['units']    = 'd';
				$post_arr['timespan'] = $hours / 24;
			}
			else
			{
				$post_arr['units']    = 'h';
				$post_arr['timespan'] = $hours;
			}
			
			$post_extra = "<br /><span style='color:red'>����������� ������ - ���������� ����� �����������</span>";
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������ {$mem['name']} ��������� ��������� �� ������?</b><br>���� ��� �� �����, ������� ������� � �������� ����." ,
												  "<input type='checkbox' name='post_indef' value='1' $post_tick> ��������� ��������
												  <br /><b>��� ��</b> ".$SKIN->form_input('post_timespan', $post_arr['timespan'], "text", "", '5' ) . '&nbsp;' . $SKIN->form_dropdown('post_units', $units, $post_arr['units'] ).$post_extra
									     ) , "subforum"     );
									     
									     
		//-----------------------------------------------------------------------------------------------
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b><br>(�� ���������� ��� ����, ���� �� ������ �������� ������!)" ,
												  $SKIN->form_input("password")
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������" );							     
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����</b>" ,
												  $SKIN->form_dropdown( "language",
																		$lang_array,
												  						$mem['language'] != "" ? $mem['language'] : $INFO['default_language']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����</b>" ,
												  $SKIN->form_dropdown( "skin",
																		$skin_array,
												  						$mem['skin'] != "" ? $mem['skin'] : $def_skin
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ e-mail ����� ����� ������������?</b>" ,
												  $SKIN->form_yes_no("hide_email", $mem['hide_email'] )
									     )      );
									     						     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �� e-mail � ����� ���������� �� PM?</b>" ,
												  $SKIN->form_yes_no("email_pm", $mem['email_pm'] )
									     )      );		
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "���������� ����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail �����</b>" ,
												  $SKIN->form_input("email", $mem['email'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � AIM</b>" ,
												  $SKIN->form_input("aim_name", $mem['aim_name'])
									     )      );							     						     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ICQ</b>" ,
												  $SKIN->form_input("icq_number", $mem['icq_number'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � Yahoo</b>" ,
												  $SKIN->form_input("yahoo", $mem['yahoo'])
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � MSN</b>" ,
												  $SKIN->form_input("msnname", $mem['msnname'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� � Integrity Messenger</b>" ,
												  $SKIN->form_input("integ_msg", $mem['integ_msg'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �����</b>" ,
												  $SKIN->form_input("website", $mem['website'])
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "������ ����������" );
									     							     							     
		//+-------------------------------
		
		$mem['signature'] = $parser->unconvert( $mem['signature'] );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������</b>" ,
												  $SKIN->form_input("avatar", $mem['avatar'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ �������</b>" ,
												  $SKIN->form_input("avatar_size", $mem['avatar_size'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ���������</b>" ,
												  $SKIN->form_input("posts", $mem['posts'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����������</b>" ,
												  $SKIN->form_input("location", $mem['location'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������</b>" ,
												  $SKIN->form_textarea("interests", str_replace( '<br>', "\n",$mem['interests']))
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������</b>" ,
												  $SKIN->form_textarea("signature", $mem['signature'])
									     )      );
		
		
									     							     							     							     
		//+-------------------------------
		
		if ($custom_out != "")
		{
		
			$ADMIN->html .= $SKIN->end_table();
			
			
			$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
			$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
			
			//+-------------------------------+
			
			$ADMIN->html .= $SKIN->start_table( "�������������� ���� �������" );
			
			
			$ADMIN->html .= $custom_out;
											 
		}
								     							     							     
		//+-------------------------------
									     							     							     							     
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}


	//+---------------------------------------------------------------------------------
	
	function do_edit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums, $HTTP_POST_VARS;
		
		$DB->query("SELECT * FROM ibf_members WHERE id='".$IN['mid']."'");
		
		$memb = $DB->fetch_row();
		
		$password = "";
		
		if ($IN['password'] != "")
		{
			$password = ", password='".md5($IN['password'])."'";
		}
		
		require ROOT_PATH."sources/lib/post_parser.php";
		
		$parser = new post_parser();
		
		$IN['signature'] = $parser->convert( array ('TEXT'      => $IN['signature'],
													'SMILIES'   => 0,
													'CODE'      => $INFO['sig_allow_ibc'],
													'HTML'      => $INFO['sig_allow_html'],
													'SIGNATURE' => 1
										   )       );
										   
		if ( $IN['override'] == 1 )
		{
			$permid = implode( ",", $HTTP_POST_VARS['permid'] );
		}
		else
		{
			$permid = "";
		}
		
		$restrict_post = 0;
		$mod_queue     = 0;
		
		if ( $IN['mod_indef'] == 1 )
		{
			$mod_queue = 1;
		}
		elseif ( $IN['mod_timespan'] > 0 )
		{
			$mod_queue = $std->hdl_ban_line( array( 'timespan' => intval($IN['mod_timespan']), 'unit' => $IN['mod_units']  ) );
		}
		
		
		if ( $IN['post_indef'] == 1 )
		{
			$restrict_post = 1;
		}
		elseif ( $IN['post_timespan'] > 0 )
		{
			$restrict_post = $std->hdl_ban_line( array( 'timespan' => intval($IN['post_timespan']), 'unit' => $IN['post_units']  ) );
		}
		
		$db_string = $DB->compile_db_update_string( array (
															'restrict_post'   => $restrict_post,
															'mgroup'       => $IN['mgroup'],
															'title'        => $IN['title'],
															'language'     => $IN['language'],
															'skin'         => $IN['skin'],
															'hide_email'   => $IN['hide_email'],
															'email_pm'     => $IN['email_pm'],
															'email'        => $IN['email'],
															'aim_name'     => $IN['aim_name'],
															'icq_number'   => $IN['icq_number'],
															'yahoo'        => $IN['yahoo'],
															'msnname'      => $IN['msnname'],
															'website'      => $IN['website'],
															'avatar'       => $IN['avatar'],
															'avatar_size'  => $IN['avatar_size'],
															'posts'        => $IN['posts'],
															'location'     => $IN['location'],
															'interests'    => $IN['interests'],
															'signature'    => $IN['signature'],
															'mod_posts'    => $mod_queue,
															'org_perm_id'  => $permid,
															'warn_level'   => $IN['warn_level'],
															'integ_msg'    => $IN['integ_msg'],
												  )       );
												  
		$DB->query("UPDATE ibf_members SET $db_string".$password." WHERE id='".$IN['mid']."'");
		
		//----------------------------------
		// Remove photo?
		//----------------------------------
		
		if ( $IN['remove_photo'] )
		{
			$DB->query("SELECT id FROM ibf_member_extra WHERE id={$IN['mid']}");
		
			if ( $DB->get_num_rows() )
			{
				$DB->query("UPDATE ibf_member_extra SET photo_location='', photo_type='', photo_dimensions='' WHERE id={$IN['mid']}");
			}
			else
			{
				$DB->query("INSERT INTO ibf_member_extra SET photo_location='', photo_type='', photo_dimensions='', id={$IN['mid']}");
			}
			
			foreach( array( 'swf', 'jpg', 'jpeg', 'gif', 'png' ) as $ext )
			{
				if ( @file_exists( $INFO['upload_dir']."/photo-".$IN['mid'].".".$ext ) )
				{
					@unlink( $INFO['upload_dir']."/photo-".$IN['mid'].".".$ext );
				}
			}
		}
		
		//----------------------------------
		// Custom profile field stuff
		//----------------------------------
		
		$custom_fields = array();
		
		$DB->query("SELECT * from ibf_pfields_data");
		
		while ( $row = $DB->fetch_row() )
		{
			$custom_fields[ 'field_'.$row['fid'] ] = str_replace( '<br>', "\n", $IN[ 'field_'.$row['fid'] ] );
		}
		
		if ( count($custom_fields) > 0 )
		{
			// Do we already have an entry in the content table?
			
			$DB->query("SELECT member_id FROM ibf_pfields_content WHERE member_id='".$IN['mid']."'");
			$test = $DB->fetch_row();
			
			if ( $test['member_id'] )
			{
				// We have it, so simply update
				
				$db_string = $DB->compile_db_update_string($custom_fields);
				
				$DB->query("UPDATE ibf_pfields_content SET $db_string WHERE member_id='".$IN['mid']."'");
			}
			else
			{
				$custom_fields['member_id'] = $IN['mid'];
				
				$db_string = $DB->compile_db_insert_string($custom_fields);
				
				$DB->query("INSERT INTO ibf_pfields_content (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
			}
		}
		
		
		if ( USE_MODULES == 1 )
		{
			$this->modules->register_class(&$this);
			
			if ( $IN['password'] )
			{
				if ( md5($IN['password']) != $memb['password'] )
				{
					$this->modules->on_pass_change($IN['mid'], $IN['password']);
				}
			}
			
			if ( $memb['mgroup'] != $IN['mgroup'] )
			{
				$this->modules->on_group_change($IN['mid'], $IN['mgroup']);
			}
			
			if ( $memb['email'] != $IN['email'] )
			{
				$this->modules->on_email_change($IN['mid'], $IN['email']);
			}
			
			if ( $memb['signature'] != $IN['signature'] )
			{
				$this->modules->on_signature_update($memb, $IN['signature']);
			}
			
			$mem_array = array(
							    'title'        => $IN['title'],
								'aim_name'     => $IN['aim_name'],
								'icq_number'   => $IN['icq_number'],
								'yahoo'        => $IN['yahoo'],
								'msnname'      => $IN['msnname'],
								'website'      => $IN['website'],
								'location'     => $IN['location'],
								'interests'    => $IN['interests'],
								'id'		   => $IN['mid']
							  );
			
			$this->modules->on_profile_update($mem_array, $custom_fields);
		}
		
		$ADMIN->save_log("�������������� ������������ '{$memb['name']}'");
		
		$ADMIN->done_screen("������������ ��������������", "���������� ��������������", "act=mem&code=edit" );
		
	}


	
	
		
}


?>