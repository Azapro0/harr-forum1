<?php

//----- SET UP CUSTOM HEADERS AND FOOTERS HERE --//

$EMAIL['header'] = "";

$EMAIL['footer'] = <<<EOF

� ���������,

������������� <#BOARD_NAME#>.
<#BOARD_ADDRESS#>

EOF;

$SUBJECT['new_topic_queue_notify'] = '����� ����, ��������� �������������';
$EMAIL['new_topic_queue_notify'] = <<<EOF
������������!

��� ������ ���������� ��: <#BOARD_NAME#>.

������� ����� ����, ��������� �������� ����������� � ������� 
� � �������������.

----------------------------------
����: <#TOPIC#>
�����: <#FORUM#>
�����: <#POSTER#>
����: <#DATE#>
���������� ���������� ������: <#LINK#>
----------------------------------

���� �� ������ �� ������ �������� ����� �����������,
������� ���� e-mail ����� �� �������� �������, ����� ����� ��������� ������.

<#BOARD_ADDRESS#>


EOF;



$SUBJECT['pm_notify'] = '� ��� ����� ������ �� PM';
$EMAIL['pm_notify'] = <<<EOF
<#NAME#>,

<#POSTER#> �������� ��� ����� ������ �� ������ ����, � ���������� "<#TITLE#>".

��� ������ ����� ������, ������� �� ������������� ������:

<#BOARD_ADDRESS#><#LINK#>


EOF;



$EMAIL['send_text']	= <<<EOF
����� ��� ������������ ��� ����: <#THE LINK#>

� ���������,
<#USER NAME#>

EOF;


$EMAIL['report_post'] = <<<EOF

<#MOD_NAME#>,

������ ���������� �� <#USERNAME#> ����� ������ "�������� ����������".

------------------------------------------------
����: <#TOPIC#>
------------------------------------------------
������ �� ���������: <#LINK_TO_POST#>
------------------------------------------------
������:

<#REPORT#>

------------------------------------------------

EOF;



$EMAIL['pm_archive'] = <<<EOF

<#NAME#>,
��� ������ ���������� �� <#BOARD_ADDRESS#>.

��� ���� �������������� ��������� ����� � ����������� � ����� ������.

EOF;

$EMAIL['reg_validate'] = <<<EOF

<#NAME#>,
��� ������ ���������� �� <#BOARD_ADDRESS#>.

�� �������� ��� ������, ��� ��� ���� e-mail ����� 
��� ����������� ��� ����������� �� ������.
���� �� �� ���������������� �� ���� ������, ������ 
�������������� ��� ������ � ������� ���. �� ������ 
�� �������� ������ ������.

------------------------------------------------
���������� �� ���������
------------------------------------------------

���������� �� �����������.
�� ������� �� ��� "�������������" ����� �����������, ��� �������� ����, 
��� �������� ���� e-mail ����� - ��������. ��� ��������� ��� ������ �� 
������������� ��������������� � �����.

��� ��������� ������ ��������, ������� �� ��������� ������:

<#THE_LINK#>

(������������� AOL E-mail, ����������� ����������� ��� ������ � �������� � �������� ������ 
��������).

------------------------------------------------
�� ���������?
------------------------------------------------

���� � ��� ������ �� ���������� � �� �� ������ ����������� ���� �����������, ������� 
�� ��������� ������:

<#MAN_LINK#>

��� �� ��� ����������� ���� ID ������������ � ����� �������������. ���� ������� ��� ������:

ID ������������: <#ID#>

���� �������������: <#CODE#>

����������� �������� ����������/�������� ��� ������� ��� ������ �������, � ��������������� 
����.

���� � ��� ���� ��������� ������ �� ����������, �������� ��� ������� �����.
� ���� ������, ���������� � ��������������, ��� ���������� ��������.

���������� ��� �� �����������!

EOF;

$EMAIL['admin_newuser'] = <<<EOF

������������ ��������� �������������!

�� �������� ��� ������, � ����� � ������������ ������ ������������!

������������ <#MEMBER_NAME#> ����������������� - <#DATE#>
IP ����� ����� ������������ - <#IP_ADDRESS#>
E-mail ����� ����� ������������ - <#EMAIL#>

�� ������ ��������� ��� ����������� ����� ����������.

������� ��� ���!

EOF;

$EMAIL['lost_pass'] = <<<EOF

<#NAME#>,
��� ������ ���������� �� <#BOARD_ADDRESS#>.

�� �������� ��� ������, � ����� � �������� �� �������������� 
�������� ������ �� <#BOARD_NAME#>.

------------------------------------------------
�����!
------------------------------------------------

���� �� �� ������ ������� �� ��������� ������, �������������� � ���������� ������� ��� 
������. ����������� ������ � ��� ������, ���� ��� ������������� ��������� �������������� ������!

------------------------------------------------
���������� �� ��������� ����.
------------------------------------------------

�� ������� �� ��� "�������������" ������ ������� �� �������������� �������� ������
��� �������� ����, ��� ��� �������� ��������� ������ ����. ��� ��������� ��� ������ �� 
������������� ���������������.

������� �� ������������� ������ � ��������� ��������� ���� �����

<#THE_LINK#>

(������������� AOL E-mail, ����������� ����������� ��� ������ � �������� � �������� ������ 
��������).

------------------------------------------------
�� ���������?
------------------------------------------------

���� ��� �� ������� ������������ ���� �����������, ������� �� ������

<#MAN_LINK#>

��� �� ������ ������ ������ ID ������������ � ���� �������������. ���� ������� ��� 
������:

ID ������������: <#ID#>

���� �������������: <#CODE#>

����������� �������� ����������/�������� ��� ������� ��� ������ �������, � ��������������� 
���� �����.

------------------------------------------------
�� ���������?
------------------------------------------------

���� ���� ������������� �� ����������, �������� ��� ������� ����� ��� �� �� ������ ������� ����������
���������, �������� ��� ����������� ��� ��������� e-mail ������. � ���� ������, ��������� ���������� ���������. ��� �����������/������������� ���������� ������, ���������� ���������� � ��������������, ��� ���������� ��������.

IP ����� �����������: <#IP_ADDRESS#>


EOF;

$EMAIL['newemail'] = <<<EOF

<#NAME#>,
��� ������ ���������� �� <#BOARD_ADDRESS#>.

�� �������� ��� ������, � ����� � ���������� e-mail ������.

------------------------------------------------
���������� �� ��������� ����
------------------------------------------------

�� ������� �� ��� "�������������" ��������� ������ e-mail ������, ��� �������� ����, ��� ��� 
�������� ��������� ������ ����. ��� ��������� ��� ������ �� ������������� 
��������������� � �����.

��� ��������� �������� ������� �� ��������� ������:

<#THE_LINK#>

(������������� AOL E-mail, ����������� ����������� ��� ������ � �������� � �������� ������ 
��������).

------------------------------------------------
�� ���������?
------------------------------------------------

���� ��� �� ������� ������������ ���� �����������, ������� �� ������

<#MAN_LINK#>

��� �� ������ ������ ������ ID ������������ � ���� �������������. ���� ������� ��� 
������:

ID ������������: <#ID#>

���� �������������: <#CODE#>

����������� �������� ����������/�������� ��� ������� ��� ������ �������, � ��������������� 
���� �����.

����� ���������� ���������, ���������� ������������������, ��� ���������� ����� ������.

------------------------------------------------
��������! � ������� ������!
------------------------------------------------

���� ���� ������������� �� ����������, �������� ��� ������� ����� ��� �� �� ������ ������� ����������
���������, �������� ��� ����������� ��� ��������� e-mail ������. � ���� ������, ��������� ���������� ���������. ��� �����������/������������� ���������� ������, ���������� ���������� � ��������������, ��� ���������� ��������.


EOF;

$EMAIL['forward_page'] = <<<EOF

<#TO_NAME#>


<#THE_MESSAGE#>

---------------------------------------------------
���������, ��� <#BOARD_NAME#> �� ���� ��������������� �� ���������� ����� ������.
---------------------------------------------------

EOF;

$SUBJECT['subs_with_post'] = '����������� �� ������ � ����������� ����';

$EMAIL['subs_with_post'] = <<<EOF
<#NAME#>,

<#POSTER#> ������� � ���� "<#TITLE#>", �� ������� �� ���������.

----------------------------------------------------------------------
<#POST#>
----------------------------------------------------------------------

���� ��������� �����:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>&view=getnewpost



�������� � ���� ������ ������ ������, �� ������ 1 e-mail ����� ��������� ��� ����� ������� ��������� 
����, �� ������� �� ���������. ��� �������� �������� �� ���������� ����� ������������ ���.

�������:
--------------

�� ������ ���������� �� ���� � ����� �����, ����� ��� �������, ������� �� ������ "��� ��������".

EOF;

$SUBJECT['subs_new_topic'] = '����������� � ����� ���� � ����������� ������';
$EMAIL['subs_new_topic'] = <<<EOF
<#NAME#>,

<#POSTER#> ������ ����� ���� � ���������� "<#TITLE#>" � ������ "<#FORUM#>".

���� ��������� �����:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>

���������, ��� ���� �� ������ �������� ����������� �� ������� � ��� ����, �� ������ �������� 
�� ������ 
"�������� �� ����", ����������� �� �������� ����� ���� ��� ������� ������������� ������
<#BOARD_ADDRESS#>?act=Track&f=<#FORUM_ID#>&t=<#TOPIC_ID#>


�������:
--------------

�� ������ ���������� �� ���� � ����� �����, ����� ��� �������, ������� �� ������ "��� ��������".

EOF;

$SUBJECT['subs_no_post'] = '������������� ������ � ����������� ����';
$EMAIL['subs_no_post'] = <<<EOF
<#NAME#>,

<#POSTER#> ������� � ���� "<#TITLE#>", �� ������� �� ���������.

���� ��������� �����:
<#BOARD_ADDRESS#>?showtopic=<#TOPIC_ID#>&view=getnewpost

�������� � ���� ������ ������ ������, �� ������ 1 e-mail ����� ��������� ��� ����� ������� ��������� 
����, �� ������� �� ���������. ��� �������� �������� �� ���������� ����� ������������ ���.

�������:
--------------

�� ������ ���������� �� ���� � ����� �����, ����� ��� �������, ������� �� ������ "��� ��������".

EOF;



$EMAIL['email_member'] = <<<EOF
<#MEMBER_NAME#>,

<#FROM_NAME#> �������� ��� ��� ������ �� <#BOARD_ADDRESS#>.


<#MESSAGE#>

---------------------------------------------------
���������, ��� <#BOARD_NAME#> �� ���� ��������������� �� ���������� ����� ������.
---------------------------------------------------


EOF;

$EMAIL['complete_reg'] = <<<EOF

������������!

������������� ���������� ���� ����������� ��� ������ �� ��������� e-mail ������ � <#BOARD_NAME#>. �� ������ ��������������
� ������ ������� � ����� ������ ������ � <#BOARD_ADDRESS#>

EOF;


?>