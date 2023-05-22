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
|   > Custom profile field functions
|   > Module written by Matt Mecham
|   > Date started: 24th June 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>Неверное обращение</h1>Вы не можете обращаться непосредственно к этому файлу. Если Вы производили обновление, проверьте то, что Вы обновили и файл 'admin.php'.";
	exit();
}


$idx = new ad_fields();


class ad_fields {

	var $base_url;

	function ad_fields() {
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
			case 'add':
				$this->main_form('add');
				break;
				
			case 'doadd':
				$this->main_save('add');
				break;
				
			case 'edit':
				$this->main_form('edit');
				break;
				
			case 'doedit':
				$this->main_save('edit');
				break;
				
			case 'delete':
				$this->delete_form();
				break;
				
			case 'dodelete':
				$this->do_delete();
				break;
						
			default:
				$this->main_screen();
				break;
		}
		
	}
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// Delete a group
	//
	//+---------------------------------------------------------------------------------
	
	function delete_form()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID группы. Попробуйте снова.");
		}
		
		$ADMIN->page_title = "Удаление дополнительного поля профиля";
		
		$ADMIN->page_detail = "Убедитесь в том, что Вы действительно хотите удалить это поле из профиля, так как <b>все данные будут утеряны!</b>";
		
		
		//+-------------------------------
		
		$DB->query("SELECT ftitle, fid FROM ibf_pfields_data WHERE fid='".$IN['id']."'");
		
		if ( ! $field = $DB->fetch_row() )
		{
			$ADMIN->error("Неыозможно выбрать ряд из базы данных");
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
												  2 => array( 'act'   , 'field'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )      );
									     
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Подтверждение удаления" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Удаляемое дополнительное поле профиля</b>" ,
												  "<b>".$field['ftitle']."</b>",
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("Удалить дополнительное поле");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("Невозможно определить ID поля. Попробуйте снова.");
		}
		
		
		// Check to make sure that the relevant groups exist.
		
		$DB->query("SELECT ftitle, fid FROM ibf_pfields_data WHERE fid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("Невозможно определить ID удаляемого поля.");
		}
		
		$DB->query("ALTER TABLE ibf_pfields_content DROP field_{$row['fid']}");
		
		$DB->query("DELETE FROM ibf_pfields_data WHERE fid='".$IN['id']."'");
		
		$ADMIN->done_screen("Поле профиля удалено", "Настройка дополнительного поля профиля", "act=field" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Save changes to DB
	//
	//+---------------------------------------------------------------------------------
	
	function main_save($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		if ($IN['ftitle'] == "")
		{
			$ADMIN->error("Необходимо ввести название поля.");
		}
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Невозможно определить id поля");
			}
			
		}
		
		$content = "";
		
		if ($HTTP_POST_VARS['fcontent'] != "")
		{
			$content = str_replace( "\n", '|', str_replace( "\n\n", "\n", trim($HTTP_POST_VARS['fcontent']) ) );
		}
		
		$db_string = array( 'ftitle'    => $IN['ftitle'],
						    'fdesc'     => $IN['fdesc'],
						    'fcontent'  => stripslashes($content),
						    'ftype'     => $IN['ftype'],
						    'freq'      => $IN['freq'],
						    'fhide'     => $IN['fhide'],
						    'fmaxinput' => $IN['fmaxinput'],
						    'fedit'     => $IN['fedit'],
						    'forder'    => $IN['forder'],
						    'fshowreg'  => $IN['fshowreg'],
						  );
		
						  
		if ($type == 'edit')
		{
			$rstring = $DB->compile_db_update_string( $db_string );
			
			$DB->query("UPDATE ibf_pfields_data SET $rstring WHERE fid='".$IN['id']."'");
			
			$ADMIN->done_screen("Поле профиля отредактировано", "Настройка дополнительного поля профиля", "act=field" );
			
		}
		else
		{
			$rstring = $DB->compile_db_insert_string( $db_string );
			
			$DB->query("INSERT INTO ibf_pfields_data (" .$rstring['FIELD_NAMES']. ") VALUES (". $rstring['FIELD_VALUES'] .")");
			
			$new_id = $DB->get_insert_id();
			
			$DB->query("ALTER TABLE ibf_pfields_content ADD field_$new_id text default ''");
			
			$DB->query("OPTIMIZE TABLE ibf_pfields_content");
			
			$ADMIN->done_screen("Поле профиля создано", "Настройка дополнительного поля профиля", "act=field" );
		}
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// Add / edit group
	//
	//+---------------------------------------------------------------------------------
	
	function main_form($type='edit')
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("Нет группы с таким id в базе данных. Попробуйте снова.");
			}
			
			$form_code = 'doedit';
			$button    = 'Сохранить изменения';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = 'Создать поле';
		}
		
		if ($IN['id'] != "")
		{
			$DB->query("SELECT * FROM ibf_pfields_data WHERE fid='".$IN['id']."'");
			$fields = $DB->fetch_row();
		}
		else
		{
			$fields = array();
		}
		
		if ($type == 'edit')
		{
			$ADMIN->page_title = "Редактирование поля профиля ".$fields['ftitle'];
		}
		else
		{
			$ADMIN->page_title = 'Создание нового поля профиля';
			$fields['ftitle'] = '';
		}
		
		$ADMIN->page_detail = "Дважды проверьте введённые данные, перед подтверждением запроса.";
		
		
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $form_code  ),
												  2 => array( 'act'   , 'field'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )  );
									     
		$fields['fcontent'] = str_replace( '|', "\n", $fields['fcontent'] );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Настройки поля" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Название поля</b><br>Максимум 200 символов" ,
												  $SKIN->form_input("ftitle", $fields['ftitle'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Описание</b><br>Максимум 250 знаков<br>Можно использовать со статусом скрытое/обязательное" ,
												  $SKIN->form_input("fdesc", $fields['fdesc'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Тип поля</b>" ,
												  $SKIN->form_dropdown("ftype",
												  					   array(
												  					   			0 => array( 'text' , 'Текстовое поле' ),
												  					   			1 => array( 'drop' , 'Выпадающее меню' ),
												  					   			2 => array( 'area' , 'Текстовая область' ),
												  					   		),
												  					   $fields['ftype'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Максимальное кол-во символов (для текстового поля или области)</b>" ,
												  $SKIN->form_input("fmaxinput", $fields['fmaxinput'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Порядковый номер отображения (при редактировании и отображении) в численном значении - минимум 1." ,
												  $SKIN->form_input("forder", $fields['forder'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Содержимое (для выпадающего меню)</b><br>Одна установка на строку<br>Пример для поля 'Пол':<br>m=Мужчина<br>f=Женщина<br>u=Не сообщаю<br>Будет отображено так:<br><select name='pants'><option value='m'>Мужчина</option><option value='f'>Женщина</option><option value='u'>Не сообщаю</option></select><br>m,f или u загружено в базу данных. При отображении поля в профиле используется значение из пары (f=Женщина, отображается как 'Женщина')" ,
												  $SKIN->form_textarea("fcontent", $fields['fcontent'] )
									     )      );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Добавить это поле и на страницу регистрации?</b>" ,
												  $SKIN->form_yes_no("fshowreg", $fields["fshowreg"] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Сделать это поле обязательным для заполнения?</b><br>(Не будет действовать, если Вы назначите это поле скрытым)" ,
												  $SKIN->form_yes_no("freq", $fields['freq'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Скрыть это поле в профиле?</b><br>При выборе 'Да', только Администраторы и Супермодераторы смогут видеть это поле. А также пользователь сможет редактировать это поле." ,
												  $SKIN->form_yes_no("fhide", $fields['fhide'] )
									     )      );						     							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>Поле может быть отредактировано пользователем?</b><br>При выборе 'Нет', пользователь не сможет редактировать информацию этого поля и поле будет видно только Администраторам и Супермодераторам. И только администраторы смогут редактировать информацию этого поля через Админцентр." ,
												  $SKIN->form_yes_no("fedit", $fields['fedit'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form($button);
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}

	//+---------------------------------------------------------------------------------
	//
	// Show "Management Screen
	//
	//+---------------------------------------------------------------------------------
	
	function main_screen()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "Дополнительное поле профиля";
		
		$ADMIN->page_detail = "Дополнительные поля профиля используются для добавления дополнительных или обязательных полей, заполняемых при регистрации или редактировании профиля. Это полезно, если Вы хотите запомнить данные Ваших пользователей, которые отсутствуют в базе форума.";
		
		$ADMIN->page_detail .= "<br /><br /><strong>Использовать дополнительные поля профиля при отображении тем?</strong><br /><br />
								При включении этой функции (через Системные настройки -> Экономия CPU) Вы можете использовать поле
								в скине отображения тем.<br />Простое добавьте, как <strong>\$author[field_1]</strong> (или, как другую переменную в 'Topicview'), прямо в 'Тело сообщений',
								как Вам больше нравится";
		
		$SKIN->td_header[] = array( "Название поля"    , "20%" );
		$SKIN->td_header[] = array( "Тип"           , "10%" );
		$SKIN->td_header[] = array( "переменная TopicView" , "20%" );
		$SKIN->td_header[] = array( "ОБЯЗАТЕЛЬНОЕ"       , "10%" );
		$SKIN->td_header[] = array( "СКРЫТОЕ"         , "10%" );
		$SKIN->td_header[] = array( "ОТОБР. ПРИ РЕГИСТРАЦИИ"       , "10%" );
		$SKIN->td_header[] = array( "Редактировать"           , "10%" );
		$SKIN->td_header[] = array( "Удалить"         , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "Настройка дополнительных полей профиля" );
		
		$real_types = array( 'drop' => 'Выпадающее меню',
							 'area' => 'Текстовая область',
							 'text' => 'Текстовое поле',
						   );
		
		$DB->query("SELECT * FROM ibf_pfields_data");
		
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
			
				$hide   = '&nbsp;';
				$req    = '&nbsp;';
				$regi   = '&nbsp;';
				
				"<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=".$r['g_id']."'>Удалить</a></center>";
				
				//-----------------------------------
				if ($r['fhide'] == 1)
				{
					$hide = '<center><span style="color:red">Да</span></center>';
				}
				//-----------------------------------
				if ($r['freq'] == 1)
				{
					$req = '<center><span style="color:red">Да</span></center>';
				}
				
				if ($r['fshowreg'] == 1)
				{
					$regi = '<center><span style="color:red">Да</span></center>';
				}
				
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$r['ftitle']}</b>" ,
														  "<center>{$real_types[$r['ftype']]}</center>",
														  "<center>field_".$r['fid']."</center>",
														  $req,
														  $hide,
														  $regi,
														  "<center><a href='{$ADMIN->base_url}&act=field&code=edit&id=".$r['fid']."'>Редактировать</a></center>",
														  "<center><a href='{$ADMIN->base_url}&act=field&code=delete&id=".$r['fid']."'>Удалить</a></center>",
											 )      );
											 
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("Нет полей", "center", "pformstrip");
		}
		
		$ADMIN->html .= $SKIN->add_td_basic("<a href='{$ADMIN->base_url}&act=field&code=add' class='fauxbutton'>СОЗДАТЬ НОВОЕ ПОЛЕ</a></center>", "center", "pformstrip");

		$ADMIN->html .= $SKIN->end_table();
		
		
		$ADMIN->output();
		
		
	}
	
		
}


?>