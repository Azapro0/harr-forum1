<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.3 Final
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|
|   > Admin: Warning Functions
|   > Module written by Matt Mecham
|   > Date started: 23rd April 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}

$idx = new ad_warning();

$root_path = "";

class ad_warning {

	var $base_url;

	function ad_warning()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;
		
		//---------------------------------------
		// Kill globals - globals bad, Homer good.
		//---------------------------------------
		
		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );
		
		foreach ( $tmp_in as $k => $v )
		{
			unset($$k);
		}
		
		$ADMIN->nav[] = array( 'act=warn', 'Настройка рейтинга' );
		
		$ADMIN->page_detail = "В этой секции, Вы можете редактировать параметры рейтинга";
		$ADMIN->page_title  = "Настройка рейтинга пользователей";
		
		//---------------------------------------

		switch($IN['code'])
		{
			
			//---------------------
			default:
				$this->overview();
				break;
		}
		
	}
	
	
	//---------------------------------------------------------------
	//
	// Overview: show um.. overview.
	//
	//---------------------------------------------------------------
	
	function overview()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $ibforums;
		
		$unit_map = array(
						   'd'  => 'Day(s)' ,
						   'h'  => 'Hour(s)',
						 );
		
		$SKIN->td_header[] = array( "Уровень рейтинга"   , "20%" );
		$SKIN->td_header[] = array( "Детали"      , "80%" );
		
		$ADMIN->html .= $SKIN->start_table( "Текущие активные задачи" );
		
		$warns = array();
		
		$DB->query("SELECT * from ibf_warn_settings ORDER BY warn_level");
		
		while ( $r = $DB->fetch_row() )
		{
			$warns[ $r['warn_level'] ][] = $r;
		}
			
		if ( count($warns) > 0 )
		{
			foreach( $warns as $id => $data )
			{
				$tmp = "";
				
				$ban = "";
				$mod = "";
				$nop = "";
				
				if ( $data['warn_ban'] != "" )
				{
					if ( $data['warn_ban'] == 'p' )
					{
						$ban = "Постоянный бан";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_ban'] );
						
						$ban = "Временная блокировка для ".$val." ".$unit_map[$unit];
					}
				}
				
				if ( $data['warn_modq'] != "" )
				{
					if ( $data['warn_modq'] == 'p' )
					{
						$mod = "Постоянная модерация сообщений";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_modq'] );
						
						$mod = "Требование модерации сообщений для ".$val." ".$unit_map[$unit];
					}
				}
				
				if ( $data['warn_nopost'] != "" )
				{
					if ( $data['warn_nopost'] == 'p' )
					{
						$nop = "Постоянный запрет сообщений";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_nopost'] );
						
						$nop = "Запрет сообщений для ".$val." ".$unit_map[$unit];
					}
				}
				
				$html = "<table width='100%' cellpadding='4'>";
				
				if ( $ban != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>Бан</strong></td>
								<td width='30%'>$ban</td>
								<td width='25%'>Редактировать</td>
								<td width='25%'>Удалить</td>
							  </tr>";
				}
				
				if ( $mod != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>Модерация сообщений</strong></td>
								<td width='30%'>$mod</td>
								<td width='25%'>Редактировать</td>
								<td width='25%'>Удалить</td>
							  </tr>";
				}
				
				if ( $nop != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>Запрет сообщений</strong></td>
								<td width='30%'>$nop</td>
								<td width='25%'>Редактировать</td>
								<td width='25%'>Удалить</td>
							  </tr>";
				}
				
				$html .= "</table>";
				
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$warns['warn_level']}</b>",
														  $html
										 )      );
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_basic( 'Добавить новую задачу', 'center', 'pformstrip' );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	

		
		
	}
	
	
	
	
	
	
	
}

?>