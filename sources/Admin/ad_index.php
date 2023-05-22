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
|   > Admin "welcome" screen functions
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


$idx = new index_page();


class index_page {

	var $mysql_version = "";
	
	function index_page()
	{
		global $DB, $IN, $INFO, $ADMIN, $MEMBER, $SKIN, $std, $ibforums;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//---------------------------------------
		
		$ADMIN->page_title  = "����� ���������� � ������ ����������������� ������";
		$ADMIN->page_detail = "�� ���� ������ ����������, �� ������ ����������� ��� ��������� � ��������� ������ ������.<br><br>������� �� ���� �� ������ � ����� ������ ��� ������ � ��������� �������� � ���� ��������� �����������������. � ������ ����� ���������� ���������� ���������� � ���������������� � �.�.";
		
		
		//---------------------------------
		// Get mySQL & PHP Version
		//---------------------------------
		
		$DB->query("SELECT VERSION() AS version");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$DB->query("SHOW VARIABLES LIKE 'version'");
			$row = $DB->fetch_row();
		}
		
   		$this->mysql_version = $row['version'];
   		
   		$phpv = phpversion();
   		
		//---------------------------------
		// Got reg code?
		//---------------------------------
		
		$reg_html = "";
		$reg_end  = "";
		
		$version_info = "<a href='http://www.invisionboard.com/download.cgi' target='_blank'><img border='0' src='http://www.invisionboard.com/acp/versioncheck/?v={$ibforums->acpversion}' vspace='10'></a><br /><b><a href='http://www.php.net' target='_blank'>PHP</a> ������:</b> $phpv, <b><a href='http://www.mysql.com' target='_blank'>MySQL</a> ������:</b> ".$this->mysql_version;
		
		if ( $INFO['ipb_reg_number'] )
		{
			list( $a, $b, $c, $d, $e ) = explode( '-', $INFO['ipb_reg_number'] );
			
			if ( strlen($e) > 9 )
			{
				$reg_end = "��������������� ��: <span style='color:green'>". $ADMIN->get_date( $e, 'SHORT' )."</span>";
			}
			else
			{
				$reg_end = "����������� �����������";
			}
			
			$reg_html = "<div style='border:1px dotted #555;padding:6px;background-color:#EEF2F7;'>
							<b style='font-size:12px;color:#336699'>������������������ Invision Power Board</b>
							<br />���������� �� ����������� ����� ����� Invision Power Board!
							<br /><br />�������� ���� <a href='http://customer.invisionpower.com' target='_blank'>������ �������</a> ��� �������� ��������, ���������� � ��� ���������.
							<br />$reg_end
						</div>";
		}
		else
		{
			$reg_html = "<div style='border:1px dotted #555;padding:6px;background-color:#EEF2F7;'>
							<b style='font-size:12px;color:#AA0000'>�������������������� Invision Power Board</b>
							<br />��� ����� Invision Power Board �� ����������������.
							<br /><br />��� ����������������? <a href='http://www.invisionboard.com/?whyregister' target='_blank'>������� ����</a> ��� ������������!
							<br />����������� <a href='http://www.invisionboard.com/download.cgi?subc=register' target='_blank'> ����������� ������� ��!</a> �� ��������� ��� � ���� ���� �����!
						</div>";
		}
		
		//---------------------------------
		// Notepad
		//---------------------------------
		
		if ( $IN['save'] == 1 )
		{
			$DB->query("UPDATE ibf_cache_store SET cs_value='".addslashes($_POST['notes'])."' WHERE cs_key='adminnotes'");
		}
		
		$text = "����� �� ������ ��������� ������� ��� ������ ���������������, � ����� ������������ ��� ������ ��� ����, � �������� �������.";
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='adminnotes'");
		
		if ( ! $notes = $DB->fetch_row() )
		{
			$DB->query("INSERT INTO ibf_cache_store (cs_key,cs_value) VALUES ('adminnotes', '$text')");
			
			$notes = array( 'cs_key' => 'adminnotes', 'cs_value' => $text );
		}
		
		$ad_notes = "<form action='{$ADMIN->base_url}&act=index&save=1' method='post'>
					 <textarea name='notes' style='background-color:#F9FFA2;border:1px solid #CCC;width:95%;font-family:verdana;font-size:10px' rows='7' cols='25'>".stripslashes($notes['cs_value'])."</textarea>
				     <div align='center'><input type='submit' value='��������� ������' style='background-color:#F9FFA2;border:1px solid #999;;font-family:verdana;font-size:10px' /></div>
				     </form>";
		
		//---------------------------------
		// Printy-poos
		//---------------------------------
		
		$ADMIN->html .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>
						 <tr>
						  <td width='49%' valign='middle' align='center' style='padding:6px;background-color:#FAFFAF;'>{$ad_notes}</td>
						  <td style='width:10px'>&nbsp;</td>
						  <td width='49%' valign='top' align='left'>{$version_info}<br /><br />{$reg_html}</td>
						 </tr>
						 </table><br />\n";
		
		//---------------------------------
		// Stats
		//---------------------------------
		
		$DB->query("SELECT * FROM ibf_stats");
		
		$row = $DB->fetch_row();
		
		if ($row['TOTAL_REPLIES'] < 0) $row['TOTAL_REPLIES'] = 0;
		if ($row['TOTAL_TOPICS']  < 0) $row['TOTAL_TOPICS']  = 0;
		if ($row['MEM_COUNT']     < 0) $row['MEM_COUNT']     = 0;
		
		$DB->query("SELECT COUNT(*) as reg FROM ibf_validating WHERE lost_pass <> 1");
		$reg = $DB->fetch_row();
		
		if ($reg['reg'] < 1 ) $reg['reg'] = 0;
		
		$DB->query("SELECT COUNT(*) as coppa FROM ibf_validating WHERE coppa_user=1");
		$coppa = $DB->fetch_row();
		
		if ($coppa['coppa'] < 1 ) $coppa['coppa'] = 0;
		
		//-------------------------------------------------
		// Make sure the uploads path is correct
		//-------------------------------------------------
		
		$uploads_size = 0;
		
		if ($dh = opendir( $INFO['upload_dir'] ))
		{
			while ( $file = readdir( $dh ) )
			{
				if ( !preg_match( "/^..?$|^index/i", $file ) )
				{
					$uploads_size += @filesize( $INFO['upload_dir'] . "/" . $file );
				}
			}
			closedir( $dh );
		}
		
		// This piece of code from Jesse's (jesse@jess.on.ca) contribution
		// to the PHP manual @ php.net
		
		if ($uploads_size >= 1048576)
		{
			$uploads_size = round($uploads_size / 1048576 * 100 ) / 100 . " mb";
		}
		else if ($uploads_size >= 1024)
		{
			$uploads_size = round($uploads_size / 1024 * 100 ) / 100 . " k";
		}
		else
		{
			$uploads_size = $uploads_size . " ����";
		}
		
		//+-----------------------------------------------------------
		// INSTALLER PRESENT?
		//+-----------------------------------------------------------
		
		$sm_install = 0;
		$lock_file  = 0;
		
		if ( @file_exists( ROOT_PATH . 'sm_install.php' ) )
		{
			$sm_install = 1;
		}
		
		if ( @file_exists( ROOT_PATH . 'install.lock' ) )
		{
			$lock_file = 1;
		}
		
		if ( $sm_install == 1 ) 
		{
			if ( $lock_file != 1 )
			{
				$ADMIN->html .= "<div style='color:red;border:1px solid red;background:#FFC0C3;padding:10px'>
				                   <span style='font-size:20px;font-weight:bold'>��������������: ����������������� ���� ��������� ������, �� ��� �� �����!</span>
				                   <br /><br /><span style='font-size:14px;'>������� � ������� ���� <b>sm_install.php</b> ���������������!
				                   <br />���������� ����� ����� �� ������� ����������� � ����� ��������� �������� ������ ������ ������.</span></div><br /><br />";
			}
			else
			{
				$ADMIN->html .= "<div style='color:red;border:1px solid red;background:#FFE1E2;padding:10px'>
				                   <span style='font-size:14px;font-weight:bold'>��������������: ���� ��������� ������, �� ��� �� �����</span>
				                   <br /><br /><span style='font-size:10px;'>���� ���� ���� � ������������ ������ �� �������, �� ��-���� ����������� ������� ��� � ������� 
				                   ��� ������ ������������.
				                   <br />������ ������� ���� <b>sm_install.php</b> � �� ������ �� ������� ����� ���������.</span></div><br /><br />";
			}
		}
		
		
		//+-----------------------------------------------------------
		// BOARD OFFLINE?
		//+-----------------------------------------------------------
		
		if ($INFO['board_offline'])
		{
			
			$SKIN->td_header[] = array( "&nbsp;", "100%" );
			
			$ADMIN->html .= $SKIN->start_table( "������ �����������" );
			

			$ADMIN->html .= $SKIN->add_td_row( array( "����� � ��������� ����� ��������<br><br>&raquo; <a href='{$ADMIN->base_url}&act=op&code=board'>�������� �����</a>"
											 )      );
											 
			$ADMIN->html .= $SKIN->end_table();
		
			$ADMIN->html .= $SKIN->add_td_spacer();
		}
			
		//+-----------------------------------------------------------
		// ADMINS USING CP
		//+-----------------------------------------------------------
		
		$SKIN->td_header[] = array( "���"           , "20%" );
		$SKIN->td_header[] = array( "IP �����"     , "20%" );
		$SKIN->td_header[] = array( "����"         , "20%" );
		$SKIN->td_header[] = array( "����. ��������"     , "20%" );
		$SKIN->td_header[] = array( "���������������"       , "20%" );
		
		$ADMIN->html .= $SKIN->start_table( "����������� ����� ��������������" );
		
		$t_time = time() - 60*10;
		
		$DB->query("SELECT MEMBER_NAME, LOCATION, LOG_IN_TIME, RUNNING_TIME, IP_ADDRESS FROM ibf_admin_sessions WHERE RUNNING_TIME > $t_time");
		
		$time_now = time();
		
		$seen_name = array();
		
		while ( $r = $DB->fetch_row() )
		{
			if ( $seen_name[ $r['MEMBER_NAME'] ] == 1 )
			{
				continue;
			}
			else
			{
				$seen_name[ $r['MEMBER_NAME'] ] = 1;
			}
			
			$log_in = $time_now - $r['LOG_IN_TIME'];
			$click  = $time_now - $r['RUNNING_TIME'];
			
			if ( ($log_in / 60) < 1 )
			{
				$log_in = sprintf("%0d", $log_in) . " ������ �����";
			}
			else
			{
				$log_in = sprintf("%0d", ($log_in / 60) ) . " ����� �����";
			}
			
			if ( ($click / 60) < 1 )
			{
				$click = sprintf("%0d", $click) . " ������ �����";
			}
			else
			{
				$click = sprintf("%0d", ($click / 60) ) . " ����� �����";
			}
			
			$ADMIN->html .= $SKIN->add_td_row( array (
														$r['MEMBER_NAME'],
														"<center><a href='javascript:alert(\"�������� �����: ".@gethostbyaddr($r['IP_ADDRESS'])."\")' title='���������� �������� �����'>".$r['IP_ADDRESS']."</a></center>",
														"<center>".$log_in."</center>",
														"<center>".$click."</center>",
														"<center>".$r['LOCATION']."</center>",
											 )       );
		}
		
		
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-----------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_spacer();
		
		//+-----------------------------------------------------------
		
		
		if ($MEMBER['mgroup'] == $INFO['admin_group'])
		{
			//+-----------------------------------------------------------
			// LAST 5 Admin Actions
			//+-----------------------------------------------------------
			
			$SKIN->td_header[] = array( "��� ������������"            , "20%" );
			$SKIN->td_header[] = array( "����������� ��������"        , "40%" );
			$SKIN->td_header[] = array( "���� ��������"         , "20%" );
			$SKIN->td_header[] = array( "IP �����"             , "20%" );
			
			$ADMIN->html .= $SKIN->start_table( "5 ��������� �������� ��������������" );
			
			$DB->query("SELECT m.*, mem.id, mem.name FROM ibf_admin_logs m, ibf_members mem
						WHERE  m.member_id=mem.id ORDER BY m.ctime DESC LIMIT 0, 5");
			
			if ( $DB->get_num_rows() )
			{
				while ( $rowb = $DB->fetch_row() )
				{
					$rowb['ctime'] = $ADMIN->get_date( $rowb['ctime'] );
					
					$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$rowb['name']}</b>",
															  "{$rowb['note']}",
															  "{$rowb['ctime']}",
															  "{$rowb['ip_address']}",
													 )      );
				
				
				}
			}
			else
			{
				$ADMIN->html .= $SKIN->add_td_basic("<center>��� �����������</center>");
			}
			
			$ADMIN->html .= $SKIN->end_table();
			
			//+-----------------------------------------------------------
			
			$ADMIN->html .= $SKIN->add_td_spacer();
		}
		
		//+-----------------------------------------------------------
		// Bots stuff
		//+-----------------------------------------------------------
		
		if ( $INFO['spider_sense'] )
		{
			$SKIN->td_header[] = array( "��������� ���"   , "20%" );
			$SKIN->td_header[] = array( "����"         , "25%" );
			$SKIN->td_header[] = array( "������"        , "20%" );
			$SKIN->td_header[] = array( "������"        , "35%" );
			
			$ADMIN->html .= $SKIN->start_table( "10 ��������� �������� ��������� ����������" );
			
			$DB->query("SELECT * FROM ibf_spider_logs ORDER BY entry_date DESC LIMIT 0,10");
			
			while ( $r = $DB->fetch_row() )
			{
				$ADMIN->html .= $SKIN->add_td_row( array( "<strong>".$INFO[ 'sp_'.$r['bot'] ]."</strong>",
														  $ADMIN->get_date( $r['entry_date'], 'SHORT' ),
														  $r['ip_address'].'&nbsp;',
														  $r['query_string'].'&nbsp;'
												 )      );
			}
			
			$ADMIN->html .= $SKIN->end_table();
			
			$ADMIN->html .= $SKIN->add_td_spacer();
		}
		
		//+-----------------------------------------------------------
		
		
		
		$SKIN->td_header[] = array( "�����������", "25%" );
		$SKIN->td_header[] = array( "��������"     , "25%" );
		$SKIN->td_header[] = array( "�����������", "25%" );
		$SKIN->td_header[] = array( "��������"     , "25%" );
		
		$ADMIN->html .= $SKIN->start_table( "��������� ������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "����� ���-�� ���" , $row['TOTAL_TOPICS'],
												  "���-�� ������� � �����"         , $row['TOTAL_REPLIES']
		 								 )      );
		 								 
		$ADMIN->html .= $SKIN->add_td_row( array( "���-�� �������������" , $row['MEM_COUNT'], "������ ����������� ������", $uploads_size ) );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<a href='{$SKIN->base_url}&act=mem&code=mod'>��������� �������������</a>" , $reg['reg'],
												  "<a href='{$SKIN->base_url}&act=mem&code=mod'>������� COPPA</a> �� '��������� �������������' total", $coppa['coppa'],
									     )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-----------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_spacer();
		
		//+-----------------------------------------------------------
		
		$ADMIN->html .= $SKIN->start_form();
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		
		$ADMIN->html .= $SKIN->start_table( "������� ��������" );
		
		$ADMIN->html .= "
				
					<script language='javascript'>
					<!--
					  function edit_member() {
						
						if (document.forms[1].username.value == \"\") {
							alert(\"�� ������ ������ ��� ������������!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=mem&code=stepone&USER_NAME=' + escape(document.forms[1].username.value);
						}
					  }
					  
					  function new_cat() {
						
						if (document.forms[1].cat_name.value == \"\") {
							alert(\"�� ������ ������ �������� ���������!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=cat&code=new&name=' + escape(document.forms[1].cat_name.value);
						}
					  }
					  
					  function new_forum() {
						
						if (document.forms[1].forum_name.value == \"\") {
							alert(\"�� ������ ������ �������� ������!\");
						} else {
							window.parent.body.location = '{$SKIN->base_url}' + '&act=forum&code=new&name=' + escape(document.forms[1].forum_name.value);
						}
					  }
					//-->
					
					</script>
					<form name='DOIT' action=''>
						
		";
		
		$ADMIN->html .= $SKIN->add_td_row( array( "������������� ������������:",
												  "<input type='text' style='width:100%' id='textinput' name='username' value='������� ��� ������������' onfocus='this.value=\"\"'>",
												  "<input type='button' value='����� ������������' id='button' onClick='edit_member()'>"
										 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "������� ����� ���������:",
												  "<input type='text' style='width:100%' name='cat_name' id='textinput' value='������� ��������' onfocus='this.value=\"\"'>",
												  "<input type='button' value='������� ���������' id='button' onClick='new_cat()'>"
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "������� ����� �����:",
												  "<input type='text' style='width:100%' name='forum_name' id='textinput' value='������� ��������' onfocus='this.value=\"\"'>",
												  "<input type='button' value='������� �����' id='button' onClick='new_forum()'>"
										 )      );
		
		$ADMIN->html .= "</form>";
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
}


?>