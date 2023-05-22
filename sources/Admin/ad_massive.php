<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.3
|   ========================================
|   by Killer
|   (c) 2001 - 2003 Invision Power Services
|   http://www.ipbr-fr.com
|   ========================================
|   Web: http://www.ipbr-fr.com
|   Email: k_i_l_l_e_r4@hotmail.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Admin Member Gestion
|   > Module written by Killer
|   > Date started: 15th november 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}



$idx = new ad_massive();


class ad_massive {

	var $base_url;

	function ad_massive() {
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
			case 'remove':
				$this->remove_member();
				break;
			case 'edit_massive':
				$this->edit_massive();
				break;
			case 'update_bdd':
				$this->update_info();
				break;
			default:
				$this->list_members();
				break;
		}
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Display all the members in a list
	//
	//+---------------------------------------------------------------------------------
	
	function list_members() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		
		$ADMIN->page_title = "Список пользователей";
		
		$ADMIN->page_detail = "Здесь Вы можете производить массовое редактирование нескольких пользователей. Этот список пользователей отсортирован по дате последнего сообщения пользователей.";
		
		//+-------------------------------
		
		$pages = $std->build_pagelinks( array( 'TOTAL_POSS'  => $count['count'],
											   'PER_PAGE'    => 50,
											   'CUR_ST_VAL'  => $IN['st'],
											   'L_SINGLE'    => $un_all."Единственная страница",
											   'L_MULTI'     => $un_all."Несколько страниц",
											   'BASE_URL'    => $SKIN->base_url."&act=mem&showsusp={$IN['showsusp']}&code={$IN['code']}".$page_query,
											 )
									  );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'edit_massive'  ),
												  2 => array( 'act'   , 'massive'     ),
												  3 => array( 'method', 'get'         ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "ID"  , "5%" );
		$SKIN->td_header[] = array( "Имя"        , "30%" );
		$SKIN->td_header[] = array( "Рег-ция"        , "15%" );
		$SKIN->td_header[] = array( "Посл.сообщ."        , "15%" );
		$SKIN->td_header[] = array( "Нет сообщ. за посл."        , "15%" );
		$SKIN->td_header[] = array( "Редактировать"        , "8%" );
		$SKIN->td_header[] = array( "Удалить"        , "10%" );
		$SKIN->td_header[] = array( "Отметить"        , "2%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Ваши пользователи" );
		
		$DB->query("SELECT * FROM ibf_members ORDER BY last_post DESC");
		
		$datoday = time();

		while ( $r = $DB->fetch_row() )
		{
			if ( $r['joined'] <> 0 ) {
				$a[0] = "<center>".$r['id']."</center>";
				$a[1] = $r['name'];
				$a[2] = "<center>".$std->get_date( $r['joined'], 'JOINED' )."</center>";
				if ( $r['last_post'] == 0 ) {
					$substract = $datoday - $r['joined'];
					$days = number_format($substract / 86400,0,",","");
					$min = number_format($hoursm / 60,0,",","");
					$a[3] = "<center><i>Не имеется</i></center>";
					$a[4] = "<center>".$days." дней</center>";
				}
				else {
					$substract = $datoday - $r['last_post'];
					$days = number_format($substract / 86400,0,",","");
					$a[3] = "<center>".$std->get_date( $r['last_post'], 'JOINED' )."</center>";
					$a[4] = "<center>".$days." дней</center>";
				}
				$a[5] = "<center><strong><a href='{$SKIN->base_url}&act=mem&code=doform&MEMBER_ID={$r['id']}' title='Отредактировать данные этого пользователя'>Редактировать</a></strong></center>";
				$a[6] = "<center><a href='{$SKIN->base_url}&act=massive&code=remove&mid={$r['id']}' title='Удалить этого пользователя'>Удалить</a></span></center>";
				$a[7] = $SKIN->form_checkbox($r['name'], 0);

				$ADMIN->html .= $SKIN->add_td_row($a);
			}
			
		}
		
		$ADMIN->html .= $SKIN->end_form("Редактировать выбранных пользователей");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Remove an account
	//
	//+---------------------------------------------------------------------------------
	
	function remove_member() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$DB->query("DELETE FROM ibf_members WHERE id='".$IN['mid']."'");
		
		$ADMIN->done_screen("Пользователь удалён", "Управление пользователями", "act=massive" );
		
		$ADMIN->output();
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Massive edition for members
	//
	//+---------------------------------------------------------------------------------
	
	function edit_massive() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Массовое редактирование пользователей";
		
		$ADMIN->page_detail = "Вы можете редактировать данные аккаунта Ваших пользователей.";
		
		//+-------------------------------
		
		$pages = $std->build_pagelinks( array( 'TOTAL_POSS'  => $count['count'],
											   'PER_PAGE'    => 50,
											   'CUR_ST_VAL'  => $IN['st'],
											   'L_SINGLE'    => $un_all."Единственная страница",
											   'L_MULTI'     => $un_all."Несколько страниц",
											   'BASE_URL'    => $SKIN->base_url."&act=mem&showsusp={$IN['showsusp']}&code={$IN['code']}".$page_query,
											 )
									  );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'update_bdd'  ),
												  2 => array( 'act'   , 'massive'     ),
											) );
											
		//+-------------------------------
		
		$SKIN->td_header[] = array( "Имя"  , "15%" );
		$SKIN->td_header[] = array( "Рейтинг"  , "5%" );
		$SKIN->td_header[] = array( "Назв.статуса"        , "10%" );
		$SKIN->td_header[] = array( "Группа"  , "5%" );
		$SKIN->td_header[] = array( "Язык"        , "10%" );
		$SKIN->td_header[] = array( "Скин"  , "10%" );
		$SKIN->td_header[] = array( "E-mail"        , "5%" );
		$SKIN->td_header[] = array( "Сайт"  , "5%" );
		$SKIN->td_header[] = array( "Аватар"        , "5%" );
		$SKIN->td_header[] = array( "Сообщений"  , "5%" );
		$SKIN->td_header[] = array( "Место жит-ва"        , "10%" );
		$SKIN->td_header[] = array( "Увлечения"  , "5%" );
		$SKIN->td_header[] = array( "Подпись"        , "5%" );
		$SKIN->td_header[] = array( "&nbsp;"        , "5%" );
		
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Вы выбрали следующих пользователей" );
		
		$DB->query("SELECT id,name,warn_level,title,mgroup,language,skin,email,website,avatar,posts,location,interests,signature FROM ibf_members");
		
		while ( $ro = $DB->fetch_row() )
		{
			if ( $IN[$ro['name']] == "1" ) {
				$line[0] = $ro['name'];
				$SKIN->form_hidden($ro['name']);
				if ( $ro['title'] == "" ) {
					$line[1] = "0";
				}
				else {
					$line[1] = $ro['warn_level'];
				}
				if ( $ro['title'] == "" ) {
					$line[2] = "<i>Нет</i>";
				}
				else {
					$line[2] = $ro['title'];
				}
				$line[3] = $ro['mgroup'];
				if ( $ro['language'] == "" ) {
					$line[4] = "<i>По умолч.</i>";
				}
				else {
					$line[4] = $ro['language'];
				}
				if ( $ro['skin'] == "" ) {
					$line[5] = "<i>По умолч.</i>";
				}
				else {
					$line[5] = $ro['skin'];
				}
				if ( $ro['email'] == "" ) {
					$line[6] = "<i>Нет</i>";
				}
				else {
					$line[6] = "<a href='mailto:".$ro['email']."'>Да</a>";
				}
				if ( $ro['website'] == "" ) {
					$line[7] = "<i>Нет</i>";
				}
				else {
					$line[7] = "<a href='".$ro['title']."'>Да</a>";
				}
				if ( $ro['avatar'] == "" ) {
					$line[8] = "<i>Нет</i>";
				}
				else {
					$line[8] = "Да";
				}
				$line[9] = $ro['posts'];
				if ( $ro['location'] == "" ) {
					$line[10] = "<i>Нет</i>";
				}
				else {
					$line[10] = $ro['location'];
				}
				if ( $ro['interests'] == "" ) {
					$line[11] = "<i>Нет</i>";
				}
				else {
					$line[11] = "Да";
				}
				if ( $ro['signature'] == "" ) {
					$line[12] = "<i>Нет</i>";
				}
				else {
					$line[12] = "Да";
				}
				$line[13] = "<input type='checkbox' name='".$ro['id']."' checked>";
				$ADMIN->html .= $SKIN->add_td_row($line);
				
			}
		}
											 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"        , "60%" );
		
		//+-------------------------------
		
		$mem_group[0] = array( '0', 'Не изменять' );
		
		$DB->query("SELECT g_id, g_title FROM ibf_groups ORDER BY g_title");
		
		while ( $r = $DB->fetch_row() )
		{
			$mem_group[] = array( $r['g_id'] , $r['g_title'] );
		}
		
		$lang_array[0] = array( '0', 'Не изменять' );
		
		$DB->query("SELECT ldir, lname FROM ibf_languages");
		
		while ( $l = $DB->fetch_row() )
		{
			$lang_array[] = array( $l['ldir'], $l['lname'] );
		}
		
		$DB->query("SELECT sid,sname FROM ibf_skins");
 		
 		$skin_array[0] = array( '0', 'Не изменять' );
		
			while ( $s = $DB->fetch_row() )
			{
				
				$skin_array[] = array( $s['sid'], $s['sname'] );
			   
			}
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Настройки безопасности" );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Уровень рейтинга</b><br>Оставьте поле пустым, если изменения не требуются." ,
												  $SKIN->form_input("warn_level", "")
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название статуса</b><br>Оставьте поле пустым, если изменения не требуются." ,
												  $SKIN->form_input("title", "")
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Группа пользователя</b>" ,
													  $SKIN->form_dropdown( "mgroup",
																			$mem_group,
																			$mem['mgroup']
																		  )
											 )      );
											 
		$ADMIN->html .= $SKIN->end_table();

		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Пароль" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Новый пароль</b><br>Не заполняйте это поле, если не хотите изменять пароль." ,
												  $SKIN->form_input("password")
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "Настройки форума" );							     
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите язык</b>" ,
												  $SKIN->form_dropdown( "language",
																		$lang_array,
												  						$mem['language'] != "" ? $mem['language'] : $INFO['default_language']
												  					  )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Выберите скин</b>" ,
												  $SKIN->form_dropdown( "skin",
																		$skin_array,
												  						$mem['skin'] != "" ? $mem['skin'] : $def_skin
												  					  )
									     )      );	
		
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "Контактная информация" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>E-mail адрес</b>" ,
												  $SKIN->form_input("email", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Имя в AIM</b>" ,
												  $SKIN->form_input("aim_name", "")
									     )      );							     						     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Номер ICQ</b>" ,
												  $SKIN->form_input("icq_number", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Имя в Yahoo</b>" ,
												  $SKIN->form_input("yahoo", "")
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Имя в MSN</b>" ,
												  $SKIN->form_input("msnname", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Адрес сайта</b>" ,
												  $SKIN->form_input("website", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------+
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------+
		
		$ADMIN->html .= $SKIN->start_table( "Прочая информация" );
									     							     							     
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удалить аватар ?</b>" ,
												  $SKIN->form_yes_no("avatar")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Счётчик сообщений</b>" ,
												  $SKIN->form_input("posts", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Место жительства</b>" ,
												  $SKIN->form_input("location", "")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Увлечения</b>" ,
												  $SKIN->form_textarea("interests", "")
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Подпись</b>" ,
												  $SKIN->form_textarea("signature", "")
									     )      );
		
		$ADMIN->html .= $SKIN->end_form("Отредактировать информацию");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
	}
	
	//+---------------------------------------------------------------------------------
	//
	// Massive edition for members
	//
	//+---------------------------------------------------------------------------------
	
	function update_info() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$i = 0;
		$clause = "";
		$DB->query("SELECT id FROM ibf_members");
		
		while ( $me = $DB->fetch_row() )
		{
			if ( strlen($IN[$me['id']]) ) {
				if ( $clause == "" ) {
					$clause = " WHERE id='".$me['id']."'";
				}
				else {
					$clause .= " OR id='".$me['id']."'";
				}
				$i++;
			}
		}
		
		if ($i == 0) {
			$ADMIN->error("Вы не указали ни одного пользователя, для редактирования. Выберите хотя бы одного пользователя.");
		}
		
		$req = "UPDATE ibf_members SET";
		$i = 0;
		if ( $IN['warn_level'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
				$req .= " warn_level='".$IN['warn_level']."'";
			}
			else {
				$req .= ", warn_level='".$IN['warn_level']."'";
			}
			$i = "1";
		}
		if ( $IN['title'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " title='".$IN['title']."'";
			}
			else {
			$req .= ", title='".$IN['title']."'";
			}
			$i = "1";
		}
		if ( $IN['mgroup'] <> "0" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " mgroup='".$IN['mgroup']."'";
			}
			else {
			$req .= ", mgroup='".$IN['mgroup']."'";
			}
			$i = "1";
		}
		if ( $IN['password'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " password='".md5($IN['password'])."'";
			}
			else {
			$req .= ", password='".md5($IN['password'])."'";
			}
			$i = "1";
		}
		if ( $IN['language'] <> "0" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " language='".$IN['language']."'";
			}
			else {
			$req .= ", language='".$IN['language']."'";
			}
			$i = "1";
		}
		if ( $IN['skin'] <> "0" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " skin='".$IN['skin']."'";
			}
			else {
			$req .= ", skin='".$IN['skin']."'";
			}
			$i = "1";
		}
		if ( $IN['email'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " email='".$IN['email']."'";
			}
			else {
			$req .= ", email='".$IN['email']."'";
			}
			$i = "1";
		}
		if ( $IN['aim_name'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " aim_name='".$IN['aim_name']."'";
			}
			else {
			$req .= ", aim_name='".$IN['aim_name']."'";
			}
			$i = "1";
		}
		if ( $IN['icq_number'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " icq_number='".$IN['icq_number']."'";
			}
			else {
			$req .= ", icq_number='".$IN['icq_number']."'";
			}
			$i = "1";
		}
		if ( $IN['yahoo'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " yahoo='".$IN['yahoo']."'";
			}
			else {
			$req .= ", yahoo='".$IN['yahoo']."'";
			}
			$i = "1";
		}
		if ( $IN['msnname'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " msnname='".$IN['msnname']."'";
			}
			else {
			$req .= ", msnname='".$IN['msnname']."'";
			}
			$i = "1";
		}
		if ( $IN['website'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " website='".$IN['website']."'";
			}
			else {
			$req .= ", website='".$IN['website']."'";
			}
			$i = "1";
		}
		if ( $IN['avatar'] <> "0" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " avatar='0'";
			}
			else {
			$req .= ", avatar='0'";
			}
			$i = "1";
		}
		if ( $IN['posts'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " posts='".$IN['posts']."'";
			}
			else {
			$req .= ", posts='".$IN['posts']."'";
			}
			$i = "1";
		}
		if ( $IN['location'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " location='".$IN['lacation']."'";
			}
			else {
			$req .= ", location='".$IN['lacation']."'";
			}
			$i = "1";
		}
		if ( $IN['interests'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " interests='".$IN['interests']."'";
			}
			else {
			$req .= ", interests='".$IN['interests']."'";
			}
			$i = "1";
		}
		if ( $IN['signature'] <> "" ) {
			if ($req == "UPDATE ibf_members SET") {
			$req .= " signature='".$IN['signature']."'";
			}
			else {
			$req .= ", signature='".$IN['signature']."'";
			}
			$i = "1";
		}
		
		$requete = $req.$clause;
		if ($i == 0) {
			$ADMIN->error("Необходимо заполнить форму. Измените хотя бы что-то одно.");
		}
		$DB->query($requete);
		
		$ADMIN->done_screen("Редактирование произведено", "Управление пользователями", "act=massive" );
		
		$ADMIN->output();
	}
}


?>