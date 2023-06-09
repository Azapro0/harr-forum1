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
|   > Warn Log functions
|   > Module written by Matt Mecham
|   > Date started: 4th June 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
	exit();
}


$idx = new ad_warnlogs();


class ad_warnlogs {

	var $base_url;

	function ad_warnlogs() {
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
		
			case 'view':
				$this->view();
				break;
				
			case 'viewcontact':
				$this->view_contact();
				break;
				
			case 'viewnote':
				$this->view_note();
				break;
				
			case 'remove':
				$this->remove();
				break;
				
				
			//-------------------------
			default:
				$this->list_current();
				break;
		}
		
	}
	
	//---------------------------------------------
	// View NOTE in da pop up innit
	//---------------------------------------------
	
	function view_note()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� email ID, ���������� �����");
		}
		
		require( ROOT_PATH.'sources/lib/post_parser.php');
        
        $this->parser  = new post_parser(1);
		
		$id = intval($IN['id']);
		
		$DB->query("SELECT l.*, m.id as a_id, m.name as a_name, p.id as p_id, p.name as p_name
						FROM ibf_warn_logs l
						  LEFT JOIN ibf_members m ON (m.id=l.wlog_mid)
						  LEFT JOIN ibf_members p ON (p.id=l.wlog_addedby)
					    WHERE l.wlog_id=$id");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� email ID, ���������� ����� ($id)");
		}
		
		$SKIN->td_header[] = array( "&nbsp;" , "100%" );
		
		$content = preg_match( "#<content>(.+?)</content>#is", $row['wlog_notes'], $cont );
		
		$ADMIN->html .= $SKIN->start_table( "���������� � ��������� ��������" );
	
		$row['date']  = $ADMIN->get_date( $row['wlog_date'], 'LONG' );
		
		$text   = $this->parser->convert( array(
													'TEXT'    => $cont[1],
													'SMILIES' => 1,
													'CODE'    => 1,
													'HTML'    => 0
										   )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array(
													"<strong>��:</strong> {$row['p_name']}
													<br /><strong>���:</strong> {$row['a_name']}
													<br /><strong>����������:</strong> {$row['date']}
													<hr>
													<br />$text
												    "
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
	
	
	}
	
	
	//---------------------------------------------
	// View contact in da pop up innit
	//---------------------------------------------
	
	function view_contact()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� email ID, ���������� �����");
		}
		
		$id = intval($IN['id']);
		
		$DB->query("SELECT l.*, m.id as a_id, m.name as a_name, p.id as p_id, p.name as p_name
						FROM ibf_warn_logs l
						  LEFT JOIN ibf_members m ON (m.id=l.wlog_mid)
						  LEFT JOIN ibf_members p ON (p.id=l.wlog_addedby)
					    WHERE l.wlog_id=$id");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� email ID, ���������� ����� ($id)");
		}
		
		$type = $row['wlog_contact'] == 'pm' ? "PM" : "EMAIL";
		
		$SKIN->td_header[] = array( "&nbsp;" , "100%" );
		
		$subject = preg_match( "#<subject>(.+?)</subject>#is", $row['wlog_contact_content'], $subj );
		$content = preg_match( "#<content>(.+?)</content>#is", $row['wlog_contact_content'], $cont );
		
		$ADMIN->html .= $SKIN->start_table( $type.": ".$subj[1] );
	
		
		
		$row['date'] = $ADMIN->get_date( $row['wlog_date'], 'LONG' );
		
		$ADMIN->html .= $SKIN->add_td_row( array(
													"<strong>��:</strong> {$row['p_name']}
													<br /><strong>���:</strong> {$row['a_name']}
													<br /><strong>����������:</strong> {$row['date']}
													<br /><strong>���������:</strong> $subj[1]
													<hr>
													<br />$cont[1]
												    "
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
	
	
	}
	
	
	
	
	//---------------------------------------------
	// Remove archived files
	//---------------------------------------------
	
	function view()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$start = $IN['st'] ? $IN['st'] : 0;
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		$ADMIN->page_detail = "�������� ���� ������ �������� ������������";
		$ADMIN->page_title  = "���������� ������ ��������";
	
		if ($IN['search_string'] == "" and $IN['mid'])
		{
			$DB->query("SELECT COUNT(wlog_id) as count FROM ibf_warn_logs WHERE wlog_mid='".$IN['mid']."'");
			$row = $DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&act=warnlog&mid={$IN['mid']}&code=view";
			
			$DB->query("SELECT l.*, m.id as a_id, m.name as a_name, p.id as p_id, p.name as p_name
						FROM ibf_warn_logs l
						  LEFT JOIN ibf_members m ON (m.id=l.wlog_mid)
						  LEFT JOIN ibf_members p ON (p.id=l.wlog_addedby)
					    WHERE l.wlog_mid={$IN['mid']}
					    ORDER BY l.wlog_date DESC LIMIT $start,30");	
		}
		else
		{
			$IN['search_string'] = urldecode($IN['search_string']);
			
			if ( ($IN['search_type'] == 'notes') )
			{
				$dbq = "l.wlog_notes LIKE '%".$IN['search_string']."%'";
			}
			else
			{
				$dbq = "l.wlog_contact_content LIKE '%".$IN['search_string']."%'";
			}
		
			$DB->query("SELECT COUNT(l.wlog_id) as count FROM ibf_warn_logs l WHERE $dbq");
			$row = $DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&act=warnlog&code=view&search_type={$IN['search_type']}&search_string=".urlencode($IN['search_string']);
			
			$DB->query("SELECT l.*, m.id as a_id, m.name as a_name, p.id as p_id, p.name as p_name
						FROM ibf_warn_logs l
						  LEFT JOIN ibf_members m ON (m.id=l.wlog_mid)
						  LEFT JOIN ibf_members p ON (p.id=l.wlog_addedby)
					    WHERE $dbq
					    ORDER BY l.wlog_date DESC LIMIT $start,30");	
		}
		
		$links = $std->build_pagelinks( array( 'TOTAL_POSS'  => $row_count,
											   'PER_PAGE'    => 30,
											   'CUR_ST_VAL'  => $start,
											   'L_SINGLE'    => "������������ ��������",
											   'L_MULTI'     => "�������: ",
											   'BASE_URL'    => $ADMIN->base_url.$query,
											 )
									  );
									  
		$ADMIN->page_detail = "�� ������ ������������� ������ ���������, ������������� ������ ������������";
		$ADMIN->page_title  = "���������� ������ ��������";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "���"        , "5%" );
		$SKIN->td_header[] = array( "��� ������������" , "15%" );
		$SKIN->td_header[] = array( "��������"   , "5%" );
		$SKIN->td_header[] = array( "�������� ���������"       , "10%" );
		$SKIN->td_header[] = array( "����������"        , "10%" );
		$SKIN->td_header[] = array( "������ ���������"     , "10%" );
		$SKIN->td_header[] = array( "����"        , "15%" );
		$SKIN->td_header[] = array( "���������"   , "15%" );
		$SKIN->td_header[] = array( "����������"   , "10%" );
		
		$ADMIN->html .= $SKIN->start_table( "����������� ���� ��������" );
		$ADMIN->html .= $SKIN->add_td_basic($links, 'right', 'pformstrip');
		
		$days = array( 'd' => "����", 'h' => "�����" );
		
		if ( $DB->get_num_rows() )
		{
			while ( $row = $DB->fetch_row() )
			{
			
				$row['wlog_date'] = $ADMIN->get_date( $row['wlog_date'], 'LONG' );
				
				$type = ( $row['wlog_type'] == 'pos' )      ? '<span style="color:green;font-weight:bold">-</span>' : '<span style="color:red;font-weight:bold">+</span>';
				$cont = ( $row['wlog_contact'] !=  'none' ) ? "<center><a href='javascript:pop_win(\"&act=warnlog&code=viewcontact&id={$row['wlog_id']}\",400,400)'>��������</a></center>" : '&nbsp;';
				
				$mod     = preg_match( "#<mod>(.+?)</mod>#is"        , $row['wlog_notes'], $mm );
				$post    = preg_match( "#<post>(.+?)</post>#is"      , $row['wlog_notes'], $pm );
				$susp    = preg_match( "#<susp>(.+?)</susp>#is"      , $row['wlog_notes'], $sm );
				$content = preg_match( "#<content>(.+?)</content>#is", $row['wlog_notes'], $cm );
				
				$content = $cm[1];
				
				$mod  = trim($mm[1]);
				$post = trim($pm[1]);
				$susp = trim($sm[1]);
				
				list($v, $u, $i) = explode(',', $mod);
				
				if ( $i == 1 )
				{
					$mod = 'INDEF';
				}
				else if ( $v == "" )
				{
					$mod = '���';
				}
				else
				{
					$mod = $v.' '.$days[$u];
				}
				
				//----------
				
				list($v, $u, $i) = explode(',', $post);
				
				if ( $i == 1 )
				{
					$post = 'INDEF';
				}
				else if ( $v == "" )
				{
					$post = '���';
				}
				else
				{
					$post = $v.' '.$days[$u];
				}
				
				list($v, $u) = explode(',', $susp);
				
				if ( $v == "" )
				{
					$susp = '���';
				}
				else
				{
					$susp = $v.' '.$days[$u];
				}
				
				//----------
		
				$ADMIN->html .= $SKIN->add_td_row( array(
														  "<center>$type</center>",
														  "<b>{$row['a_name']}</b>",
														  $cont,
														  $mod,
														  $susp,
														  $post,
														  "{$row['wlog_date']}",
														  "<b>{$row['p_name']}</b>",
														  "<center><a href='javascript:pop_win(\"&act=warnlog&code=viewnote&id={$row['wlog_id']}\",400,400)'>��������</a></center>"
												 )      );
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>��� �����������</center>");
		}
		
		$ADMIN->html .= $SKIN->add_td_basic($links, 'right', 'pformstrip');
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
		
	}
	
	//---------------------------------------------
	// Remove archived files
	//---------------------------------------------
	
	function remove()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		if ($IN['mid'] == "")
		{
			$ADMIN->error("�� �� ������� ������������, ��� �������� ��� �����!");
		}
		
		$DB->query("DELETE FROM ibf_warn_logs WHERE wlog_mid={$IN['mid']}");
		
		$ADMIN->save_log("�������� ����� ��������");
		
		$std->boink_it($ADMIN->base_url."&act=warnlog");	
	}
	
	

	
	
	//-------------------------------------------------------------
	// SHOW LOGS
	//-------------------------------------------------------------
	
	function list_current()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
	
		$ADMIN->page_detail = "�� ������ ������������� � ������� ��������, ����������� ����� ����������.<br />����������: ��� �������� �����, ������� �������� �������������, �� �����������";
		$ADMIN->page_title  = "���������� ������ ��������";
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		//+-------------------------------
		// VIEW LAST 5
		//+-------------------------------
		
		$DB->query("SELECT l.*, m.id as a_id, m.name as a_name, p.id as p_id, p.name as p_name
				     FROM ibf_warn_logs l
				     LEFT JOIN ibf_members m ON (m.id=l.wlog_mid)
				     LEFT JOIN ibf_members p ON (p.id=l.wlog_addedby)
				    ORDER BY l.wlog_date DESC LIMIT 0,10");
		
		$SKIN->td_header[] = array( "���"            , "5%" );
		$SKIN->td_header[] = array( "������������"   , "25%" );
		$SKIN->td_header[] = array( "��������?"      , "5%" );
		$SKIN->td_header[] = array( "����"            , "25%" );
		$SKIN->td_header[] = array( "���������"       , "25%" );

		$ADMIN->html .= $SKIN->start_table( "10 ��������� ��������� ���������" );

		if ( $DB->get_num_rows() )
		{
			while ( $row = $DB->fetch_row() )
			{
			
				$row['wlog_date'] = $ADMIN->get_date( $row['wlog_date'], 'LONG' );
				
				$type = ( $row['wlog_type'] == 'pos' ) ? '<span style="color:green;font-weight:bold">-</span>' : '<span style="color:red;font-weight:bold">+</span>';
				$cont = ( $row['wlog_contact'] !=  'none' ) ? "<center><a title='�������� ���������' href='javascript:pop_win(\"&act=warnlog&code=viewcontact&id={$row['wlog_id']}\",400,400)'><img src='{$SKIN->img_url}/acp_check.gif' border='0' alt='X'></a></center>" : '&nbsp;';
				
				$ADMIN->html .= $SKIN->add_td_row( array(
														  "<center>$type</center>",
														  "<b>{$row['a_name']}</b>",
														  $cont,
														  "{$row['wlog_date']}",
														  "<b>{$row['p_name']}</b>",
												 )      );
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>��� �����������</center>");
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "��� ������������"            , "30%" );
		$SKIN->td_header[] = array( "���-�� ���������"           , "20%" );
		$SKIN->td_header[] = array( "�������� ���� �����"     , "20%" );
		$SKIN->td_header[] = array( "�������� ���� �����"   , "30%" );
		
		$ADMIN->html .= $SKIN->start_table( "����������� ���� ��������" );
		
		$DB->query("SELECT l.*, m.name, count(l.wlog_mid) as act_count from ibf_warn_logs l, ibf_members m WHERE m.id=l.wlog_mid GROUP BY l.wlog_mid ORDER BY act_count DESC");
		
		while ( $r = $DB->fetch_row() )
		{
			
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$r['name']}</b>",
													  "<center>{$r['act_count']}</center>",
													  "<center><a href='".$SKIN->base_url."&act=warnlog&code=view&mid={$r['wlog_mid']}'>��������</a></center>",
													  "<center><a href='".$SKIN->base_url."&act=warnlog&code=remove&mid={$r['wlog_mid']}'>�������</a></center>",
											 )      );
		}
			
		
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'view'     ),
												  2 => array( 'act'   , 'warnlog'       ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "����� ����� ��������" );
		
		$form_array = array(
							  0 => array( 'notes'  , '�����������' ),
							  1 => array( 'contact', '������������ �� e-mail/PM'  ),
						   );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������...</b>" ,
										  		  $SKIN->form_input( "search_string")
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ �...</b>" ,
										  		  $SKIN->form_dropdown( "search_type", $form_array)
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("�����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
	
}


?>