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
|   > Email Logs Stuff
|   > Module written by Matt Mecham
|   > Date started: 11nd September 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}


$idx = new ad_emaillogs();


class ad_emaillogs
{

	var $base_url;
	var $colours = array();

	function ad_emaillogs()
	{
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
			$ADMIN->error("Эти функции только для Администраторов");
		}
		

		switch($IN['code'])
		{
		
			case 'list':
				$this->list_current();
				break;
				
			case 'remove':
				$this->remove_entries();
				break;
				
		    case 'viewemail':
		    	$this->view_email();
		    	break;
				
				
			//-------------------------
			default:
				$this->list_current();
				break;
		}
		
	}
	
	//---------------------------------------------
	// View a single email.
	//---------------------------------------------
	
	function view_email()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID email, попробуйте снова");
		}
		
		$id = intval($IN['id']);
		
		$DB->query("SELECT email.*, m.id, m.name, mem.id as to_id, mem.name as to_name FROM ibf_email_logs email
					 LEFT JOIN ibf_members m ON (m.id=email.from_member_id)
					 LEFT JOIN ibf_members mem ON (mem.id=email.to_member_id)
				    WHERE email.email_id=$id");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить ID email, попробуйте снова ($id)");
		}
		
		$SKIN->td_header[] = array( "&nbsp;" , "100%" );
		
		$ADMIN->html .= $SKIN->start_table( $row['email_subject'] );
	
		
		
		$row['email_date'] = $ADMIN->get_date( $row['email_date'], 'LONG' );
		
		$ADMIN->html .= $SKIN->add_td_row( array(
													"<strong>От:</strong> {$row['name']} &lt;{$row['from_email_address']}&gt;
													<br /><strong>Для:</strong> {$row['to_name']} &lt;{$row['to_email_address']}&gt;
													<br /><strong>Отправлено:</strong> {$row['email_date']}
													<br /><strong>С IP адреса:</strong> {$row['from_ip_address']}
													<br /><strong>Заголовок:</strong> {$row['email_subject']}
													<hr>
													<br />{$row['email_content']}
												    "
										 )      );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->print_popup();
	}
	

	
	//---------------------------------------------
	// Remove row(s)
	//---------------------------------------------
	
	function remove_entries()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ( $IN['type'] == 'all' )
		{
			$DB->query("DELETE FROM ibf_email_logs");
		}
		else
		{
			$ids = array();
		
			foreach ($IN as $k => $v)
			{
				if ( preg_match( "/^id_(\d+)$/", $k, $match ) )
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
				$ADMIN->error("Вы не выбрали ни одного e-mail лога для проверки или удаления");
			}
			
			$DB->query("DELETE FROM ibf_email_logs WHERE email_id IN (".implode(',', $ids ).")");
		}
		
		$ADMIN->save_log("Удаление e-mail логов");
		
		$std->boink_it($ADMIN->base_url."&act=emaillog");
		exit();
	
	
	}
	
	

	
	
	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------
	
	function list_current()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->html .= $SKIN->js_pop_win();
		
		$form_array = array();
		
		$start = intval($IN['st']);
	
		$ADMIN->page_detail = "Сохранённые e-mail логи";
		$ADMIN->page_title  = "Менеджер e-mail логов";
		
		//+-------------------------------
		// Check URL parameters
		//+-------------------------------
		
		$url_query = array();
		$db_query  = array();
		
		if ( $IN['type'] != "" )
		{
			$ADMIN->page_title .= " (Результаты поиска)";
		
			switch( $IN['type'] )
			{
				case 'fromid':
					$url_query[] = 'type=fromid';
					$url_query[] = 'id='.intval($IN['id']);
					$db_query[]  = 'email.from_member_id='.intval($IN['id']);
					break;
				case 'toid':
					$url_query[] = 'type=toid';
					$url_query[] = 'id='.intval($IN['id']);
					$db_query[]  = 'email.to_member_id='.intval($IN['id']);
					break;
				case 'subject':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					$db_query[]  = $IN['match'] == 'loose' ? "email.email_subject LIKE '%{$string}%'" : "email.email_subject='{$string}'";
					break;
				case 'content':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					$db_query[]  = $IN['match'] == 'loose' ? "email.email_content LIKE '%{$string}%'" : "email.email_content='{$string}'";
					break;
				case 'email_from':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					$db_query[]  = $IN['match'] == 'loose' ? "email.from_email_address LIKE '%{$string}%'" : "email.from_email_address='{$string}'";
					break;
				case 'email_to':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					$db_query[]  = $IN['match'] == 'loose' ? "email.to_email_address LIKE '%{$string}%'" : "email.to_email_address='{$string}'";
					break;
				case 'name_from':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					
					if ( $IN['match'] == 'loose' )
					{
						$DB->query("SELECT id,name FROM ibf_members WHERE name LIKE '%{$string}%'");
						
						if ( ! $DB->get_num_rows() )
						{
							$ADMIN->error("В e-mail логах ничего не найдено по Вашему запросу");
						}
						
						$ids = array();
						
						while ( $r = $DB->fetch_row() )
						{
							$ids[] = $r['id'];
						}
						
						$db_query[] = 'email.from_member_id IN('.implode( ',', $ids ).')';
					}
					else
					{
						$DB->query("SELECT id,name FROM ibf_members WHERE name='{$string}'");
						
						if ( ! $DB->get_num_rows() )
						{
							$ADMIN->error("В e-mail логах ничего не найдено по Вашему запросу");
						}
						
						$r = $DB->fetch_row();
						
						$db_query[] = 'email.from_member_id IN('.$r['id'].')';
					}
					
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					break;
				case 'name_to':
					$string = urldecode($IN['string']);
					if ( $string == "" )
					{
						$ADMIN->error("Вы должны ввести какие-нибудь данные для поиска");
					}
					
					if ( $IN['match'] == 'loose' )
					{
						$DB->query("SELECT id,name FROM ibf_members WHERE name LIKE '%{$string}%'");
						
						if ( ! $DB->get_num_rows() )
						{
							$ADMIN->error("В e-mail логах ничего не найдено по Вашему запросу");
						}
						
						$ids = array();
						
						while ( $r = $DB->fetch_row() )
						{
							$ids[] = $r['id'];
						}
						
						$db_query[] = 'email.to_member_id IN('.implode( ',', $ids ).')';
					}
					else
					{
						$DB->query("SELECT id,name FROM ibf_members WHERE name='{$string}'");
						
						if ( ! $DB->get_num_rows() )
						{
							$ADMIN->error("В e-mail логах ничего не найдено по Вашему запросу");
						}
						
						$r = $DB->fetch_row();
						
						$db_query[] = 'email.to_member_id IN('.$r['id'].')';
					}
					
					$url_query[] = 'type='.$IN['type'];
					$url_query[] = 'string='.urlencode($string);
					break;
				default:
					//
					break;
			}
		}
		
		//+-------------------------------
		// LIST 'EM
		//+-------------------------------
		
		$dbe = "";
		$url = "";
		
		if ( count($db_query) > 0 )
		{
			$dbe = ' WHERE '.implode(' AND ', $db_query );
		}
		
		if ( count($url_query) > 0 )
		{
			$url = '&'.implode( '&', $url_query);
		}
		
		$DB->query("SELECT count(email.email_id) as cnt FROM ibf_email_logs email".$dbe);
		
		$count = $DB->fetch_row();
		
		$links = $std->build_pagelinks( array( 'TOTAL_POSS'  => $count['cnt'],
											   'PER_PAGE'    => 25,
											   'CUR_ST_VAL'  => $start,
											   'L_SINGLE'    => "Единственная страница",
											   'L_MULTI'     => "Страниц: ",
											   'BASE_URL'    => $ADMIN->base_url.'&act=emaillog'.$url,
											 )
									  );
		
		
		$DB->query("SELECT email.*, m.id, m.name, mem.id as to_id, mem.name as to_name FROM ibf_email_logs email
					 LEFT JOIN ibf_members m ON (m.id=email.from_member_id)
					 LEFT JOIN ibf_members mem ON (mem.id=email.to_member_id) $dbe
					ORDER BY email_date DESC LIMIT $start,25");
					
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'remove'     ),
												  2 => array( 'act'   , 'emaillog'       ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"         , "5%" );
		$SKIN->td_header[] = array( "Отправитель"    , "20%" );
		$SKIN->td_header[] = array( "Заголовок"        , "30%" );
		$SKIN->td_header[] = array( "Получатель"      , "20%" );
		$SKIN->td_header[] = array( "Дата и время отправки"      , "25%" );

		$ADMIN->html .= $SKIN->start_table( "E-mail логи" );
		
		if ( $DB->get_num_rows() )
		{
			while ( $row = $DB->fetch_row() )
			{
			
				$row['email_date'] = $ADMIN->get_date( $row['email_date'], 'SHORT' );
				
				$ADMIN->html .= $SKIN->add_td_row( array( 
														  "<center><input type='checkbox' class='checkbox' name='id_{$row['email_id']}' value='1' /></center>",
														  "<a href='{$ADMIN->base_url}&act=emaillog&code=list&type=fromid&id={$row['id']}' title='Все логи данного пользователя'><img src='{$SKIN->img_url}/acp_search.gif' border='0' alt='..by id'></a>&nbsp;<b><a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$row['id']}' title='Профиль пользователя (в новом окне)' target='blank'>{$row['name']}</a></b>",
														  "<a href='javascript:pop_win(\"&act=emaillog&code=viewemail&id={$row['email_id']}\",400,400)' title='Прочитать письмо'>{$row['email_subject']}</a>",
														  "<a href='{$ADMIN->base_url}&act=emaillog&code=list&type=toid&id={$row['to_id']}' title='Обзор всех писем, отправленных данному пользователю'><img src='{$SKIN->img_url}/acp_search.gif' border='0' alt='..by id'></a>&nbsp;<a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?act=Profile&MID={$row['to_id']}'  title='Профиль пользователя (в новом окне)' target='blank'>{$row['to_name']}</a>",
														  "{$row['email_date']}",
												 )      );
			
			
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>Нет результатов</center>");
		}
		
		$ADMIN->html .= $SKIN->add_td_basic('<div style="float:left;width:auto"><input type="submit" value="Удалить выбранные" id="button" />&nbsp;<input type="checkbox" id="checkbox" name="type" value="все" />&nbsp;Удалить все?</div><div align="right">'.$links.'</div></form>', 'left', 'pformstrip');
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'list'     ),
												  2 => array( 'act'   , 'emaillog'       ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"  , "100%" );
		
		$ADMIN->html .= $SKIN->start_table( "Поиск e-mail логов" );
		
		$form_array = array(
							  0 => array( 'subject'    , 'В заголовках'    ),
							  1 => array( 'content'    , 'В тексте писем' ),
							  2 => array( 'email_from' , 'В e-mail адресах отправителя' ),
							  3 => array( 'email_to'   , 'В e-mail адресах получателя'   ),
							  4 => array( 'name_from'  , 'В имени отправителя'),
							  5 => array( 'name_to'    , 'В имени получателя' ),
						   );
						   
		$type_array = array(
							  0 => array( 'exact'      , 'с точным совпадением' ),
							  1 => array( 'loose'      , 'с содержанием'   ),
						   );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Где искать</b> &nbsp;"
												  . $SKIN->form_dropdown( "type", $form_array) ." "
												  . $SKIN->form_dropdown( "match", $type_array) ." "
												  . $SKIN->form_input( "string"),
										  		
								 )      );
								 
		$ADMIN->html .= $SKIN->end_form("Найти");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->nav[] = array( 'act=emaillog', 'E-mail логи (Показать все)' );
		
		$ADMIN->output();
	
	}
	
	
	
}


?>