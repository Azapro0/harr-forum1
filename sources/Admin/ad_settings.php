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
|   > Admin Setting functions
|   > Module written by Matt Mecham
|   > Date started: 20th March 2002
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

	function ad_settings() 
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		//---------------------------------------
		
		$DB->query("SELECT VERSION() AS version");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$DB->query("SHOW VARIABLES LIKE 'version'");
			$row = $DB->fetch_row();
		}
		
		$this->true_version = $row['version'];
		
		$no_array = explode( '.', preg_replace( "/^(.+?)[-_]?/", "\\1", $row['version']) );
		
		$one   = (!isset($no_array) || !isset($no_array[0])) ? 3  : $no_array[0];
		$two   = (!isset($no_array[1]))                      ? 21 : $no_array[1];
		$three = (!isset($no_array[2]))                      ? 0  : $no_array[2];
		
   		$this->mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));

		switch($IN['code'])
		{
			//----PORTAL---------------
            case 'portal':
                $this->portal();
                break;
            case 'doportal':
                $this->save_config( array( 'portal_poll', 'portal_newsforum', 'portal_newsposts', 'portal_googlebar', 'portal_latestposts', 'portal_num_latestposts', 
                                           'portal_navigation', 'portal_activemembers', 'portal_post_stats', 'portal_birthdays', 'portal_latestposts_big', 'portal_num_latestposts_big', 
                                           'portal_top_posters', 'portal_num_topposters', 'portal_new_members', 'portal_num_newmembers', 'portal_top_forums', 'portal_num_top_forums',
                                           'portal_newsforum_expert', 'portal_loginbox', 'portal_newposts', 'portal_num_newposts', 'portal_old_news', 'portal_num_old_news', 
                                           'portal_member_moment', 'portal_welcomepanel', 'portal_tease_news', 'portal_tease_length','portal_calendar_events') );
                break;
            //-------------------------
            
			case 'fulltext':
				$this->fulltext();
				break;
				
			case 'dofulltext':
				$this->do_fulltext();
				break;
				
			case 'phpinfo':
				phpinfo();
				exit;
				
			case 'glines':
				$this->guidelines();
				break;
			case 'doglines':
				$this->do_guidelines();
				break;
				
			case 'cookie':
				$this->cookie();
				break;
			case 'docookie':
				$this->save_config( array( 'cookie_domain', 'cookie_id', 'cookie_path' ) );
				break;
				
			case 'warn':
				$this->warn();
				break;
			case 'dowarn':
				$HTTP_POST_VARS['warn_protected'] = ','. @implode( ",", $HTTP_POST_VARS['groups'] ) . ',';
				$this->save_config( array( 	'warn_show_rating', "warn_past_max", 'warn_show_own', 'warn_min', 'warn_protected', 'warn_mod_day', 'warn_gmod_day', "warn_gmod_ban","warn_gmod_modq","warn_gmod_post","warn_mod_post","warn_mod_modq","warn_mod_ban",'warn_max',"warn_on" ) );
				break;

			case 'secure':
				$this->secure();
				break;
			case 'dosecure':
				$this->save_config( array ( 'strip_space_chr', 'validate_day_prune', 'bot_antispam', 'use_ttf', 'gd_width', 'gd_height',"gd_font",  'disable_admin_anon', 'disable_online_ip', 'disable_reportpost', 'allow_dynamic_img','session_expiration','match_browser','allow_dup_email','allow_images', 'force_login', 'no_reg',
											'allow_flash','new_reg_notify','use_mail_form','flood_control', 'allow_online_list', 'reg_auth_type', 'reg_chars' ) );
				break;
			//-------------------------
			case 'post':
				$this->post();
				break;
			case 'dopost':
				$this->save_config( array ( 'post_order_column', 'post_order_sort','poll_disable_noreply', 'siu_thumb', 'siu_width', 'siu_height', 'startpoll_cutoff', 'post_wordwrap', 'allow_result_view', 'max_poll_choices', 'poll_tags', 'guest_name_pre', 'guest_name_suf', 'max_w_flash', 'max_h_flash', 'hot_topic', 'display_max_topics','display_max_posts','max_emos','max_images','emo_per_row', 'etfilter_punct', 'etfilter_shout',
											'strip_quotes', 'max_post_length','show_img_upload','pre_polls','pre_moved','pre_pinned','img_ext' ) );
				break;
			//-------------------------
			case 'avatars':
				$this->avatars();
				break;
			case 'doavatars':
				$this->save_config( array ( 'av_gal_cols' , "disable_ipbsize", "photo_ext", 'subs_autoprune', 'topicpage_contents', 'postpage_contents', 'allow_skins', 'max_sig_length', 'sig_allow_ibc', 'sig_allow_html','avatar_ext','avatar_url','avup_size_max','avatars_on','avatar_dims','avatar_def', 'max_location_length', 'max_interest_length', 'post_titlechange', 'guests_ava', 'guests_img', 'guests_sig', 'latest_show' , 'latest_amount' ) );
				break;
			//-------------------------
			case 'dates':
				$this->dates();
				break;
			case 'dodates':
				$this->save_config( array ( 'time_offset','clock_short','clock_joined','clock_long', 'time_adjust' ) );
				break;
			//-------------------------
			
			case 'calendar':
				$this->calendar();
				break;
			case 'docalendar':
				$this->save_config( array ( 'autohide_bday', 'autohide_calendar', 'show_birthdays', 'show_bday_calendar', 'show_calendar','calendar_limit', 'year_limit', 'start_year' ) );
				break;
			//-------------------------
			
			case 'cpu':
				$this->cpu();
				break;
			case 'docpu':
				$this->save_config( array ( 'custom_profile_topic', 'min_search_word', 'short_forum_jump', 'no_au_forum', 'no_au_topic', 'au_cutoff', 'load_limit','show_active','show_birthdays','show_totals','allow_search', 'search_post_cut', 'show_user_posted', 'nocache' ) );
				break;
			//-------------------------
			case 'email':
				$this->email();
				break;
			case 'doemail':
				$this->save_config( array ( 'email_in', 'email_out', 'mail_method', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass' ) );
				break;
			//-------------------------
			case 'url':
				$this->url();
				break;
			case 'dourl':
				$this->save_config( array ( 'number_format', 'html_dir','safe_mode_skins', 'board_name','board_url','home_name','home_url', 'disable_gzip',
										    'html_url','upload_url', 'upload_dir', 'print_headers', 'header_redirect', 'debug_level', 'sql_debug' ) );
				break;
			//-------------------------
			case 'pm':
				$this->pm();
				break;
			case 'dopm':
				$this->save_config( array ( 'show_max_msg_list', 'msg_allow_code', 'msg_allow_html' ) );
				break;
			//-------------------------
			case 'news':
				$this->news();
				break;
			case 'donews':
				$this->save_config( array ( 'news_forum_id', 'index_news_link' ) );
				break;
			//-------------------------
			case 'coppa':
				$this->coppa();
				break;
			case 'docoppa':
				$this->save_config( array ( 'use_coppa', 'coppa_fax', 'coppa_address' ) );
				break;
			//-------------------------
			case 'board':
				$this->board();
				break;
			case 'doboard':
				$this->save_config( array ( 'board_offline', 'offline_msg' ) );
				break;
			//-------------------------
			case 'spider':
				$this->spider();
				break;
			case 'dospider':
				$this->save_config( array (  'spider_suit', "spider_sense","spider_visit","spider_group","spider_active",'sp_google' ,'sp_inktomi','sp_lycos'  ,'sp_jeeves','sp_wuseek', 'sp_Archive_org', 'spider_anon' ) );
				break;
			//-------------------------
			case 'bw':
				$this->badword();
				break;
			case 'bw_add':
				$this->add_badword();
				break;
			case 'bw_remove':
				$this->remove_badword();
				break;
			case 'bw_edit':
				$this->edit_badword();
				break;
			case 'bw_doedit':
				$this->doedit_badword();
				break;
			//-------------------------
			case 'emo':
				$this->emoticons();
				break;
			case 'emo_add':
				$this->add_emoticons();
				break;
			case 'emo_remove':
				$this->remove_emoticons();
				break;
			case 'emo_edit':
				$this->edit_emoticons();
				break;
			case 'emo_doedit':
				$this->doedit_emoticons();
				break;
			case 'emo_upload':
				$this->upload_emoticon();			
			//-------------------------
			case 'count':
				$this->countstats();
				break;
			case 'docount':
				$this->docount();
				break;
			default:
				$this->cookie();
				break;
		}
		
	}
	
	
	
	//-------------------------------------------------------------
	// Full Text options page
	//--------------------------------------------------------------
	
	function fulltext()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
	
		//---------------------------------------
		// Get the mySQL version.
		// Adapted from phpMyAdmin
		//---------------------------------------
		
		$DB->query("SELECT VERSION() AS version");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$DB->query("SHOW VARIABLES LIKE 'version'");
			$row = $DB->fetch_row();
		}
		
		$this->true_version = $row['version'];
		
		$no_array = explode( '.', preg_replace( "/^(.+?)[-_]?/", "\\1", $row['version']) );
		
		$one   = (!isset($no_array) || !isset($no_array[0])) ? 3  : $no_array[0];
		$two   = (!isset($no_array[1]))                      ? 21 : $no_array[1];
		$three = (!isset($no_array[2]))                      ? 0  : $no_array[2];
		
   		$this->mysql_version = (int)sprintf('%d%02d%02d', $one, $two, intval($three));
   		
   		$this->common_header('dofulltext', '��������� ��������������� ������', '�� ������ ��������������� ������������, ����');
   		
   		if ( $this->mysql_version < 32323 )
   		{
   			$ADMIN->html .= $SKIN->add_td_basic("<strong>���� MySQL �� � ��������� ������������ �������������� �����</strong><br />���������� � ������ �������, ��� ���������� MySQL.");
											
   			$ADMIN->html .= $SKIN->end_form($button);
										 
			$ADMIN->html .= $SKIN->end_table();
		
			$ADMIN->output();
			
			exit();
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<strong>��� ����� �������������� �����?</strong><br />�������������� �����, �������� ����� ����������� � ������� ��������
												����������� ������ ������� �������.");
												
			//-------------------------------------------
			// Do we already have full text enabled?
			//-------------------------------------------
			
			$DB->query("SHOW CREATE TABLE ibf_posts");
			
			$tbl_info = $DB->fetch_row();
			
			if ( preg_match( "/FULLTEXT KEY/i", $tbl_info['Create Table'] ) )
			{
				$ADMIN->html .= $SKIN->add_td_basic("<b>�������������� ���������� ��� ����������</b><input type='hidden' name='ftexist' value='1' />");
									 			 
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������� ������?</b>" ,
												  $SKIN->form_dropdown( "search_sql_method", array( 0 => array( 'ftext', '��������������' ), 1 => array( 'man', '������' ) ), $INFO['search_sql_method'] )
										 )      );
	
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������ �� ���������?</b><br>�������� ������ ��� ��������� ��������������� ������" ,
												  $SKIN->form_dropdown( "search_default_method", array( 0 => array( 'simple', '������� �����' ), 1 => array( 'adv', '����������� �����' ) ), $INFO['search_default_method']  )
										 )      );
			}
			else
			{
				$ADMIN->html .= $SKIN->add_td_basic( "<b>����� ���������� ���� �������, �� ������ ������� �������������� ����������. ������ ������� �� ������ ����, ��� ������ ����������</b>" .
											       "<input type='hidden' name='setup' value='1'>");
			}
			
			$this->common_footer();
		}						 
	}
	
	//-------------------------------------------------------------
	// Save full text options
	//--------------------------------------------------------------
	
	function do_fulltext()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ( $IN['ftexist'] == 1)
		{
			$master = array();
			$master['search_sql_method']      = $IN['search_sql_method'];
			$master['search_default_method']  = $IN['search_default_method'];
			
			$ADMIN->rebuild_config($master);
		}
		else
		{
			// They don't.
			// Check for correct version and if need be, attempt to create the indexes...
			
			if ( $this->mysql_version >= 32323 )
			{
				// How many posts do we have?
				
				$DB->query("SELECT COUNT(*) as cnt from ibf_posts");
				
				$result = $DB->fetch_row();
				
				// If over 15,000 posts...
				
				if ( $result['cnt'] > 15000 )
				{
					// Explain how, why and what to do..
					
					$ADMIN->page_detail = "";
					$ADMIN->page_title  = "����������� ����������";
		
					$ADMIN->html .= $SKIN->add_td_basic( $this->return_sql_no_no_cant_do_it_sorry_text(), 'left', 'faker' );
					
					$ADMIN->output();
				}
				else
				{
					// Index away!
					
					$DB->query("alter table ibf_topics add fulltext(title)");
					
					$DB->query("alter table ibf_posts add fulltext(post)");
					
				}
			}
			else
			{
				$ADMIN->error("���� ������ MySQL �� � ��������� ������������� ��������������� ������");
			}
		}
		
		$ADMIN->save_log("���������� ����� ��������������� ������");
		
		$ADMIN->done_screen("��������� ��������������� ������ ���������", "��������� ��������������� ������", "act=op&code=fulltext" );
		
	}
	
	//-------------------------------------------------------------
	// WARNY PORNY!
	//--------------------------------------------------------------
	
	function warn()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dowarn', '��������� �������� �������������', '�� ������ ��������������� ������������, ����.' );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		$protected = explode(',', trim($INFO['warn_protected']) );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������� ��������?</b>" ,
										  $SKIN->form_yes_no( "warn_on", $INFO['warn_on']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��������...</b><br />��� ���������� ��������� �������� � ������� �������������." ,
										 '����������� '.$SKIN->form_simple_input( 'warn_min' , $INFO['warn_min']  == "" ? 0 : $INFO['warn_min'] ) .'  '.
										 '������������ '.$SKIN->form_simple_input( 'warn_max' , $INFO['warn_max']  == "" ? 10 : $INFO['warn_max'] ) .
										 "<br>����������� ������������� ������������� �����. ��� ������������� �������������� �����, �� ������������� ������������� ������������ �����, �.�. � ����������� ����� �� ������������ ������������� �������.<br>������������, ��� ����������� ������������� �������� ������� �������."
								 )      );	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������...</b><br>������, � ������� ���������� �������� �������<br />����� ������� ����� ��������� �����, ��������� ������� <b>Ctrl</b>." ,
												  $SKIN->form_multiselect( "groups[]", $mem_group, $protected )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ������������ ����� ����� ������ <em>����</em> ������� ��������?</b>" ,
										  $SKIN->form_yes_no( "warn_show_own", $INFO['warn_show_own']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������� ������ �������� ��� ���������� ��������� ��� ��������?</b>" ,
										  $SKIN->form_yes_no( "warn_past_max", $INFO['warn_past_max']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ���������� �����������?</b>" ,
										  $SKIN->form_dropdown( 'warn_show_rating', array( 0 => array( 0, '��� ��������������: ����������� � ���� ������������ �����' ), 1 => array( 1, '��� ��������: ����������� � ���� < ��� | ������� | ���� > ������' ) ), $INFO['warn_show_rating']  )
								 )      );				 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '���������� ��� ����������� ������', 'left', 'catrow2' );
		
		//----------------------------------------------------------------------------------------------------------- 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� ������, ����� ����� �������� ������������?</b><br />��������� ������ � ��� �����������, ������� ��������� ������������ ������� ��������<br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_mod_ban", $INFO['warn_mod_ban']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� ������������� ��� ������������ ��������������� �������� ���� ��� ���������, ����� ����� �������� ������������?</b><br />��������� ������ � ��� �����������, ������� ��������� ������������ ������� ��������<br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_mod_modq", $INFO['warn_mod_modq']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� ��������� ������������ ���������� ���������, ����� ����� �������� ������������?</b><br />��������� ������ � ��� �����������, ������� ��������� ������������ ������� ��������<br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_mod_post", $INFO['warn_mod_post']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� �������� ������� ������������...</b>" ,
										 $SKIN->form_input( 'warn_mod_day' , $INFO['warn_mod_day']  == "" ? 1 : $INFO['warn_mod_day'] ).'... ���(�) � ����'
								 )      );		
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '���������� ��� ����������������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������������� ����� ������, ����� ����� �������� ������������?</b><br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_gmod_ban", $INFO['warn_gmod_ban']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������������� ����� ������������� ��� ������������ ��������������� �������� ���� ��� ���������, ����� ����� �������� ������������?</b><br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_gmod_modq", $INFO['warn_gmod_modq']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������������� ����� ��������� ������������ ���������� ���������, ����� ����� �������� ������������?</b><br />�������������� ����� ������������� ����������� ��� �������� ����� ����� �������� ������������" ,
										  $SKIN->form_yes_no( "warn_gmod_post", $INFO['warn_gmod_post']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������������� ����� �������� ������� ������������...</b>" ,
										 $SKIN->form_input( 'warn_gmod_day' , $INFO['warn_gmod_day']  == "" ? 1 : $INFO['warn_gmod_day'] ).'... ���(�) � ����'
								 )      );
								 
		$this->common_footer();
	
	}
	
	//-------------------------------------------------------------
	// SPIDER MAN! CHECK OUT THOSE CRAZY PANTS!
	//--------------------------------------------------------------
	
	function spider()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dospider', '��������� ��������� ������', '�� ������ ��������������� ������������, ����.<br />'.$SKIN->js_help_link('set_spider') );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		$mem_group = array();
		
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
		
		$DB->query("SELECT sname, sid FROM ibf_skins ORDER BY sname");
		
		$skin_sets = array( 0 => array('', "������������� �� ���������" ) );
		
		while ( $s = $DB->fetch_row() )
		{
			$skin_sets[] = array( $s['sid'], $s['sname'] );
		}
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����������� ���������� ��������?</b>" ,
										  $SKIN->form_yes_no( "spider_sense", $INFO['spider_sense']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ����� ��� ������ ��������� �����?</b><br />���� ��� �������, ������ ��������� ���!" ,
										  $SKIN->form_yes_no( "spider_visit", $INFO['spider_visit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ��������� �����, ��� ������������� ����� ������?</b>" ,
										  $SKIN->form_dropdown( "spider_group", $mem_group, $INFO['spider_group'] == "" ? $INFO['guest_group'] : $INFO['spider_group']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� ������ ������������ ����:</b>" ,
										  $SKIN->form_dropdown( "spider_suit", $skin_sets, $INFO['spider_suit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� � ������ ��� � �������?</b>" ,
										  $SKIN->form_yes_no( "spider_active", $INFO['spider_active']  )
										  ."<br />".$SKIN->form_checkbox( 'spider_anon', $INFO['spider_anon'] )." ���������� ��� ������� ������������� (������ �������������� ������ ������ ��)"
								 )      );
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '� ������ ��� � �������...', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� Googlebot �...</b>" ,
										 $SKIN->form_input( 'sp_google' , $INFO['sp_google']  == "" ? 'GoogleBot'   : $INFO['sp_google'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� Microsoft / Hotbot �...</b>" ,
										 $SKIN->form_input( 'sp_inktomi', $INFO['sp_inktomi'] == "" ? 'Hot Bot'     : $INFO['sp_inktomi'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� Lycos �...</b>" ,
										 $SKIN->form_input( 'sp_lycos'  , $INFO['sp_lycos']   == "" ? 'Lycos'       : $INFO['sp_lycos'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� Ask Jeeves �...</b>" ,
										 $SKIN->form_input( 'sp_jeeves' , $INFO['sp_jeeves']  == "" ? 'Ask Jeeves'  : $INFO['sp_jeeves'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� What U Seek �...</b>" ,
										 $SKIN->form_input( 'sp_wuseek' , $INFO['sp_wuseek']  == "" ? 'What U Seek' : $INFO['sp_wuseek'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������� Archive.org �...</b>" ,
										 $SKIN->form_input( 'sp_Archive_org' , $INFO['sp_Archive_org']  == "" ? 'Archive.org' : $INFO['sp_Archive_org'] )
								 )      );
		
		$this->common_footer();
	
	
	}
	
	
	//-------------------------------------------------------------
	// Board Guidelines
	//--------------------------------------------------------------
	
	function do_guidelines()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$master = array();
		$master['gl_show']  = $IN['gl_show'];
		$master['gl_link']  = $IN['gl_link'];
		$master['gl_title'] = $IN['gl_title'];
		
		$ADMIN->rebuild_config($master);
		
		$glines = stripslashes($HTTP_POST_VARS['gl_guidelines']);
		$glines = str_replace( "<br>", "<br />", $glines);
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='boardrules'");

		if ( $row = $DB->fetch_row() )
		{
			$DB->query("UPDATE ibf_cache_store SET cs_value='".addslashes($glines)."' WHERE cs_key='boardrules'");
		}
		else
		{
			$db_string = $DB->compile_db_insert_string( array(
															   'cs_key' => 'boardrules',
															   'cs_value' => $glines,
										  ) );

			$DB->query("INSERT INTO ibf_cache_store (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		}

		$ADMIN->save_log("���������� ���������� ������");
		
		$ADMIN->done_screen("������������ ������ ���������", "������� �������� �����������", "act=index" );
		
	}
	
	//---------------------------------------------
	
	function guidelines()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('doglines', '�����������/������� ������', '�� ������ ��������������� ������������, ����.');
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='boardrules'");
		
		$row = $DB->fetch_row();
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ������ � ��������� ����������?</b>" ,
										  $SKIN->form_yes_no( "gl_show", $INFO['gl_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ http:// �� ������� �������� ����������</b><br>�� ����������, ���� ������ ������������ ���������� ������" ,
										  $SKIN->form_input( "gl_link", $INFO['gl_link'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������, ��� ������������� � ���������?</b>" ,
										  $SKIN->form_input( "gl_title", $INFO['gl_title'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� �� ����������� ������� ������; ������� ����� �����������/������</b><br>HTML ��������" ,
										  $SKIN->form_textarea( "gl_guidelines", $std->my_br2nl($row['cs_value']), 65, 20  )
								 )      );
								 
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
    // PORTAL: Easier then before :)
    //--------------------------------------------------------------

    function portal()
    {
        global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

        $DB->query("SELECT * FROM ibf_topics WHERE poll_state <> \"0\" ORDER BY 'tid' DESC");

        $top_polls[]=array(0,"�� ���������� ������");
        $top_polls[]=array(0,"-----------------------");

        while ($poll = $DB->fetch_row()){
            $top_polls[]=array($poll['tid'],$poll['tid']." - ".$poll['title']);
        }


        $DB->query("SELECT id, name FROM ibf_forums WHERE subwrap = 0");

        $form_array = array();

        while ( $r = $DB->fetch_row() )
        {
            $form_array[] = array( $r['id'], $r['name'] );
        }
        $form_array[] = array (0, "�������, � ���� ������������ ��������� ����");


        $this->common_header('doportal', '������', '����������� ��������� ������� �����������');

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ��� �������</b><br>�������� (����� �������� ������ ���� � ��������)." ,
                                          $SKIN->form_dropdown( "portal_poll", $top_polls , $INFO['portal_poll'] )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>� ������ ������ �������������� �������?</b>" ,
                                          $SKIN->form_dropdown( "portal_newsforum", $form_array, $INFO['portal_newsforum']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>� ����� ������� �������������� �������?</b><br>������� ID ������� ����� ������� (,)" ,
                                          $SKIN->form_input( "portal_newsforum_expert", $INFO['portal_newsforum_expert']  )
                                 )      );
        
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>������� �������� ���������� �� ������� ��������?</b>" ,
                                          $SKIN->form_input( "portal_newsposts", $INFO['portal_newsposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� Google?</b>" ,
                                          $SKIN->form_yes_no( "portal_googlebar", $INFO['portal_googlebar']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� ���� (������� ������)?</b>" ,
                                          $SKIN->form_yes_no( "portal_latestposts", $INFO['portal_latestposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ��������� ��� (��� ������� ������)?</b>" ,
                                          $SKIN->form_input( "portal_num_latestposts", $INFO['portal_num_latestposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� ���� (����������� ������)?</b>" ,
                                          $SKIN->form_yes_no( "portal_latestposts_big", $INFO['portal_latestposts_big']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ��������� ��� (��� ����������� ������)?</b>" ,
                                          $SKIN->form_input( "portal_num_latestposts_big", $INFO['portal_num_latestposts_big']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� �� ������?</b>" ,
                                          $SKIN->form_yes_no( "portal_navigation", $INFO['portal_navigation']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������� �������������?</b>" ,
                                          $SKIN->form_yes_no( "portal_activemembers", $INFO['portal_activemembers']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� (���-�� ���, ������������� � �.�.)?</b>" ,
                                          $SKIN->form_yes_no( "portal_post_stats", $INFO['portal_post_stats']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �����������?</b>" ,
                                          $SKIN->form_yes_no( "portal_birthdays", $INFO['portal_birthdays']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������ �������?</b>" ,
                                          $SKIN->form_yes_no( "portal_top_posters", $INFO['portal_top_posters']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ������ �������?</b>" ,
                                          $SKIN->form_input( "portal_num_topposters", $INFO['portal_num_topposters']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��������� ������������������?</b>" ,
                                          $SKIN->form_yes_no( "portal_new_members", $INFO['portal_new_members']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ��������?</b>" ,
                                          $SKIN->form_input( "portal_num_newmembers", $INFO['portal_num_newmembers']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� �������� ������?</b>" ,
                                          $SKIN->form_yes_no( "portal_top_forums", $INFO['portal_top_forums']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ �������� �������?</b>" ,
                                          $SKIN->form_input( "portal_num_top_forums", $INFO['portal_num_top_forums']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� ����������� (��� ��������������������)?</b>" ,
                                          $SKIN->form_yes_no( "portal_loginbox", $INFO['portal_loginbox']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� ���������, � ������� �� ���������� ���������?</b><br>�� ������������� ������������, ���� �� �������� ����������� ��������� ���, �.�. ��� ����� ���� � �� ��." ,
                                          $SKIN->form_yes_no( "portal_newposts", $INFO['portal_newposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ��������� ���������  � ������� ���������� ���������?</b>" ,
                                          $SKIN->form_input( "portal_num_newposts", $INFO['portal_num_newposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������ �������?</b>" ,
                                          $SKIN->form_yes_no( "portal_old_news", $INFO['portal_old_news']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ ������ ��������:</b>" ,
                                          $SKIN->form_input( "portal_num_old_news", $INFO['portal_num_old_news']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������ �����������?</b>" ,
                                          $SKIN->form_yes_no( "portal_welcomepanel", $INFO['portal_welcomepanel']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���� ���������� ������������?</b>" ,
                                          $SKIN->form_yes_no( "portal_member_moment", $INFO['portal_member_moment']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����� ������� ����� X ������?</b>" ,
                                          $SKIN->form_yes_no( "portal_tease_news", $INFO['portal_tease_news']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������, ����� ������� ������� ����� ���������� � ����� ���������� ������ [�����]?</b><br>����� ��������� �������� � HTML/IBF ������ � ������ �������." ,
                                          $SKIN->form_input( "portal_tease_length", $INFO['portal_tease_length']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����������� �������?</b>" ,
                                          $SKIN->form_yes_no( "portal_calendar_events", $INFO['portal_calendar_events']  )
                                 )      );
                                 
        $this->common_footer();
    }
    
	//-------------------------------------------------------------
	// COPPA
	//--------------------------------------------------------------
	
	function coppa()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('docoppa', '��������� COPPA', '�� ������ ��������������� ������������, ����. ����������. ��� ��������� ������ <a href="http://www.ftc.gov/ogc/coppa1.htm" target="_blank">COPPA</a> , ���� �� 13 ���, ������ ����� �������� ������������ �������� � ��������� ���, ��� �� ���� ��� �� ��� �����.');
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���. ������� COPPA?</b>" ,
										  $SKIN->form_yes_no( "use_coppa", $INFO['use_coppa']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �����, �� ������� �������� ������ ����� �������� ��� ��� ������������ ��������</b>" ,
										  $SKIN->form_input( "coppa_fax", $INFO['coppa_fax']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �����, �� ������� �������� ������ ����� �������� ��� ��� ������������ ��������</b>" ,
										  $SKIN->form_textarea( "coppa_address", str_replace( "\n\n", "\n", $std->my_br2nl(str_replace( "\r\n", "\n", $INFO['coppa_address']) ) )  )
								 )      );
		
		
		$this->common_footer();
	
	
	}
	
	//=====================================================
	
	function docount()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ( (! $IN['posts']) and (! $IN['members'] ) and (! $IN['lastreg'] ) )
		{
			$ADMIN->error("������ �������������!");
		}
		
		$stats = array();
		
		if ($IN['posts'])
		{
			$DB->query("SELECT COUNT(pid) as posts FROM ibf_posts WHERE queued <> 1");
			$r = $DB->fetch_row();
			$stats['TOTAL_REPLIES'] = $r['posts'];
			$stats['TOTAL_REPLIES'] < 1 ? 0 : $stats['TOTAL_REPLIES'];
			
			$DB->query("SELECT COUNT(tid) as topics FROM ibf_topics WHERE approved = 1");
			$r = $DB->fetch_row();
			$stats['TOTAL_TOPICS'] = $r['topics'];
			$stats['TOTAL_TOPICS'] < 1 ? 0 : $stats['TOTAL_TOPICS'];
			
			$stats['TOTAL_REPLIES'] -= $stats['TOTAL_TOPICS'];
		}
		
		if ($IN['members'])
		{
			$DB->query("SELECT COUNT(id) as members from ibf_members WHERE mgroup <> '".$INFO['auth_group']."'");
			$r = $DB->fetch_row();
			$stats['MEM_COUNT'] = $r['members'];
			// Remove "guest" account...
			$stats['MEM_COUNT']--;
			$stats['MEM_COUNT'] < 1 ? 0 : $stats['MEM_COUNT'];
		}
		
		if ($IN['lastreg'])
		{
			$DB->query("SELECT id, name FROM ibf_members WHERE mgroup <> '".$INFO['auth_group']."' ORDER BY id DESC LIMIT 0,1");
			$r = $DB->fetch_row();
			$stats['LAST_MEM_NAME'] = $r['name'];
			$stats['LAST_MEM_ID']   = $r['id'];
		}
		
		if ($IN['online'])
		{
			$stats['MOST_DATE'] = time();
			$stats['MOST_COUNT'] = 1;
		}
		
		if ( count($stats) > 0 )
		{
			$db_string = $DB->compile_db_update_string( $stats );
			$DB->query("UPDATE ibf_stats SET $db_string");
		}
		else
		{
			$ADMIN->error("������ �������������!");
		}
		
		$ADMIN->done_screen("���������� �����������", "������� �������� �����������", "act=index" );
		
	}
	
	
	
	function countstats()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "�������� ����������, ������� ���������� �����������.";
		$ADMIN->page_title  = "�������� ���������� ������";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'docount' ),
												  2 => array( 'act'   , 'op'     ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "����������"    , "70%" );
		$SKIN->td_header[] = array( "�����"       , "30%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������� ����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "����������� ��� ���� � ���������",
												  $SKIN->form_dropdown( 'posts', array( 0 => array( 1, '��'  ), 1 => array( 0, '���' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "����������� �������������",
												  $SKIN->form_dropdown( 'members', array( 0 => array( 1, '��'  ), 1 => array( 0, '���' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "�������� ���������� � ��������� ������������������ ������������",
												  $SKIN->form_dropdown( 'lastreg', array( 0 => array( 1, '��'  ), 1 => array( 0, '���' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "�������� ���������� '������ ������������'?",
												  $SKIN->form_dropdown( 'online', array( 0 => array( 0, '���'  ), 1 => array( 1, '��' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('����������� ����������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// CALENDAR
	//--------------------------------------------------------------
	
	function calendar()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('docalendar', '��������� ���������', '�� ������ ��������������� ������������, ����.');
		
		$INFO['start_year'] = (isset($INFO['start_year'])) ? $INFO['start_year'] : 2001;
		$INFO['year_limit'] = (isset($INFO['year_limit'])) ? $INFO['year_limit'] : 5;
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����������� �� ������� �������� ���������?</b>" ,
										  $SKIN->form_yes_no( "show_bday_calendar", $INFO['show_bday_calendar'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����������� ����������� �� ������� �������� ������?</b>" ,
										  $SKIN->form_yes_no( "show_birthdays", $INFO['show_birthdays'] )
										  ."<br />".$SKIN->form_checkbox( "autohide_bday", $INFO["autohide_bday"] )." ������������� �������� �������, ���� ���� �����������?"
								 )      );
								 						 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������� �������?</b><br>����� ���������� ����������� ������� �� ������� �������� ������, � ������ ����������." ,
										  $SKIN->form_yes_no( "show_calendar", $INFO['show_calendar'] )
										  ."<br />".$SKIN->form_checkbox( "autohide_calendar", $INFO["autohide_calendar"] )." ������������� ��������, ���� ������� �����������?"
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������� ������� � �������� [�] ����</b><br>��������� � ������������� �����." ,
										  $SKIN->form_input( "calendar_limit", $INFO['calendar_limit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� � ���������� ���� '���' ���������</b><br>��� ���������� ��� ��������� ������� / ��������� ���������." ,
										  $SKIN->form_input( "start_year", $INFO['start_year']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��� � ���������� ���� '���' ���������</b><br>��� ���������� ��� ��������� ������� / ��������� ���������.<br>������: ���� ������� ��� 2003 � �� ������ ����� ����� 5, �� �������� ��� � ��������� ����� 2008" ,
										  $SKIN->form_input( "year_limit", $INFO['year_limit']  )
								 )      );
								 
 		
								 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// URLs and ADDRESSES
	//--------------------------------------------------------------
	
	function board()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('doboard', '���/���� ������', '�� ������ ��������������� ������������, ����.');
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �����?</b><br>�� ����� ������ �������� ������ ��, ��� ����� �� ��� ����������" ,
										  $SKIN->form_yes_no( "board_offline", $INFO['board_offline'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������, ������� ����� ���������� �������������</b>" ,
										  $SKIN->form_textarea( "offline_msg", $INFO['offline_msg']  )
								 )      );
								 
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// EMOTICON FUNCTIONS
	//-------------------------------------------------------------
	
	function doedit_emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['before'] == "")
		{
			$ADMIN->error("�� ������ ������ ��� ��������!");
		}
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�� ������ ������� �������!");
		}
		
		if ( strstr( $IN['before'], '&#092;' ) )
		{
			$ADMIN->error("��������� ������������ �������� ���� � \"{$IN['before']}\". ����������� ������ �������.");
		}
		
		$IN['clickable'] = $IN['clickable'] ? 1 : 0;
		
		$db_string = $DB->compile_db_update_string( array( 'typed'     => $IN['before'],
														   'image'     => $IN['after'],
														   'clickable' => $IN['click'],
												  )      );
												  
		$DB->query("UPDATE ibf_emoticons SET $db_string WHERE id='".$IN['id']."'");
		
		$std->boink_it($SKIN->base_url."&act=op&code=emo");
		exit();
			
		
	}
	
	//=====================================================
	
	
	function edit_emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "����� �� ������ ������������� ������ ��������";
		$ADMIN->page_title  = "�������������� ��������";
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�� ������ ������ ���!");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_emoticons WHERE id='".$IN['id']."'");
		
		if ( ! $r = $DB->fetch_row() )
		{
			$ADMIN->error("���� ������� �� ������ � ���� ������");
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'emo_doedit' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'id'    , $IN['id'] ),
									     )      );
		
		
		
		$SKIN->td_header[] = array( "���"  , "40%" );
		$SKIN->td_header[] = array( "�������"   , "40%" );
		$SKIN->td_header[] = array( "+ Clickable"  , "20%" );
		
		//+-------------------------------
		
		$emos = array();
		
		if (! is_dir($INFO['html_dir'].'emoticons') )
		{
			$ADMIN->error("���������� ���������� �������������� ���������� ���������. ��������� ���� � ���������� 'html_dir'");
		}
		
		//+-------------------------------
		
		
		$dh = opendir( $INFO['html_dir'].'emoticons' ) or die("���������� ���������� ������ �� ���������� ���������. ��������� ������������� ���� � ��������.");
 		while ( $file = readdir( $dh ) )
 		{
 			if ( !preg_match( "/^..?$|^index|htm$|html$|^\./i", $file ) )
 			{
 				$emos[] = array( $file, $file );
 			}
 		}
 		closedir( $dh );
 		
 		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������������� ��������" );
		
		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {
						 	
						 		var emo_url = '{$INFO['html_url']}/emoticons/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;
						 		
						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before', stripslashes($r['typed']) ),
												  $SKIN->form_dropdown('after', $emos, $r['image'], "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='html/emoticons/{$r['image']}' name='emopreview' border='0'>",
												  $SKIN->form_dropdown( 'click', array( 0 => array( 1, '��'  ), 1 => array( 0, '���' ) ), $r['clickable'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('��������� ���������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//=====================================================
	
	function remove_emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�� ������ ������� �������!");
		}
		
		$DB->query("DELETE FROM ibf_emoticons WHERE id='".$IN['id']."'");
		
		$std->boink_it($SKIN->base_url."&act=op&code=emo");
		exit();
			
		
	}
	
	//=====================================================
	
	function add_emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['before'] == "")
		{
			$ADMIN->error("�� ������ ������ ��� ��� ��������!");
		}
		
		if ( strstr( $IN['before'], '&#092;' ) )
		{
			$ADMIN->error("��������� ������������ �������� ���� � \"{$IN['before']}\". ����������� ������ �������.");
		}
		
		$IN['click'] = $IN['click'] ? 1 : 0;
		
		$db_string = $DB->compile_db_insert_string( array( 'typed'      => $IN['before'],
														   'image'      => $IN['after'],
														   'clickable'  => $IN['click'],
												  )      );
												  
		$DB->query("INSERT INTO ibf_emoticons (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		$std->boink_it($SKIN->base_url."&act=op&code=emo");
		exit();
			
		
	}
	
	
	function perly_length_sort($a, $b)
	{
		if ( strlen($a['typed']) == strlen($b['typed']) )
		{
			return 0;
		}
		return ( strlen($a['typed']) > strlen($b['typed']) ) ? -1 : 1;
	}
	
	function perly_word_sort($a, $b)
	{
		if ( strlen($a['type']) == strlen($b['type']) )
		{
			return 0;
		}
		return ( strlen($a['type']) > strlen($b['type']) ) ? -1 : 1;
	}
	
	//=====================================================
	
	function upload_emoticon()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_FILES;
		
		$FILE_NAME = $HTTP_POST_FILES['FILE_UPLOAD']['name'];
		$FILE_SIZE = $HTTP_POST_FILES['FILE_UPLOAD']['size'];
		$FILE_TYPE = $HTTP_POST_FILES['FILE_UPLOAD']['type'];
		
		// Naughty Opera adds the filename on the end of the
		// mime type - we don't want this.
		
		$FILE_TYPE = preg_replace( "/^(.+?);.*$/", "\\1", $FILE_TYPE );
		
		if (! is_dir($INFO['html_dir'].'emoticons') )
		{
			$ADMIN->error("���������� ���������� �������������� ���������� ���������. ��������� ���� � ���������� 'html_dir'");
		}
							
		// Naughty Mozilla likes to use "none" to indicate an empty upload field.
		// I love universal languages that aren't universal.
		
		if ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "" or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "none") )
		{
			$ADMIN->error("�� �� ������� ���� ��� ��������!");
		}
		
		//-------------------------------------------------
		// Copy the upload to the uploads directory
		//-------------------------------------------------
		
		if (! @move_uploaded_file( $HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['html_dir'].'emoticons'."/".$FILE_NAME) )
		{
			$ADMIN->error("��������� ��������");
		}
		else
		{
			@chmod( $INFO['html_dir'].'emoticons'."/".$FILE_NAME, 0777 );
		}
		
		$std->boink_it($SKIN->base_url."&act=op&code=emo");
		exit();
		
		
	}
	
	function emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "� ���� ������ �� ������ ���������, ������������� ��� ������� ��������.<br>�� ������ ������� ������ ��������, ����������� � ���������� 'html/emoticons'.<br><br>Clickable - ��� ��������, ��������� ����� �� ����� ������ � ����, � ������� '��������'.";
		$ADMIN->page_title  = "���������� ����������";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "���"      , "30%" );
		$SKIN->td_header[] = array( "�������"       , "30%" );
		$SKIN->td_header[] = array( "+ Clickable" , "20%" );
		$SKIN->td_header[] = array( "�������������"        , "10%" );
		$SKIN->td_header[] = array( "�������"      , "10%" );
		
		//+-------------------------------
		
		
		
		$ADMIN->html .= $SKIN->start_table( "������� ��������" );
		
		$DB->query("SELECT * from ibf_emoticons");
		
		$emo_url = $INFO['html_url'] . '/emoticons';
		
		$smilies = array();
			
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
				$smilies[] = $r;
			}
			
			usort($smilies, array( 'ad_settings', 'perly_length_sort' ) );
			
			foreach( $smilies as $array_idx => $r )
			{
			
				$click = $r['clickable'] ? '��' : '���';
				
				$ADMIN->html .= $SKIN->add_td_row( array( stripslashes($r['typed']),
														  "<center><img src='$emo_url/{$r['image']}'></center>",
														  "<center>$click</center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=emo_edit&id={$r['id']}'>�������������</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=emo_remove&id={$r['id']}'>�������</a></center>",
												 )      );
												   
			
				
			}
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$emos = array();
		
		if (! is_dir($INFO['html_dir'].'emoticons') )
		{
			$ADMIN->error("���������� ���������� �������������� ���������� ���������. ��������� ���� � ���������� 'html_dir'");
		}
		
		//+-------------------------------
		
		$cnt   = 0;
		$start = "";
		
		$dh = opendir( $INFO['html_dir'].'emoticons' ) or die("���������� ���������� ������ �� ���������� ���������. ��������� ������������� ���� � ��������.");
 		while ( $file = readdir( $dh ) )
 		{
 			if ( !preg_match( "/^..?$|^index|htm$|html$|^\./i", $file ) )
 			{
 				$emos[] = array( $file, $file );
 				
 				if ($cnt == 0)
 				{
 					$cnt = 1;
 					$start = $file;
 				}
 			}
 		}
 		closedir( $dh );
 		
 		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'emo_add' ),
												  2 => array( 'act'   , 'op'     ),
									     )      );
		
		
		$SKIN->td_header[] = array( "���"       , "40%" );
		$SKIN->td_header[] = array( "�������"        , "40%" );
		$SKIN->td_header[] = array( "+ Clickable"  , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= "<script language='javascript'>
						 <!--
						 	function show_emo() {
						 	
						 		var emo_url = '{$INFO['html_url']}/emoticons/' + document.theAdminForm.after.options[document.theAdminForm.after.selectedIndex].value;
						 		
						 		document.images.emopreview.src = emo_url;
							}
						//-->
						</script>
						";
		
		$ADMIN->html .= $SKIN->start_table( "�������� ����� �������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before'),
												  $SKIN->form_dropdown('after', $emos, "", "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='html/emoticons/$start' name='emopreview' border='0'>",
												  $SKIN->form_dropdown( 'click', array( 0 => array( 1, '��'  ), 1 => array( 0, '���' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('�������� �������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'emo_upload' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'MAX_FILE_SIZE', '10000000000' ),
									     ) , "uploadform", " enctype='multipart/form-data'"     );
		
		
		$SKIN->td_header[] = array( "&nbsp;"       , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"       , "60%" );
		
		
		$ADMIN->html .= $SKIN->start_table( "�������� �������� � ���������� emoticons" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �������� ������ ��������, �������� ���� �� ����� ����������</b><br>����� ��������, �������� ����� �������� �������� � ���������� ���� ����, ��� ������� '��������'.",
												  $SKIN->form_upload(),
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('��������� �������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// BADWORD FUNCTIONS
	//--------------------------------------------------------------
	
	
	function doedit_badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['before'] == "")
		{
			$ADMIN->error("�� �� ����� ����� ��� ������!");
		}
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�������� id �������!");
		}
		
		$IN['match'] = $IN['match'] ? 1 : 0;
		
		strlen($IN['swop']) > 1 ?  $IN['swop'] : "";
		
		$db_string = $DB->compile_db_update_string( array( 'type'    => $IN['before'],
														   'swop'    => $IN['after'],
														   'm_exact' => $IN['match'],
												  )      );
												  
		$DB->query("UPDATE ibf_badwords SET $db_string WHERE wid='".$IN['id']."'");
		
		$std->boink_it($SKIN->base_url."&act=op&code=bw");
		exit();
			
		
	}
	
	//=====================================================
	
	function edit_badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "����� �� ������ ��������������� ��������� ������";
		$ADMIN->page_title  = "������ ����������� ����";
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�������� id �������!");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_badwords WHERE wid='".$IN['id']."'");
		
		if ( ! $r = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ����� ���� ������ � ���� ������");
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'bw_doedit' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'id'    , $IN['id'] ),
									     )      );
		
		
		
		$SKIN->td_header[] = array( "�����"  , "40%" );
		$SKIN->td_header[] = array( "�������� ��"   , "40%" );
		$SKIN->td_header[] = array( "������"  , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "�������������� �������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before', stripslashes($r['type']) ),
												  $SKIN->form_input('after' , stripslashes($r['swop']) ),
												  $SKIN->form_dropdown( 'match', array( 0 => array( 1, '������'  ), 1 => array( 0, '�����' ) ), $r['m_exact'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('��������� ���������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//=====================================================
	
	function remove_badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("�������� id �������!");
		}
		
		$DB->query("DELETE FROM ibf_badwords WHERE wid='".$IN['id']."'");
		
		$std->boink_it($SKIN->base_url."&act=op&code=bw");
		exit();
			
		
	}
	
	//=====================================================
	
	function add_badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['before'] == "")
		{
			$ADMIN->error("�� �� ����� ����� ��� ������!");
		}
		
		$IN['match'] = $IN['match'] ? 1 : 0;
		
		strlen($IN['swop']) > 1 ?  $IN['swop'] : "";
		
		$db_string = $DB->compile_db_insert_string( array( 'type'    => $IN['before'],
														   'swop'    => $IN['after'],
														   'm_exact' => $IN['match'],
												  )      );
												  
		$DB->query("INSERT INTO ibf_badwords (".$db_string['FIELD_NAMES'].") VALUES(".$db_string['FIELD_VALUES'].")");
		
		$std->boink_it($SKIN->base_url."&act=op&code=bw");
		exit();
			
		
	}
	
	//=====================================================
	
	function badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "� ���� ������ �� ������ ���������, ������������� ��� ������� ������� ����������� ����.<br>������ ����������� ����, �������� ��������� ���� ����������� ����� � ����������,  ��������� ��� � ��������.<br><br><b>���������� �� �����</b>: ��� ������ ������� '�����', �������� ���� ����� ����� ���������� ���� � ������, ���������� � ���� ��� �����. ��������, ���� �� ������ � ������ ����� '����', �� ��� ����� ����� �������� �� ��������� ���� ���������� � � ����� '��������'. ���� �� �� ������ ����������, �� ����������� ����� ����� �������� �� ����� ������ '����' (#). <br><br><b>������ ����������</b>: ��� ������ ������� '������', �������� ���� ����� ����� ���������� �� ���������� ������ ��� ������ ���������� ����� �����. ��������, ���� �� ������ � ������ ����� '����', �� ��� ����� ����� �������� �� ��������� ���� ���������� , ������ � ����� '����'. ���� �� �� ������ ����������, �� ����������� ����� ����� �������� �� ����� ������ '����' (#).";
		$ADMIN->page_title  = "������ ����������� ����";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'bw_add' ),
												  2 => array( 'act'   , 'op'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "�����"  , "30%" );
		$SKIN->td_header[] = array( "�������� ��"   , "30%" );
		$SKIN->td_header[] = array( "������"  , "20%" );
		$SKIN->td_header[] = array( "�������������"    , "10%" );
		$SKIN->td_header[] = array( "�������"  , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������� �������" );
		
		$DB->query("SELECT * from ibf_badwords");
		
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
				$words[] = $r;
			}
			
			usort($words, array( 'ad_settings', 'perly_word_sort' ) );
			
			foreach($words as $idx => $r)
			{
			
				$replace = $r['swop']    ? stripslashes($r['swop']) : '######';
				
				$method  = $r['m_exact'] ? '������' : '�����';
				
				$ADMIN->html .= $SKIN->add_td_row( array( stripslashes($r['type']),
														  $replace,
														  $method,
														  "<center><a href='".$SKIN->base_url."&act=op&code=bw_edit&id={$r['wid']}'>�������������</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=bw_remove&id={$r['wid']}'>�������</a></center>",
												 )      );
			}
			
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		$SKIN->td_header[] = array( "�����"  , "40%" );
		$SKIN->td_header[] = array( "�������� ��"   , "40%" );
		$SKIN->td_header[] = array( "������"  , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� �������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before'),
												  $SKIN->form_input('after'),
												  $SKIN->form_dropdown( 'match', array( 0 => array( 1, '������'  ), 1 => array( 0, '�����' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('�������� ������');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// NEWS
	//--------------------------------------------------------------
	
	function news()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('donews', '��������� �������� ��������', '�� ������ ��������������� ������������, ����.');
		
		$DB->query("SELECT id, name FROM ibf_forums ORDER BY name");
		
		$form_array = array();
		
		while ( $r = $DB->fetch_row() )
		{
			$form_array[] = array( $r['id'], $r['name'] );
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�� ������ ������ �������������� ����� ����?</b>" ,
										  $SKIN->form_dropdown( "news_forum_id", $form_array, $INFO['news_forum_id']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������ '��������� �������' �� ������� ��������?</b>" ,
										  $SKIN->form_yes_no( "index_news_link", $INFO['index_news_link']  )
								 )      );
		
		
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// PM
	//--------------------------------------------------------------
	
	function pm()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dopm', '��������� PM', '�� ������ ��������������� ������������, ����.');
		
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� ������ � �������?</b>" ,
										  $SKIN->form_yes_no( "msg_allow_code", $INFO['msg_allow_code']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� HTML � �������?</b>" ,
										  $SKIN->form_yes_no( "msg_allow_html", $INFO['msg_allow_html']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� ����� �� ��������, ��� ��������� ������ �����</b><br>�� ��������� 50" ,
										  $SKIN->form_input( "show_max_msg_list", $INFO['show_max_msg_list']  )
								 )      );
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// EMAIL functions
	//--------------------------------------------------------------
	
	function email()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('doemail', '��������� E-mail', '�� ������ ��������������� ������������, ����.');
		
		$ADMIN->html .= $SKIN->add_td_basic( 'E-mail ������', 'left', 'catrow2' );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ������ ��� �������� �����</b>" ,
										  $SKIN->form_input( "email_in", $INFO['email_in']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail ������ ��� ��������� �����</b>" ,
										  $SKIN->form_input( "email_out", $INFO['email_out']  )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( '��� �����', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �����</b><br>���� PHP mail() �� ��������, ���������� ������������ SMTP" ,
										  $SKIN->form_dropdown( "mail_method", 
										  						 array(
										  						 		0 => array( 'mail', 'PHP mail()' ),
										  						 		1 => array( 'smtp', 'SMTP'  ),
										  						 	  ),
										  						 $INFO['mail_method']  )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( '������ SMTP', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� SMTP ����?</b><br>������ ������������ 'localhost'" ,
										  $SKIN->form_input( "smtp_host", $INFO['smtp_host']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� SMTP ����</b><br>������ ������������ 25" ,
										  $SKIN->form_input( "smtp_port", $INFO['smtp_port']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������ SMTP</b><br>��� ������������� 'localhost', ������ �� ���������" ,
										  $SKIN->form_input( "smtp_user", $INFO['smtp_user']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ������������ SMTP</b><br>��� ������������� 'localhost', ������ �� ���������" ,
										  $SKIN->form_input( "smtp_pass", $INFO['smtp_pass'], 'password'  )
								 )      );
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// URLs and ADDRESSES
	//--------------------------------------------------------------
	
	function url()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dourl', '������� ���������', '�� ������ ��������������� ������������, ����.');
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( '�������� ������ � HTTP ������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������</b>" ,
										  $SKIN->form_input( "board_name", $INFO['board_name']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������</b>" ,
										  $SKIN->form_input( "board_url", $INFO['board_url']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �����</b>" ,
										  $SKIN->form_input( "home_name", $INFO['home_name']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� �����</b>" ,
										  $SKIN->form_input( "home_url", $INFO['home_url']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ����� HTML</b><br>��� ��� ��������� �����������, � �.�." ,
										  $SKIN->form_input( "html_url", $INFO['html_url']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ����� Uploads</b>" ,
										  $SKIN->form_input( "upload_url", $INFO['upload_url']  )
								 )      );
								 					 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '��������� ���� ������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� �� ���������� 'html'</b><br>����������: ���� ������ ������ ����, � �� ������<br>�� �������� ��������� � ����� ����." ,
										  $SKIN->form_input( "html_dir", $INFO['html_dir']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� �� ���������� 'uploads'</b><br>���� � ����� �� ���������" ,
										  $SKIN->form_input( "upload_dir", $INFO['upload_dir']  )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '����� HTTP', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� HTTP ���������?</b><br>(��������� ������� NT ������� ���������� �����)" ,
										  $SKIN->form_yes_no( "print_headers", $INFO['print_headers'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>���������</I> GZIP ������?</b><br>(��� ��������� GZIP, ���������� ����� ������� �������� ������� � ����������� ������ �������)" ,
										  $SKIN->form_yes_no( "disable_gzip", $INFO['disable_gzip'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� �������������?</b><br>" ,
										  $SKIN->form_dropdown( 'header_redirect', 
										  						 array(
										  						 		0 => array( 'location', 'Location type (*nix savvy)' ),
										  						 		1 => array( 'refresh' , 'Refresh (Windows savvy)' ),
										  						 		2 => array( 'html'    , 'HTML META redirect (If all else fails...)' ),
										  						 	  ),
										  						 $INFO['header_redirect']  )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '�������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������</b>" ,
										  $SKIN->form_dropdown( "debug_level", 
										  						 array(
										  						 		0 => array( 0, '0: ��� - �� ���������� ���������� ����������' ),
										  						 		1 => array( 1, '1: ���������� �������� �������, ����� ��������� ������� � ������� ��������' ),
										  						 		2 => array( 2, '2: ���������� ������� 1 (����), � ����� ������������ � ���������� ����������'),
										  						 		3 => array( 3, '3: ���������� ������� 1 + 2 � ������� ���� ������'),
										  						 	  ),
										  						 $INFO['debug_level']  )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>��������</I> SQL �������?</b><br>(��� ������ ��, �������� '&debug=1' �� ����� �������� ��� ������������ ���������� ���������� mySQL)" ,
										  $SKIN->form_yes_no( "sql_debug", $INFO['sql_debug'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '�������� ��������� �����', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���������� ����� ������?</b><br>(����������: ����� ��������� �����, �������� ��� ���� ����� ����������� ��������������� ����� ��������, ����� ������� �������������� ������)" ,
										  $SKIN->form_dropdown( 'safe_mode_skins', 
										  						 array(
										  						 		0 => array( '0', '���' ),
										  						 		1 => array( '1' , '��' ),
										  						 	  ),
										  						 $INFO['safe_mode_skins']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������� �����</b><br>�� ������ ������� ������ ��� ���������� ����� � �����<br>(��������: � ��� � ������, ������������ �������)" ,
										  $SKIN->form_dropdown( 'number_format', 
										  						 array(
										  						 		0 => array( 'none', '�� �������������' ),
										  						 		1 => array( 'space' , '������' ),
										  						 		2 => array( ',' , ',' ),
										  						 		3 => array( '.' , '.' ),
										  						 	  ),
										  						 $INFO['number_format']  )
								 )      );
								 
 
		$this->common_footer();
	
	
	}
	
	
	
	//-------------------------------------------------------------
	// CPU SAVING
	//--------------------------------------------------------------
	
	function cpu()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('docpu', '�������� CPU', '�� ������ ��������� ��������� �����, ��� �������� ��������<br>�� �������� ������������� ��� ������, ����� ����������');
		
		
		if ($INFO['au_cutoff'] == "")
		{
			$INFO['au_cutoff'] = 15;
		}
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������� SQL', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������� �������������?</b>" ,
										  $SKIN->form_yes_no( "show_active", $INFO['show_active'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������� ������������� �� ��������� [�] �����</b>" ,
										  $SKIN->form_input( "au_cutoff", $INFO['au_cutoff'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �����������?</b>" ,
										  $SKIN->form_yes_no( "show_birthdays", $INFO['show_birthdays'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ����� ���������� ������?</b>" ,
										  $SKIN->form_yes_no( "show_totals", $INFO['show_totals'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����������� �������������� ����� ������� � �����?</b>" ,
										  $SKIN->form_yes_no( "custom_profile_topic", $INFO['custom_profile_topic'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���� � ������ �������� �� ������?</b>" ,
										  $SKIN->form_yes_no( "show_user_posted", $INFO['show_user_posted'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������� '������������ ��������������� ���� <u>�����</u>'?</b><br>(���� �� ���������� �� ������ �������, ��� ������� ��������� ������)" ,
										  $SKIN->form_yes_no( "no_au_forum", $INFO['no_au_forum'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������� '������������ ��������������� ��� <u>����</u>'?</b><br>(���� �� ���������� �� ������ �������, ��� ������� ��������� ����)" ,
										  $SKIN->form_yes_no( "no_au_topic", $INFO['no_au_topic'] )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������� CPU', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ �������� �������</b><br>��� ���������� �������, ������������� ����� ������ ��������� '������'<br>������ �� ���������, ��� ������ ���� �������" ,
										  $SKIN->form_input( "load_limit", $INFO['load_limit']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ������ (��� ��������)?</b>" ,
										  $SKIN->form_yes_no( "allow_search", $INFO['allow_search'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ����� [x] ������</b><br>��� ����������, ���� ���-�� ������ ��� ������ ����������� ����������� � ���� ���������" ,
										  $SKIN->form_input( "search_post_cut", $INFO['search_post_cut'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���-�� ��������, � ����� ��� ������</b><br>��� ������ �����, ��� ������ ����������� ����� ���������� � ����� ���-�� ���������� ����� �������" ,
										  $SKIN->form_input( "min_search_word", $INFO['min_search_word'] )."<br>����������, ���� �� �������� �������������� �����, ��� ����� ������� 4 ������� � ��� ���������� �������� ����� IPB"
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������� �������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ������������ ��������� HTTP?</b><br>(��� �������� ����������� ������� ����������)" ,
										  $SKIN->form_yes_no( "nocache", $INFO['nocache'] )
								 )      );
								 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ������ ������� � ���� �������� ��������</b><br>�� ����������� ���� '������� �� �������' ����� ������� ��� ���������. ��� �������, ���� � ��� ����� ����� ������� � ����������" ,
										  $SKIN->form_yes_no( "short_forum_jump", $INFO['short_forum_jump'] )
								 )      );
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// DATES
	//--------------------------------------------------------------
	
	function dates()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dodates', '����', '��������� ������� ����');
		
		$time_array = array();
		
		require ROOT_PATH."lang/en/lang_ucp.php";
		
		foreach( $lang as $off => $words )
 		{
 			if (preg_match("/^time_(\S+)$/", $off, $match))
 			{
 				$time_select[] = array( $match[1], $words );
 			}
 		}
 		
 		$d_date = $std->get_date(time(), 'LONG');
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ���� �������</b><br><span style='color:red'>���������� ����������� ��� ������� ����, � ��� �������� �� ������ �����, ������������ ������ ���������������� ���� � ����� ������ ���������� �������.</span>" ,
										  $SKIN->form_dropdown( "time_offset", $time_select, $INFO['time_offset']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� ������� (� �������)</b><br>��������� ����� �������. ���� �������� ��� ���������� ������� ������ �� ������� �� �������, ��������� ����� ��������� ����� ���� ����� '-' (��� �������)." ,
										  $SKIN->form_input( "time_adjust", $INFO['time_adjust'] ) . "<br>����� �� ������ � ��������� ������ (������� ������������� ������� ���� � ���������): $d_date"
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ������ �������</b><br>������� �� ������������, �� <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
										  $SKIN->form_input( "clock_short", $INFO['clock_short'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ���� �����������</b><br>������� �� ������������ �� <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
										  $SKIN->form_input( "clock_joined", $INFO['clock_joined'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ������ �������</b><br>������� �� ������������ �� <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
										  $SKIN->form_input( "clock_long", $INFO['clock_long'] )
								 )      );
 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// AVATARS
	//--------------------------------------------------------------
	
	function avatars()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('doavatars', '������� �������������', '��������� �������� ��� ������� �������������');
		
		$INFO['avatar_ext'] = preg_replace( "/\|/", ",", $INFO['avatar_ext'] );
		$INFO['photo_ext']  = preg_replace( "/\|/", ",", $INFO['photo_ext'] );
		
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������� ������������� � �����', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� �������� �����?</b>" ,
										  $SKIN->form_yes_no( "allow_skins", $INFO['allow_skins'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ���������, ����������� ������������, ��� ����������� ���������������� ��������� �������� ������ ������� �� ������</b><br>�������� ������ ��� ������������ ���� �������" ,
										  $SKIN->form_input( "post_titlechange", $INFO['post_titlechange'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����� (� ������), ��� ���������� ���� ����� ����������</b>" ,
										  $SKIN->form_input( "max_location_length", $INFO['max_location_length'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����� (� ������), ��� ���������� ���� ���������</b>" ,
										  $SKIN->form_input( "max_interest_length", $INFO['max_interest_length'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����� (� ������), ��� �������</b>" ,
										  $SKIN->form_input( "max_sig_length", $INFO['max_sig_length'] )
								 )      );						 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� HTML � ��������?</b>" ,
										  $SKIN->form_yes_no( "sig_allow_html", $INFO['sig_allow_html'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ����� ������ � ��������?</b>" ,
										  $SKIN->form_yes_no( "sig_allow_ibc", $INFO['sig_allow_ibc'] )
								 )      );
								 
		if ($INFO['postpage_contents'] == "")
		{
			$INFO['postpage_contents'] = '5,10,15,20,25,30,35,40';
		}
		
		if ($INFO['topicpage_contents'] == "")
		{
			$INFO['topicpage_contents'] = '5,10,15,20,25,30,35,40';
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� �� ��������, � ���������� ����, ������� ����� ������� ������������</b><br>��������� ����� �������, '�� ���������' ��� ������������� �����������<br>������: 5,15,20,25,30" ,
										  $SKIN->form_input( "postpage_contents", $INFO['postpage_contents'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��� �� �������� ������, � ���������� ����, ������� ����� ������� ������������</b><br>��������� ����� �������, '�� ���������' ��� ������������� �����������<br>������: 5,15,20,25,30" ,
										  $SKIN->form_input( "topicpage_contents", $INFO['topicpage_contents'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������� ������� ���� �������� �� ����, ���� ���� �� ����� ������� � ������� [x] ����</b><br>(������� �������� ���-�� ����)<br>�������� ������ ��� ������ �������������� �������" ,
										  $SKIN->form_input( "subs_autoprune", $INFO['subs_autoprune'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� � ������� ����������</b><br>��������� ����� �������, (gif,png,jpeg) � �.�." ,
										  $SKIN->form_input( "photo_ext", strlen($INFO['photo_ext']) > 1 ? $INFO['photo_ext'] : "gif,jpg,jpeg,png" )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '�������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������������� ��������� �������, �������� ������� ����������/��������?</b><br/ >��� ������ ��, ������������, ������ ����� ������� ������� �������" ,
										  $SKIN->form_yes_no( "disable_ipbsize", $INFO['disable_ipbsize'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ��������?</b>" ,
										  $SKIN->form_yes_no( "avatars_on", $INFO['avatars_on'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� ��� ��������</b><br>��������� ����� ������� (gif,png,jpeg) � �.�." ,
										  $SKIN->form_input( "avatar_ext", $INFO['avatar_ext'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� �������� URL ��� ��������?</b>" ,
										  $SKIN->form_yes_no( "avatar_url", $INFO['avatar_url'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ������ ����� ��� ����������� �������� (� ��)</b>" ,
										  $SKIN->form_input( "avup_size_max", $INFO['avup_size_max'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ��������� ��������</b><br>(������<b>x</b>������)" ,
										  $SKIN->form_input( "avatar_dims", $INFO['avatar_dims'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� � ������� ��������</b><br>(������<b>x</b>������)" ,
										  $SKIN->form_input( "avatar_def", $INFO['avatar_def'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������� � ������� ��������?</b>" ,
										  $SKIN->form_input( 'av_gal_cols' 	, $INFO['av_gal_cols'] = $INFO['av_gal_cols'] ? $INFO['av_gal_cols'] : 5 )
								 )      );						 
								 	
//-----------------------------------------------------------------------------------------------------------

	$ADMIN->html .= $SKIN->add_td_basic( '��������� ��������� � �������', 'left', 'catrow2' );

//-----------------------------------------------------------------------------------------------------------

	$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ������� ���� ���������?</b>" ,
					$SKIN->form_yes_no( "latest_show", $INFO['latest_show'] )
					)      );

	$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� ��������� ��� ����������� � �������?</b>" ,
					$SKIN->form_input( 'latest_amount' 	, $INFO['latest_amount'] = $INFO['latest_amount'] ? $INFO['latest_amount'] : 5 )
					)      );
											//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_basic( '������� ��� ������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����� ������ �������?</b>" ,
										  $SKIN->form_yes_no( "guests_sig", $INFO['guests_sig'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����� ������ ����������� � ����������?</b>" ,
										  $SKIN->form_yes_no( "guests_img", $INFO['guests_img'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ����� ������ ������� �������������?</b>" ,
										  $SKIN->form_yes_no( "guests_ava", $INFO['guests_ava'] )
								 )      );
								 
			     
								 					 
		$this->common_footer();
	
	
	}
	
	
	//-------------------------------------------------------------
	// TOPICS and POSTS
	//--------------------------------------------------------------
	
	function post()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$INFO['img_ext'] = preg_replace( "/\|/", ",", $INFO['img_ext'] );
	
		$this->common_header('dopost', '���� � ���������', '��������� �������� ��� ��� � ���������.');
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '����', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��� �� �������� ������</b>" ,
										  $SKIN->form_input( "display_max_topics", $INFO['display_max_topics'] )
								 )      );
									     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� � ����, ����� ���� ��������� '������� �����'?</b>" ,
										  $SKIN->form_input( "hot_topic", $INFO['hot_topic'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� ������������� ���</b>" ,
										  $SKIN->form_input( "pre_pinned", $INFO['pre_pinned'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� ������٨���� ���</b>" ,
										  $SKIN->form_input( "pre_moved", $INFO['pre_moved'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� ���-�������</b>" ,
										  $SKIN->form_input( "pre_polls", $INFO['pre_polls'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��������� ����� � ��������� ���?</b><br>(����� ���� ����� ������������������ � ����� ����)" ,
										  $SKIN->form_yes_no( "etfilter_shout", $INFO['etfilter_shout'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ���������� ��������������/��������������� ����� � ��������� ���?</b><br>(����� ����!!!!! ����� ������������������ � ����� ����!)" ,
										  $SKIN->form_yes_no( "etfilter_punct", $INFO['etfilter_punct'] )
								 )      );						 
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '���������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� �� �������� ����</b>" ,
										  $SKIN->form_input( "display_max_posts", $INFO['display_max_posts'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��������� � ����� ��</b>" ,
										  $SKIN->form_dropdown( "post_order_column",
										  				        array(
										  				        		0 => array( 'pid'       , 'ID ���������'   ),
										  				        		1 => array( 'post_date' , '���� ���������' )
										  				        	 ),
																$INFO['post_order_column'] ? $INFO['post_order_column'] : 'pid'
															  )
															  . ' '.
										  $SKIN->form_dropdown( "post_order_sort",
										  				        array(
										  				        		0 => array( 'asc'       , '����������� (0-9)'  ),
										  				        		1 => array( 'desc'      , '�������� (9-0)' )
										  				        	 ),
																$INFO['post_order_sort'] ? $INFO['post_order_sort'] : 'asc'
															  )
								 )      );					 
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������� ������� ���������, � ����� ������</b>" ,
										  $SKIN->form_input( "emo_per_row", $INFO['emo_per_row'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� ��������� � ���������</b>" ,
										  $SKIN->form_input( "max_emos", $INFO['max_emos'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ���-�� ����������� � ���������</b>" ,
										  $SKIN->form_input( "max_images", $INFO['max_images'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ������ ��������� (� ���������� [��])</b>" ,
										  $SKIN->form_input( "max_post_length", $INFO['max_post_length'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ������ Flash �������, � ��������� (� ��������)</b>" ,
										  $SKIN->form_input( "max_w_flash", $INFO['max_w_flash'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����. ������ Flash �������, � ��������� (� ��������)</b>" ,
										  $SKIN->form_input( "max_h_flash", $INFO['max_h_flash'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� ��������</b><br>(��������� ����� ������� (gif,jpeg,jpg) � �.�." ,
										  $SKIN->form_input( "img_ext", $INFO['img_ext'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������ � ���������� �����������?</b>" ,
										  $SKIN->form_yes_no( "show_img_upload", $INFO['show_img_upload'] )
										 ."<br />".$SKIN->form_checkbox( 'siu_thumb', $INFO['siu_thumb'] )."��������� �����������? ������ ".$SKIN->form_simple_input( 'siu_width', $INFO['siu_width'] )." x ".$SKIN->form_simple_input( 'siu_height', $INFO['siu_height'] )
								 )      );					 
		
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����������� �����?</b><br>����� ������� ������ ����� � ����������, ���������� ������<br><a href='#' title='� ���� �� ��������� ���, �� ������� ������ ���������� ���� �����'>..</a>" ,
										  $SKIN->form_yes_no( "strip_quotes", $INFO['strip_quotes'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>���������</i> ��� ������</b><br>���� ����� ����� ������������ � ��������� ��� ����-�� �� ������������������ �������������, �� ����� ��� ������ ����� ��������� ��� ���������)" ,
										  $SKIN->form_input( "guest_name_pre", $INFO['guest_name_pre'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>���������</i> ��� ������</b><br>(���� ����� ����� ������������ � ��������� ��� ����-�� �� ������������������ �������������, �� ����� ��� ����� ����� ��������� ��� ���������)" ,
										  $SKIN->form_input( "guest_name_suf", $INFO['guest_name_suf'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� �������� � �����, ����� �������� ����� ����� �������� � ���������� �� ������ ������?</b><br>��� ������ ��� ������������� �� ������� ������� ����, ������� �������� ����������� ����. �������������, 80 - 100" ,
										  $SKIN->form_input( "post_wordwrap", $INFO['post_wordwrap'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���� [IMG] � [URL] � �������?</b>" ,
										  $SKIN->form_yes_no( "poll_tags", $INFO['poll_tags'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ���������� ���-�� ������� � ������</b><br>" ,
										  $SKIN->form_input( "max_poll_choices", $INFO['max_poll_choices'] ? $INFO['max_poll_choices'] : 10)
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� �������� ����������� ������, ��� ������ ����������� ������?</b>" ,
										  $SKIN->form_yes_no( 'allow_result_view', $INFO['allow_result_view'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������������, ����������� ������ � �����-�������, ��������� '������ ��� �������'?</b>" ,
										  $SKIN->form_yes_no( 'poll_disable_noreply', $INFO['poll_disable_noreply'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ����� ��� ������ ����, � ������� �������, �� ������ �������� ����� � ����� ����</b><br>��� ��������������� � ����������������, ����������� ���" ,
										  $SKIN->form_input( 'startpoll_cutoff', $INFO['startpoll_cutoff'] ? $INFO['startpoll_cutoff'] : 24)
								 )      );
								 					 						 						 						 					 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// SECURITY
	//--------------------------------------------------------------
	
	function secure()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$this->common_header('dosecure', '������������', '����, �� ������ ��������������� ��������� ������ ������������ ������');
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������������ (����-�������� ������ ��������/�����)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��� ����������� ����-�������� ������ ��������/�����?</b><br>������������ ��� ����������� ������ ����� ������� ��������������� ������� ���, ��� ������������� �� �����.".$SKIN->js_help_link('s_reg_antispam') ,
										  $SKIN->form_dropdown( "bot_antispam", 
										  						array(
										  								0 => array( '0'    , '�� ������������'                  ),
										  								1 => array( 'gd'   , '����������� (��������� ��������� GD)'    ),
										  								2 => array( 'gif'  , '���������� (������ �� ���������)'  ),
										  								
										  							 ),
										  					    $INFO['bot_antispam']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������� GD; ���������� [��], ��� TTF ������, ��� [���] - ��� �������� ������?</b><br />TTF ����� �������� �������� �������" ,
										  $SKIN->form_yes_no( "use_ttf", isset($INFO['use_ttf']) ? $INFO['use_ttf'] : 1 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������� GD � TTF; ������ �����������</b>" ,
										  $SKIN->form_input( "gd_width", isset($INFO['gd_width']) ? $INFO['gd_width'] : 250 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������� GD � TTF; ������ �����������</b>" ,
										  $SKIN->form_input( "gd_height", isset($INFO['gd_height']) ? $INFO['gd_height'] : 70 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ������������� GD � TTF; ���� � ������������� ����� .ttf</b>" ,
										  $SKIN->form_input( "gd_font", isset($INFO['gd_font']) ? $INFO['gd_font'] : getcwd().'/fonts/progbot.ttf' )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������������ (�������)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������ �����������?</b><br>��� ������ '��' ������������ ������ ������������ ��������������� ������� � ���� �����������" ,
										  $SKIN->form_yes_no( "allow_dynamic_img", $INFO['allow_dynamic_img'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������������� ������ (� ��������)</b><br>���������� ������ ����� ������� �� ��������� �������, ������� �� ����������" ,
										  $SKIN->form_input( "session_expiration", $INFO['session_expiration'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������ ��������� �������������?</b>" ,
										  $SKIN->form_yes_no( "match_browser", $INFO['match_browser'] )
								 )      );
								 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������������ (�������)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� mail ����� ��� �������� ����� �������������?</b><br>������� email ������� �������������" ,
										  $SKIN->form_yes_no( "use_mail_form", $INFO['use_mail_form'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������������ (������)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------						 
								 					 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� � ����������?</b><br>��������� ������������ ����� ��������� ������� � �������� ��������. IBF ������ �� ��������������� ����." ,
										  $SKIN->form_yes_no( "allow_images", $INFO['allow_images'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ flash ������ � ���������� � � ��������?</b><br>Flash �������� �� ����� ��������, ������� ����� ����������������� ������������" ,
										  $SKIN->form_yes_no( "allow_flash", $INFO['allow_flash'] )
								 )      );	
								 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '������������ (������ ���������������)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------						 					 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ������������ e-mail ������� ��� �����������?</b><br>��� ������ ���, ����� ������������� �������� �� ������������ e-mail ������" ,
										  $SKIN->form_yes_no( "allow_dup_email", $INFO['allow_dup_email'] )
								 )      );
		
						 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ����� ����������� ����� e-mail?</b><br>����������� ������������� �������������� ��� ������������� ����� e-mail" ,
										  $SKIN->form_dropdown( "reg_auth_type", 
										  						array(
										  								0 => array( 'user' , '������������� �� e-mail' ),
										  								1 => array( 'admin', '������������� �������'      ),
										  								2 => array( '0'    , '�� ������������'                  )
										  							 ),
										  					    $INFO['reg_auth_type']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� �� ������������� ����������� �������������, �����...</b>" ,
										  $SKIN->form_simple_input( 'validate_day_prune', $INFO['validate_day_prune'], 3 ). "... ����"
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������������� �� e-mail � ����� �������������?</b>" ,
										  $SKIN->form_dropdown( "new_reg_notify", 
										  						array(
										  								0 => array( '1' , '��' ),
										  								1 => array( '0' , '���'  )
										  							 ),
										  					    $INFO['new_reg_notify']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������������� ����������� ��� ������, ����� �������� �� �����?</b>" ,
										  $SKIN->form_yes_no( "force_login", $INFO['force_login'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� �����������?</b>" ,
										  $SKIN->form_yes_no( "no_reg", $INFO['no_reg'] )
								 )      );
							 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��� ����������� ������ ����������� ������� ��� ���? (�����, ���������� � ������� �����)</b>" ,
										  $SKIN->form_yes_no( "reg_chars", $INFO['reg_chars'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� �������� chr(0xCA)?</b><br />����� ���� ����������� ��� '�������' �������, ��� �������� ������������������ ��� - �� ����� ��������� �������� ��� ������������ ��������.<br>��������, ��� �������� �����, ��� ��������� ���� �������, � ����������, �� ������������� ������� ����� �" ,
										  $SKIN->form_yes_no( 'strip_space_chr', $INFO['strip_space_chr'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������� '�������� ����������'?</b>" ,
										  $SKIN->form_yes_no( "disable_reportpost", $INFO['disable_reportpost'] )
								 )      ); // 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� �������� (� ��������)</b><br>������������ ������ ����� ���������, ����� ��������� ���������� ���������<br>�� ���������� ��� ����, ���� �� ������ ������������ ����-��������" ,
										  $SKIN->form_input( "flood_control", $INFO['flood_control'] )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( '�����������', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� �������������� ������ '��� � �������'?</b>" ,
										  $SKIN->form_yes_no( "allow_online_list", $INFO['allow_online_list'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������������, ������ ������� �������������</b></br>� ������� �������������, ����� ����� ����� ������������ ��������" ,
										  $SKIN->form_yes_no( "disable_admin_anon", $INFO['disable_admin_anon'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ���������������� IP ������� ������������� � ������ '��� � �������'?</b>" ,
										  $SKIN->form_yes_no( "disable_online_ip", $INFO['disable_online_ip'] )
								 )      );
								 
		
								 					 
		$this->common_footer();
	
	
	}
	
	//-------------------------------------------------------------
	// COOKIES: Yum Yum!
	//--------------------------------------------------------------
	
	function cookie()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('docookie', 'Cookies', '����� ������ ������ �� �������������. ��� ������ ������ �������������������� � ����� ����������� � ��������� ����������� �� ��������� ������ �����');
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Cookie ������</b><br>���������: ����������� <b>.your-domain.com</b> ��� �������� cookies" ,
										  $SKIN->form_input( "cookie_domain", $INFO['cookie_domain'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� Cookie</b><br>��������� ������������� ���������� ������� �� ����� �����." ,
										  $SKIN->form_input( "cookie_id", $INFO['cookie_id'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� Cookie</b><br>������������� ���� ������ �� �������� ���������� IPB" ,
										  $SKIN->form_input( "cookie_path", $INFO['cookie_path'] )
								 )      );
		
		$this->common_footer();
	
	
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
					
					if ($field == 'img_ext' or $field == 'avatar_ext' or $field == 'photo_ext')
					{
						$HTTP_POST_VARS[ $field ] = preg_replace( "/[\.\s]/", "" , $HTTP_POST_VARS[ $field ] );
						$HTTP_POST_VARS[ $field ] = str_replace('|', "&#124;", $HTTP_POST_VARS[ $field ]);
						$HTTP_POST_VARS[ $field ] = preg_replace( "/,/"     , '|', $HTTP_POST_VARS[ $field ] );
					}
					else if ($field == 'coppa_address')
					{
						$HTTP_POST_VARS[ $field ] = nl2br( $HTTP_POST_VARS[ $field ] );
					}
					
					if ( $field == 'gd_font' OR $field == 'html_dir' OR $field == 'upload_dir')
					{
						$HTTP_POST_VARS[ $field ] = preg_replace( "/'/", "&#39;", $HTTP_POST_VARS[ $field ] );
					}
					else
					{
						$HTTP_POST_VARS[ $field ] = preg_replace( "/'/", "&#39;", stripslashes($HTTP_POST_VARS[ $field ]) );
					}
					
					$master[ $field ] = stripslashes($HTTP_POST_VARS[ $field ]);
				}
				
				$ADMIN->rebuild_config($master);
			}
		}
		
		$ADMIN->save_log("���������� �������� ������, Back Up ������");
		
		$ADMIN->done_screen("��������� ������ ���������", "������� �������� �����������", "act=index" );
		
		
		
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
		
		$ADMIN->page_detail = $extra . "����������� ���������� �������� ���� ������, ����� �� �����������";
		$ADMIN->page_title  = "��������� ������ ($section)";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $formcode ),
												  2 => array( 'act'   , 'op'      ),
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
	
	function return_sql_no_no_cant_do_it_sorry_text()
	{
return "
<div style='line-height:150%'>
<span id='large'>�������������� �������� �������������� ����������, ����������</span>
<br /><br />
� ��� ������� ����� ���������, ��� �������� �������������� ����������. ������ �����, PHP �� ������ ��������� ���������� ��-�� ��������� ����������� 
�� ������� ���������� ��������, ��� ����� ������� � ��� ������������ ����������.
<br />
�������� �������������� ����������, �������� ����� ��������� ���������, �� � �� �� ����� ��� ����� �������� �������, 
��� ��� �������� ������� ������������� ��� ������, ��� � ��� �������� ����� ��������.
<br />
� �������, ���������� ���������, �������������� ����� 80.000 ��������� � ���, �� ��� � ������ ������.
� ���� � ��� MySQL 4.0.12+ ��� ����� ������� ����������.
<br />
<br />
<strong style='color:red;font-size:14px'>��� �������������� ������� ����������</strong>
<br />
���� �� ������ shell (SSH / Telnet) ������ � mysql, �� ���� �������, ����� �����. ���� � ��� ��� shell �������, ���������� � ������
������� � ��������, ����� ��� ��� ������� �� ���.
<br /><br />
<strong>��� 1: ������������� mysql</strong>
<br />
� shell �������:
<br />
<pre>mysql -u{your_sql_user_name} -p{your_sql_password}</pre>
<br />
��� ��� ������������ � ������ MySQL, �� ������ ����� �� ������ ����� conf_global.php
<br />
<br />
<strong>��� 2: �������� ���� ������</strong>
<br />
� mysql �������:
<br />
<pre>use {your_database_name_here};</pre>
<br />
�� �������� ��������� � ����� ���� ������� ����� � �������. ��� ����� MySQL ����, �� ������ ����� �� ������ ����� conf_global.php
<br /><br />
<strong>��� 3: ���������� ������� ���</strong>
<br />
� mysql �������:
<br />
<pre>\g alter table ibf_topics add fulltext(title);</pre>
<br />
���� �� �� ����������� ������� ������ 'ibf_' , �������������� ������� � ���� �������, �� ��� �������. �� ���������� ����� �������, ����� ������������� �����-�� ���-�� �������, 
� ����������� �� ���-�� ���, �� ����� ������.
<br />
<br />
<strong>��� 4: ���������� ������� ���������</strong>
<br />
� mysql �������:
<br />
<pre>\g alter table ibf_posts add fulltext(post);</pre>
<br />
���� �� �� ����������� ������� ������ 'ibf_' , �������������� ������� � ���� �������, �� ��� �������. �� ���������� ����� �������, ����� ������������� �����-�� ���-�� �������, 
� ����������� �� ���-�� ���, �� ����� ������. � �������, MySQL ����������� ����� 80,000 ��������� � ���. � ���� � ��� ����������� MySQL 4, �� ��� ����� ������� ����������.
</div>
";
	}
}


?>