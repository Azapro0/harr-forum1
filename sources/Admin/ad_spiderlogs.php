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
|   > Spider (MAN) Logs
|   > Module written by Matt Mecham
|   > Date started: 28th May 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}

$idx = new ad_spiderlogs();


class ad_spiderlogs {

	var $base_url;

	function ad_spiderlogs() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		$ADMIN->nav[] = array( 'act=spiderlog', 'Логи ботов поисковых двигателей' );
		
		//---------------------------------------

		switch($IN['code'])
		{
		
			case 'view':
				$this->view();
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
	// Remove archived files
	//---------------------------------------------
	
	function view()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$start = $IN['st'] ? $IN['st'] : 0;
		
		$ADMIN->page_detail = "Просмотр всех действий поискового двигателя";
		$ADMIN->page_title  = "Управление логами поискового двигателя";
		
		$botty = urldecode($IN['bid']);
	
		if ($IN['search_string'] == "")
		{
			$DB->query("SELECT COUNT(sid) as count FROM ibf_spider_logs WHERE bot='$botty'");
			
			$row = $DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&act=spiderlog&bid={$IN['bid']}&code=view";
			
			$DB->query("SELECT * FROM ibf_spider_logs WHERE bot='$botty' ORDER BY entry_date DESC LIMIT $start, 20");
			
		}
		else
		{
			$IN['search_string'] = urldecode($IN['search_string']);
		
			$DB->query("SELECT COUNT(sid) as count FROM ibf_spider_logs WHERE query_string LIKE '%{$IN['search_string']}%'");
			
			$row = $DB->fetch_row();
			
			$row_count = $row['count'];
			
			$query = "&act=spiderlog&code=view&search_string=".urlencode($IN['search_string']);
			
			$DB->query("SELECT * FROM ibf_spider_logs WHERE query_string LIKE '%{$IN['search_string']}%' ORDER BY entry_date DESC LIMIT $start, 20");
			
		
		
		}
		
		$links = $std->build_pagelinks( array( 'TOTAL_POSS'  => $row_count,
											   'PER_PAGE'    => 20,
											   'CUR_ST_VAL'  => $start,
											   'L_SINGLE'    => "Единственная страница",
											   'L_MULTI'     => "Страниц: ",
											   'BASE_URL'    => $ADMIN->base_url.$query,
											 )
									  );
									  
		$ADMIN->page_detail = "Вы можете просматривать и удалять действия, выполенные поисковым ботом";
		$ADMIN->page_title  = "Управление логами поискового двигателя";
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Имя бота"            , "15%" );
		$SKIN->td_header[] = array( "Строка запроса"        , "15%" );
		$SKIN->td_header[] = array( "Дата и время"      , "20%" );
		$SKIN->td_header[] = array( "IP адрес"          , "10%" );
		
		$ADMIN->html .= $SKIN->start_table( "Сохранённые логи поисковых двигателей" );
		$ADMIN->html .= $SKIN->add_td_basic($links, 'right', 'pformstrip');
		
		if ( $DB->get_num_rows() )
		{
			while ( $row = $DB->fetch_row() )
			{
			
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>".$INFO[ 'sp_'.$row['bot'] ]."</b>",
														  "<a href='{$INFO['board_url']}/index.{$INFO['php_ext']}?{$row['query_string']}' target='_blank'>{$row['query_string']}</a>",
														  $ADMIN->get_date( $row['entry_date'], 'LONG' ),
														  "{$row['ip_address']}",
												 )      );
			
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("<center>Нет результатов</center>");
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
	
		if ($IN['bid'] == "")
		{
			$ADMIN->error("Вы не выбрали бота, для удаления его логов");
		}
		
		$botty = urldecode($IN['bid']);
		
		$DB->query("DELETE FROM ibf_spider_logs WHERE bot='$botty'");
		
		$ADMIN->save_log("Удаление логов поискового двигателя");
		
		$std->boink_it($ADMIN->base_url."&act=spiderlog");
		exit();
	
	
	}
	
	

	
	
	//-------------------------------------------------------------
	// SHOW ALL LANGUAGE PACKS
	//-------------------------------------------------------------
	
	function list_current()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
	
		$ADMIN->page_detail = "Вы можете просматривать и удалять логи Ваших поисковых двигателей";
		$ADMIN->page_title  = "Управление логами поискового двигателя";

		//+-------------------------------
		
		$SKIN->td_header[] = array( "Имя бота"            , "20%" );
		$SKIN->td_header[] = array( "Хитов"                , "20%" );
		$SKIN->td_header[] = array( "Запущен"             , "20%" );
		$SKIN->td_header[] = array( "Просмотр всех действий"     , "20%" );
		$SKIN->td_header[] = array( "Удалить все действия"   , "20%" );
		
		$ADMIN->html .= $SKIN->start_table( "Сохранённые логи поисковых двигателей" );
		
		$DB->query("SELECT count(*) as cnt, bot, entry_date, query_string FROM `ibf_spider_logs` group by bot order by entry_date DESC");
		
		while ( $r = $DB->fetch_row() )
		{
		
			$url_butt = urlencode($r['bot']);
			
			$ADMIN->html .= $SKIN->add_td_row( array( $INFO[ 'sp_'.$r['bot'] ],
													  "<center>{$r['cnt']}</center>",
													   $ADMIN->get_date( $r['entry_date'], 'SHORT' ),
													  "<center><a href='".$SKIN->base_url."&act=spiderlog&code=view&bid={$url_butt}'>Просмотр</a></center>",
													  "<center><a href='".$SKIN->base_url."&act=spiderlog&code=remove&bid={$url_butt}'>Удалить</a></center>",
											 )      );
		}
			
		
		
		$ADMIN->html .= $SKIN->end_table();
		
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'view'     ),
												  2 => array( 'act'   , 'spiderlog'       ),
									     )      );
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "Поиск логов поискового двигателя" );
			
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Искать...</b>" ,
										  		  $SKIN->form_input( "search_string").'... в строке запроса'
								 )      );
		
		$ADMIN->html .= $SKIN->end_form("Найти");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	
	}
	
	
	
}


?>