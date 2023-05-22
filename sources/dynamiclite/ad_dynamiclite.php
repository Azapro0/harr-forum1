<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2 (Click Site)
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
|   > Admin Setting
|   > Module written by Matt Mecham
|   > Date started: 1st July 03
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


$idx = new ad_settings();


class ad_settings {

	var $base_url;

	function ad_settings() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}

		switch($IN['code'])
		{
			case 'settings':
				$this->settings();
				break;
			case 'dosettings':
				$this->save_config( array( 'csite_on','csite_article_forum','csite_article_max','csite_article_recent_on','csite_article_recent_max','csite_article_chars','csite_discuss_max','csite_discuss_on',
	 									   'csite_discuss_len', 'csite_article_len', 'csite_pm_show', 'csite_stats_show', 'csite_search_show', 'csite_poll_show', 'csite_poll_url', 'csite_online_show','csite_skinchange_show',
	 									   'csite_nav_show', 'csite_title', 'csite_article_date','csite_fav_show' ) );
				break;
				
			
			default:
				$this->settings();
				break;
		}
		
	}
	
	//-------------------------------------------------------------
	// SETTINGS: Do Settings for the clickywickydickytavi
	//-------------------------------------------------------------- 
	 
	
	function settings()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dosettings', '��������� IPDynamic Lite', '����� �� ������ ��������������� ���������');
		
		// Get links
		
		$def_nav = "{board_url} [�����]\n{board_url}act=Search&amp;CODE=getactive [�������� ����]\n{board_url}act=Stats [10 ������� �������]\n{board_url}act=Stats&amp;CODE=leaders [�������������]";
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key IN ('csite_nav_contents', 'csite_fav_contents')");
		
		$links = array( 'csite_nav_contents' => $def_nav, 'csite_fav_contents' => "" );
		
		while ( $row = $DB->fetch_row() )
		{
			$links[ $row['cs_key'] ] = $row['cs_value'];
		}
		
		// Save default informidificationally?
		
		if ( $INFO['csite_configured'] != 1 )
		{
			$DB->query("REPLACE INTO ibf_cache_store SET cs_key='csite_nav_contents', cs_value=\"$def_nav\"");
			$DB->query("REPLACE INTO ibf_cache_store SET cs_key='csite_fav_contents', cs_value=''");
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������ IPDynamic Lite?</b>" ,
										  $SKIN->form_yes_no( "csite_on", $INFO['csite_on'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������?</b><br />��� ��������� ��� ����� &lt;title&gt; �������� �������" ,
										  $SKIN->form_input( "csite_title", str_replace( "'", "&#39;", $INFO['csite_title'] ) )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������� � �������� ��������, ���� �� �������...</b><br>���������� ������ id �������, ����� �������." ,
										  $SKIN->form_input( "csite_article_forum", $INFO['csite_article_forum'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������, ��� ����������� �� ������� ��������</b>" ,
										  $SKIN->form_simple_input( "csite_article_max", $INFO['csite_article_max'] ? $INFO['csite_article_max'] : 15)
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� �������?</b><br />�������, �� ���������� �� �������� ��������, ����� ���������� � ���� ������.",
										          $SKIN->form_yes_no( "csite_article_recent_on", $INFO['csite_article_recent_on'] )
										          ."<br />����. ���-�� ������������ ��������� ��������: ".$SKIN->form_simple_input( "csite_article_recent_max", $INFO['csite_article_recent_max'] ? $INFO['csite_article_recent_max'] : 5)
								 				  ."<br />����. ����� ��������� ���: ".$SKIN->form_simple_input( "csite_article_len", $INFO['csite_article_len'] ? $INFO['csite_article_len'] : 30)
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ���� ��� ��������?</b><br>������� ������� �� <a href='http://www.php.net/date'>PHP date." ,
										  $SKIN->form_input( "csite_article_date", $INFO['csite_article_date'] ? $INFO['csite_article_date'] : 'j.m.Y - H:i' )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� ������������ �������� � ��������</b><br>��� ������� ����������� ������ �������, �������� ��� ���� ������, ���� ������� 0.<br />��� ���������, ����� ���������� �������� � ������ ������ � ������ �������." ,
										  $SKIN->form_input( "csite_article_chars", $INFO['csite_article_chars'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ����������?</b><br />� ������, ����� ������������ ��� ����, ������� ����� ������.",
										          $SKIN->form_yes_no( "csite_discuss_on", $INFO['csite_discuss_on'] )
										          ."<br />����. ���-�� ������������ ��������� ����������: ".$SKIN->form_simple_input( "csite_discuss_max", $INFO['csite_discuss_max'] ? $INFO['csite_discuss_max'] : 10)
										          ."<br />����. ����� ��������� ���: ".$SKIN->form_simple_input( "csite_discuss_len", $INFO['csite_discuss_len'] ? $INFO['csite_discuss_len'] : 30)
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������������� ����?</b>" ,
										  $SKIN->form_yes_no( "csite_pm_show", $INFO['csite_pm_show'] )
								 )      );
								 
		/*$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� ������?</b><br />���-�� ���������, ���, �������������" ,
										  $SKIN->form_yes_no( "csite_stats_show", $INFO['csite_stats_show'] )
								 )      );*/
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� � �������?</b><br />" ,
										  $SKIN->form_yes_no( "csite_online_show", $INFO['csite_online_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� ������?</b>" ,
										  $SKIN->form_yes_no( "csite_search_show", $INFO['csite_search_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���������� ���� ��� ����� ������?</b><br />���������� ������ ������ �����" ,
										  $SKIN->form_yes_no( "csite_skinchange_show", $INFO['csite_skinchange_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �����?</b>" ,
										  $SKIN->form_yes_no( "csite_poll_show", $INFO['csite_poll_show'] )
										   ."<br />������� ������ ���� � �������: ".$SKIN->form_input( "csite_poll_url", $INFO['csite_poll_url'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� ��������� �� �����?</b>" ,
										  $SKIN->form_yes_no( "csite_nav_show", $INFO['csite_nav_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<span style='vertical-align:top'><b>������ ��� ���� ��������� �� �����</b><br>�� ����� ������ �� ������, � ��������� �������<br>http://www.mysite.com [��� ����]<br><br>{board_url} ����� ����������������� � ������ ������ ������</span>" ,
										  $SKIN->form_textarea( "csite_nav_contents", preg_replace( "/&(middot|quot|copy|amp)/", "&amp;\\1", $links['csite_nav_contents'] ), 70, 20 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���� ������������� �����?</b>" ,
										  $SKIN->form_yes_no( "csite_fav_show", $INFO['csite_fav_show'] ) 
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<span style='vertical-align:top'><b>���������� ���� ������������� �����</b><br>������������� HTML ��������</span>" ,
										  $SKIN->form_textarea( "csite_fav_contents", preg_replace( "/&(middot|quot|copy|amp)/", "&amp;\\1", $links['csite_fav_contents'] ), 70, 20 )
								 )      );
								 
		$this->common_footer();
	
	
	}
	
	
	
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
					$master[ $field ] = $std->txt_stripslashes($HTTP_POST_VARS[ $field ]);
				}
				
				$master['csite_title']        = str_replace( "'", "&#39;", $master['csite_title'] );
				$master['csite_article_date'] = str_replace( "'", "&#39;", $master['csite_article_date'] );
				
				$master['csite_configured'] = 1;
				
				$ADMIN->rebuild_config($master);
			}
		}
		
		$nav_contents = preg_replace( "/&amp;(middot|quot|copy|\#|amp)/", "&\\1", $std->txt_stripslashes($HTTP_POST_VARS['csite_nav_contents']) );
		$fav_contents = preg_replace( "/&amp;(middot|quot|copy|\#|amp)/", "&\\1", $std->txt_stripslashes($HTTP_POST_VARS['csite_fav_contents']) );
		
		$DB->query("UPDATE ibf_cache_store SET cs_value='".str_replace( "'", '&#39;', $nav_contents )."' WHERE cs_key='csite_nav_contents'");
		$DB->query("UPDATE ibf_cache_store SET cs_value='".str_replace( "'", '&#39;', $fav_contents )."' WHERE cs_key='csite_fav_contents'");
		
		$ADMIN->save_log("���������� ������������ IPDynamic Lite, Back Up ������");
		
		$ADMIN->done_screen("������������ IPDynamic Lite ���������", "��������� IPDynamic Lite", "act=csite" );
		
		
		
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
		
		$ADMIN->page_detail = $extra . "��������� ��������� �������� ���� ������, ����� ����������� ���������";
		$ADMIN->page_title  = "��������� ������ ($section)";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $formcode ),
												  2 => array( 'act'   , 'csite'      ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
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