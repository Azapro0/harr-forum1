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
	print "<h1>�������� ���������</h1>�� �� ������ ���������� ��������������� � ����� �����. ���� �� ����������� ����������, ��������� ��, ��� �� �������� � ���� 'admin.php'.";
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
		
		$ADMIN->nav[] = array( 'act=warn', '��������� ��������' );
		
		$ADMIN->page_detail = "� ���� ������, �� ������ ������������� ��������� ��������";
		$ADMIN->page_title  = "��������� �������� �������������";
		
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
		
		$SKIN->td_header[] = array( "������� ��������"   , "20%" );
		$SKIN->td_header[] = array( "������"      , "80%" );
		
		$ADMIN->html .= $SKIN->start_table( "������� �������� ������" );
		
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
						$ban = "���������� ���";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_ban'] );
						
						$ban = "��������� ���������� ��� ".$val." ".$unit_map[$unit];
					}
				}
				
				if ( $data['warn_modq'] != "" )
				{
					if ( $data['warn_modq'] == 'p' )
					{
						$mod = "���������� ��������� ���������";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_modq'] );
						
						$mod = "���������� ��������� ��������� ��� ".$val." ".$unit_map[$unit];
					}
				}
				
				if ( $data['warn_nopost'] != "" )
				{
					if ( $data['warn_nopost'] == 'p' )
					{
						$nop = "���������� ������ ���������";
					}
					else
					{
						list ($val, $unit) = explode( ',', $data['warn_nopost'] );
						
						$nop = "������ ��������� ��� ".$val." ".$unit_map[$unit];
					}
				}
				
				$html = "<table width='100%' cellpadding='4'>";
				
				if ( $ban != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>���</strong></td>
								<td width='30%'>$ban</td>
								<td width='25%'>�������������</td>
								<td width='25%'>�������</td>
							  </tr>";
				}
				
				if ( $mod != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>��������� ���������</strong></td>
								<td width='30%'>$mod</td>
								<td width='25%'>�������������</td>
								<td width='25%'>�������</td>
							  </tr>";
				}
				
				if ( $nop != "" )
				{
					$html .= "<tr>
								<td width='20%'><strong>������ ���������</strong></td>
								<td width='30%'>$nop</td>
								<td width='25%'>�������������</td>
								<td width='25%'>�������</td>
							  </tr>";
				}
				
				$html .= "</table>";
				
				
				$ADMIN->html .= $SKIN->add_td_row( array( "<b>{$warns['warn_level']}</b>",
														  $html
										 )      );
			}
		}
		
		$ADMIN->html .= $SKIN->add_td_basic( '�������� ����� ������', 'center', 'pformstrip' );
										 
		$ADMIN->html .= $SKIN->end_table();
		
		//+-------------------------------
		//+-------------------------------
		
		$ADMIN->output();
	

		
		
	}
	
	
	
	
	
	
	
}

?>