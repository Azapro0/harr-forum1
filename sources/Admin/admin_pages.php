<?php

// Simple library that holds all the links for the admin cp

// CAT_ID => array(  PAGE_ID  => (PAGE_NAME, URL ) )

// $PAGES[ $cat_id ][$page_id][0] = Page name
// $PAGES[ $cat_id ][$page_id][1] = Url

 
$PAGES = array(

				/*0 => array (
							 1 => array( '������� IPS'         , 'act=ips&code=news'   ),
							 2 => array( '�������� ����������'      , 'act=ips&code=updates'  ),
							 3 => array( '������������'     , 'act=ips&code=docs'    ),
							 4 => array( '���������'       , 'act=ips&code=support' ),
							 5 => array( 'IPS �������'  , 'act=ips&code=host'   ),
							 6 => array( '������� ������'    , 'act=ips&code=purchase'     ),
						   ),*/
						   
				1 => array (
				
							1 => array( 'IP ���'              , 'act=pin&code=ipchat'  ),
							2 => array( 'IPS �������'          , 'act=ips&code=host'    ),
							3 => array( '����������� IPB'     , 'act=pin&code=reg'     ),
							4 => array( '�������� ���������� IPB', 'act=pin&code=copy'    ),
							5 => array( '���������� ����������'    , 'act=msubs' ),
							6 => array( '&#0124;-- ����'          , 'act=msubs&code=searchlog', 'modules/subsmanager' ),
							7 => array( '&#0124;-- ��������� �����' , 'act=msubs&code=currency', 'modules/subsmanager' ),
							8 => array( '&#039;-- ������'   , 'act=msubs&code=dosearch', 'modules/subsmanager' ),
							
							
						   ),

				2 => array (
							 1 => array( '������� ������������', 'act=op&code=url'   ),
							 2 => array( '������������'      , 'act=op&code=secure'  ),
							 3 => array( '����, ���������, ������', 'act=op&code=post'    ),
							 4 => array( '������� �������������'      , 'act=op&code=avatars' ),
							 5 => array( '������ ���� � �������'  , 'act=op&code=dates'   ),
							 6 => array( '�������� CPU'    , 'act=op&code=cpu'     ),
							 7 => array( 'Cookies'       , 'act=op&code=cookie'  ),
							 8 => array( '��������� PM'       , 'act=op&code=pm'    ),
							 9 => array( '���/���� ������'    , 'act=op&code=board' ),
							 10 =>array( '��������� ��������'    , 'act=op&code=news' ),
							 11 =>array( '���������/����������'    , 'act=op&code=calendar' ),
							 12 =>array( '��������� COPPA'       , 'act=op&code=coppa' ),
							 13 =>array( 'IBF ������'         , 'act=op&code=portal' ),
							 14 =>array( '��������� Email'       , 'act=op&code=email' ),
							 15 =>array( '������ �������' , 'act=op&code=phpinfo' ),
							 16 =>array( '����������� ������'   , 'act=op&code=glines' ),
							 17 =>array( '�������������� �����', 'act=op&code=fulltext'),
							 18 =>array( '��������� ������', 'act=op&code=spider' ),
							 19 =>array( '��������� ��������'       , 'act=op&code=warn' ),
							 20 =>array( '��������� IPDynamic Lite'    , 'act=csite', 'sources/dynamiclite' ),
							 21 =>array( '��������� Online/Offline'   , 'act=sonline' ),
							 ),

				3 => array (
							 1 => array( '����� ���������'        , 'act=cat&code=new'        ),
							 2 => array( '����� �����'           , 'act=forum&code=newsp'    ),
							 3 => array( '���������� ��������'    , 'act=cat&code=edit'       ),
							 4 => array( '����� �������'    , 'act=group&code=permsplash'),
							 5 => array( '�������������� ���������' , 'act=cat&code=reorder'    ),
							 6 => array( '�������������� �������'     , 'act=forum&code=reorder'  ),
							 7 => array( '����������'          , 'act=mod'                 ),
							 //8 => array( '������-��������� ���', 'act=multimod'          ),
						   ),
						   
				
				4 => array (
							 1 => array( '������ �����������'       , 'act=modcp'     , 1     ),
							 2 => array( '������-��������� ���', 'act=multimod'          ),
						   ),
						   
						   
				5 => array (
							1 => array ( '�����. �����������'        , 'act=mem&code=add'  ),
							2 => array ( '�����/������/���� �������.'      , 'act=mem&code=edit' ),
							3 => array ( '�������� �������������'      , 'act=mem&code=del'  ),
							4 => array ( '������ ���������������', 'act=mem&code=advancedsearch&showsusp=1' ),
							5 => array ( '��� �������������'        , 'act=mem&code=ban'  ),
							6 => array ( '������� �������������'    , 'act=mem&code=title'),
							7 => array ( '���������� ��������'  , 'act=group'         ),
							8 => array ( '������������� ���-���', 'act=mem&code=mod'  ),
							9 => array ( '���-��� ���� �������', 'act=field'       ),
							10 => array ( '�������� Email ��������'   , 'act=mem&code=mail' ),
							11 => array ( '���������������� ��������'         , 'act=mtools'  ),
							12 => array ( '������������'  , 'act=massive' ),
							13 => array ( '����� ������ �����'   , 'act=shadow' ),
							
						   ),
						   
				6 => array (
							1 => array( '������ ����������� ����', 'act=op&code=bw'    ),
							2 => array( '��������� ���������', 'act=op&code=emo'   ),
							3 => array( '��������� ������', 'act=help'         ),
							4 => array( '�������� ����������', 'act=op&code=count'    ),
							
						   ),
						   
				7 => array (
							1 => array( '<b>��������� ������</b>' , 'act=sets'        ),
							2 => array( '&#0124;-- ������� ������'   , 'act=wrap'        ),
							3 => array( '&#0124;-- HTML �������'   , 'act=templ'       ),
							4 => array( '&#0124;-- �����'    , 'act=style'       ),
							5 => array( '&#039;-- �������'           , 'act=image'       ),
							6 => array( '������ ������'       , 'act=import'      ),
							7 => array( '�������� ������ �����'    , 'act=skinfix'      ),
							
						   ),
						   
				8 => array (
							1 => array( '��������� ������' , 'act=lang'             ),
							2 => array( '�������������� �����', 'act=lang&code=import' ),
						   ),
						   
				9 => array (
							1 => array( '���������� �����������' , 'act=stats&code=reg'   ),
							2 => array( '���������� ����� ���'    , 'act=stats&code=topic' ),
							3 => array( '���������� ���������'         , 'act=stats&code=post'  ),
							4 => array( '���������� PM'    , 'act=stats&code=msg'   ),
							5 => array( '��������� ���'        , 'act=stats&code=views' ),
						   ),
						   
				10 => array (
							1 => array( '�������� mySQL'   , 'act=mysql'           ),
							2 => array( '��������� ����� mySQL'   , 'act=mysql&code=backup'    ),
							3 => array( '���������� SQL Runtime', 'act=mysql&code=runtime'   ),
							4 => array( '���������� SQL' , 'act=mysql&code=system'    ),
							5 => array( '�������� SQL'   , 'act=mysql&code=processes' ),
						   ),
				
				11 => array(
							1 => array( '���� �����������', 'act=modlog'    ),
							2 => array( '���� �������'    , 'act=adminlog'  ),
							3 => array( 'Email ����'    , 'act=emaillog'  ),
							4 => array( '���� �����'      , 'act=spiderlog' ),
							5 => array( '���� ��������'     , 'act=warnlog'   ),
						   ),
						   
			   12 => array(
							1 => array( '����������' , 'act=downloads'),
							2 => array( '���������' , 'act=downloads&code=settings'),
							3 => array( '������� ���������' , 'act=downloads&code=showaddcat'),
							4 => array( '������������� ���������' , 'act=downloads&code=showeditcat'),
							5 => array( '������� ���������' , 'act=downloads&code=showdelcat'),
							6 => array( '�������������� ���������' , 'act=downloads&code=reorder'),
							7 => array( '�������������� ����' , 'act=dfield'),
							8 => array( '���/���� ������' , 'act=downloads&code=switch'),
						),
						
			   );
			   
			   
$CATS = array (   
				  //0 => "IPS ������",
				  1 => "���������� IPB",
				  2 => "��������� ���������",
			      3 => '��������� �������',
			      4 => '������������� ������',
				  5 => '������������ � ������',
				  6 => '�����������������',
				  7 => '����� � �������',
				  8 => '�����',
				  9 => '����� ����������',
				  10 => '���������� SQL',
				  11 => '���� ������',
				  12 => '�������� �����',
			  );
			  
$DESC = array (
				 // 0 => "����� ��������� �������, ������������, ������� ���������, ������������ ���. ����� � �.�.",
				  1 => "��������� � ��������� ��������� �������� ��� ������",
				  2 => "�������� ��������� ������, ����� ��� cookies, ������������, �������� ��������� � �.�.",
				  3 => "��������, ��������������, �������� ���������, �������, �����������",
				  4 => "���� � ������ ������������� � ������-��������� ���",
				  5 => "��������������, �����������, ��������, ��� �������������. ��������� ��������. ���������� �������� � �.�.",
				  6 => "��������� ������ ������, ������� ����������� ���� � ���������",
				  7 => "��������� ������, ��������, ������ � �����������.",
				  8 => "��������� ������",
				  9 => "���������� ����������� � ���������",
				  10 => "���������� ����� SQL �����; ������, �����������, ������� ���� � �.�.",
				  11 => "�������� ����� �������, ����������� � �.�. (������ ��� �������)",
				  12 => "���������� �������� �������",
			  );
			  
			  
?>