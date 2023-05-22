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
|   > Admin Category functions
|   > Module written by Matt Mecham
|   > Date started: 1st march 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
	exit();
}

$idx = new ad_cat();


class ad_cat {

	var $base_url;

	function ad_cat() {
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
			case 'new':
				$this->new_form();
				break;
			case 'donew':
				$this->do_new();
				break;
			//+-------------------------
			case 'edit':
				$this->show_cats();
				break;
			case 'doeditform':
				$this->edit_form();
				break;
			case 'doedit':
				$this->do_edit();
				break;
			//+-------------------------
			case 'remove':
				$this->remove_form();
				break;
			case 'doremove':
				$this->do_remove();
				break;
			//+-------------------------
			case 'reorder':
				$this->reorder_form(); //Get it? No... ok.
				break;
			case 'doreorder':
				$this->do_reorder();
				break;
			
			default:
				$this->show_cats();
				break;
		}
		
	}
	
	
	
	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function reorder_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "�������������� ���������";
		$ADMIN->page_detail  = "��� �������������� ���������, �������� ����� �������, � ���������� ����, ����� � ������ ���������� � ������� ������ ��������� ���������";
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doreorder'),
												  2 => array( 'act'   , 'cat'     ),
											) );
		
		
		$SKIN->td_header[] = array( "&nbsp;"       , "10%" );
		$SKIN->td_header[] = array( "�������� ������"   , "60%" );
		$SKIN->td_header[] = array( "���������"        , "15%" );
		$SKIN->td_header[] = array( "���"       , "15%" );
		
		$ADMIN->html .= $SKIN->start_table( "���� ��������� � ������" );
		
		$cats   = array();
		$forums = array();
		
		$DB->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");
		while ($r = $DB->fetch_row())
		{
			$cats[] = $r;
		}
		
		$DB->query("SELECT * from ibf_forums ORDER BY position ASC");
		while ($r = $DB->fetch_row())
		{
			$forums[] = $r;
		}
		
		
		// Build up the drop down box
		
		$form_array = array();
		
		for ($i = 1 ; $i <= count($cats) ; $i++ )
		{
			$form_array[] = array( $i , $i );
		}
		
		
		$last_cat_id = -1;
		
		foreach ($cats as $c)
		{
			
			$ADMIN->html .= $SKIN->add_td_row( array(  $SKIN->form_dropdown( 'POS_'.$c['id'], $form_array, $c['position'] ),
													   $c['name'],
													   '&nbsp;',
													   '&nbsp;',
											 ), 'pformstrip'     );
			$last_cat_id = $c['id'];
			
			
			foreach($forums as $r)
			{	
			
				if ($r['category'] == $last_cat_id)
				{
					$ADMIN->html .= $SKIN->add_td_row( array(
															   '&nbsp;',
															   "<b>".$r['name']."</b>",
															   $r['posts'],
															   $r['topics'],
													 )      );
				}
			}
		}
		
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_reorder() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$cat_query = $DB->query("SELECT id from ibf_categories");
		
		while ( $r = $DB->fetch_row($cat_query) )
		{
			$order_query = $DB->query("UPDATE ibf_categories SET position='".$IN[ 'POS_' . $r['id'] ]."' WHERE id='".$r['id']."'");
		}
		
		$ADMIN->save_log("�������������� ���������");
		
		$ADMIN->done_screen("��������� ���������������", "���������� �����������", "act=cat" );
		
	}
	
	
	//+---------------------------------------------------------------------------------
	//
	// REMOVE CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function remove_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$form_array = array();
		
		if ($IN['c'] == "")
		{
			$ADMIN->error("���������� ���������� ID ��������� ��� ��������������.");
		}
		
		$DB->query("SELECT id, name FROM ibf_categories WHERE id > 0 ");
		
		//+-------------------------------
		// Make sure we have more than 1
		// category..
		//+-------------------------------
		
		if ($DB->get_num_rows() < 2)
		{
			$ADMIN->error("���������� ������� ���������. ����� ��������� ���������, ���������� ������� ����� ���������.");
		}
		
		while ( $r = $DB->fetch_row() )
		{
			if ($r['id'] == $IN['c'])
			{
				continue;
			}
			
			$form_array[] = array( $r['id'] , $r['name'] );
		}
		
		//+-------------------------------
		// Get the details for this category...
		//+-------------------------------
		
		$DB->query("SELECT * FROM ibf_categories WHERE id='".$IN['c']."'");
		$cat = $DB->fetch_row();
		
		//+-------------------------------
		
		$ADMIN->page_title = "�������� ��������� '{$cat['name']}'";
		
		$ADMIN->page_detail = "����� ��������� ���������, ��������� � ���, ��� � ���� ��������� �� �� �������� ����������� ��� �������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doremove'),
												  2 => array( 'act'   , 'cat'     ),
												  3 => array( 'c'     , $IN['c']  ),
												  4 => array( 'name'  , $cat['name'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "40%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������: </b>" , $cat['name'] )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��� <i>������������ ������ ���� ���������</i> � ���������</b>" ,
												  $SKIN->form_dropdown( "MOVE_ID", $form_array )
									     )      );
									     
		$ADMIN->html .= $SKIN->end_form("����������� ������ � ������� ���������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
		
	}
	
	//+---------------------------------------------------------------------------------
	
	function do_remove() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		if ($IN['c'] == "")
		{
			$ADMIN->error("���������� ���������� ID �������� ���������.");
		}
		
		if ($IN['MOVE_ID'] == "")
		{
			$ADMIN->error("���������� ���������� ID ��������� ������������.");
		}
		
		
		$DB->query("UPDATE ibf_forums SET category='".$IN['MOVE_ID']."' WHERE category='".$IN['c']."'");
		
		$DB->query("DELETE FROM ibf_categories WHERE id='".$IN['c']."'");
		
		$ADMIN->save_log("�������� ��������� '{$IN['name']}'");
		
		$ADMIN->done_screen("��������� �������", "���������� �����������", "act=cat" );
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// EDIT CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function edit_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$subcats = array();
		
		$DB->query("SELECT id, name FROM ibf_categories WHERE id > 0");
		
		while ( $r = $DB->fetch_row() )
		{
			if ($r['id'] == $IN['c'])
			{
				continue;
			}
			
			$subcats[] = array( $r['id'] , $r['name'] );
		}
		
		$DB->query("SELECT * FROM ibf_categories WHERE id='".$IN['c']."'");
		$cat = $DB->fetch_row();
		
		$ADMIN->page_title = "�������������� ���������";
		
		$ADMIN->page_detail = "� ���� ������ �� ������ ������������� ���� ���������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doedit'),
												  2 => array( 'VIEW'  , '*'    ),
												  3 => array( 'act'   , 'cat'  ),
												  4 => array( 'c'     , $IN['c'] ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���������</b>" ,
												  $SKIN->form_input("CAT_NAME", $cat['name'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������</b><br />���� �� ���������, ��������� ����� ������������ � ���������� ���� �� �������� ������" ,
												  $SKIN->form_dropdown( "CAT_STATE",
																			array( 
																					0 => array( 1, '�������' ),
																					1 => array( 0, '�������'  ),
																					2 => array( 2, '��������� � ������ �������, �� ��������� ����� URL � ���� �������� �� �������'  ),
																					3 => array( 3, '��������� � ������ ������� � ��������� ������ ����� URL'  ),
																				 ),
												  						$cat['state']
												  					  )
									     )      );
									     
		/*$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "�������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��������� ��������</b><br>(������: http://www.domain.com/image.gif)" ,
												  $SKIN->form_input("IMAGE", $cat['image'])
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ��������� ��������</b><br>(������: http://www.domain.com/)" ,
												  $SKIN->form_input("URL", $cat['url'])
									     )      );*/
									     
		$ADMIN->html .= $SKIN->end_form("��������� ���������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}

	//+---------------------------------------------------------------------------------
	
	function do_edit() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$IN['CAT_NAME'] = trim($IN['CAT_NAME']);
		
		if ($IN['CAT_NAME'] == "")
		{
			$ADMIN->error("���������� ������ �������� ���������");
		}
		
		if ($IN['c'] == "")
		{
			$ADMIN->error("���������� ���������� ID ����������� ���������.");
		}
		
		$db_string = $DB->compile_db_update_string( array (
															'state'         => $IN['CAT_STATE'],
															'name'          => $IN['CAT_NAME'],
															'description'   => $IN['CAT_DESC'],
															'image'         => $IN['IMAGE'],
															'url'           => $IN['URL'],
												  )       );
												  
		$DB->query("UPDATE ibf_categories SET $db_string WHERE id='".$IN['c']."'");
		
		$ADMIN->save_log("�������������� ��������� '{$IN['CAT_NAME']}'");
		
		$ADMIN->done_screen("��������� '{$IN['CAT_NAME']}' ���������������", "���������� �����������", "act=cat" );
		
	}

	
	//+---------------------------------------------------------------------------------
	//
	// SHOW CATS
	// Renders a complete listing of all the forums and categories.
	//
	//+---------------------------------------------------------------------------------
	
	function show_cats() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title   = "����� ��������� � �������";
		$ADMIN->page_detail  = "<img src='{$SKIN->img_url}/acp_rules.gif' border='0'> <b>������� ������</b> ����� �� ������ ���������, ������������� ��� ������� ������� ��� �������� ������
							   <br /><img src='{$SKIN->img_url}/acp_edit.gif' border='0'> <b>����� �����</b> ����� �� ������ ���������, ������������� ��� ������� ���� ��� �������� ������
							   <br /><img src='{$SKIN->img_url}/acp_resync.gif' border='0'> <b>���������������</b> ����� ����� ���������� �������� ���������, ��� � ���������� � ��������� ���������";
		
		$cats     = array();
		$forums   = array();
		$children = array();
		$skins    = array();
		
		$DB->query("SELECT * from ibf_categories WHERE id > 0 ORDER BY position ASC");
		while ($r = $DB->fetch_row())
		{
			$cats[$r['id']] = $r;
		}
		
		$DB->query("SELECT * from ibf_forums ORDER BY position ASC");
		while ($r = $DB->fetch_row())
		{
			
			if ($r['parent_id'] > 0)
			{
				$children[ $r['parent_id'] ][] = $r;
			}
			else
			{
				$forums[] = $r;
			}
			
		}
		
		$DB->query("SELECT uid, sname, sid FROM ibf_skins");
		while ($s = $DB->fetch_row())
		{
			$skins[$s['sid']] = $s['sname'];
		}
		
		$SKIN->td_header[] = array( "{none}", "40%" );
		$SKIN->td_header[] = array( "{none}", "20%" );
		$SKIN->td_header[] = array( "{none}", "15%" );
		$SKIN->td_header[] = array( "{none}", "20%" );
		
		$ADMIN->html .= $SKIN->start_table("���� ��������� � ������");
		
		$last_cat_id = -1;
		
		foreach ($cats as $c)
		{
			
		
			$ADMIN->html .= $SKIN->add_td_row( array(
													   "<a href='{$INFO['board_url']}/index.php?c={$c['id']}' target='_blank'>".$c['name']."</a>",
													   "<center><a href='{$ADMIN->base_url}&act=cat&code=doeditform&c={$c['id']}'>�������������</a></center>",
													   '&nbsp;',
													   "<center><a href='{$ADMIN->base_url}&act=cat&code=remove&c={$c['id']}'>�������</a></center>",
											 ), 'pformstrip'     );
			$last_cat_id = $c['id'];
			
			
			foreach($forums as $r)
			{	
			
				if ($r['category'] == $last_cat_id)
				{
					
					if ( ($r['skin_id'] != "") and ($r['skin_id'] >= 0) )
					{
						$skin_stuff = "<br>[ "."������������ ����: ".$skins[$r['skin_id']]." ]";
					}
					else
					{
						$skin_stuff = "";
					}
					
					$redirect =  ($r['redirect_on'] == 1) ? ' (Redirect Forum)' : '';
				
					if ($r['subwrap'] == 1)
					{
					
						if ($r['sub_can_post'])
						{
							$ADMIN->html .= $SKIN->add_td_row( array(
																	   " - <b>".$r['name']."</b>$redirect $skin_stuff",
																	   "<center><b><a href='{$ADMIN->base_url}&act=forum&code=subedit&f={$r['id']}'>���������</a></b>".
																	   " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$r['id']}'>��������� �������</a></center>",
																	   
																	   "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$r['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='������� ������'></a>&nbsp;&nbsp;".
																	   "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='����� �����'></a>&nbsp;&nbsp;".
																	   "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$r['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='���������������'></a></center>",
																	   
																	   "<center><a href='{$ADMIN->base_url}&act=forum&code=subdelete&f={$r['id']}'>�������</a>".
																	   " | <b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$r['id']}'>�������� �����</a></b></center>",
															 )  , 'subforum'  );
						}
						else
						{
							$ADMIN->html .= $SKIN->add_td_row( array(
																	   " - <b>".$r['name']."</b>$redirect $skin_stuff",
																	   "<a href='{$ADMIN->base_url}&act=forum&code=subedit&f={$r['id']}'>�������������</a>",
																	   "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'>����� �����</a>",
																	   "<a href='{$ADMIN->base_url}&act=forum&code=subdelete&f={$r['id']}'>�������</a>",
															 )   , 'subforum' );
						}
					}
					else
					{
						$ADMIN->html .= $SKIN->add_td_row( array(
																   "<b>".$r['name']."</b>$redirect $skin_stuff<br>",
																   "<center><b><a href='{$ADMIN->base_url}&act=forum&code=edit&f={$r['id']}'>���������</a></b>".
																   " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$r['id']}'>��������� �������</a></center>",
																   
																   "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$r['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='������� ������'></a>&nbsp;&nbsp;".
																   "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$r['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='����� �����'></a>&nbsp;&nbsp;".
																   "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$r['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='���������������'></a></center>",
																   
																   "<center><a href='{$ADMIN->base_url}&act=forum&code=delete&f={$r['id']}'>�������</a>".
																   " | <b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$r['id']}'>�������� �����</a></b></center>",
														 )      );
					}
													 
					if ( ( isset($children[ $r['id'] ]) ) and ( count ($children[ $r['id'] ]) > 0 ) )
					{
						foreach($children[ $r['id'] ] as $idx => $rd)
						{
						
							if ( ($rd['skin_id'] != "") and ($rd['skin_id'] >= 0) )
							{
								$skin_stuff = "<br>[ "."������������ ����: ".$skins[$rd['skin_id']]." ]";
							}
							else
							{
								$skin_stuff = "";
							}
							
							$redirect =  ($rd['redirect_on'] == 1) ? ' (Redirect Forum)' : '';
					
							$ADMIN->html .= $SKIN->add_td_row( array(
															   " +-- <b>".$rd['name']."</b>$redirect $skin_stuff<br>",
															   "<center><b><a href='{$ADMIN->base_url}&act=forum&code=edit&f={$rd['id']}'>���������</a></b>".
															   " | <a href='{$ADMIN->base_url}&act=forum&code=pedit&f={$rd['id']}'>��������� �������</a></center>",
															   "<center><a href='{$ADMIN->base_url}&act=forum&code=frules&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_rules.gif' border='0' title='������� ������'></a>&nbsp;&nbsp;".
															   "<a href='{$ADMIN->base_url}&act=forum&code=skinedit&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_edit.gif' border='0' title='����� �����'></a>&nbsp;&nbsp;".
															   "<a href='{$ADMIN->base_url}&act=forum&code=recount&f={$rd['id']}'><img src='{$SKIN->img_url}/acp_resync.gif' border='0' title='���������������'></a></center>",
															   "<center><a href='{$ADMIN->base_url}&act=forum&code=delete&f={$rd['id']}'>�������</a>".
															   " | <b><a href='{$ADMIN->base_url}&act=forum&code=empty&f={$rd['id']}'>�������� �����</a></b></center>",
													 ) , 'subforum' );
						}
					}					 
												
				}
			}
		}
		
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
		
	}
	
	//+---------------------------------------------------------------------------------
	//
	// NEW CATEGORY
	//
	//+---------------------------------------------------------------------------------
	
	function new_form() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_GET_VARS;
		
		$cat_name = "";
		
		if ($HTTP_GET_VARS['name'] != "")
		{
			$cat_name = $std->txt_stripslashes(urldecode($HTTP_GET_VARS['name']));
		}
		
		$ADMIN->page_title = "�������� ����� ���������";
		
		$ADMIN->page_detail = "� ���� ������ �� ������ ��������� ����� ���������.";
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'donew'),
												  2 => array( 'act'   , 'cat'  ),
											) );
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		//+-------------------------------
		
		$ADMIN->html .= $SKIN->start_table( "����������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ���������</b>" ,
												  $SKIN->form_input("CAT_NAME", $cat_name)
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������</b><br />���� �� ���������, ��������� ����� ������������ � ���������� ���� �� �������� ������" ,
												  $SKIN->form_dropdown( "CAT_STATE",
																			array( 
																					0 => array( 1, '�������' ),
																					1 => array( 0, '�������'  ),
																					2 => array( 2, '��������� � ������ �������, �� ��������� ����� URL � ����� ���� �������� �������� �� �������'  ),
																					3 => array( 3, '��������� � ������ ������� � ��������� ������ ����� URL'  ),
																				 ),
												  						"1"
												  					  )
									     )      );
									     
		/*$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		
		$SKIN->td_header[] = array( "&nbsp;"  , "30%" );
		$SKIN->td_header[] = array( "&nbsp;"  , "60%" );
		
		$ADMIN->html .= $SKIN->start_table( "�������������" );
		
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��������� ��������</b><br>(������: http://www.domain.com/image.gif)" ,
												  $SKIN->form_input("IMAGE")
									     )      );
									     
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ��������� ��������</b><br>(������: http://www.domain.com/)" ,
												  $SKIN->form_input("URL")
									     )      );*/
		
		$ADMIN->html .= $SKIN->end_form("������� ���������");
										 
		$ADMIN->html .= $SKIN->end_table();
		
		$ADMIN->output();
			
			
	}


	//+---------------------------------------------------------------------------------
	
	function do_new() {
		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$IN['CAT_NAME'] = trim($IN['CAT_NAME']);
		
		if ($IN['CAT_NAME'] == "")
		{
			$ADMIN->error("���������� ������ �������� ���������");
		}
		
		// Get the new cat id. We could use auto_incrememnt, but we need the ID to use as the default
		// category position...
		
		$DB->query("SELECT MAX(id) as top_cat FROM ibf_categories");   //ooh, top cat - he's the leader of our gang..
		$row = $DB->fetch_row();
		
		if ($row['top_cat'] < 1) $row['top_cat'] = 0;
		
		$row['top_cat']++;
		
		$db_string = $DB->compile_db_insert_string( array (
															'id'            => $row['top_cat'],
															'position'      => $row['top_cat'],
															'state'         => $IN['CAT_STATE'],
															'name'          => $IN['CAT_NAME'],
															'description'   => $IN['CAT_DESC'],
															'image'         => $IN['IMAGE'],
															'url'           => $IN['URL'],
												  )       );
												  
		$DB->query("INSERT INTO ibf_categories (".$db_string['FIELD_NAMES'].") VALUES (".$db_string['FIELD_VALUES'].")");
		
		$ADMIN->save_log("�������� ��������� '{$IN['CAT_NAME']}'");
		
		$ADMIN->done_screen("��������� {$IN['CAT_NAME']} �������", "���������� �����������", "act=cat" );
		
		
		
	}


	
}


?>