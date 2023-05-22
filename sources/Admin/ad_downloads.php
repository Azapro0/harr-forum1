<?php

/*
+--------------------------------------------------------------------------
|   Download manager
|   ========================================
|   by Parmeet Singh (Improved By Ryan Ong and Brandon Farber)
|
|	Extended by bfarber for use with Download System
|     (c) 2001,2002,2003 IBForums
|	{c} 2003 Bfarber
|   	http://www.phpwiz.net
|	http://bfarber.com
|   ========================================
|   Web: http://www.phpwiz.net
|   Email: parmeet@emirates.net.ae
|   IBFORUMS: Licence Info: phpib-licence@ibforums.com
+---------------------------------------------------------------------------
|   ========================================
|   Web: http://bfarber.net
|   Email: bfarber@bfarber.com
+---------------------------------------------------------------------------
|
|
|   > Admin Forum function
|   > Module written by Parmeet
|   > Extended by bfarber for use with Download System
|   > Date started: 23th July 2002
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/


$idx = new ad_downloads();


class ad_downloads {

	var $base_url;

	function ad_downloads() {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		switch($IN['code'])
		{
		    case 'showaddcat':
				$this->show_cat('add');
				break;

			case 'showdelcat':
				$this->show_cat('del');
				break;

			case 'showeditcat':
				$this->show_cat('edit');
				break;

			case 'showeditcat1':
				$this->show_cat('edit1');
				break;

			case 'doaddcat':
				$this->do_cat('add');
				break;

			case 'dodelcat':
				$this->do_cat('del');
				break;

			case 'doeditcat':
				$this->do_cat('edit');
				break;

			case 'switch':
				$this->switch_download( );
				break;

			case 'settings':
				$this->show_edit_vars( );
				break;

			case 'do_switch':
				$this->save_config( array( 'd_section_close'));
				break;
				
            	case 'editvars':
                		$this->edit_vars( );
                		break;
                
			case 'reorder':
				$this->reorder_form();
				break;

			case 'doreorder':
				$this->do_reorder();
				break;

            default:
				$this->main_screen( );
				break;
		}
		
	}

	
	//-------------------------------------------------------------
	//
	// Save config. Does the hard work, so you don't have to.
	//
	//--------------------------------------------------------------
	
	function save_config( $new )
	{
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;
		
		$master = array();
		
		if ( is_array($new) )
		{
			if ( count($new) > 0 )
			{
				foreach( $new as $field )
				{
				
					// Handle special..
					
					if ($field == 'img_ext' or $field == 'avatar_ext')
					{
						$HTTP_POST_VARS[ $field ] = preg_replace( "/[\.\s]/", "" , $HTTP_POST_VARS[ $field ] );
						$HTTP_POST_VARS[ $field ] = preg_replace( "/,/"     , '|', $HTTP_POST_VARS[ $field ] );
					}
					else if ($field == 'coppa_address')
					{
						$HTTP_POST_VARS[ $field ] = nl2br( $HTTP_POST_VARS[ $field ] );
					}
					
					$HTTP_POST_VARS[ $field ] = preg_replace( "/'/", "&#39;", stripslashes($HTTP_POST_VARS[ $field ]) );
				
					$master[ $field ] = stripslashes($HTTP_POST_VARS[ $field ]);
				}
				
				$ADMIN->rebuild_config($master);
			}
		}
		
		$ADMIN->save_log("���������� �������� ��������� ������, Back Up ������");
		
		$ADMIN->done_screen("������������ ��������� ������ ���������", "������� �������� ��������� ������", "act=downloads" );
		
		
		
	}

 
 	function show_cat( $type = "add" ) {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		if( $type == "add" ) {
			$a_array   = array( );
			$a_array[] = array( 1 , "��" );
			$a_array[] = array( 0 , "���" );

			$s_array   = array( );
			$s_array[] = array( 1 , "��" );
			$s_array[] = array( 0 , "���" );

			$sn_array   = array( );
			$sn_array[] = array( 1 , "��" );
			$sn_array[] = array( 0 , "���" );

			$as_array   = array( );
			$as_array[] = array( 1 , "��" );
			$as_array[] = array( 0 , "���" );

			$DB->query( "SELECT * from ibf_files_cats WHERE sub=0" );
			$e_array   = array( );
			$e_array[] = array( 0 , "�� ���������" );
			while( $row = $DB->fetch_row( ) ) {
				$e_array[] = array( $row['cid'] , $row['cname'] );

			}

		$DB->query( "SELECT * FROM ibf_forums" );
		$tt_array   = array( );
		$tt_array[] = array( 0 , "<b>�� ������������</b>" );
		while( $row = $DB->fetch_row( ) ) {
			$tt_array[] = array( $row['id'] , "--{$row['name']}" );

		}


			$ADMIN->page_title = "�������� ��������� ��������� ������!";

			$ADMIN->page_detail = "�� ���� �������� �� ������ ������� ��������� ��� ������ ��������� ������.";

			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doaddcat' ),
													  2 => array( 'act'   , 'downloads'     ),
									     	)      );

			$ADMIN->html .= $SKIN->start_table( "�������� ���������" );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������</b>" ,
													  $SKIN->form_input("cname","","text"),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� (�� �����������)</b>" ,
													  $SKIN->form_input("cdesc","","text"),
											     )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ��������� �� ������� �������� ���������?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_dropdown("dis_screen_cat",$s_array,"text"),
											     )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ���������, �� �������� ���������� �����?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_dropdown("dis_screen",$s_array,"text"),
											     )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������������� �������� ������ ����������� ����� �������� � ����������?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_dropdown("authorize",$as_array,"text"),
											     )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�����, � ������� ����� ������������� ����������� ���� ��������� ��� ����� �����?</b><br />--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ �����-�� �����, �������� �� \"������������\", �� ��� ��������� ����� ���������������." ,
												  $SKIN->form_dropdown("fordaforum",$tt_array,"text"),
											    )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ���� ��������� ������� ��������������?</b>" ,
												  $SKIN->form_dropdown("show_notes",$sn_array,"text"),
											    )		);


			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ������� ��� ���� ���������:</b>" ,
													  $SKIN->form_textarea("cnotes",""),
											     )		);



			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��� ��������� ���������?</b>" ,
													  $SKIN->form_dropdown("copen",$a_array,"text"),
											     )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ��� ���������� ���� ��������� � ��, � ���� ������������.</b><br>���� �� �� ������ ��������� ��� ��������� �������������, �������� '�� ���������'." ,
													  $SKIN->form_dropdown("sub",$e_array,"text"),
									 	    )      );

			$ADMIN->html .= $SKIN->end_form("������� ���������");

			$ADMIN->html .= $SKIN->end_table( );

			$ADMIN->output( );
			}
		elseif( $type == "del" ) {

			$ADMIN->page_title = "�������� ���������!";

			$ADMIN->page_detail = "����� �� ������ ������� ���� ���������.";


			$DB->query( "SELECT * from ibf_files_cats" );
			$d_array   = array( );
			$w_array   = array( );

			while( $row = $DB->fetch_row( ) ) {
				$d_array[] = array( $row['cid'] , $row['cname'] );
				$w_array[] = array( $row['cid'] , $row['cname'] );
			}
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'dodelcat' ),
													  2 => array( 'act'   , 'downloads'     ),
									     	)      );

			$ADMIN->html .= $SKIN->start_table( "�������� ���������" );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ��� ��������</b>" ,
													  $SKIN->form_dropdown("del",$d_array,"text"),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>� ����� ��������� ����������� ����� �� ���� ���������?</b>" ,
													  $SKIN->form_dropdown("trans",$w_array,"text"),
											     )      );

			$ADMIN->html .= $SKIN->end_form("������� ���������");

			$ADMIN->html .= $SKIN->end_table( );

			$ADMIN->output( );
		}
		elseif( $type == "edit" ) {

			$ADMIN->page_title = "�������������� ��������� ��������� ������!";

			$ADMIN->page_detail = "����� �� ������ ������������� ��������� ������ ��������� ������.";


			$DB->query( "SELECT * from ibf_files_cats" );
			$e_array   = array( );


			while( $row = $DB->fetch_row( ) ) {
				$e_array[] = array( $row['cid'] , $row['cname'] );
			}
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'showeditcat1' ),
													  2 => array( 'act'   , 'downloads'     ),
									     	)      );

			$ADMIN->html .= $SKIN->start_table( "�������������� ���������" );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ��� ��������������</b>" ,
													  $SKIN->form_dropdown("cid",$e_array,"text"),
									 	    )      );

			$ADMIN->html .= $SKIN->end_form("�������������");

			$ADMIN->html .= $SKIN->end_table( );

			$ADMIN->output( );

		}
		elseif( $type == "edit1" ) {

			if( $IN['cid'] == "" ) {
				$ADMIN->error("�� �� ������� ���������...");
			}

			$DB->query( "SELECT * from ibf_files_cats WHERE sub=0" );
			$e_array   = array( );
			$e_array[] = array( 0 , "�� ���������" );
			while( $row = $DB->fetch_row( ) ) {
				$e_array[] = array( $row['cid'] , $row['cname']);

			}

		$DB->query( "SELECT * FROM ibf_forums" );
		$tt_array   = array( );
		$tt_array[] = array( 0 , "<b>�� ���������</b>" );
		while( $row = $DB->fetch_row( ) ) {
			$tt_array[] = array( $row['id'] , "--{$row['name']}" );

		}


			$ADMIN->page_title = "�������������� ��������� ��������� ������!";

			$ADMIN->page_detail = "����� �� ������ ������������� ��������� ������ ��������� ������.";


			$DB->query( "SELECT * FROM ibf_files_cats WHERE cid = " . $IN['cid'] );

			$row = $DB->fetch_row( );



			$auth = $row['authorize'] == 1 ? 1 : 0;
			$dis = $row['dis_screen'] == 1 ? 1 : 0;
			$notes = $row['show_notes'] == 1 ? 1 : 0;

			$open = $row['open'] == 1 ? 1 : 0;
			//+-------------------------------

			$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doeditcat' ),
													  2 => array( 'act'   , 'downloads' ),
									     	)      );
			$a = array( );
			$a[] = array('cid', $IN['cid']);


			$ADMIN->html .= $SKIN->form_hidden($a);

			$ADMIN->html .= $SKIN->start_table( "�������������� ���������" );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������</b>" ,
													  $SKIN->form_input("cname",$row['cname'],"text"),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� (�� �����������)</b>" ,
													  $SKIN->form_input("cdesc",$row['cdesc'],"text"),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ��������� �� ������� �������� ���������?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_yes_no("dis_screen_cat", $dis),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ���������, �� �������� ���������� �����?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_yes_no("dis_screen", $dis),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������������� �������� ������ ����������� ����� �������� � ����������?</b><br>--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ ����� �� ��� ���, �� ��� ��������� ����� ���������������." ,
													  $SKIN->form_yes_no("authorize", $auth),
									 	    )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�����, � ������� ����� ������������� ����������� ���� ��������� ��� ����� �����?</b><br />--����������: ���� �� � ���������� ������, � ���������� ���������� ������� ��� ���� ������ �����-�� �����, �������� �� \"������������\", �� ��� ��������� ����� ���������������." ,
												  $SKIN->form_dropdown("fordaforum",$tt_array, $row['fordaforum']),
											    )		);

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ���� ��������� ������� ��������������?" ,
												  $SKIN->form_yes_no("show_notes", $notes),
											    )		);


			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ������� ��� ���� ���������:</b>" ,
													  $SKIN->form_textarea("cnotes",$row['cnotes']),
											     )		);



			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������� ��� ���������� ���� ��������� � ��, � ���� ������������.</b><br>���� �� �� ������ ��������� ��� ��������� �������������, �������� '�� ���������'." ,
													  $SKIN->form_dropdown("sub",$e_array,$row['sub']),
									 	    )      );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ��� ��������� ���������?</b>" ,
													  $SKIN->form_yes_no("copen", $open),
									 	    )      );

			$ADMIN->html .= $SKIN->end_form("��������� ���������");

			$ADMIN->html .= $SKIN->end_table( );

			$ADMIN->output( );

		}
	}
	//+---------------------------------------------------------------------------------
	//
	// RE-ORDER CATEGORY
	//
	//+---------------------------------------------------------------------------------
	function reorder_form() {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$ADMIN->page_title = "�������������� ���������";
		$ADMIN->page_detail  = "��� �������������� ���������, �������� ����� �������, � ���������� ����, ����� � ������ ���������� � ������� ������ ��������� ���������.";

		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'doreorder'),
									2 => array( 'act'   , 'downloads'     ),
											) );


		$SKIN->td_header[] = array( "�������"       , "10%" );
		$SKIN->td_header[] = array( "�������� ���������"   , "90%" );

		$ADMIN->html .= $SKIN->start_table( "���� ���������" );

		$cats   = array();

		$DB->query("SELECT * from ibf_files_cats WHERE cid > 0 ORDER BY position ASC");
		while ($r = $DB->fetch_row())
		{
			$cats[] = $r;
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

			$ADMIN->html .= $SKIN->add_td_row( array(  $SKIN->form_dropdown( 'POS_'.$c['cid'], $form_array, $c['position'] ),
													   $c['cname'],
											 ), 'catrow'     );
			$last_cat_id = $c['cid'];




				if ($r['category'] == $last_cat_id)
				{
					$ADMIN->html .= $SKIN->add_td_row( array(
															   '&nbsp;',
															   "<b>".$r['cname']."</b><br>".$r['cdesc'],
															   $r['posts'],
															   $r['topics'],
													 )      );
				}
				}

		$ADMIN->html .= $SKIN->end_form("��������� ���������");

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}

	//+---------------------------------------------------------------------------------

	function do_reorder() {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$cat_query = $DB->query("SELECT cid from ibf_files_cats");

		while ( $r = $DB->fetch_row($cat_query) )
		{
			$order_query = $DB->query("UPDATE ibf_files_cats SET position='".$IN[ 'POS_' . $r['cid'] ]."' WHERE cid='".$r['cid']."'");
		}

		$ADMIN->done_screen("��������� ���������������", "����������������� ��������� ������", "act=downloads" );
		
		
	}
	function do_cat( $type = "add" ) {

		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		if( $type == "add" ) {
			if( $IN['cname'] == "" ) {
				$ADMIN->error("�� �� ��������� ���� - ��������...");
			}

			$DB->query( "SELECT MAX(cid) as mid FROM ibf_files_cats" );
			$row = $DB->fetch_row( );

			$last_id = $row['mid'] + 1;
			if( $IN['sub'] == 0 || $IN['sub'] == "" ) {
				$DB->query( "SELECT cname FROM ibf_files_cats WHERE cname = '" . $IN['cname'] . "' AND sub = 0" );
			}
			else {
				$DB->query( "SELECT cname FROM ibf_files_cats WHERE cname = '" . $IN['cname'] . "' AND sub = '" . $IN['sub'] . "'" );
			}

			if( $DB->get_num_rows( ) > 0 ) {
				$ADMIN->error("����� �������� ����������...");
			}

			$DB->query( "SELECT cid FROM ibf_files_cats WHERE cid = '" . $IN['sub'] . "'" );

			if( $DB->get_num_rows( ) > 0 ) {
				$ADMIN->error("ID ��������� ��������� �� ���������� ��� ��� ��������� ��� ������������ � ���� ������������...");
			}

			$DB->query( "INSERT INTO ibf_files_cats ( cid , sub , cname , cdesc , copen, dis_screen, dis_screen_cat, authorize, fordaforum, show_notes, cnotes ) VALUES( '$last_id' , '" . $IN['sub'] . "' , '" . $IN['cname'] . "' , '" . $IN['cdesc'] . "' , '" . $IN['copen'] . "', '" . $IN['dis_screen'] . "', '" . $IN['dis_screen_cat'] . "', '" . $IN['authorize'] ."', '" . $IN['fordaforum'] ."', '" . $IN['show_notes'] ."', '" . $IN['cnotes'] ."'  )" );

			$ADMIN->done_screen("��������� �������", "����������������� ��������� ������", "act=downloads" );
		}
		if( $type == "edit" ) {
			if( $IN['cid'] == "" ) {
				$ADMIN->error("�� �� ������� ���������...");
			}
			elseif( $IN['cname'] == "" ) {
				$ADMIN->error("�� �� ������� �������� ���������...");
			}

			$DB->query( "UPDATE ibf_files_cats SET cname = '" . $IN['cname'] . "' , sub = '" . $IN['sub'] . "' , cdesc = '" . $IN['cdesc'] . "' , copen = '" . $IN['copen'] . "', dis_screen = '" . $IN['dis_screen'] . "', dis_screen_cat = '" . $IN['dis_screen_cat'] . "', authorize = '" .$IN['authorize'] . "', fordaforum = '" . $IN['fordaforum'] . "', show_notes = '" . $IN['show_notes'] . "', cnotes = '" . $IN['cnotes'] . "'  WHERE cid = " . $IN['cid'] );

			$ADMIN->done_screen("��������� ���������������", "����������������� ��������� ������", "act=downloads" );
		}
		if( $type == "del" ) {
			if( $IN['del'] == "" ) {
				$ADMIN->error("�� �� ������� ���������...");
			}
			if( $IN['trans'] == "" ) {
				$ADMIN->error("���������, � ������� �� ������ ����������� �����, �� ����������");
			}
			$DB->query( "UPDATE ibf_files SET cat = '" . $IN['trans'] . "' WHERE cat = '" . $IN['del'] . "'" );
			$DB->query( "DELETE FROM ibf_files_cats WHERE cid = '" . $IN['del'] . "'" );

			$ADMIN->done_screen("��������� ������� � ����� ����������!", "����������������� ��������� ������", "act=downloads" );
		}
	}

	function switch_download( ) {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
        if (!$INFO['d_section_close']) {
            $status = "�������";
        } else {
            $status = "��������";
        }
        $ADMIN->page_title   = "��������� ��������� ������ (���/���� ������)";
        $ADMIN->page_detail  = "������ ����� ".$status;
		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'do_switch' ),
												  2 => array( 'act'   , 'downloads'     ),
									     )      );

		$ADMIN->html .= $SKIN->start_table( "���������" );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� �����?</b>" ,
												  $SKIN->form_yes_no("d_section_close", $INFO['d_section_close'] )
									     )      );
		$ADMIN->html .= $SKIN->end_form("��������� ���������");

		$ADMIN->html .= $SKIN->end_table( );

		$ADMIN->output( );
	}

	function show_edit_vars(  ) {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		$o_array   = array( );
		$o_array[] = array( 1 , "��" );
		$o_array[] = array( 0 , "���" );

		$ADMIN->page_title = "�������������� �������� ��������� ������";

		$ADMIN->page_detail = "����� �� ������ ������������� ��������� ������ ��������� ������";

		//+-------------------------------

		$ADMIN->html .= $SKIN->start_form( array( 1 => array( 'code'  , 'editvars' ),
												  2 => array( 'act'   , 'downloads'     ),
									     )      );

		$ADMIN->html .= $SKIN->start_table( "�������������� ��������" );
		$max_size=ini_get('upload_max_filesize');


		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ���� � ���������� ��� �������� ������</b><br>�� �������� ������� � ����� ����" ,
												  $SKIN->form_input("d_download_dir",$INFO['d_download_dir'],"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ ���� � ���������� ��� �������� ����������</b><br>�� �������� ������� � ����� ����" ,
												  $SKIN->form_input("d_screen_dir",$INFO['d_screen_dir'],"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ URL ���������� ��� �������� ������</b><br>�� �������� ������� � ����� ����</b>" ,
												  $SKIN->form_input("d_download_url",$INFO['d_download_url'],"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b><b>������ URL ���������� ��� �������� ����������</b><br>�� �������� ������� � ����� ����" ,
												  $SKIN->form_input("d_screen_url",$INFO['d_screen_url'],"text"),
											    )		);


        $ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ����� �� ��� ������?</b>" ,
												  $SKIN->form_yes_no("d_upload",$INFO['d_upload'] ),
											    )		);
											    
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ��������� ������ �� �����?<br>(���������� ������ � ������ ��������... �� ������ ������, ��� ���������� ������)</b>" ,
												  $SKIN->form_yes_no("d_linking", $INFO['d_linking'] )
									            )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ������ ������������ � ����� �����?</b><br />� ����� ����� php.ini, ���������� upload_max_filesize ����������� �� ".$max_size."<br />� ��(����������)" ,
												  $SKIN->form_input("d_max_dwnld_size",$INFO['d_max_dwnld_size'],"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� �������� ����������?</b>" ,
												  $SKIN->form_yes_no("d_screenshot_allowed", $INFO['d_screenshot_allowed'] )
									            )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� �������� ���������� ������������ �����������?</b>" ,
												  $SKIN->form_yes_no("d_screenshot_required", $INFO['d_screenshot_required'] )
											    )		);


		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������������ ������ ������������ � ����� ����� ���������?</b><br>� ��(����������)" ,
												  $SKIN->form_input("d_screen_max_dwnld_size",$INFO['d_screen_max_dwnld_size'],"text"),
											    )		);

		// Reconvert array into a text string
		$dext = "";
        foreach( $INFO['d_allowable_ext'] as $value){
            $dext .= $value."|";
        }
        $dext = substr($dext ,0 ,-1);	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� ��� ����������� ������</b><br>���������� ��������� ����� ������ '|', ������ \".txt|.zip\"" ,
												  $SKIN->form_input("d_allowable_ext",$dext,"text"),
											    )		);
		// Reconvert array into a text string
		$sext = "";
        foreach( $INFO['d_screenshot_ext'] as $value){
            $sext .= $value."|";
        }
        $sext = substr($sext ,0 ,-1);	
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ���������� ��� ����������� ����������</b><br>���������� ��������� ����� ������ '|', ������ \".gif|.jpeg\"" ,
												  $SKIN->form_input("d_screenshot_ext",$sext,"text"),
											    )		);

		// Reconvert array into a text string

	if( $INFO['d_files_perpage'] ==""){
		$pages = "10|20|30|40|50";
	} else {
		$pages = "";
        foreach( $INFO['d_files_perpage'] as $value){
            $pages .= $value."|";
        }
        $pages = substr($pages ,0 ,-1);	
	}
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ������ �������������� ����������� ���-�� ������ �� ��������, � ���������� ����</b><br />���������� ��������� ����� ������ '|', ������ \"10|20|30\"" ,
												  $SKIN->form_input("d_files_perpage",$pages,"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������������ �� �������� ������, �� ���������</b>" ,
												  $SKIN->form_input("d_perpage",$INFO['d_perpage'],"text"),
											    )		);


        $ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������� ������ ������?(��� ����������� ����������� ������ �����, ������ �������� �� �� ������ �������)</b>" ,
												  $SKIN->form_yes_no("d_force",$INFO['d_force'] ),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>������ �������� ��� ���������� ������ (������������ �������� ����������� ������ � Kb/s. ��� �����, ���������� �������� ������ ������. ��� ���������� �������, ������� 0)</b>" ,
												  $SKIN->form_input("d_speed",$INFO['d_speed'],"text"),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������������� ���������� (������) ����������?</b><br /> " ,
										 $SKIN->form_checkbox( 'd_show_thumb', $INFO['d_show_thumb'] )."���������� ����������� ���������? <br /> ������ ".$SKIN->form_simple_input( 'd_thumb_w', $INFO['d_thumb_w'] )." x ".$SKIN->form_simple_input( 'd_thumb_h', $INFO['d_thumb_h'] )
								 )      );					 
																								

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������������� �������� ������ �� ����� � ���� �����-�� ������ �������� ������, ���������� ���������� �� ���� �� �������� ���������� �����?</b><br />(����������: ���� ��� ������ �� ��������� ������������ fopen ������ ��� ����� �������� ����������, ��� �� ����� ��������)" ,
												  $SKIN->form_yes_no("d_link_check", $INFO['d_link_check'] )
									            )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ������������� ��������� ����������� � ������?</b><br />(����������: �� ������������� ������������ ���, ���� �� �������� �������������� �������� ��� �� ������ ��� �������� ������. ����������� ���-������ ����.)" ,
												  $SKIN->form_yes_no("d_use_comments", $INFO['d_use_comments'] )
									            )      );

											    
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ���������� \"[�������� ���������]\" ����� ���������� ������������� ����������� ����?</b><br />��� ��������� ������ � ��� ������, ���� �� �������� �������������� �������� ���, ��� �������� ������." ,
												  $SKIN->form_yes_no("d_cat_add",$INFO['d_cat_add'] ),
											    )		);

        $ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� �������������� �������� ������, ������� �������� �������������?</b>" ,
												  $SKIN->form_yes_no("d_admin_auto",$INFO['d_admin_auto'] ),
											    )		);
                                                									
        $ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ����� ���-�� ����������� ������������� ������?</b>" ,
												  $SKIN->form_yes_no("d_topic",$INFO['d_topic'] ),
											    )		);
		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� � ����� ���-�� ��������� ������������� ������?</b>" ,
												  $SKIN->form_yes_no("d_downloads",$INFO['d_downloads'] ),
												)       );

			$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����������� ������� ������� ��������������?</b><br />����������: �� ������ ��������� ��� �������, �� �� ��� ������������ ������� �������������� ��� ������ ���������, ���� ������ �������� � ������������ � �� � ������." ,
												  $SKIN->form_yes_no("d_show_global_notes", $INFO['d_show_global_notes'] ),
											    )		);

		$return_nl = str_replace("<br />", "\n", $INFO['d_global_notes']);
			$ADMIN->html .= $SKIN->add_td_row( array( "<b>������� ����� ������� �������:</b>" ,
													  $SKIN->form_textarea("d_global_notes",$return_nl),
											     )		);


		//-----------------------------------------------------------------------------------------------------------						 
		
		$ADMIN->html .= $SKIN->end_table( );
		$ADMIN->html .= $SKIN->start_table("���������� ��������� ��� ��������� ��� ������������");
		
		//-----------------------------------------------------------------------------------------------------------


		$ds_array   = array( );
		$ds_array[] = array( 0 , "���" );
		$ds_array[] = array( 1 , "��" );
		$ds_array[] = array( 2 , "������������" );

		$as_array   = array( );
		$as_array[] = array( 0 , "���" );
		$as_array[] = array( 1 , "��" );
		$as_array[] = array( 2 , "������������" );


		$DB->query( "SELECT * FROM ibf_forums" );
		$e_array   = array( );
		$e_array[] = array( 0 , "<b>�� ���������</b>" );
		$e_array[] = array( 'percat' , "<b>������������</b>" );
		while( $row = $DB->fetch_row( ) ) {
			$e_array[] = array( $row['id'] , "--{$row['name']}" );

		}



		$ADMIN->html .= $SKIN->add_td_basic( '��� ������, - ��� ��������� ������ �������� ��� ���� ���������.<br>&nbsp;&nbsp;���� �� ������ ���������������� ������������� ��������� ��� ������ ��������� � �����������, � ��� ��������� ������������� ������ ��� ������������, ��������� � ������ ������� ��������, ����� \'������������\'', 'left', 'catrow2' );


 		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ��������������� �������� ������ �����������, ����� �������� � ����������?" ,
												  $SKIN->form_dropdown("d_authorize",$as_array,$INFO['d_authorize']),
									     )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ��������� �� ������� �������� ���������?</b>" ,
												  $SKIN->form_dropdown("d_dis_screen_cat",$ds_array,$INFO['d_dis_screen_cat']),
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���������� ������������� ��������� � ���� ���������, �� �������� ���������� �����?</b>" ,
												  $SKIN->form_dropdown("d_dis_screen",$ds_array,$INFO['d_dis_screen']),
											    )		);


		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�����, � ������� ����� ������������� ����������� ���� ��������� ��� ����� �����.</b><br>���� �� ������ ��������� �������������� �������� ���, �������� ����� �� ���������, � ��� ������������� ���������������� ������ ������� ��� ������ ��������� � �����������, �������� \"������������\"" ,
												  $SKIN->form_dropdown("d_create_topic",$e_array,$INFO['d_create_topic']),
											    )		);
		$ADMIN->html .= $SKIN->end_table( );


		$ADMIN->html .= $SKIN->end_form("��������� ���������");

		$ADMIN->output( );
	}

	function edit_vars( ) {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP, $HTTP_POST_VARS;

		if( $IN['d_max_dwnld_size'] == "" ) {
			$ADMIN->error("�� �� ������� ������������ ������ ����������� ������...");
		}
		elseif( $IN['d_download_dir'] == "" ) {
			$ADMIN->error("�� �� ������� ���� � ���������� ����������� ������...");
		}
		elseif( $IN['d_download_url'] == "" ) {
			$ADMIN->error("�� �� ������� URL ���������� ����������� ������...");
		}
		elseif( $IN['d_allowable_ext'] == "" ) {
			$ADMIN->error("�� �� ������� ���������� ���������� ������...");
		}
		elseif( $IN['d_files_perpage'] == "" ) {
			$ADMIN->error("�� �� ���������� ����� ������ ���-�� ������������ �� �������� ������ ��� ����������� ����...");
		}

		$fix_notes = nl2br($HTTP_POST_VARS['d_global_notes']);
		$content = "<?php\n";
		$allow1 = preg_replace( "/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_allowable_ext']) );
		$allow1 = explode('|',$allow1);
		$allowable = "";
        foreach( $allow1 as $value){
            $allowable .= "'".strtolower($value)."', ";
        }
        $allowable = substr($allowable ,0 ,-2);
		$content .= "\$INFO['d_allowable_ext']         = array($allowable);\n";
		$screen = preg_replace( "/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_screenshot_ext']) );
		$screen = explode('|',$screen);
		$screenshot = "";
        foreach( $screen as $value){
            $screenshot .= "'".$value."', ";
        }
        $screenshot = substr($screenshot ,0 ,-2);
		$content .= "\$INFO['d_screenshot_ext']        	= array({$screenshot});\n";

		$filesperpage = preg_replace( "/&#124;/", "|", stripslashes($HTTP_POST_VARS['d_files_perpage']) );
		$filesperpage = explode('|',$filesperpage);
		$theoptions = "";
        foreach( $filesperpage as $value){
            $theoptions .= "'".$value."', ";
        }
        $theoptions = substr($theoptions ,0 ,-2);
		$content .= "\$INFO['d_files_perpage']        	= array({$theoptions});\n";
		$content .= "\$INFO['d_max_dwnld_size']        	= {$IN['d_max_dwnld_size']};\n";
		$content .= "\$INFO['d_screen_max_dwnld_size'] 	= {$IN['d_screen_max_dwnld_size']};\n";
		$content .= "\$INFO['d_download_dir']          	= '{$IN['d_download_dir']}';\n";
		$content .= "\$INFO['d_download_url']          	= '{$IN['d_download_url']}';\n";
		$content .= "\$INFO['d_screen_dir']            	= '{$IN['d_screen_dir']}';\n";
		$content .= "\$INFO['d_screen_url']            	= '{$IN['d_screen_url']}';\n";
		$content .= "\$INFO['d_screenshot_allowed']     = '{$IN['d_screenshot_allowed']}';\n";
		$content .= "\$INFO['d_screenshot_required']    = '{$IN['d_screenshot_required']}';\n";
		$content .= "\$INFO['d_authorize']		      = {$IN['d_authorize']};\n";
		$content .= "\$INFO['d_speed']               	= '{$IN['d_speed']}';\n";
		$content .= "\$INFO['d_force']               	= {$IN['d_force']};\n";
		$content .= "\$INFO['d_linking']               	= {$IN['d_linking']};\n";
		$content .= "\$INFO['d_link_check']               	= '{$IN['d_link_check']}';\n";
		$content .= "\$INFO['d_use_comments']		= {$IN['d_use_comments']};\n";
		$content .= "\$INFO['d_upload']               	= {$IN['d_upload']};\n";
		$content .= "\$INFO['d_perpage']		      = '{$IN['d_perpage']}';\n";
		$content .= "\$INFO['d_create_topic']		= '{$IN['d_create_topic']}';\n";
		$content .= "\$INFO['d_topic']                 	= {$IN['d_topic']};\n";
		$content .= "\$INFO['d_downloads']			= {$IN['d_downloads']};\n";
		$content .= "\$INFO['d_dis_screen_cat']		= {$IN['d_dis_screen_cat']};\n";
		$content .= "\$INFO['d_dis_screen']			= {$IN['d_dis_screen']};\n";
		$content .= "\$INFO['d_cat_add']			= {$IN['d_cat_add']};\n";
		$content .= "\$INFO['d_admin_auto']			= {$IN['d_admin_auto']};\n";
		$content .= "\$INFO['d_show_thumb']              = '{$IN['d_show_thumb']}';\n";
		$content .= "\$INFO['d_thumb_w']               	= '{$IN['d_thumb_w']}';\n";
		$content .= "\$INFO['d_thumb_h']               	= '{$IN['d_thumb_h']}';\n";
		$content .= "\$INFO['d_show_global_notes']      = '{$IN['d_show_global_notes']}';\n";
		$content .= "\$INFO['d_global_notes']           = '{$fix_notes}';\n";

		$content .= "?".">\n";

		$open = fopen( $root_path."downloads_config.php" , "w" );
		fwrite( $open , $content );
		fclose( $open );

		$ADMIN->done_screen("��������� ������� ���������", "����������������� ��������� ������", "act=downloads" );
	}
	

	function main_screen( ) {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$DB->query( "SELECT cid FROM ibf_files_cats" );
		$cats 	 = $DB->get_num_rows( );

		$DB->query( "SELECT id FROM ibf_files" );
		$scripts = $DB->get_num_rows( );

		$DB->query( "SELECT DISTINCT author FROM ibf_files" );

		$authors = "";

		while( $row = $DB->fetch_row( ) ) {
			if( $authors == "" ) {
				$authors .= $row['author'];
			}
			else {
				$authors .= " , " . $row['author'];
			}
		}

		$DB->query( "SELECT SUM( downloads ) as down FROM ibf_files" );
		$downs	 = $DB->fetch_row( );

		$DB->query( "SELECT sname FROM ibf_files ORDER BY downloads DESC LIMIT 0,1" );
		$maxd	 = $DB->fetch_row( );

		$DB->query( "SELECT sname FROM ibf_files ORDER BY views DESC LIMIT 0,1" );
		$maxv	 = $DB->fetch_row( );

		$ADMIN->page_title = "����� ���������� � ������ ����������������� ��������� ������";

		$ADMIN->page_detail = "���, ������� �������� ����������������� ��������� ������.";

		//+-------------------------------
		$SKIN->td_header[] = array( "&nbsp;", "50%" );
		$SKIN->td_header[] = array( "&nbsp;", "50%" );
		$ADMIN->html .= $SKIN->start_table( "�������� ����������������� ��������� ������" );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ��������� � ������</b>" , $cats)      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ������ � ������</b>" , $scripts)		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���-�� ���������� ������</b>" , $downs['down'])		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>�������������� ������</b>" ,$authors
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���������� ���� �� ���-�� ����������</b>" ,
												  $maxd['sname']
											    )		);

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����� ���������� ���� �� ���-�� ����������</b>" ,
												  $maxv['sname']
											    )		);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}
}


?>
