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
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
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
   		
   		$this->common_header('dofulltext', 'Настройка полнотекстового поиска', 'Вы можете отредактировать конфигурацию, ниже');
   		
   		if ( $this->mysql_version < 32323 )
   		{
   			$ADMIN->html .= $SKIN->add_td_basic("<strong>Ваша MySQL не в состоянии использовать полнотекстовый поиск</strong><br />Обратитесь к Вашему хостеру, для обновления MySQL.");
											
   			$ADMIN->html .= $SKIN->end_form($button);
										 
			$ADMIN->html .= $SKIN->end_table();
		
			$ADMIN->output();
			
			exit();
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<strong>Что такое полнотекстовый поиск?</strong><br />Полнотекстовый поиск, является самым эффективным и быстрым способом
												тщательного поиска больших текстов.");
												
			//-------------------------------------------
			// Do we already have full text enabled?
			//-------------------------------------------
			
			$DB->query("SHOW CREATE TABLE ibf_posts");
			
			$tbl_info = $DB->fetch_row();
			
			if ( preg_match( "/FULLTEXT KEY/i", $tbl_info['Create Table'] ) )
			{
				$ADMIN->html .= $SKIN->add_td_basic("<b>Полнотекстовая индексация уже существует</b><input type='hidden' name='ftexist' value='1' />");
									 			 
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>Тип используемого поиска?</b>" ,
												  $SKIN->form_dropdown( "search_sql_method", array( 0 => array( 'ftext', 'Полнотекстовый' ), 1 => array( 'man', 'Ручной' ) ), $INFO['search_sql_method'] )
										 )      );
	
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>Способ поиска по умолчанию?</b><br>Работает только при включении полнотекстового поиска" ,
												  $SKIN->form_dropdown( "search_default_method", array( 0 => array( 'simple', 'Простой поиск' ), 1 => array( 'adv', 'Расширенный поиск' ) ), $INFO['search_default_method']  )
										 )      );
			}
			else
			{
				$ADMIN->html .= $SKIN->add_td_basic( "<b>Перед включением этой функции, Вы должны создать полнотекстовую индексацию. Просто нажмите на кнопку ниже, для начала индексации</b>" .
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
					$ADMIN->page_title  = "Продолжение невозможно";
		
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
				$ADMIN->error("Ваша версия MySQL не в состоянии использования полнотекстового поиска");
			}
		}
		
		$ADMIN->save_log("Обновление опций полнотекстового поиска");
		
		$ADMIN->done_screen("Настройки полнотекстового поиска обновлены", "Настройка полнотекстового поиска", "act=op&code=fulltext" );
		
	}
	
	//-------------------------------------------------------------
	// WARNY PORNY!
	//--------------------------------------------------------------
	
	function warn()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dowarn', 'Настройка рейтинга пользователей', 'Вы можете отредактировать конфигурацию, ниже.' );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		$protected = explode(',', trim($INFO['warn_protected']) );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить систему рейтинга?</b>" ,
										  $SKIN->form_yes_no( "warn_on", $INFO['warn_on']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Уровень рейтинга...</b><br />Это визуальная индикация рейтинга в профиле пользователей." ,
										 'Минимальный '.$SKIN->form_simple_input( 'warn_min' , $INFO['warn_min']  == "" ? 0 : $INFO['warn_min'] ) .'  '.
										 'Максимальный '.$SKIN->form_simple_input( 'warn_max' , $INFO['warn_max']  == "" ? 10 : $INFO['warn_max'] ) .
										 "<br>Разрешается использование отрицательных чисел. При использовании отрицательного числа, не рекомендуется использование графического блока, т.к. в графическом блоке не отображается отрицательный рейтинг.<br>Пользователи, при регистрации автоматически получают нулевой рейтинг."
								 )      );	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Защищённые группы...</b><br>Группы, у которых невозможно изменить рейтинг<br />Можно выбрать сразу несколько групп, удерживая клавишу <b>Ctrl</b>." ,
												  $SKIN->form_multiselect( "groups[]", $mem_group, $protected )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Пользователи незащищённых групп могут видеть <em>свой</em> уровень рейтинга?</b>" ,
										  $SKIN->form_yes_no( "warn_show_own", $INFO['warn_show_own']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить изменение уровня рейтинга при достижении максимума или минимума?</b>" ,
										  $SKIN->form_yes_no( "warn_past_max", $INFO['warn_past_max']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Тип индикатора отображения?</b>" ,
										  $SKIN->form_dropdown( 'warn_show_rating', array( 0 => array( 0, 'Тип предупреждений: Отображение в виде графического блока' ), 1 => array( 1, 'Тип рейтинга: Отображение в виде < мин | текущий | макс > уровня' ) ), $INFO['warn_show_rating']  )
								 )      );				 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Разрешения для модераторов форума', 'left', 'catrow2' );
		
		//----------------------------------------------------------------------------------------------------------- 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модераторы могут банить, через Центр рейтинга пользователя?</b><br />Относится только к тем модераторам, которым разрешено использовать систему рейтинга<br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_mod_ban", $INFO['warn_mod_ban']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модераторы могут устанавливать для пользователя предварительную проверку всех его сообщений, через Центр рейтинга пользователя?</b><br />Относится только к тем модераторам, которым разрешено использовать систему рейтинга<br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_mod_modq", $INFO['warn_mod_modq']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модераторы могут запрещать пользователю отправлять сообщения, через Центр рейтинга пользователя?</b><br />Относится только к тем модераторам, которым разрешено использовать систему рейтинга<br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_mod_post", $INFO['warn_mod_post']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Модераторы могут изменять рейтинг пользователя...</b>" ,
										 $SKIN->form_input( 'warn_mod_day' , $INFO['warn_mod_day']  == "" ? 1 : $INFO['warn_mod_day'] ).'... раз(а) в день'
								 )      );		
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Разрешения для супермодераторов', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Супермодераторы могут банить, через Центр рейтинга пользователя?</b><br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_gmod_ban", $INFO['warn_gmod_ban']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Супермодераторы могут устанавливать для пользователя предварительную проверку всех его сообщений, через Центр рейтинга пользователя?</b><br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_gmod_modq", $INFO['warn_gmod_modq']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Супермодераторы могут запрещать пользователю отправлять сообщения, через Центр рейтинга пользователя?</b><br />Администраторы могут автоматически производить это действие через Центр рейтинга пользователя" ,
										  $SKIN->form_yes_no( "warn_gmod_post", $INFO['warn_gmod_post']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Супермодераторы могут изменять рейтинг пользователя...</b>" ,
										 $SKIN->form_input( 'warn_gmod_day' , $INFO['warn_gmod_day']  == "" ? 1 : $INFO['warn_gmod_day'] ).'... раз(а) в день'
								 )      );
								 
		$this->common_footer();
	
	}
	
	//-------------------------------------------------------------
	// SPIDER MAN! CHECK OUT THOSE CRAZY PANTS!
	//--------------------------------------------------------------
	
	function spider()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('dospider', 'Настройка поисковой машины', 'Вы можете отредактировать конфигурацию, ниже.<br />'.$SKIN->js_help_link('set_spider') );
		
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
		
		$skin_sets = array( 0 => array('', "Установленный по умолчанию" ) );
		
		while ( $s = $DB->fetch_row() )
		{
			$skin_sets[] = array( $s['sid'], $s['sname'] );
		}
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить опознование поисковыми машинами?</b>" ,
										  $SKIN->form_yes_no( "spider_sense", $INFO['spider_sense']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Записывать в логах все визиты поисковых машин?</b><br />Если Вас атакуют, срочно отключите это!" ,
										  $SKIN->form_yes_no( "spider_visit", $INFO['spider_visit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать поисковых ботов, как пользователей какой группы?</b>" ,
										  $SKIN->form_dropdown( "spider_group", $mem_group, $INFO['spider_group'] == "" ? $INFO['guest_group'] : $INFO['spider_group']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Боты должны использовать скин:</b>" ,
										  $SKIN->form_dropdown( "spider_suit", $skin_sets, $INFO['spider_suit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать ботов в списке Кто в онлайне?</b>" ,
										  $SKIN->form_yes_no( "spider_active", $INFO['spider_active']  )
										  ."<br />".$SKIN->form_checkbox( 'spider_anon', $INFO['spider_anon'] )." Отображать как скрытых пользователей (только Администраторы смогут видеть их)"
								 )      );
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'В списке Кто в онлайне...', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать Googlebot в...</b>" ,
										 $SKIN->form_input( 'sp_google' , $INFO['sp_google']  == "" ? 'GoogleBot'   : $INFO['sp_google'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать Microsoft / Hotbot в...</b>" ,
										 $SKIN->form_input( 'sp_inktomi', $INFO['sp_inktomi'] == "" ? 'Hot Bot'     : $INFO['sp_inktomi'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать Lycos в...</b>" ,
										 $SKIN->form_input( 'sp_lycos'  , $INFO['sp_lycos']   == "" ? 'Lycos'       : $INFO['sp_lycos'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать Ask Jeeves в...</b>" ,
										 $SKIN->form_input( 'sp_jeeves' , $INFO['sp_jeeves']  == "" ? 'Ask Jeeves'  : $INFO['sp_jeeves'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать What U Seek в...</b>" ,
										 $SKIN->form_input( 'sp_wuseek' , $INFO['sp_wuseek']  == "" ? 'What U Seek' : $INFO['sp_wuseek'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Переименовать Archive.org в...</b>" ,
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

		$ADMIN->save_log("Обновление руководств форума");
		
		$ADMIN->done_screen("Конфигурация форума обновлена", "Главная страница Админцентра", "act=index" );
		
	}
	
	//---------------------------------------------
	
	function guidelines()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('doglines', 'Руководства/Правила форума', 'Вы можете отредактировать конфигурацию, ниже.');
		
		$DB->query("SELECT * FROM ibf_cache_store WHERE cs_key='boardrules'");
		
		$row = $DB->fetch_row();
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать ссылку в заголовке руководств?</b>" ,
										  $SKIN->form_yes_no( "gl_show", $INFO['gl_show'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка http:// на внешнюю страницу руководств</b><br>Не заполняйте, если хотите использовать внутреннюю ссылку" ,
										  $SKIN->form_input( "gl_link", $INFO['gl_link'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название, для использования в заголовке?</b>" ,
										  $SKIN->form_input( "gl_title", $INFO['gl_title'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Если не используете внешнюю ссылку; введите текст руководства/правил</b><br>HTML разрешён" ,
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

        $top_polls[]=array(0,"Не отображать опросы");
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
        $form_array[] = array (0, "Никакой, я буду использовать настройки ниже");


        $this->common_header('doportal', 'Портал', 'Производите настройку портала внимательно');

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Опрос для портала</b><br>Выберите (здесь показаны только темы с опросами)." ,
                                          $SKIN->form_dropdown( "portal_poll", $top_polls , $INFO['portal_poll'] )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>С какого форума экспортировать новости?</b>" ,
                                          $SKIN->form_dropdown( "portal_newsforum", $form_array, $INFO['portal_newsforum']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>С каких форумов экспортировать новости?</b><br>Вводите ID форумов через запятую (,)" ,
                                          $SKIN->form_input( "portal_newsforum_expert", $INFO['portal_newsforum_expert']  )
                                 )      );
        
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Сколько новостей отображать на главной странице?</b>" ,
                                          $SKIN->form_input( "portal_newsposts", $INFO['portal_newsposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать поисковик Google?</b>" ,
                                          $SKIN->form_yes_no( "portal_googlebar", $INFO['portal_googlebar']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать последние темы (Простая версия)?</b>" ,
                                          $SKIN->form_yes_no( "portal_latestposts", $INFO['portal_latestposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых последних тем (для простой версии)?</b>" ,
                                          $SKIN->form_input( "portal_num_latestposts", $INFO['portal_num_latestposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать последние темы (Расширенная версия)?</b>" ,
                                          $SKIN->form_yes_no( "portal_latestposts_big", $INFO['portal_latestposts_big']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых последних тем (для расширенной версии)?</b>" ,
                                          $SKIN->form_input( "portal_num_latestposts_big", $INFO['portal_num_latestposts_big']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать навигацию по форуму?</b>" ,
                                          $SKIN->form_yes_no( "portal_navigation", $INFO['portal_navigation']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать активных пользователей?</b>" ,
                                          $SKIN->form_yes_no( "portal_activemembers", $INFO['portal_activemembers']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать статистику (Кол-во тем, пользователей и т.д.)?</b>" ,
                                          $SKIN->form_yes_no( "portal_post_stats", $INFO['portal_post_stats']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать именинников?</b>" ,
                                          $SKIN->form_yes_no( "portal_birthdays", $INFO['portal_birthdays']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать лучших авторов?</b>" ,
                                          $SKIN->form_yes_no( "portal_top_posters", $INFO['portal_top_posters']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых лучших авторов?</b>" ,
                                          $SKIN->form_input( "portal_num_topposters", $INFO['portal_num_topposters']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать последних зарегистрированных?</b>" ,
                                          $SKIN->form_yes_no( "portal_new_members", $INFO['portal_new_members']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых новичков?</b>" ,
                                          $SKIN->form_input( "portal_num_newmembers", $INFO['portal_num_newmembers']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать самые активные форумы?</b>" ,
                                          $SKIN->form_yes_no( "portal_top_forums", $INFO['portal_top_forums']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых активных форумов?</b>" ,
                                          $SKIN->form_input( "portal_num_top_forums", $INFO['portal_num_top_forums']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать блок авторизации (для незарегистрированных)?</b>" ,
                                          $SKIN->form_yes_no( "portal_loginbox", $INFO['portal_loginbox']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать пользователям последние сообщения, с момента их последнего посещения?</b><br>Не рекомендуется использовать, если Вы включили отображение последних тем, т.к. это почти одно и то же." ,
                                          $SKIN->form_yes_no( "portal_newposts", $INFO['portal_newposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых последних сообщений  с момента последнего посещения?</b>" ,
                                          $SKIN->form_input( "portal_num_newposts", $INFO['portal_num_newposts']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать старые новости?</b>" ,
                                          $SKIN->form_yes_no( "portal_old_news", $INFO['portal_old_news']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во отображаемых старых новостей:</b>" ,
                                          $SKIN->form_input( "portal_num_old_news", $INFO['portal_num_old_news']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать панель приветствия?</b>" ,
                                          $SKIN->form_yes_no( "portal_welcomepanel", $INFO['portal_welcomepanel']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать блок случайного пользователя?</b>" ,
                                          $SKIN->form_yes_no( "portal_member_moment", $INFO['portal_member_moment']  )
                                 )      );
                                 
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Обрезать текст новости после X знаков?</b>" ,
                                          $SKIN->form_yes_no( "portal_tease_news", $INFO['portal_tease_news']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во символов, после которых новость будет обрезаться и будет появляться ссылка [Далее]?</b><br>Могут появиться проблемы с HTML/IBF кодами в тексте новости." ,
                                          $SKIN->form_input( "portal_tease_length", $INFO['portal_tease_length']  )
                                 )      );

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать календарные события?</b>" ,
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
	
		$this->common_header('docoppa', 'Настройка COPPA', 'Вы можете отредактировать конфигурацию, ниже. Примечание. При включении режима <a href="http://www.ftc.gov/ogc/coppa1.htm" target="_blank">COPPA</a> , дети до 13 лет, должны будут получить родительское согласие и отправить его, Вам на факс или на Ваш адрес.');
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать рег. систему COPPA?</b>" ,
										  $SKIN->form_yes_no( "use_coppa", $INFO['use_coppa']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Номер факса, на который родители должны будут высылать Вам своё родительское согласие</b>" ,
										  $SKIN->form_input( "coppa_fax", $INFO['coppa_fax']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ваш Адрес, на который родители должны будут высылать Вам своё родительское согласие</b>" ,
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
			$ADMIN->error("Нечего пересчитывать!");
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
			$ADMIN->error("Нечего пересчитывать!");
		}
		
		$ADMIN->done_screen("Статистика пересчитана", "Главная страница Админцентра", "act=index" );
		
	}
	
	
	
	function countstats()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$ADMIN->page_detail = "Выберите статистику, которую необходимо пересчитать.";
		$ADMIN->page_title  = "Пересчёт статистики форума";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'docount' ),
												  2 => array( 'act'   , 'op'     ),
									     )      );
									     
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Статистика"    , "70%" );
		$SKIN->td_header[] = array( "Опция"       , "30%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Пересчёт статистики" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "Пересчитать все темы и сообщения",
												  $SKIN->form_dropdown( 'posts', array( 0 => array( 1, 'Да'  ), 1 => array( 0, 'Нет' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "Пересчитать пользователей",
												  $SKIN->form_dropdown( 'members', array( 0 => array( 1, 'Да'  ), 1 => array( 0, 'Нет' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "Обновить информацию о последнем зарегистрированном пользователе",
												  $SKIN->form_dropdown( 'lastreg', array( 0 => array( 1, 'Да'  ), 1 => array( 0, 'Нет' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->add_td_row( array( "Обновить статистику 'Рекорд посещаемости'?",
												  $SKIN->form_dropdown( 'online', array( 0 => array( 0, 'Нет'  ), 1 => array( 1, 'Да' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Пересчитать статистику');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// CALENDAR
	//--------------------------------------------------------------
	
	function calendar()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('docalendar', 'Настройка календаря', 'Вы можете отредактировать конфигурацию, ниже.');
		
		$INFO['start_year'] = (isset($INFO['start_year'])) ? $INFO['start_year'] : 2001;
		$INFO['year_limit'] = (isset($INFO['year_limit'])) ? $INFO['year_limit'] : 5;
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать именинников на главной странице календаря?</b>" ,
										  $SKIN->form_yes_no( "show_bday_calendar", $INFO['show_bday_calendar'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать сегодняшних именинников на главной странице форума?</b>" ,
										  $SKIN->form_yes_no( "show_birthdays", $INFO['show_birthdays'] )
										  ."<br />".$SKIN->form_checkbox( "autohide_bday", $INFO["autohide_bday"] )." Автоматически скрывать таблицу, если нету именинников?"
								 )      );
								 						 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать будущие события?</b><br>Будут отображены календарные события на Главной странице форума, в секции статистики." ,
										  $SKIN->form_yes_no( "show_calendar", $INFO['show_calendar'] )
										  ."<br />".$SKIN->form_checkbox( "autohide_calendar", $INFO["autohide_calendar"] )." Автоматически скрывать, если события отсутствуют?"
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отображать будущие события в пределах [х] дней</b><br>Относится к вышеуказанной опции." ,
										  $SKIN->form_input( "calendar_limit", $INFO['calendar_limit']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Начальный год в выпадающем меню 'Год' календаря</b><br>Это необходимо для просмотра событий / сообщений календаря." ,
										  $SKIN->form_input( "start_year", $INFO['start_year']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Конечный год в выпадающем меню 'Год' календаря</b><br>Это необходимо для просмотра событий / сообщений календаря.<br>Пример: если текущий год 2003 и Вы введёте здесь цифру 5, то конечный год в календаре будет 2008" ,
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
	
		$this->common_header('doboard', 'Вкл/Выкл форума', 'Вы можете отредактировать конфигурацию, ниже.');
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить форум?</b><br>На форум смогут заходить только те, кто имеет на это разрешение" ,
										  $SKIN->form_yes_no( "board_offline", $INFO['board_offline'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сообщение, которое будет отображено пользователям</b>" ,
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
			$ADMIN->error("Вы должны ввести код смайлика!");
		}
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны выбрать смайлик!");
		}
		
		if ( strstr( $IN['before'], '&#092;' ) )
		{
			$ADMIN->error("Запрещено использовать обратный слэш в \"{$IN['before']}\". Используйте другие символы.");
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
	
		$ADMIN->page_detail = "Здесь Вы можете редактировать данные смайлика";
		$ADMIN->page_title  = "Редактирование смайлика";
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны ввести код!");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_emoticons WHERE id='".$IN['id']."'");
		
		if ( ! $r = $DB->fetch_row() )
		{
			$ADMIN->error("Этот смайлик не найден в базе данных");
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'emo_doedit' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'id'    , $IN['id'] ),
									     )      );
		
		
		
		$SKIN->td_header[] = array( "Код"  , "40%" );
		$SKIN->td_header[] = array( "Смайлик"   , "40%" );
		$SKIN->td_header[] = array( "+ Clickable"  , "20%" );
		
		//+-------------------------------
		
		$emos = array();
		
		if (! is_dir($INFO['html_dir'].'emoticons') )
		{
			$ADMIN->error("Невозможно определить местоположение директории смайликов. Проверьте пути к директории 'html_dir'");
		}
		
		//+-------------------------------
		
		
		$dh = opendir( $INFO['html_dir'].'emoticons' ) or die("Невозможно произвести чтение из директории смайликов. Проверьте установленные пути и атрибуты.");
 		while ( $file = readdir( $dh ) )
 		{
 			if ( !preg_match( "/^..?$|^index|htm$|html$|^\./i", $file ) )
 			{
 				$emos[] = array( $file, $file );
 			}
 		}
 		closedir( $dh );
 		
 		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Редактирование смайлика" );
		
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
												  $SKIN->form_dropdown( 'click', array( 0 => array( 1, 'Да'  ), 1 => array( 0, 'Нет' ) ), $r['clickable'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Сохранить изменения');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//=====================================================
	
	function remove_emoticons()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Вы должны выбрать смайлик!");
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
			$ADMIN->error("Вы должны ввести код для смайлика!");
		}
		
		if ( strstr( $IN['before'], '&#092;' ) )
		{
			$ADMIN->error("Запрещено использовать обратный слэш в \"{$IN['before']}\". Используйте другие символы.");
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
			$ADMIN->error("Невозможно определить местоположение директории смайликов. Проверьте путь к директории 'html_dir'");
		}
							
		// Naughty Mozilla likes to use "none" to indicate an empty upload field.
		// I love universal languages that aren't universal.
		
		if ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "" or !$HTTP_POST_FILES['FILE_UPLOAD']['name'] or ($HTTP_POST_FILES['FILE_UPLOAD']['name'] == "none") )
		{
			$ADMIN->error("Вы не выбрали файл для загрузки!");
		}
		
		//-------------------------------------------------
		// Copy the upload to the uploads directory
		//-------------------------------------------------
		
		if (! @move_uploaded_file( $HTTP_POST_FILES['FILE_UPLOAD']['tmp_name'], $INFO['html_dir'].'emoticons'."/".$FILE_NAME) )
		{
			$ADMIN->error("Неудачная загрузка");
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
	
		$ADMIN->page_detail = "В этой секции Вы можете добавлять, редактировать или удалять смайлики.<br>Вы можете выбрать только смайлики, загруженные в директорию 'html/emoticons'.<br><br>Clickable - это смайлики, находящие слева от формы ответа в тему, в таблице 'Смайлики'.";
		$ADMIN->page_title  = "Управление смайликами";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Код"      , "30%" );
		$SKIN->td_header[] = array( "Смайлик"       , "30%" );
		$SKIN->td_header[] = array( "+ Clickable" , "20%" );
		$SKIN->td_header[] = array( "Редактировать"        , "10%" );
		$SKIN->td_header[] = array( "Удалить"      , "10%" );
		
		//+-------------------------------
		
		
		
		$ADMIN->html .= $SKIN->start_table( "Текущие смайлики" );
		
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
			
				$click = $r['clickable'] ? 'Да' : 'Нет';
				
				$ADMIN->html .= $SKIN->add_td_row( array( stripslashes($r['typed']),
														  "<center><img src='$emo_url/{$r['image']}'></center>",
														  "<center>$click</center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=emo_edit&id={$r['id']}'>Редактировать</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=emo_remove&id={$r['id']}'>Удалить</a></center>",
												 )      );
												   
			
				
			}
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$emos = array();
		
		if (! is_dir($INFO['html_dir'].'emoticons') )
		{
			$ADMIN->error("Невозможно определить местоположение директории смайликов. Проверьте путь к директории 'html_dir'");
		}
		
		//+-------------------------------
		
		$cnt   = 0;
		$start = "";
		
		$dh = opendir( $INFO['html_dir'].'emoticons' ) or die("Невозможно произвести чтение из директории смайликов. Проверьте установленные пути и атрибуты.");
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
		
		
		$SKIN->td_header[] = array( "Код"       , "40%" );
		$SKIN->td_header[] = array( "Смайлик"        , "40%" );
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
		
		$ADMIN->html .= $SKIN->start_table( "Добавить новый смайлик" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before'),
												  $SKIN->form_dropdown('after', $emos, "", "onChange='show_emo()'") . "&nbsp;&nbsp;<img src='html/emoticons/$start' name='emopreview' border='0'>",
												  $SKIN->form_dropdown( 'click', array( 0 => array( 1, 'Да'  ), 1 => array( 0, 'Нет' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Добавить смайлик');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'emo_upload' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'MAX_FILE_SIZE', '10000000000' ),
									     ) , "uploadform", " enctype='multipart/form-data'"     );
		
		
		$SKIN->td_header[] = array( "&nbsp;"       , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"       , "60%" );
		
		
		$ADMIN->html .= $SKIN->start_table( "Загрузка смайлика в директорию emoticons" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Для загрузки нового смайлика, выберите файл на Вашем компьютере</b><br>После загрузки, название файла смайлика появится в выпадающем меню выше, над кнопкой 'Добавить'.",
												  $SKIN->form_upload(),
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Загрузить смайлик');
										 
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
			$ADMIN->error("Вы не ввели слово для замены!");
		}
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Неверный id фильтра!");
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
	
		$ADMIN->page_detail = "Здесь Вы можете отредактировать выбранный фильтр";
		$ADMIN->page_title  = "Фильтр нецензурных слов";
		
		//+-------------------------------
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Неверный id фильтра!");
		}
		
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_badwords WHERE wid='".$IN['id']."'");
		
		if ( ! $r = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно найти этот фильтр в базе данных");
		}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'bw_doedit' ),
												  2 => array( 'act'   , 'op'     ),
												  3 => array( 'id'    , $IN['id'] ),
									     )      );
		
		
		
		$SKIN->td_header[] = array( "Слово"  , "40%" );
		$SKIN->td_header[] = array( "Заменять на"   , "40%" );
		$SKIN->td_header[] = array( "Способ"  , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Редактирование фильтра" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before', stripslashes($r['type']) ),
												  $SKIN->form_input('after' , stripslashes($r['swop']) ),
												  $SKIN->form_dropdown( 'match', array( 0 => array( 1, 'Точный'  ), 1 => array( 0, 'Маска' ) ), $r['m_exact'] )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Сохранить изменения');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//=====================================================
	
	function remove_badword()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Неверный id фильтра!");
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
			$ADMIN->error("Вы не ввели слово для замены!");
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
	
		$ADMIN->page_detail = "В этой секции Вы можете добавлять, редактировать или удалять фильтры нецензурных слов.<br>Фильтр нецензурных слов, изменяет указанные Вами нецензурные слова в сообщениях,  названиях тем и подписях.<br><br><b>Совпадение по маске</b>: При выборе способа 'Маска', введённое Вами слово будет изменяться даже в словах, содержащих в себе это слово. Например, если Вы введёте в фильтр слово 'коза', то это слово будет изменено на указанный Вами заменитель и в слове 'стрекоза'. Если Вы не введёте заменитель, то нецензурное слово будет изменено на шесть знаков 'Диез' (#). <br><br><b>Точное совпадение</b>: При выборе способа 'Точный', введённое Вами слово будет изменяться на заменитель только при точном совпадении этого слова. Например, если Вы введёте в фильтр слово 'коза', то это слово будет изменено на указанный Вами заменитель , только в слове 'коза'. Если Вы не введёте заменитель, то нецензурное слово будет изменено на шесть знаков 'Диез' (#).";
		$ADMIN->page_title  = "Фильтр нецензурных слов";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'bw_add' ),
												  2 => array( 'act'   , 'op'     ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Слово"  , "30%" );
		$SKIN->td_header[] = array( "Заменять на"   , "30%" );
		$SKIN->td_header[] = array( "Способ"  , "20%" );
		$SKIN->td_header[] = array( "Редактировать"    , "10%" );
		$SKIN->td_header[] = array( "Удалить"  , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Текущие фильтры" );
		
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
				
				$method  = $r['m_exact'] ? 'Точный' : 'Маска';
				
				$ADMIN->html .= $SKIN->add_td_row( array( stripslashes($r['type']),
														  $replace,
														  $method,
														  "<center><a href='".$SKIN->base_url."&act=op&code=bw_edit&id={$r['wid']}'>Редактировать</a></center>",
														  "<center><a href='".$SKIN->base_url."&act=op&code=bw_remove&id={$r['wid']}'>Удалить</a></center>",
												 )      );
			}
			
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		$SKIN->td_header[] = array( "Слово"  , "40%" );
		$SKIN->td_header[] = array( "Заменять на"   , "40%" );
		$SKIN->td_header[] = array( "Способ"  , "20%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Добавление фильтра" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( $SKIN->form_input('before'),
												  $SKIN->form_input('after'),
												  $SKIN->form_dropdown( 'match', array( 0 => array( 1, 'Точный'  ), 1 => array( 0, 'Маска' ) ) )
										 )      );
										 
		$ADMIN->html .= $SKIN->end_form('Добавить фильтр');
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	
	}
	
	//-------------------------------------------------------------
	// NEWS
	//--------------------------------------------------------------
	
	function news()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
	
		$this->common_header('donews', 'Настройка экспорта новостей', 'Вы можете отредактировать конфигурацию, ниже.');
		
		$DB->query("SELECT id, name FROM ibf_forums ORDER BY name");
		
		$form_array = array();
		
		while ( $r = $DB->fetch_row() )
		{
			$form_array[] = array( $r['id'], $r['name'] );
		}
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Из какого форума экспортировать новые темы?</b>" ,
										  $SKIN->form_dropdown( "news_forum_id", $form_array, $INFO['news_forum_id']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать ссылку 'Последние Новости' на главной странице?</b>" ,
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
	
		$this->common_header('dopm', 'Настройки PM', 'Вы можете отредактировать конфигурацию, ниже.');
		
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить коды форума в письмах?</b>" ,
										  $SKIN->form_yes_no( "msg_allow_code", $INFO['msg_allow_code']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить HTML в письмах?</b>" ,
										  $SKIN->form_yes_no( "msg_allow_html", $INFO['msg_allow_html']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. кол-во писем за страницу, при просмотре списка писем</b><br>По умолчанию 50" ,
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
	
		$this->common_header('doemail', 'Настройка E-mail', 'Вы можете отредактировать конфигурацию, ниже.');
		
		$ADMIN->html .= $SKIN->add_td_basic( 'E-mail адреса', 'left', 'catrow2' );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail форума для входящей почты</b>" ,
										  $SKIN->form_input( "email_in", $INFO['email_in']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail форума для исходящей почты</b>" ,
										  $SKIN->form_input( "email_out", $INFO['email_out']  )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( 'Тип почты', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Тип почты</b><br>Если PHP mail() не доступен, попробуйте использовать SMTP" ,
										  $SKIN->form_dropdown( "mail_method", 
										  						 array(
										  						 		0 => array( 'mail', 'PHP mail()' ),
										  						 		1 => array( 'smtp', 'SMTP'  ),
										  						 	  ),
										  						 $INFO['mail_method']  )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( 'Данные SMTP', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Укажите SMTP хост?</b><br>Обычно используется 'localhost'" ,
										  $SKIN->form_input( "smtp_host", $INFO['smtp_host']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Укажите SMTP порт</b><br>Обычно используется 25" ,
										  $SKIN->form_input( "smtp_port", $INFO['smtp_port']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Имя пользователя SMTP</b><br>При использовании 'localhost', обычно не требуется" ,
										  $SKIN->form_input( "smtp_user", $INFO['smtp_user']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Пароль пользователя SMTP</b><br>При использовании 'localhost', обычно не требуется" ,
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
	
		$this->common_header('dourl', 'Главные настройки', 'Вы можете отредактировать конфигурацию, ниже.');
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_basic( 'Название форума и HTTP адреса', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название форума</b>" ,
										  $SKIN->form_input( "board_name", $INFO['board_name']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Адрес форума</b>" ,
										  $SKIN->form_input( "board_url", $INFO['board_url']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название сайта</b>" ,
										  $SKIN->form_input( "home_name", $INFO['home_name']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Адрес сайта</b>" ,
										  $SKIN->form_input( "home_url", $INFO['home_url']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка папки HTML</b><br>Там где находятся изображения, и т.д." ,
										  $SKIN->form_input( "html_url", $INFO['html_url']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Ссылка папки Uploads</b>" ,
										  $SKIN->form_input( "upload_url", $INFO['upload_url']  )
								 )      );
								 					 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Серверные пути форума', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Путь до директории 'html'</b><br>Примечание: надо ввести именно путь, а не ссылку<br>Не забудьте поставить в конце слэш." ,
										  $SKIN->form_input( "html_dir", $INFO['html_dir']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Путь до директории 'uploads'</b><br>Слэш в конце не требуется" ,
										  $SKIN->form_input( "upload_dir", $INFO['upload_dir']  )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Среда HTTP', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Печатать HTTP заголовки?</b><br>(Некоторые серверы NT требуют выключения этого)" ,
										  $SKIN->form_yes_no( "print_headers", $INFO['print_headers'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>ОТКЛЮЧИТЬ</I> GZIP сжатие?</b><br>(При включении GZIP, происходит более быстрая загрузка страниц и расходуется меньше трафика)" ,
										  $SKIN->form_yes_no( "disable_gzip", $INFO['disable_gzip'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Тип авторедиректа?</b><br>" ,
										  $SKIN->form_dropdown( 'header_redirect', 
										  						 array(
										  						 		0 => array( 'location', 'Location type (*nix savvy)' ),
										  						 		1 => array( 'refresh' , 'Refresh (Windows savvy)' ),
										  						 		2 => array( 'html'    , 'HTML META redirect (If all else fails...)' ),
										  						 	  ),
										  						 $INFO['header_redirect']  )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Отладка', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отладочный уровень</b>" ,
										  $SKIN->form_dropdown( "debug_level", 
										  						 array(
										  						 		0 => array( 0, '0: Нет - Не отображать отладочные информации' ),
										  						 		1 => array( 1, '1: Отображать загрузку сервера, время генерации страниц и счётчик запросов' ),
										  						 		2 => array( 2, '2: Отображать уровень 1 (выше), а также отправляемую и получаемую информацию'),
										  						 		3 => array( 3, '3: Отображать уровень 1 + 2 и запросы базы данных'),
										  						 	  ),
										  						 $INFO['debug_level']  )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>ВКЛЮЧИТЬ</I> SQL отладку?</b><br>(При выборе да, добавьте '&debug=1' на любую страницу для отображаения отладочной информации mySQL)" ,
										  $SKIN->form_yes_no( "sql_debug", $INFO['sql_debug'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Основные настройки скина', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать безопасный режим скинов?</b><br>(Примечание: После включения этого, возможно Вам надо будет производить ресинхронизацию Ваших шаблонов, после каждого редактирования скинов)" ,
										  $SKIN->form_dropdown( 'safe_mode_skins', 
										  						 array(
										  						 		0 => array( '0', 'Нет' ),
										  						 		1 => array( '1' , 'Да' ),
										  						 	  ),
										  						 $INFO['safe_mode_skins']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Форматирование чисел</b><br>Вы можете выбрать символ для разделения сотен и тысяч<br>(Например: в США и Англии, используется запятая)" ,
										  $SKIN->form_dropdown( 'number_format', 
										  						 array(
										  						 		0 => array( 'none', 'Не форматировать' ),
										  						 		1 => array( 'space' , 'Пробел' ),
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
	
		$this->common_header('docpu', 'Экономия CPU', 'Вы можете отключить некоторые опции, для экономии ресурсов<br>Не забудьте перепроверить все данные, перед изменением');
		
		
		if ($INFO['au_cutoff'] == "")
		{
			$INFO['au_cutoff'] = 15;
		}
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Расходы SQL', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать активных пользователей?</b>" ,
										  $SKIN->form_yes_no( "show_active", $INFO['show_active'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать активных пользователей за последние [х] минут</b>" ,
										  $SKIN->form_input( "au_cutoff", $INFO['au_cutoff'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать именинников?</b>" ,
										  $SKIN->form_yes_no( "show_birthdays", $INFO['show_birthdays'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать общую статистику форума?</b>" ,
										  $SKIN->form_yes_no( "show_totals", $INFO['show_totals'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить отображение дополнительных полей профиля в темах?</b>" ,
										  $SKIN->form_yes_no( "custom_profile_topic", $INFO['custom_profile_topic'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выделять темы с новыми ответами на форуме?</b>" ,
										  $SKIN->form_yes_no( "show_user_posted", $INFO['show_user_posted'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить функцию 'Пользователи просматривающие этот <u>форум</u>'?</b><br>(Этим Вы сэкономите по одному запросу, для каждого просмотра форума)" ,
										  $SKIN->form_yes_no( "no_au_forum", $INFO['no_au_forum'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить функцию 'Пользователи просматривающие эту <u>тему</u>'?</b><br>(Этим Вы сэкономите по одному запросу, для каждого просмотра темы)" ,
										  $SKIN->form_yes_no( "no_au_topic", $INFO['no_au_topic'] )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Расходы CPU', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Предел загрузки сервера</b><br>При превышении предела, пользователям будет выдано сообщение 'занято'<br>Можете не заполнять, для отмены этой функции" ,
										  $SKIN->form_input( "load_limit", $INFO['load_limit']  )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование Поиска (где допущено)?</b>" ,
										  $SKIN->form_yes_no( "allow_search", $INFO['allow_search'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Обрезать сообщения после [x] знаков</b><br>Это необходимо, если кто-то выбрал при поиске отображение результатов в виде сообщений" ,
										  $SKIN->form_input( "search_post_cut", $INFO['search_post_cut'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Минимальное кол-во символов, в слове для поиска</b><br>Чем короче слово, тем больше результатов будет отображено и найти что-то конкретное будет труднее" ,
										  $SKIN->form_input( "min_search_word", $INFO['min_search_word'] )."<br>Примечание, если Вы включили полнотекстовый поиск, это будет минимум 4 символа и это невозможно изменить через IPB"
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Расходы трафика', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Печатать некэшируемые заголовки HTTP?</b><br>(Это запретит кеширование страниц браузерами)" ,
										  $SKIN->form_yes_no( "nocache", $INFO['nocache'] )
								 )      );
								 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Краткий список форумов в меню быстрого перехода</b><br>Из выпадающего меню 'Переход по форумам' будут удалены все подфорумы. Это полезно, если у Вас очень много форумов и подфорумов" ,
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
	
		$this->common_header('dodates', 'Даты', 'Настройка формата даты');
		
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
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Часовой пояс сервера</b><br><span style='color:red'>Установите необходимый Вам часовой пояс, а при переходе на летнее время, пользователи смогут откорректировать часы в своей панели управления Профиля.</span>" ,
										  $SKIN->form_dropdown( "time_offset", $time_select, $INFO['time_offset']  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Коррекция часов сервера (в минутах)</b><br>Коррекция часов сервера. Если например Вам необходимо вычесть минуты от времени на сервере, пропишите перед значением минут знак минус '-' (без кавычек)." ,
										  $SKIN->form_input( "time_adjust", $INFO['time_adjust'] ) . "<br>Время на форуме в настоящий момент (включая установленный часовой пояс и настройку): $d_date"
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Краткий формат времени</b><br>Образец по конфигурации, на <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
										  $SKIN->form_input( "clock_short", $INFO['clock_short'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Формат даты регистрации</b><br>Образец по конфигурации на <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
										  $SKIN->form_input( "clock_joined", $INFO['clock_joined'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Длинный формат времени</b><br>Образец по конфигурации на <a href='http://www.php.net/date' target='_blank'>PHP Date</a>" ,
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
	
		$this->common_header('doavatars', 'Профиль пользователей', 'Установки настроек для профиля пользователей');
		
		$INFO['avatar_ext'] = preg_replace( "/\|/", ",", $INFO['avatar_ext'] );
		$INFO['photo_ext']  = preg_replace( "/\|/", ",", $INFO['photo_ext'] );
		
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Профиль пользователей и опции', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить пользователям выбирать скины?</b>" ,
										  $SKIN->form_yes_no( "allow_skins", $INFO['allow_skins'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во сообщений, необходимое пользователю, для возможности самостоятельного изменения названия своего статуса на форуме</b><br>Оставьте пробел для блокирования этой функции" ,
										  $SKIN->form_input( "post_titlechange", $INFO['post_titlechange'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальная длина (в байтах), для заполнения поля Место жительства</b>" ,
										  $SKIN->form_input( "max_location_length", $INFO['max_location_length'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальная длина (в байтах), для заполнения поля Увлечения</b>" ,
										  $SKIN->form_input( "max_interest_length", $INFO['max_interest_length'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальная длина (в байтах), для подписи</b>" ,
										  $SKIN->form_input( "max_sig_length", $INFO['max_sig_length'] )
								 )      );						 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование HTML в подписях?</b>" ,
										  $SKIN->form_yes_no( "sig_allow_html", $INFO['sig_allow_html'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование тэгов форума в подписях?</b>" ,
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
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во сообщений за страницу, в выпадающем меню, которое могут выбрать пользователи</b><br>Разделять через запятую, 'По умолчанию' уже автоматически установлено<br>Пример: 5,15,20,25,30" ,
										  $SKIN->form_input( "postpage_contents", $INFO['postpage_contents'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во тем за страницу форума, в выпадающем меню, которое могут выбрать пользователи</b><br>Разделять через запятую, 'По умолчанию' уже автоматически установлено<br>Пример: 5,15,20,25,30" ,
										  $SKIN->form_input( "topicpage_contents", $INFO['topicpage_contents'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Автоматическая отписка всех подписок на тему, если тема не имеет ответов в течение [x] дней</b><br>(Введите значение кол-ва дней)<br>Оставьте пробел для отмены автоматической отписки" ,
										  $SKIN->form_input( "subs_autoprune", $INFO['subs_autoprune'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Допущенные расширения в ссылках фотографий</b><br>Разделять через запятую, (gif,png,jpeg) и т.д." ,
										  $SKIN->form_input( "photo_ext", strlen($INFO['photo_ext']) > 1 ? $INFO['photo_ext'] : "gif,jpg,jpeg,png" )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Аватары', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить автоматическое изменение форумом, размеров больших фотографий/аватаров?</b><br/ >При выборе Да, пользователи, должны будут вручную вводить размеры" ,
										  $SKIN->form_yes_no( "disable_ipbsize", $INFO['disable_ipbsize'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование аватаров?</b>" ,
										  $SKIN->form_yes_no( "avatars_on", $INFO['avatars_on'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Допущенные расширения для аватаров</b><br>Разделять через запятую (gif,png,jpeg) и т.д." ,
										  $SKIN->form_input( "avatar_ext", $INFO['avatar_ext'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование удалённых URL для аватаров?</b>" ,
										  $SKIN->form_yes_no( "avatar_url", $INFO['avatar_url'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. размер файла для загружаемых аватаров (в Кб)</b>" ,
										  $SKIN->form_input( "avup_size_max", $INFO['avup_size_max'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальные измерения аватаров</b><br>(ШИРИНА<b>x</b>ВЫСОТА)" ,
										  $SKIN->form_input( "avatar_dims", $INFO['avatar_dims'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Измерения в галерее аватаров</b><br>(ШИРИНА<b>x</b>ВЫСОТА)" ,
										  $SKIN->form_input( "avatar_def", $INFO['avatar_def'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во колонок в галерее аватаров?</b>" ,
										  $SKIN->form_input( 'av_gal_cols' 	, $INFO['av_gal_cols'] = $INFO['av_gal_cols'] ? $INFO['av_gal_cols'] : 5 )
								 )      );						 
								 	
//-----------------------------------------------------------------------------------------------------------

	$ADMIN->html .= $SKIN->add_td_basic( 'Последние сообщения в профиле', 'left', 'catrow2' );

//-----------------------------------------------------------------------------------------------------------

	$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать в профиле сами сообщения?</b>" ,
					$SKIN->form_yes_no( "latest_show", $INFO['latest_show'] )
					)      );

	$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во последних сообщений для отображения в профиле?</b>" ,
					$SKIN->form_input( 'latest_amount' 	, $INFO['latest_amount'] = $INFO['latest_amount'] ? $INFO['latest_amount'] : 5 )
					)      );
											//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Доступы для гостей', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Гости могут видеть подписи?</b>" ,
										  $SKIN->form_yes_no( "guests_sig", $INFO['guests_sig'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Гости могут видеть изображения в сообщениях?</b>" ,
										  $SKIN->form_yes_no( "guests_img", $INFO['guests_img'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Гости могут видеть аватары пользователей?</b>" ,
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
	
		$this->common_header('dopost', 'Темы и сообщения', 'Настройка пределов для тем и сообщений.');
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Темы', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во тем за страницу форума</b>" ,
										  $SKIN->form_input( "display_max_topics", $INFO['display_max_topics'] )
								 )      );
									     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во сообщений в теме, чтобы тема считалась 'горячей темой'?</b>" ,
										  $SKIN->form_input( "hot_topic", $INFO['hot_topic'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Приставка для ФИКСИРОВАННЫХ тем</b>" ,
										  $SKIN->form_input( "pre_pinned", $INFO['pre_pinned'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Приставка для ПЕРЕМЕЩЁННЫХ тем</b>" ,
										  $SKIN->form_input( "pre_moved", $INFO['pre_moved'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Приставка для тем-ОПРОСОВ</b>" ,
										  $SKIN->form_input( "pre_polls", $INFO['pre_polls'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запретить заглавные буквы в названиях тем?</b><br>(НОВАЯ ТЕМА будет переконвертировано в Новая Тема)" ,
										  $SKIN->form_yes_no( "etfilter_shout", $INFO['etfilter_shout'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удалять избыточные вопросительные/восклицательные знаки в названиях тем?</b><br>(Новая Тема!!!!! будет переконвертировано в Новая Тема!)" ,
										  $SKIN->form_yes_no( "etfilter_punct", $INFO['etfilter_punct'] )
								 )      );						 
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Сообщения', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во сообщений за страницу темы</b>" ,
										  $SKIN->form_input( "display_max_posts", $INFO['display_max_posts'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сортировать сообщения в темах по</b>" ,
										  $SKIN->form_dropdown( "post_order_column",
										  				        array(
										  				        		0 => array( 'pid'       , 'ID сообщений'   ),
										  				        		1 => array( 'post_date' , 'Дате сообщений' )
										  				        	 ),
																$INFO['post_order_column'] ? $INFO['post_order_column'] : 'pid'
															  )
															  . ' '.
										  $SKIN->form_dropdown( "post_order_sort",
										  				        array(
										  				        		0 => array( 'asc'       , 'Возрастанию (0-9)'  ),
										  				        		1 => array( 'desc'      , 'Убыванию (9-0)' )
										  				        	 ),
																$INFO['post_order_sort'] ? $INFO['post_order_sort'] : 'asc'
															  )
								 )      );					 
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во колонок таблицы смайликов, в форме ответа</b>" ,
										  $SKIN->form_input( "emo_per_row", $INFO['emo_per_row'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. кол-во смайликов в сообщении</b>" ,
										  $SKIN->form_input( "max_emos", $INFO['max_emos'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. кол-во изображений в сообщении</b>" ,
										  $SKIN->form_input( "max_images", $INFO['max_images'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. размер сообщения (в килобайтах [кб])</b>" ,
										  $SKIN->form_input( "max_post_length", $INFO['max_post_length'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. ширина Flash роликов, в сообщении (в пикселах)</b>" ,
										  $SKIN->form_input( "max_w_flash", $INFO['max_w_flash'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Макс. высота Flash роликов, в сообщении (в пикселах)</b>" ,
										  $SKIN->form_input( "max_h_flash", $INFO['max_h_flash'] )
								 )      );
								 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Допустимые расширения картинок</b><br>(Разделять через запятую (gif,jpeg,jpg) и т.д." ,
										  $SKIN->form_input( "img_ext", $INFO['img_ext'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Показывать прикреплённые к сообщениям изображения?</b>" ,
										  $SKIN->form_yes_no( "show_img_upload", $INFO['show_img_upload'] )
										 ."<br />".$SKIN->form_checkbox( 'siu_thumb', $INFO['siu_thumb'] )."Уменьшать изображения? Размер ".$SKIN->form_simple_input( 'siu_width', $INFO['siu_width'] )." x ".$SKIN->form_simple_input( 'siu_height', $INFO['siu_height'] )
								 )      );					 
		
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запретить цитирование цитат?</b><br>Будут удалены тексты цитат в сообщениях, содержащих цитату<br><a href='#' title='и если Вы осмыслите это, то уровень Вашего интеллекта выше моего'>..</a>" ,
										  $SKIN->form_yes_no( "strip_quotes", $INFO['strip_quotes'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>Приставка</i> для гостей</b><br>Если гость будет использовать в сообщении имя кого-то из зарегистрированных пользователей, то перед его именем будет добавлена эта приставка)" ,
										  $SKIN->form_input( "guest_name_pre", $INFO['guest_name_pre'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b><i>Окончание</i> для гостей</b><br>(Если гость будет использовать в сообщении имя кого-то из зарегистрированных пользователей, то после его имени будет добавлено это окончание)" ,
										  $SKIN->form_input( "guest_name_suf", $INFO['guest_name_suf'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во символов в слове, после которого слово будет обрезано и перенесено на другую строку?</b><br>Это служит для предохранения от длинных цельных слов, которые искажают отображение темы. Рекомендуется, 80 - 100" ,
										  $SKIN->form_input( "post_wordwrap", $INFO['post_wordwrap'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Опросы', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить тэги [IMG] и [URL] в опросах?</b>" ,
										  $SKIN->form_yes_no( "poll_tags", $INFO['poll_tags'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимально допустимое кол-во пунктов в опросе</b><br>" ,
										  $SKIN->form_input( "max_poll_choices", $INFO['max_poll_choices'] ? $INFO['max_poll_choices'] : 10)
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить пользователям просмотр результатов опроса, без потери возможности голоса?</b>" ,
										  $SKIN->form_yes_no( 'allow_result_view', $INFO['allow_result_view'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>ОТКЛЮЧИТЬ пользователям, возможность ответа в темах-опросах, созданных 'только для голосов'?</b>" ,
										  $SKIN->form_yes_no( 'poll_disable_noreply', $INFO['poll_disable_noreply'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Кол-во часов для автора темы, в течение которых, он сможет добавить опрос к своей теме</b><br>Для администраторов и супермодераторов, ограничений нет" ,
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
		
		$this->common_header('dosecure', 'Безопасность', 'Ниже, Вы можете отредактировать настройки уровня безопасности форума');
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Безопасность (Флуд-контроль против скриптов/ботов)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить при регистрации флуд-контроль против скриптов/ботов?</b><br>Пользователи при регистрации должны будут вводить сгенерированный форумом код, для предохранения от спама.".$SKIN->js_help_link('s_reg_antispam') ,
										  $SKIN->form_dropdown( "bot_antispam", 
										  						array(
										  								0 => array( '0'    , 'Не использовать'                  ),
										  								1 => array( 'gd'   , 'Расширенный (Требуется установка GD)'    ),
										  								2 => array( 'gif'  , 'Нормальный (Ничего не требуется)'  ),
										  								
										  							 ),
										  					    $INFO['bot_antispam']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>При использовании GD; Установите [ДА], для TTF метода, или [НЕТ] - для простого метода?</b><br />TTF метод является красивым методом" ,
										  $SKIN->form_yes_no( "use_ttf", isset($INFO['use_ttf']) ? $INFO['use_ttf'] : 1 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>При использовании GD и TTF; ШИРИНА изображения</b>" ,
										  $SKIN->form_input( "gd_width", isset($INFO['gd_width']) ? $INFO['gd_width'] : 250 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>При использовании GD и TTF; ВЫСОТА изображения</b>" ,
										  $SKIN->form_input( "gd_height", isset($INFO['gd_height']) ? $INFO['gd_height'] : 70 )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>При использовании GD и TTF; Путь к используемому файлу .ttf</b>" ,
										  $SKIN->form_input( "gd_font", isset($INFO['gd_font']) ? $INFO['gd_font'] : getcwd().'/fonts/progbot.ttf' )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Безопасность (Высокая)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить динамические изображения?</b><br>При выборе 'да' пользователи смогут использовать сгенерированные скрипты в тэге изображения" ,
										  $SKIN->form_yes_no( "allow_dynamic_img", $INFO['allow_dynamic_img'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Продолжительность сессии (в секундах)</b><br>Неактивные сессии будут удалены по истечении времени, которое Вы установите" ,
										  $SKIN->form_input( "session_expiration", $INFO['session_expiration'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Проверять соответствие браузеров пользователей?</b>" ,
										  $SKIN->form_yes_no( "match_browser", $INFO['match_browser'] )
								 )      );
								 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Безопасность (Средняя)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------
		
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Безопасная mail форма для отправки писем пользователям?</b><br>Скрытие email адресов пользователей" ,
										  $SKIN->form_yes_no( "use_mail_form", $INFO['use_mail_form'] )
								 )      );
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Безопасность (Низкая)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------						 
								 					 
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить картинки в сообщениях?</b><br>Некоторые программисты могут загружать скрипты в качестве картинок. IBF лишает их воспользоваться этим." ,
										  $SKIN->form_yes_no( "allow_images", $INFO['allow_images'] )
								 )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Использовать flash ролики в сообщениях и в аватарах?</b><br>Flash создаётся на языке скриптов, которые могут скомпрометировать безопасность" ,
										  $SKIN->form_yes_no( "allow_flash", $INFO['allow_flash'] )
								 )      );	
								 
								 
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Безопасность (Против злоумышленников)', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------						 					 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить использование существующих e-mail адресов при регистрации?</b><br>При выборе нет, будет производиться проверка на существующие e-mail адреса" ,
										  $SKIN->form_yes_no( "allow_dup_email", $INFO['allow_dup_email'] )
								 )      );
		
						 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Подтверждать новые регистрации через e-mail?</b><br>Используйте подтверждение администратора или подтверждение через e-mail" ,
										  $SKIN->form_dropdown( "reg_auth_type", 
										  						array(
										  								0 => array( 'user' , 'Подтверждение по e-mail' ),
										  								1 => array( 'admin', 'Подтверждение админом'      ),
										  								2 => array( '0'    , 'Не использовать'                  )
										  							 ),
										  					    $INFO['reg_auth_type']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удалять не подтвердивших регистрацию пользователей, через...</b>" ,
										  $SKIN->form_simple_input( 'validate_day_prune', $INFO['validate_day_prune'], 3 ). "... дней"
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Уведомлять Администратора по e-mail о новых пользователях?</b>" ,
										  $SKIN->form_dropdown( "new_reg_notify", 
										  						array(
										  								0 => array( '1' , 'Да' ),
										  								1 => array( '0' , 'Нет'  )
										  							 ),
										  					    $INFO['new_reg_notify']
										  					  )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить принудительную регистрацию для гостей, перед допуском на форум?</b>" ,
										  $SKIN->form_yes_no( "force_login", $INFO['force_login'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запретить новые регистрации?</b>" ,
										  $SKIN->form_yes_no( "no_reg", $INFO['no_reg'] )
								 )      );
							 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Включить при регистрации только определённые символы для имён? (цифры, английские и русские буквы)</b>" ,
										  $SKIN->form_yes_no( "reg_chars", $INFO['reg_chars'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удалять вводимые chr(0xCA)?</b><br />Может быть использовно как 'скрытые' пробелы, для эмуляции зарегистрированных имён - но могут появиться проблемы при национальных символах.<br>Например, для русского языка, при включении этой функции, в сообщениях, не прописывается большая буква К" ,
										  $SKIN->form_yes_no( 'strip_space_chr', $INFO['strip_space_chr'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить функцию 'Сообщить модератору'?</b>" ,
										  $SKIN->form_yes_no( "disable_reportpost", $INFO['disable_reportpost'] )
								 )      ); // 
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Флуд контроль (в секундах)</b><br>Пользователи должны будут подождать, перед отправкой повторного сообщения<br>Не заполняйте это поле, если не хотите использовать флуд-контроль" ,
										  $SKIN->form_input( "flood_control", $INFO['flood_control'] )
								 )      );
		
		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Приватность', 'left', 'catrow2' );
		
		//-----------------------------------------------------------------------------------------------------------	
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Разрешить просмотр пользователями списка 'Кто в онлайне'?</b>" ,
										  $SKIN->form_yes_no( "allow_online_list", $INFO['allow_online_list'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Запретить Администраторам, видеть скрытых пользователей</b></br>У скрытых пользователей, после имени будет отображаться звёздочка" ,
										  $SKIN->form_yes_no( "disable_admin_anon", $INFO['disable_admin_anon'] )
								 )      );
								 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Отключить просмотр администраторами IP адресов пользователей в списке 'Кто в онлайне'?</b>" ,
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
	
		$this->common_header('docookie', 'Cookies', 'Здесь можете ничего не редактировать. Или можете просто поэкспериментировать с этими настройками и правильно настроиться на установки Вашего хоста');
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Cookie домена</b><br>Подсказка: используйте <b>.your-domain.com</b> для основных cookies" ,
										  $SKIN->form_input( "cookie_domain", $INFO['cookie_domain'] )
								 )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название Cookie</b><br>Допускает использование нескольких форумов на одном хосте." ,
										  $SKIN->form_input( "cookie_id", $INFO['cookie_id'] )
								 )      );
 
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Путь Cookie</b><br>Относительный путь домена до корневой директории IPB" ,
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
		
		$ADMIN->save_log("Обновление настроек форума, Back Up создан");
		
		$ADMIN->done_screen("Настройки форума обновлены", "Главная страница Админцентра", "act=index" );
		
		
		
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
		
		$ADMIN->page_detail = $extra . "Обязательно проверяйте введённые Вами данные, перед их сохранением";
		$ADMIN->page_title  = "Настройки форума ($section)";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $formcode ),
												  2 => array( 'act'   , 'op'      ),
									     )      );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "{none}"  , "40%" );
		$SKIN->td_header[] = array( "{none}"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Настройки" );
		
	}

	//-------------------------------------------------------------
	//
	// Common footer: Saves writing the same stuff out over and over
	//
	//--------------------------------------------------------------
	
	function common_footer( $button="Сохранить изменения" )
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
<span id='large'>Автоматическое создание полнотекстовой индексации, невозможно</span>
<br /><br />
У Вас слишком много сообщений, для создания полнотекстовой индексации. Скорее всего, PHP не успеет завершить индексацию из-за установок ограничении 
во времени выполнения скриптов, что может вызвать у Вас неправильную индексацию.
<br />
Создание полнотекстовой индексации, является очень медленным процессом, но в то же время это очень полезная функция, 
как для экономии времени пользователей при поиске, так и для экономии Ваших ресурсов.
<br />
В среднем, нормальный вебсервер, проиндексирует около 80.000 сообщений в час, но это в лучшем случае.
А если у Вас MySQL 4.0.12+ это время немного уменьшится.
<br />
<br />
<strong style='color:red;font-size:14px'>Как самостоятельно создать индексацию</strong>
<br />
Если Вы имеете shell (SSH / Telnet) доступ к mysql, то этот процесс, очень прост. Если у Вас нет shell доступа, обратитесь к Вашему
хостеру с просьбой, чтобы они это сделали за Вас.
<br /><br />
<strong>Шаг 1: Инициализация mysql</strong>
<br />
В shell введите:
<br />
<pre>mysql -u{your_sql_user_name} -p{your_sql_password}</pre>
<br />
Ваш имя пользователя и пароль MySQL, Вы можете взять из Вашего файла conf_global.php
<br />
<br />
<strong>Шаг 2: Выберите базу данных</strong>
<br />
В mysql введите:
<br />
<pre>use {your_database_name_here};</pre>
<br />
Не забудьте поставить в конце этой команды точку с запятой. Имя Вашей MySQL базы, Вы можете взять из Вашего файла conf_global.php
<br /><br />
<strong>Шаг 3: Индексация таблицы тем</strong>
<br />
В mysql введите:
<br />
<pre>\g alter table ibf_topics add fulltext(title);</pre>
<br />
Если Вы НЕ используете префикс таблиц 'ibf_' , отредактируйте префикс в этом запросе, на Ваш префикс. На выполнение этого запроса, может потребоваться какое-то кол-во времени, 
в зависимости от кол-ва тем, на Вашем форуме.
<br />
<br />
<strong>Шаг 4: Индексация таблицы сообщений</strong>
<br />
В mysql введите:
<br />
<pre>\g alter table ibf_posts add fulltext(post);</pre>
<br />
Если Вы НЕ используете префикс таблиц 'ibf_' , отредактируйте префикс в этом запросе, на Ваш префикс. На выполнение этого запроса, может потребоваться какое-то кол-во времени, 
в зависимости от кол-ва тем, на Вашем форуме. В среднем, MySQL индексирует около 80,000 сообщений в час. А если у Вас установлена MySQL 4, то это время намного уменьшится.
</div>
";
	}
}


?>