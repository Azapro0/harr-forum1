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
|   > Help Control functions
|   > Module written by Matt Mecham
|   > Date started: 2nd April 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
	exit();
}


$idx = new ad_settings();


class ad_settings {

	var $base_url;

	function ad_settings() {
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
			case 'edit':
				$this->show_form('edit');
				break;
			case 'new':
				$this->show_form('new');
				break;
			
			case 'doedit':
				$this->doedit();
				break;
				
			case 'donew':
				$this->doadd();
				break;
				
			case 'remove':
				$this->remove();
				break;
			
			//-------------------------
			default:
				$this->list_files();
				break;
		}
		
	}
	
	//-------------------------------------------------------------
	// HELP FILE FUNCTIONS
	//-------------------------------------------------------------
	
	function doedit()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�� ������ ���������� id ��������!");
		}
		
		$text  = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['text'] ) );
		//$title = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['title'] ) );
		$desc  = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['description'] ) );
		
		$text  = preg_replace( "/\\\/", "&#092;", $text );
		
		$db_string = $DB->compile_db_update_string( array( 'title'       => $IN['title'],
														   'text'        => $text,
														   'description' => $desc,
												  )      );
												  
		$DB->query("UPDATE ibf_faq SET $db_string WHERE id='".$IN['id']."'");
		
		$ADMIN->save_log("�������������� �������� ������");
		
		$std->boink_it($SKIN->base_url."&act=help");
		exit();
			
		
	}
	
	//=====================================================
	
	
	function show_form($type='new')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "���� �� ������ ���������/������������� � ������� ������� ������.";
		$ADMIN->page_title  = "���������� ��������� ������";
		
		//+-------------------------------
		
		if ($type != 'new')
		{
		
			if ($IN['id'] == "")
			{
				$ADMIN->error("�� ������ ���������� id ������� ������!");
			}
		
			//+-------------------------------
		
			$DB->query("SELECT * FROM ibf_faq WHERE id='".$IN['id']."'");
		
			if ( ! $r = $DB->fetch_row() )
			{
				$ADMIN->error("���������� ����� ���� ������ ������ � ���� ������");
			}
		
			//+-------------------------------
			
			$button = '��������������� ������ ������';
			$code   = 'doedit';
		}
		else
		{
			$r = array();
			$button = '������� ������ ������';
			$code   = 'donew';
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $code ),
												  2 => array( 'act'   , 'help'     ),
												  3 => array( 'id'    , $IN['id'] ),
									     )      );
		
		
		
		$SKIN->td_header[] = array( "&nbsp;"  , "20%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "80%" );
		
		$r['text'] = preg_replace( "/<br>/i", "\n", stripslashes($r['text']) );
 		
 		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( $button );
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "��������� ������� ������",
												  $SKIN->form_input('title'  , stripslashes($r['title']) ),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "�������� ������� ������",
												  $SKIN->form_textarea('description', stripslashes($r['description']) ),
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "����� ������",
												  $SKIN->form_textarea('text', $r['text'], "60", "10" ),
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//=====================================================
	
	function remove()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�� ������ ���������� id ������� ������!");
		}
		
		$DB->query("DELETE FROM ibf_faq WHERE id='".$IN['id']."'");
		
		$ADMIN->save_log("�������� ������� ������");
		
		$std->boink_it($SKIN->base_url."&act=help");
		exit();
			
		
	}
	
	//=====================================================
	
	function doadd()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['title'] == "")
		{
			$ADMIN->error("���������� ������ ��������� ������� ������!");
		}
		
		
		
		$text  = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['text'] ) );
		$title = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['title'] ) );
		$desc  = preg_replace( "/\n/", "<br>", stripslashes($HTTP_POST_VARS['description'] ) );
		
		$text  = preg_replace( "/\\\/", "&#092;", $text );
		
		$db_string = $DB->compile_db_insert_string( array( 'title'       => $title,
														   'text'        => $text,
														   'description' => $desc,
												  )      );
												  
		$DB->query("INSERT INTO ibf_faq (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		$ADMIN->save_log("�������� ������� ������");
		
		$std->boink_it($SKIN->base_url."&act=help");
		exit();
			
		
	}
	
	//=====================================================
	
	function list_files()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "���� �� ������ ���������/������������� � ������� ������� ������.";
		$ADMIN->page_title  = "���������� ��������� ������";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "���������"  , "50%" );
		$SKIN->td_header[] = array( "�������������"   , "30%" );
		$SKIN->td_header[] = array( "�������" , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������ ������� ������" );
		
		$DB->query("SELECT * from ibf_faq ORDER BY id ASC");
		
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".stripslashes($r['title'])."</b><br>".stripslashes($r['description']),
														  "<center><a href='".$SKIN->base_url."&act=help&code=edit&id={$r['id']}'>�������������</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=help&code=remove&id={$r['id']}'>�������</a></center>",
												 )      );
												   
			
				
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_basic("<a href='".$SKIN->base_url."&act=help&code=new'>������� ����� ������ ������</a>", "center", "title" );
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
}


?>