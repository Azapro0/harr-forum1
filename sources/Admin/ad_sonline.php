<?php

/*
+--------------------------------------------------------------------------
|  Image/Text Online/Offline  Mod v3.3 by Shadow Fox (c) 2003
+---------------------------------------------------------------------------
|
|   > Image/Text Online/Offline  Mod v3.3
|   > Module written by Shadow Fox
|   > Date started: May 26th 2003
|   > Module Version Number: 1.0
+--------------------------------------------------------------------------
*/

$idx = new ad_sonline();


class ad_sonline {

	var $base_url;

		function ad_sonline() {

		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$tmp_in = array_merge( $_GET, $_POST, $_COOKIE );



		foreach ( $tmp_in as $k => $v )

		{

			unset($$k);

		};

		switch($IN['code'])
		{
                        case 'sonline':

				$this->sonline();

				break;

			case 'dosonline':

				$this->save_config( array ( 'on_status_color', 'off_status_color','on_status_image', 'off_status_image','status_type','status_set', 'status_prefix', ) );

				break;
			
			default:
				$this->sonline();
				break;
		}
		
	}	
	
	function sonline() {
		global $IN, $root_path, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;
		
		$ADMIN->page_title = "��������� ������� Online/Offline";
		$ADMIN->page_detail  = "��� �����������, ��� ����������� Online/Offline ������� �������������, ��� �������� ������������� � ���������� ������
                                        <br><br>
                                        If you have any problems with this mod please see the support topic <a href='http://forums.ibplanet.com/index.php?&act=ST&f=50&t=7863'>here</a>
                                        <br><br>
                                        ����� ����������� <a href='http://forums.ibplanet.com/index.php?act=Profile&CODE=03&MID=5'>Shadow Fox</a> - ����������� �������� ��� <a href='http://ibplanet.com'>IBPlanet.com</a>";
		
                $ADMIN->html .= $SKIN->start_form( array( 					1 => array( 'code'  , 'dosonline'),
												2 => array( 'act'   , 'sonline'     ),
											) );
  
                $SKIN->td_header[] = array( ""       , "60%" );
                $SKIN->td_header[] = array( ""       , "40%" );


                $ADMIN->html .= $SKIN->start_table( "��������� ������� Online/Offline" );

                $ADMIN->html .= $SKIN->add_td_basic( '�������� ���������', 'left', 'pformstrip' );



                $ADMIN->html .= $SKIN->add_td_row( array( "<b>�������� ����������� ������� Online/Offline?</b>" ,

										  $SKIN->form_yes_no( "status_set", $INFO['status_set'] )

								 )      );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>��� ����������� �������</b><br>(����� ������������ ����������� � ���� ����������� ��� � ���� ������)" ,



		$SKIN->form_dropdown( 'status_type', array( 0 => array( 'text', '�����' ), 1 => array( 'image' , '�����������' ), ), $INFO['status_type']  )

								 )      );

               $ADMIN->html .= $SKIN->add_td_row( array( "<b>��������� ��� ��������</b><br>(��������, '������:' <br>����� �� ���������)" ,

										  $SKIN->form_input( "status_prefix", $INFO['status_prefix'] )

								 )      );
         
                $ADMIN->html .= $SKIN->add_td_basic( '��������� ������', 'left', 'pformstrip' );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>���� ��� Online �������</b><br>(�� ��������� ������������ ������ ����. ������ ������������ hex ����.)" ,

										  $SKIN->form_input( "on_status_color", $INFO['on_status_color'] )

								 )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>���� ��� Offline �������</b><br>(�� ��������� ������������ ������ ����. ������ ������������ hex ����.)" ,

										  $SKIN->form_input( "off_status_color", $INFO['off_status_color'] )

								 )      );
         
               $ADMIN->html .= $SKIN->add_td_basic( '��������� �����������', 'left', 'pformstrip' );

		$ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��� Online �������</b><br>(������ ������� �������� �����, ��������: Online.gif)" ,

										  $SKIN->form_input( "on_status_image", $INFO['on_status_image'] )

								 )      );

                $ADMIN->html .= $SKIN->add_td_row( array( "<b>����������� ��� Offline �������</b><br>(������ ������� �������� �����, ��������: Offline.gif)" ,

										  $SKIN->form_input( "off_status_image", $INFO['off_status_image'] )

								 )      );
  
                $this->common_footer();
                
		$ADMIN->output();
		
	}
 
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

				$HTTP_POST_VARS[ $field ] = preg_replace( "/'/", "&#39;", stripslashes($HTTP_POST_VARS[ $field ]) );

					$master[ $field ] = stripslashes($HTTP_POST_VARS[ $field ]);
				}

				$ADMIN->rebuild_config($master);
			}
		}

		$ADMIN->save_log("���������� �������� ������, Back Up ������");


		$ADMIN->done_screen("��������� ������ ���������", "������� �������� �����������", "act=index" );
}

	function common_footer( $button="��������� ���������" )

	{

		global $IN, $INFO, $DB, $SKIN, $ADMIN, $std, $MEMBER, $GROUP;

		$ADMIN->html .= $SKIN->end_form($button);

		$ADMIN->html .= $SKIN->end_table();

		$ADMIN->output();

	}
}

?>
