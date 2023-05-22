<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|	Modified to work with Download System
|	by bfarber
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Custom download field functions
|   > Module written by Matt Mecham
|   > Extended by bfarber (http://bfarber.com | bfarber@bfarber.com)
|   > Date started: 24th June 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/




$idx = new ad_dfields();


class ad_dfields {

	var $base_url;

	function ad_dfields() {
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
			$ADMIN->error("���������� ���������� ID ������. ���������� �����.");
		}
		
		$ADMIN->page_title = "�������� ��������������� ����";
		
		$ADMIN->page_detail = "��������� �������� ��, ��� �� ����������� ������� ������������� �������� ����, �.�. <b>��� ������ ����� ���� ����� �������!</b>.";
		
		
		//+-------------------------------
		
		$DB->query("SELECT ftitle, fid FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		if ( ! $field = $DB->fetch_row() )
		{
			$ADMIN->error("���������� �������� ��� � ���� ������");
		}
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelete'  ),
												  2 => array( 'act'   , 'dfield'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )      );
									     
		
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "������������� ��������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������������� ����</b>" ,
												  "<b>".$field['ftitle']."</b>",
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("������� ��� ����");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}
	
	function do_delete()
	{
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['id'] == "")
		{
			$ADMIN->error("���������� ���������� ID ����. ���������� �����.");
		}
		
		
		// Check to make sure that the relevant groups exist.
		
		$DB->query("SELECT ftitle, fid FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		if ( ! $row = $DB->fetch_row() )
		{
			$ADMIN->error("���������� ���������� ID ���������� ����.");
		}
		
		$DB->query("ALTER TABLE ibf_files_custentered DROP field_{$row['fid']}");
		
		$DB->query("DELETE FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
		
		$ADMIN->done_screen("�������������� ���� ������ �������", "�������������� ���� ������", "act=dfield" );
		
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
			$ADMIN->error("���������� ������ �������� ����.");
		}
		
		if ($type == 'edit')
		{
			if ($IN['id'] == "")
			{
				$ADMIN->error("���������� ���������� id ����");
			}
			
		}
		
		$content = "";
		
		if ($HTTP_POST_VARS['fcontent'] != "")
		{
			$content = str_replace( "\n", '|', str_replace( "\n\n", "\n", trim($HTTP_POST_VARS['fcontent']) ) );
		}
		
		$db_string = array( 'ftitle'    => $IN['ftitle'],
						    'fcontent'  => stripslashes($content),
						    'ftype'     => $IN['ftype'],
						    'freq'      => $IN['freq'],
						    'fmaxinput' => $IN['fmaxinput'],
						    'fshow'     => $IN['fshow'],
						    'ftopic'    => $IN['ftopic'],
						  );
		
						  
		if ($type == 'edit')
		{
			$rstring = $DB->compile_db_update_string( $db_string );
			
			$DB->query("UPDATE ibf_files_custfields SET $rstring WHERE fid='".$IN['id']."'");
			
			$ADMIN->done_screen("�������������� ���� ������ ���������������", "�������������� ���� ������", "act=dfield" );
			
		}
		else
		{
			$rstring = $DB->compile_db_insert_string( $db_string );
			
			$DB->query("INSERT INTO ibf_files_custfields (" .$rstring['FIELD_NAMES']. ") VALUES (". $rstring['FIELD_VALUES'] .")");
			
			$new_id = $DB->get_insert_id();
			
			$DB->query("ALTER TABLE ibf_files_custentered ADD field_$new_id text default ''");
			
			$DB->query("OPTIMIZE TABLE ibf_files_custfields");
			
			$ADMIN->done_screen("�������������� ���� �������", "�������������� ���� ������", "act=dfield" );
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
				$ADMIN->error("���������� ������� id ������ �� ���� ������. ���������� �����.");
			}
			
			$form_code = 'doedit';
			$button    = '��������� ���������';
				
		}
		else
		{
			$form_code = 'doadd';
			$button    = '������� ����';
		}
		
		if ($IN['id'] != "")
		{
			$DB->query("SELECT * FROM ibf_files_custfields WHERE fid='".$IN['id']."'");
			$fields = $DB->fetch_row();
		}
		else
		{
			$fields = array();
		}
		
		if ($type == 'edit')
		{
			$ADMIN->page_title = "�������������� ��������������� ���� ������ ".$fields['ftitle'];
		}
		else
		{
			$ADMIN->page_title = '�������� ������ ���� ������';
			$fields['ftitle'] = '';
		}
		
		$ADMIN->page_detail = "������ ��������� �������� ������, ����� �������������� �������.";
		
		
		
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , $form_code  ),
												  2 => array( 'act'   , 'dfield'     ),
												  3 => array( 'id'    , $IN['id']   ),
									     )  );
									     
		$fields['fcontent'] = str_replace( '|', "\n", $fields['fcontent'] );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "��������� ����" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����</b><br>��������: 200 ��������" ,
												  $SKIN->form_input("ftitle", $fields['ftitle'] )
									     )      );
									     
	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ����</b>" ,
												  $SKIN->form_dropdown("ftype",
												  					   array(
												  					   			0 => array( 'text' , '��������� ����' ),
												  					   			1 => array( 'drop' , '���������� ����' ),
												  					   			2 => array( 'area' , '��������� �������' ),
												  					   		),
												  					   $fields['ftype'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ���-�� �������� �������� (��� ���������� ���� ��� �������)</b>" ,
												  $SKIN->form_input("fmaxinput", $fields['fmaxinput'] )
									     )      );
									     
								     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� (��� ����������� ����)</b><br>���� ��������� �� ������<br>������ ��� ���� '����������':<br>64=640x480<br>80=800x640<br>u=�� ����������<br>����� ���������� ���:<br><select name='pants'><option value='64'>640x480</option><option value='80'>800x640</option><option value='u'>�� ����������</option></select><br>64, 80, ��� u ��������� � ���� ������. ��� ����������� ���� �� �������� ����� ��������/�������������� ����� ������������ �������� �� ���� (64=640x480, ������������ ��� '640x480')" ,
												  $SKIN->form_textarea("fcontent", $fields['fcontent'] )
									     )      );
		
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��� ���� ������������ ��� ����������??</b>" ,
												  $SKIN->form_yes_no("freq", $fields['freq'] )
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ��� ���� � �� �������� �������������� �����?</b>" ,
												  $SKIN->form_yes_no("fshow", $fields['fshow'] )
									     )      );						     							     
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� �� ������� �������������� �������� ��� ��� �������� ������, �������� ���������� ����� ���� � ����� ����������� ����?</b>" ,
												  $SKIN->form_yes_no("ftopic", $fields['ftopic'] )
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
		
		$ADMIN->page_title = "�������������� ���� ������";
		
		$ADMIN->page_detail = "�������������� ���� ������, ������ ��� ���������� ��������� ������������ � �� ������������ � ���������� �����, ��� ��������, �������������� ������. �� ������ ����� �������, ��������� ���������� � ��������������� ���� � ������������� ����������� ���� ��� ���.";
		
		$SKIN->td_header[] = array( "�������� ����"    , "20%" );
		$SKIN->td_header[] = array( "���"           , "10%" );
		$SKIN->td_header[] = array( "�������� ����������" , "20%" );
		$SKIN->td_header[] = array( "������������"       , "10%" );
		$SKIN->td_header[] = array( "�������"         , "10%" );
		$SKIN->td_header[] = array( "�����. � ����"       , "10%" );
		$SKIN->td_header[] = array( "�������������"           , "10%" );
		$SKIN->td_header[] = array( "�������"         , "10%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "���������� ��������������� ������ ������" );
		
		$real_types = array( 'drop' => '���������� ����',
							 'area' => '��������� �������',
							 'text' => '��������� ����',
						   );
		
		$DB->query("SELECT * FROM ibf_files_custfields");
		
		if ( $DB->get_num_rows() )
		{
			while ( $r = $DB->fetch_row() )
			{
			
				$hide   = '&nbsp;';
				$req    = '&nbsp;';
				$regi   = '&nbsp;';
				
				"<center><a href='{$ADMIN->base_url}&act=group&code=delete&id=".$r['g_id']."'>�������</a></center>";
				
				//-----------------------------------
				if ($r['fshow'] == 1)
				{
					$hide = '<center><span style="color:red">��</span></center>';
				}
				//-----------------------------------
				if ($r['freq'] == 1)
				{
					$req = '<center><span style="color:red">��</span></center>';
				}
				
				if ($r['ftopic'] == 1)
				{
					$regi = '<center><span style="color:red">��</span></center>';
				}
				
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$r['ftitle']}</b>" ,
														  "<center>{$real_types[$r['ftype']]}</center>",
														  "<center>field_".$r['fid']."</center>",
														  $req,
														  $hide,
														  $regi,
														  "<center><a href='{$ADMIN->base_url}&act=dfield&code=edit&id=".$r['fid']."'>�������������</a></center>",
														  "<center><a href='{$ADMIN->base_url}&act=dfield&code=delete&id=".$r['fid']."'>�������</a></center>",
											 )      );
											 
			}
		}
		else
		{
			$ADMIN->html .= $SKIN->add_td_basic("��� �����", "center", "pformstrip");
		}
		
		$ADMIN->html .= $SKIN->add_td_basic("<a href='{$ADMIN->base_url}&act=dfield&code=add' class='fauxbutton'>������� ����� ����</a></center>", "center", "pformstrip");

		$ADMIN->html .= $SKIN->end_table();
		
		
		$ADMIN->output();
		
		
	}
	
		
}


?>