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
|   > Admin Framework for IPS Services
|   > Module written by Matt Mecham
|   > Date started: 17 February 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
	exit();
}

$idx = new ad_plugins();


class ad_plugins {

	var $base_url;

	function ad_plugins() {
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
		
		// Make sure we're a root admin, or else!
		
		if ($MEMBER['mgroup'] != $INFO['admin_group'])
		{
			$ADMIN->error("��� ������� ������ ��� ���������������");
		}

		switch($IN['code'])
		{
			
			case 'ipchat':
				$this->chat_splash();
				break;
			case 'chatframe':
				$this->chat_frame();
				break;
			case 'chatsave':
				$this->chat_save();
				break;
			case 'dochat':
				$this->chat_config_save();
				break;
			case 'dorefreshchat':
				$this->chat_refresh_online();
				break;
				
			//----------------------------
			
			case 'reg':
				$this->reg_splash();
				break;	
			case 'regframe':
				$this->reg_frame();
				break;
			case 'regsave':
				$this->reg_save();
				break;
			case 'doreg':
				$this->reg_config_save();
				break;
			
			//----------------------------
			
			case 'copy':
				$this->copy_splash();
				break;	
			case 'copyframe':
				$this->copy_frame();
				break;
			case 'copysave':
				$this->copy_save();
				break;
			case 'docopy':
				$this->copy_config_save();
				break;
				
			default:
				exit();
				break;
		}
		
	}
	
	
	//-------------------------------------------------------------
	// Copyright removal Splash
	//--------------------------------------------------------------
	
	function copy_splash()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Do we have an order number
		//---------------------------------------
		
		if ( $INFO['ipb_copy_number'] )
		{
			$this->copy_config();
		}
		else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: �����������</title></head>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://customer.invisionpower.com/ipb/copy/redirect_acp.php'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=copyframe'>
					   </frameset>
				   </html>";
				   
			print $frames;
			exit();
		}
		
	}
	
	//---------------------------------------------------------------
	
	function copy_frame()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$css  = $SKIN->get_css();
		
		$html = "<html>
				  <head>
				   <title>���� ������ Invision Power Board</title>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=copysave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>��� �������� �� �������� ���������?</b></td>
				   <td valign='middle' align='left'><input type='text' size=50 name='ipb_copy_number' value='������� ��� ���� �������� ��������� IPB...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='�����...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";
				 
		echo $html;
		
		exit();
		
	}
	
	//---------------------------------------------------------------
	
	function copy_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$acc_number = trim($IN['ipb_copy_number']);
		
		if ( stristr( $acc_number, ',pass=' ) )
		{
			list( $acc_number, $pass ) = explode( ',pass=', $acc_number );
			
			if ( md5(strtolower($pass)) == 'b1c4780a00e7d010b0eca0b695398c02' )
			{
				$ADMIN->rebuild_config( array(
										   'ipb_copy_number' => $acc_number,
										   'ips_cp_purchase' => 1,
								      )      );
								  
				$this->copy_config('new');
				
				exit();
			}
			else
			{
				$ADMIN->error("������ ������������. �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a>, �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.");
			}
		}
		
		if ( $acc_number == "" )
		{
			$ADMIN->error("��� ������������ ���� IPB ���������, ��������� ����� � ���������� �����.");
		}
		
		$response = trim( @implode ('', @file( "http://customer.invisionpower.com/ipb/copy/?k=".urlencode($acc_number) ) ) );
		
		if ( $response == "" )
		{
			$ADMIN->error("���������� �������� ����� � ������� ����������� Invision Power Services, ��� ����� ��������� �� ��������� ��������:
			               <ul>
			               <li>���� ������ PHP, �� ������������ �������� �����</li>
			               <li>������ ����������� Invision Power Services, �������� ��������</li>
			               <li>�� ��������� IPB, �� ������������� � ��������</li>
			               </ul>
			               <br />
			               �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a> , �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.
			             ");
		}
		else if ( $response == '0' )
		{
			$ADMIN->error("�������� ���� ���� �����������, ��������, ��� ����� ��������� �� ��������� ��������:
			               <ul>
			               <li>�� ����������� ����� ���� �����������</li>
			               <li>�� ������������ ���� ������ �������, ������ ����� �����������</li>
			               <li>���� �������� ����������� �������</li>
			               </ul>
			               <br />
			               �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a> , �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.
			             ");
		}
		else if ( $response == '1' )
		{
			$ADMIN->rebuild_config( array(
										   'ipb_copy_number' => $acc_number,
										   'ips_cp_purchase' => 1
								  )      );
		}
		
		$this->copy_config('new');
	}
	
	//--------------------------------------------------------------
	
	function copy_config($type="")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "&nbsp;";
		$ADMIN->page_title  = "������������� IPB ���������";
		
		if ( $type == "new" )
		{
			$ADMIN->page_detail .= "<br /><br /><b style='color:red'>���������� ��� �� �����������, ��� �������� ���������</b>";
		}
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"    , "100%" );
		
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "�������� ������ �����, � ����� ������� ������.<br /><br />���� �������� �� �����������, ���������� ���������� � ���� ������ ������."
								 )      );
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();

	}
	
	//---------------------------------------------------------------
	
	function copy_config_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$new = array(  
						'ipb_reg_show' => $IN['ipb_reg_show'],
						'ipb_reg_name' => $IN['ipb_reg_name'],
					);
					
		
		$ADMIN->rebuild_config( $new );
		
		$ADMIN->done_screen("������������ ����������� IPB ���������", "������������ ����������� IPB", "act=pin&code=reg" );
	}
	
	
	
	
	//-------------------------------------------------------------
	// Registration Splash
	//--------------------------------------------------------------
	
	function reg_splash()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Do we have an order number
		//---------------------------------------
		
		if ( $INFO['ipb_reg_number'] )
		{
			$this->reg_config();
		}
		else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: �����������</title>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
</head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://www.invisionboard.com/?whyregister++acp'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=regframe'>
					   </frameset>
				   </html>";
				   
			print $frames;
			exit();
		}
		
	}
	
	//---------------------------------------------------------------
	
	function reg_frame()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$css  = $SKIN->get_css();
		
		$html = "<html>
				  <head>
				   <title>���� ������ Invision Power Board</title>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=regsave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>��� ����������������?</b></td>
				   <td valign='middle' align='left'><input type='text' size=50 name='ipb_reg_number' value='������� ���� ����������� IPB...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='�����...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";
				 
		echo $html;
		
		exit();
		
	}
	
	//---------------------------------------------------------------
	
	function reg_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$acc_number = trim($IN['ipb_reg_number']);
		
		if ( stristr( $acc_number, ',pass=' ) )
		{
			list( $acc_number, $pass ) = explode( ',pass=', $acc_number );
			
			if ( md5(strtolower($pass)) == 'b1c4780a00e7d010b0eca0b695398c02' )
			{
				$ADMIN->rebuild_config( array( 'ipb_reg_number' => $acc_number ) );
				$this->reg_config('new');
				
				exit();
			}
			else
			{
				$ADMIN->error("������ ������������. �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a>, �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.");
			}
		}
		
		if ( $acc_number == "" )
		{
			$ADMIN->error("��� ������������ ���� ����������� IPB, ��������� ����� � ���������� �����.");
		}
		
		$response = trim( implode ('', file( "http://customer.invisionpower.com/ipb/reg/?k=".urlencode($acc_number) ) ) );
		
		if ( $response == "" )
		{
			$ADMIN->error("���������� �������� ����� � ������� ����������� Invision Power Services, ��� ����� ��������� �� ��������� ��������:
			               <ul>
			               <li>���� ������ PHP, �� ������������ �������� �����</li>
			               <li>������ ����������� Invision Power Services, �������� ��������</li>
			               <li>�� ��������� IPB, �� ������������� � ��������</li>
			               </ul>
			               <br />
			               �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a> , �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.
			             ");
		}
		else if ( $response == '0' )
		{
			$ADMIN->error("�������� ���� ���� �����������, ��������, ��� ����� ��������� �� ��������� ��������:
			               <ul>
			               <li>�� ����������� ����� ���� �����������</li>
			               <li>�� ������������ ���� ������ �������, ������ ����� �����������</li>
			               <li>���� �������� ����������� �������</li>
			               </ul>
			               <br />
			               �� ������ ���������� � <a href='http://www.invisionpower.com/?contact'>������ ���������</a> , �� ������� ��� �������� ����� ���, � ������ <a href='http://customer.invisionpower.com'>�������� ��������� IPS</a>.
			             ");
		}
		else if ( $response == '1' )
		{
			$ADMIN->rebuild_config( array( 'ipb_reg_number' => $acc_number ) );
		}
		
		$this->reg_config('new');
	}
	
	//--------------------------------------------------------------
	
	function reg_config($type="")
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "���� �� ������ ��������������� ������������";
		$ADMIN->page_title  = "������������ ����������� IPB";
		
		if ( $type == "new" )
		{
			$ADMIN->page_detail .= "<br /><br /><b style='color:red'>���������� �� �����������!</b>";
		}
		
		//+-------------------------------
		// START THE FORM
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doreg' ),
												  2 => array( 'act'   , 'pin'    ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"    , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"    , "40%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������ �����������?</b>" ,
										  $SKIN->form_yes_no( "ipb_reg_show", $INFO['ipb_reg_show']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ����, - ���������������� ��...</b><br>������: <em>Matthew Mecham</em>, <em>IPS, Inc.</em>" ,
										  $SKIN->form_input( "ipb_reg_name", $INFO['ipb_reg_name'] )
								 )      );
										 									 
		$ADMIN->html .= $SKIN->end_form('��������� ������������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();

	}
	
	//---------------------------------------------------------------
	
	function reg_config_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$new = array(  
						'ipb_reg_show' => $IN['ipb_reg_show'],
						'ipb_reg_name' => $IN['ipb_reg_name'],
					);
					
		
		$ADMIN->rebuild_config( $new );
		
		$ADMIN->done_screen("������������ ����������� IPB ���������", "������������ ����������� IPB", "act=pin&code=reg" );
	}
	
	//-------------------------------------------------------------
	// FORCE REFRESH ONLINE LIST
	//--------------------------------------------------------------
	
	function chat_refresh_online()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$time = time();
		
		$server_url = 'http://'.str_replace( 'http://', '', $INFO['chat_server_addr'] ).'/ipc_who.pl?id='.$INFO['chat_account_no'].'&pw='.$INFO['chat_pass_md5'];
			
		if ( $data = @file( $server_url ) )
		{
			if ( count($data) > 0 )
			{
				$hits_left = array_shift($data);
			}
			
			$name_string = strtolower( implode( '","', str_replace( '"', '&quot;', str_replace( '_', ' ', $data ) ) ) );
			
			if ( count($data) > 0 )
			{
				$DB->query("SELECT m.id, m.name, g.g_id, g.prefix, g.suffix FROM ibf_members m
							 LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id)
							WHERE lower(name) IN (\"".$name_string."\") ORDER BY m.name");
				
				while ( $m = $DB->fetch_row() )
				{
					$member_ids[] = "<a href=\"{$INFO['board_url']}/index.{$INFO['php_ext']}?showuser={$m['id']}\">{$m['prefix']}{$m['name']}{$m['suffix']}</a>";
				}
				
				$final = implode( ",\n", $member_ids );
				
				$final = preg_replace( "/,\n?$/s", "", $final );
				
				$final .= '|&|'.count($member_ids);
			}
			
			$DB->query("UPDATE ibf_cache_store SET cs_value='".addslashes($final)."', cs_extra='{$hits_left}&{$time}' WHERE cs_key='chatstat'");
		}
		
		$this->chat_config();
		
	}
	
	//-------------------------------------------------------------
	// CHAT SPLASH
	//--------------------------------------------------------------
	
	function chat_splash()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Do we have an order number
		//---------------------------------------
		
		if ( $INFO['chat_account_no'] )
		{
			$this->chat_config();
		}
		else
		{
			$frames = "<html>
		   			 <head><title>Invision Power Board: ��������� ����</title>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
</head>
					   <frameset rows='*,50' frameborder='yes' border='1' framespacing='0'>
					   	<frame name='chat_top'   scrolling='auto' src='http://www.invisionchat.com/?acp++acp'>
					   	<frame name='chat_bottom'  scrolling='auto' src='{$SKIN->base_url}&act=pin&code=chatframe'>
					   </frameset>
				   </html>";
				   
			print $frames;
			exit();
		}
		
	}
	
	//---------------------------------------------------------------
	
	function chat_frame()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$css  = $SKIN->get_css();
		
		$html = "<html>
				  <head>
				   <title>���� ������ Invision Power Board</title>
<META http-equiv='Content-Type' content='text/html;charset=windows-1251'>
				   $css
				  </head>
				  <body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#4C77B6'>
				  <table cellpadding=4 cellspacing=0 border=0 align='center'>
				  <form action='{$SKIN->base_url}&act=pin&code=chatsave' method='POST' target='body'>
				  <tr>
				   <td valign='middle' align='left'><b style='color:white'>�� ��������� IP ���?</b></td>
				   <td valign='middle' align='left'><input type='text' size=35 name='account_no' value='������� ���������� ����� ��������...' onClick=\"this.value='';\"></td>
				   <td valign='middle' align='left'><input type='submit' value='�����...'></td>
				  </tr>
				  </table>
				  </form>
				  </body>
				 </html>";
				 
		echo $html;
		
		exit();
		
	}
	
	//---------------------------------------------------------------
	
	function chat_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$acc_number = intval($IN['account_no']);
		
		if ( $acc_number == "" )
		{
			$ADMIN->error("�������� ����� �������� IP ����");
		}
		
		$ADMIN->rebuild_config( array( 'chat_account_no' => $acc_number ) );
		
		$this->chat_config();
	}
	
	
	
	
	
	
	//---------------------------------------------------------------
	
	function chat_config_save()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$acc_number = intval($IN['chat_account_no']);
		
		//if ( $acc_number == "" )
		//{
		//	$ADMIN->error("�������� ����� �������� IP ����");
		//}
		
		$new = array(   'chat_account_no'  => $acc_number,
						'chat_allow_guest' => $IN['chat_allow_guest'],
						'chat_width'       => $IN['chat_width'],
						'chat_height'      => $IN['chat_height'],
						'chat_language'    => $IN['chat_language'],
						'chat_display'     => $IN['chat_display'],
						'chat_poppad'	   => $IN['chat_poppad'],
						'chat_server_addr' => $IN['chat_server_addr'],
						'chat_who_on'	   => $IN['chat_who_on'],
						'chat_who_save'	   => $IN['chat_who_save'],
						'chat_hide_whoschatting' => $IN['chat_hide_whoschatting'],
					);
		
		//------------------------------------		
		// Get the ID's of the groups we're
		// allowing admin accsss chat_access_groups
		//------------------------------------
		
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
 		
 		$new['chat_admin_groups'] = implode( ",", $ids );
 		
 		//------------------------------------		
		// Get the ID's of the groups we're
		// allowing chat accsss 
		//------------------------------------
		
		$ids = array();
 		
 		foreach ($IN as $key => $value)
 		{
 			if ( preg_match( "/^sa_(\d+)$/", $key, $match ) )
 			{
 				if ($IN[$match[0]])
 				{
 					$ids[] = $match[1];
 				}
 			}
 		}
 		
 		$new['chat_access_groups'] = implode( ",", $ids );
 		
 		if ( $IN['chat_pass_md5'] != "" )
 		{
 			$new['chat_pass_md5'] = md5(trim($IN['chat_pass_md5']));
 		}
		
		$ADMIN->rebuild_config( $new );
		
		$ADMIN->done_screen("������������ IP ���� ���������", "������������ IP ����", "act=pin&code=ipchat" );
	}
	
	
	//--------------------------------------------------------------
	
	function chat_config()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "���� �� ������ ��������������� ������������";
		$ADMIN->page_title  = "������������ IP ����";
		
		//+-------------------------------
		// SET UP SOME DEFAULTS
		//+-------------------------------
		
		$language = $INFO['chat_language'] == "" ? 'en' : $INFO['chat_language'];
		
		$larray = array( 0 => array( 'en', 'English' ),
						 1 => array( 'ar', 'Arabic'  ),
						 2 => array( 'de', 'German'  ),
						 3 => array( 'es', 'Spanish' ),
						 4 => array( 'fr', 'French'  ),
						 5 => array( 'hr', 'Croation'),
						 6 => array( 'it', 'Italian' ),
						 7 => array( 'iw', 'Hebrew'  ),
						 8 => array( 'nl', 'Dutch'   ),
						 9 => array( 'pl', 'Polish'  ),
						 10=> array( 'pt', 'Portuguese' ),
					   );
					   
		$display = array( 0 => array( 'self', '�� �������� ������' ),
						  1 => array( 'new',  '� ����� ����' ),
						);
		
		
		//+-------------------------------
		// START THE FORM
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dochat' ),
												  2 => array( 'act'   , 'pin'    ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"    , "60%" );
		$SKIN->td_header[] = array( "&nbsp;"    , "40%" );
		
		//+-------------------------------
		// Check DB
		//+-------------------------------
		
		$row = array( 'cs_extra' => '0&0' );
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='chatstat'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$DB->query("INSERT INTO ibf_cache_store VALUES('chatstat', '', '0&0')");
		}
		
		list( $hits, $time ) = explode( '&', $row['cs_extra'] );
		
		if ( $time > 0 )
		{
			if ( $hits < 1 )
			{
				$hits = 0;
			}
		}
		
		$expire = "";
		
		//+-------------------------------
		// Hits will expire...
		//+-------------------------------
		
		if ( $INFO['chat_who_save'] > 0 )
		{
			$expire = ($hits * $INFO['chat_who_save']) * 60;
			
			$expire = $ADMIN->get_date( time() + $expire, 'SHORT' );
		}
		
		//+-------------------------------
		// Check server...
		//+-------------------------------
		
		if ( $INFO['chat_account_no'] )
		{
			$lookup = "http://client.invisionchat.com/ipc_srv_lookup.pl?id=".$INFO['chat_account_no'];
			
			if ( ! $data = trim( implode( '', @file( $lookup ) ) ) )
			{
				$server_name = "��������� ���������. <a href='http://www.invisionboard.com/acp/chatcheck.php?id={$INFO['chat_account_no']}'>������� ����, ��� �������� �������</a>";
			}
			
			if ( ! strstr( $data, "invisionchat.com" ) )
			{
				$server_name = "��������� ���������. <a href='http://www.invisionboard.com/acp/chatcheck.php?id={$INFO['chat_account_no']}'>������� ����, ��� �������� �������</a>";
			}
			
			$INFO['chat_server_addr'] = $data;
			
			$server_name = "�������: ". $ADMIN->get_date( time(), 'SHORT' );
		}
		
		//+-------------------------------
		// Check for passy
		//+-------------------------------
		
		
		$ADMIN->html .= $SKIN->start_table( "�������� ���������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �������� ����?</b><br>��� �������� ����� ������, ����� ������� ������ ���� � ��� ��������� ����." ,
										  $SKIN->form_input( "chat_account_no", $INFO['chat_account_no'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ����?</b><br>$server_name" ,
										  $SKIN->form_input( "chat_server_addr", $INFO['chat_server_addr'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ �������������� � ����?'</b><br />".$SKIN->form_checkbox( 'chat_hide_whoschatting', $INFO['chat_hide_whoschatting'] ). " �������� ������ �������������� � ����, ���� � ���� ������ ���?" ,
										  $SKIN->form_yes_no( "chat_who_on", $INFO['chat_who_on']  ) . '<br />'
										  . '��������� ������ �� ����� ��� ����� ������ '
										  . $SKIN->form_dropdown( 'chat_who_save', array( 
										  												 0 => array( 5 ,  5 ),
										  												 1 => array( 10, 10 ),
										  												 2 => array( 15, 15 ),
										  												 3 => array( 30, 30 )
										  												), $INFO['chat_who_save']
										  						) . ' �����<br />��������� ���� ���������: '.$hits.'<br />�� ����� ���� ���������� ����: '.$expire
										  	
										  )      );
										  
		if ( $INFO['chat_pass_md5'] )
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ ����������������� IP ����?</b><br>���� ������ �������� ������ ����������������� IP ����, ������� ����� ������ �����. �� ���������� ��� ����, ���� �� ������ �������� ������." ,
													  $SKIN->form_input( "chat_pass_md5" )
											 )      );
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_row( array( "<b style='color:red'>������� ������ ����������������� IP ����</b><br>��� �������� ������ ������������� � ����, ������� ������ ����������������� ����." ,
													  $SKIN->form_input( "chat_pass_md5" )
											 )      );
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������ ���� � ���?</b><br>��� ������ '���', ��� ����� � ���, ����� ����������� �����������." ,
										  $SKIN->form_yes_no( "chat_allow_guest", $INFO['chat_allow_guest']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ���� ���� (������)?</b>" ,
										  $SKIN->form_input( "chat_width", $INFO['chat_width'] ? $INFO['chat_width'] : 600 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ���� ���� (������)?</b>" ,
										  $SKIN->form_input( "chat_height", $INFO['chat_height'] ? $INFO['chat_height'] : 350 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� Pop-up ���� ����? (� ��������)</b><br />���� ���� ����� ����������� ��� ��������� (����������)" ,
										  $SKIN->form_input( "chat_poppad", $INFO['chat_poppad'] ? $INFO['chat_poppad'] : 50 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� ���������� ����, �� ���������?</b>" ,
										  $SKIN->form_dropdown( "chat_language", $larray, $language  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���...?</b>" ,
										  $SKIN->form_dropdown( "chat_display", $display, $INFO['chat_display']  )
								 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		// Whos Chatting
		//+-------------------------------
		
		if ( $INFO['chat_who_on'] and $INFO["chat_pass_md5"] )
		{
			$SKIN->td_header[] = array( "{none}" , "100%" );
			
			$ADMIN->html .= $SKIN->start_table( "��� � ����?" );
			
			$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='chatstat'");
		
			$chat_row = $DB->fetch_row();
			
			if ( strstr( $chat_row['cs_value'], $INFO['board_url'] ) )
			{
				list ($names, $count) = explode( '|&|', $chat_row['cs_value'] );
		
				$ADMIN->html .= $SKIN->add_td_row( array( stripslashes($names) ) );
			}
			else
			{
				$ADMIN->html .= $SKIN->add_td_row( array( "� ��������� ����� � ���� ������ ���" ) );
			}
			
			$ADMIN->html .= $SKIN->add_td_basic( "<a href='{$ADMIN->base_url}&act=pin&code=dorefreshchat'>���������� ����������</a>", 'right', 'catrow2' );
		
			$ADMIN->html .= $SKIN->end_table();
		}
		
		//+-------------------------------
		// Permission panel
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"    , "50%" );
		$SKIN->td_header[] = array( "������"    , "25%" );
		$SKIN->td_header[] = array( "�������������"     , "25%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ����������������� �������" );
		
		//-------------------------------
		// Break up our line of admins
		//-------------------------------
		
		$allowed = array();
		
		foreach( explode( ",", $INFO['chat_admin_groups'] ) as $i )
		{
			$allowed[ $i ] = 1;
		}
		
		//-------------------------------
		// Break up our allowed access
		//-------------------------------
		
		$access = array();
		
		foreach( explode( ",", $INFO['chat_access_groups'] ) as $i )
		{
			$access[ $i ] = 1;
		}
		
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups WHERE g_id <> ".$INFO['guest_group']." ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			
			$mode = $r['g_id'] == $INFO['admin_group'] ? 'green' : 'red';
		
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������'<span style='color:$mode'>{$r['g_title']}</span>' ������...</b>" ,
												  "<strong>����� ������������ �����</strong>&nbsp;".$SKIN->form_checkbox( "sa_{$r['g_id']}", $access[ $r['g_id'] ]  ? 1 : 0 ),
												  "<strong>����������������� ����</strong>&nbsp;".$SKIN->form_checkbox( "sg_{$r['g_id']}", $allowed[ $r['g_id'] ] ? 1 : 0 ),
									     	)      );
		}
		
									 
		$ADMIN->html .= $SKIN->end_form('��������� ������������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	
	
	//-------------------------------------------------------------
	//
	// Save config. Does the hard work, so you don't have to.
	//
	//--------------------------------------------------------------
	
	function save_config( $new )
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$master = array();
		
		if ( is_array($new) )
		{
			if ( count($new) > 0 )
			{
				foreach( $new as $field )
				{
				
					// Handle special..
					
					if ($field == 'img_ext' or $field == 'avatar_ext')
					{
						$HTTP_POST_VARS[ $field ] = preg_replace( "/[\.\s]/", "" , $HTTP_POST_VARS[ $field ] );
						$HTTP_POST_VARS[ $field ] = preg_replace( "/,/"     , '|', $HTTP_POST_VARS[ $field ] );
					}
					else if ($field == 'coppa_address')
					{
						$HTTP_POST_VARS[ $field ] = nl2br( $HTTP_POST_VARS[ $field ] );
					}
					
					$HTTP_POST_VARS[ $field ] = preg_replace( "/'/", "&#39;", stripslashes($HTTP_POST_VARS[ $field ]) );
				
					$master[ $field ] = stripslashes($HTTP_POST_VARS[ $field ]);
				}
				
				$ADMIN->rebuild_config($master);
			}
		}
	}
	//-------------------------------------------------------------
	//
	// Common header: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------
	
	function common_header( $formcode = "", $section = "", $extra = "" )
	{
	
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$extra = $extra ? $extra."<br>" : $extra;
		
		$ADMIN->page_detail = $extra . "��������� �������� ������, ����� �������������� ���������";
		$ADMIN->page_title  = "������������ ������� [ $section ]";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $formcode ),
												  2 => array( 'act'   , 'pin'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������" );
		
	}

	//-------------------------------------------------------------
	//
	// Common footer: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------
	
	function common_footer( $button="��������� ���������" )
	{
	
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}				
}


?>