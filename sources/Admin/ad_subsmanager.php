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
|   > Subscription Manager For IPB
|   > Module written by Matt Mecham
|   > Date started: 19th August 2003
|
|	> Module Version Number: 1.0.0
+--------------------------------------------------------------------------
*/

if ( ! defined( 'IN_ACP' ) )
{
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
	exit();
}

$idx = new ad_subscriptions();

class ad_subscriptions {

	var $base_url;

	function ad_subscriptions()
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
		
		$ADMIN->page_title = "��������� ���������� IPB";
		
		$ADMIN->page_detail = "� ���� ������ �� ������ ������������� � ��������� ������������� ���������� �������������.";
		
		$ADMIN->nav[] = array( 'act=msubs'              , '������� �������� ���������� ����������' );
		$ADMIN->nav[] = array( 'act=msubs&code=dosearch', '�������� ���� ����������� �������������' );
		
		//---------------------------------------
		// Do some set up
		//---------------------------------------
		
		if ( ! @is_dir( ROOT_PATH.'/modules/subsmanager' ) )
		{
			$std->boink_it("http://customer.invisionpower.com/ipb/subs/redirect_acp.php");
		}
		else
		{
			define( 'IPB_CALLED', 1 );
			
			require ROOT_PATH.'/modules/subsmanager/ad_plugin_subsm.php';
			
			$PLUGIN = new ad_plugin_subsm();
		}
		
		//---------------------------------------
		
		switch($IN['code'])
		{
			case 'editpkginfo':
				$PLUGIN->edit_pkg_gateway_info();
				break;
			case 'doeditpkg':
				$PLUGIN->doedit_pkg_gateway_info();
				break;
			//-------------------------
			case 'removepackage':
				$PLUGIN->remove_package();
				break;
			case 'doremovepackage':
				$PLUGIN->do_remove_package();
				break;
			//-------------------------
			case 'removemembers':
				$PLUGIN->remove_members();
				break;
			case 'doremovemembers':
				$PLUGIN->do_remove_members();
				break;
			//-------------------------
			case 'addpackage':
				$PLUGIN->alter_package_form('add');
				break;
				
			case 'doaddpackage':
				$PLUGIN->do_add_package();
				break;
			//-------------------------
			case 'editpackage':
				$PLUGIN->alter_package_form('edit');
				break;
				
			case 'doeditpackage':
				$PLUGIN->do_edit_package();
				break;
			//-------------------------
			case 'editmethod':
				$PLUGIN->edit_method();
				break;
				
			case 'doeditmethod':
				$PLUGIN->do_edit_method();
				break;
			//-------------------------
			case 'dosearch':
				$PLUGIN->do_search();
				break;
			case 'searchlog':
				$PLUGIN->do_search_log();
				break;
			case 'searchlogview':
				$PLUGIN->do_search_log_view();
				break;
			//-------------------------
			case 'domodifytrans':
				$PLUGIN->do_modify_trans();
				break;
				
			case 'dotransdelete':
				$PLUGIN->do_delete_trans();
				break;
			
			//-------------------------
			case 'edittransaction':
				$PLUGIN->edit_transaction('edit');
				break;
				
			case 'addtransaction':
				$PLUGIN->edit_transaction('add');
				break;
			
			case 'doedittransaction':
				$PLUGIN->save_transaction('edit');
				break;
				
			case 'doaddtransaction':
				$PLUGIN->save_transaction('add');
				break;
				
			case 'overview':
				$PLUGIN->do_overview();
				break;
				
			//-------------------------
			
			case 'currency':
				$PLUGIN->currency_index();
				break;
			case 'editcurrency':
				$PLUGIN->currency_edit();
				break;
			case 'deletecurrency':
				$PLUGIN->currency_delete();
				break;
				
			default:
				$PLUGIN->index_screen();
				break;
		}
		
	}
		
}


?>