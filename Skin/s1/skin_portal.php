<?php
class skin_portal {

function member_moment($data) {
global $ibforums;
return <<<EOF
           <tr>
                       <td class='darkrow1' colspan='2'>{$ibforums->lang['member_of_moment']}</td>
               </tr>
           <tr>
                   <td class='row2' width='5%' valign='middle'><{F_ACTIVE}></td>
                   <td class='row4' width="95%" align='left'>{$ibforums->lang['member_of_moment']}:<b>
               <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['member_id']}">{$data['member_name']}</a></b><br>
               {$ibforums->lang['most_active_in']} {$data['forum_url']} ({$data['fav_posts']})<br>
               {$ibforums->lang['total_posts']} {$data['total_posts']} {$ibforums->lang['posts_since']} {$data['join_date']}<br> {$data['avatar']}
                   </td>
               </tr>
EOF;
}

function old_news($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['old_news']}</b></td>
           </tr>
           <tr>
                 <td class='row4' colspan='2'>
                     {$data}
                     </td>
               </tr>           
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

/*
not yet,,, not yet,,,
*/
function upload_form($forum, $maxsize=0) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['upload_form']}</b></td>
           </tr>
<form action='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}' method='POST' name='REPLIER' onSubmit='return ValidateForm()' enctype='multipart/form-data'>
<input type='hidden' name='st' value='0'>
<input type='hidden' name='act' value='Post'>
<input type='hidden' name='s' value='{$ibforums->session_id}'>
<input type='hidden' name='f' value='{$forum}'>
<input type='hidden' name='MAX_FILE_SIZE' value='$maxsize'>
           <tr>
             <td class='row1'>{$ibforums->lang['u_title']}</td>
             <td class='row1'><input type='text' size='30' maxlength='50' name='TopicTitle' value='' tabindex='1' class='forminput'></td>
           </tr>
           <tr> 
             <td class='row1'>{$ibforums->lang['u_description']}</td>
             <td class='row1' width="100%" valign="top"><input type='text' size='40' maxlength='40' name='TopicDesc' value='' tabindex='2' class='forminput'></td>
           </tr>
           <tr>
             <td class='row1' colspan='2'></td>
           </tr>
          </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function new_posts_big($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='8' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['new_posts']}</b></td>
           </tr>
           <tr>
             <td class='row1'><img src='{$ibforums->vars['imgurl']}/spacer.gif' alt='' width='20' height='1'></td>
             <td class='row1'><img src='{$ibforums->vars['imgurl']}/spacer.gif' alt='' width='20' height='1'></td>
             <td class='row1' valign='middle' align='left'><b>{$ibforums->lang['l_title']}</b></td>
             <td class='row1' valign='middle' align='center' width='15%'><b>{$ibforums->lang['l_starter']}</b></td>
             <td class='row1' valign='middle' align='center' width='8%'><b>{$ibforums->lang['l_replies']}</b></td>
             <td class='row1' valign='middle' align='left' width='33%'><b>{$ibforums->lang['l_lastpost']}</b></td>
               </tr>
{$data}
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}
function latest_posts_big($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='8' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['latest_posts']}</b></td>
           </tr>
           <tr>
             <td class='row1'><img src='{$ibforums->vars['imgurl']}/spacer.gif' alt='' width='20' height='1'></td>
             <td class='row1'><img src='{$ibforums->vars['imgurl']}/spacer.gif' alt='' width='20' height='1'></td>
             <td class='row1' valign='middle' align='left'><b>{$ibforums->lang['l_title']}</b></td>
             <td class='row1' valign='middle' align='center' width='15%'><b>{$ibforums->lang['l_starter']}</b></td>
             <td class='row1' valign='middle' align='center' width='8%'><b>{$ibforums->lang['l_replies']}</b></td>
             <td class='row1' valign='middle' align='left' width='33%'><b>{$ibforums->lang['l_lastpost']}</b></td>
               </tr>
{$data}
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}
function RenderRow($Data) {
global $ibforums;
return <<<EOF
    <!-- Begin Topic Entry {$Data['tid']} -->
    <tr> 
          <td align='center' class='row4'>{$Data['folder_img']}</td>
      <td align='center' class='row2'>{$Data['topic_icon']}</td>
      <td class='row4'>
          <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                  <tr> 
                        <td valign='middle'>{$Data['go_new_post']}</td>
            <td width='100%'><span class='linkthru'>{$Data['prefix']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=ST&f={$Data['forum_id']}&t={$Data['tid']}&s={$ibforums->session_id}' class='linkthru' title='{$ibforums->lang['topic_started_on']} {$Data['start_date']}'>{$Data['title']}</a></span>  {$Data[PAGES]}</td>
          </tr>
        </table>
        <span class='desc'>{$Data['description']}</span></td>
      <td align='center' class='row2'>{$Data['starter']}</td>
      <td align='center' class='row4'>{$Data['posts']}</td>
      <td class='row2'>{$Data['last_post']}<br>
                      {$ibforums->lang['in']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SF&f={$Data['forum_id']}'>{$Data['name']}</a><br>
                      <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$Data['forum_id']}&t={$Data['tid']}&view=getlastpost'>{$Data['last_text']}</a> <b>{$Data['last_poster']}</b></td>
    </tr>
    <!-- End Topic Entry {$Data['tid']} -->
EOF;
}

function top_forums($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='3' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['top_forums']}</b></td>
           </tr>
{$data}
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function top_forums_row($data) {
global $ibforums;
return <<<EOF
               <tr>  
                 <td class='row4'>{$data['rating']}. <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=SF&f={$data['id']}'>{$data['name']}</a></td>
                 <td class='row4' width='15%'>{$data['topics']}</td>
                 <td class='row4' width='15%'>{$data['posts']}</td>
               </tr>
EOF;
}



function new_members($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['new_members']}</b></td>
           </tr>
{$data}
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function new_members_row($data) {
global $ibforums;
return <<<EOF
               <tr>  
                 <td class='row4'><a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['id']}'>{$data['name']}</a> {$ibforums->lang['n_joined']} {$data['joined']}</td>
               </tr>
EOF;
}

function top_posters($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['top_posters']}</b></td>
           </tr>
           <tr>
             <td class='row1' valign='middle' align='left' width='35%'><b>{$ibforums->lang['t_username']}</b></td>
             <td class='row1' valign='middle' align='left' width='65%'><b>{$ibforums->lang['t_posts']}</b></td>
               </tr>
{$data}
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function top_posters_row($data) {
global $ibforums;
return <<<EOF
               <tr>  
                 <td class='row4' width='80%'>{$data['rating']}. <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['id']}'>{$data['name']}</a></td>
                 <td class='row4' width='20%' align='right'>{$data['posts']}</td>
               </tr>
EOF;
}

function loginbox() {
global $ibforums;
return <<<EOF
    <script language='JavaScript'>
    <!--
    function ValidateForm() {
        var Check = 0;
        if (document.LOGIN.UserName.value == '') { Check = 1; }
        if (document.LOGIN.PassWord.value == '') { Check = 1; }

        if (Check == 1) {
            alert("{$ibforums->lang['blank_fields']}");
            return false;
        } else {
            document.LOGIN.submit.disabled = true;
            return true;
        }
    }
    //-->
    </script>     

    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['loginbox']}</b></td>
           </tr>
     <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}" method="post" name='LOGIN' onSubmit='return ValidateForm()'>
     <input type='hidden' name='act' value='Login'>
     <input type='hidden' name='CODE' value='01'>
     <input type='hidden' name='s' value='{$ibforums->session_id}'>
     <input type='hidden' name='referer' value="">
     <input type='hidden' name='CookieDate' value="1">

           <tr><td class='row2'><b>{$ibforums->lang['b_username']}</b></td>
             <td class='row4'><input type='text' name='UserName' style="font-size:10px"></td></tr>
           <tr><td class='row2'><b>{$ibforums->lang['b_password']}</b></td>
             <td class='row4'><input type='password' name='PassWord' style="font-size:10px"></td></tr>
           <tr><td class='row4' colspan='2' align='center'><input type='submit' value='{$ibforums->lang['b_submit']}'></td></tr>

</form>
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function googlesearch() {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['googlesearch']}</b></td>
           </tr>
           <tr>
                 <td class='row4' colspan='2'>
<TABLE><tr><td>
<FORM method=GET action="http://www.google.com/search" target="_blank">
<IMG SRC="{$ibforums->vars['img_url']}/google.gif" 
border="0" ALT="Google" align="absmiddle"></A>
<INPUT TYPE=text name=q size=25 maxlength=255 value="" style="font-size:10px">
<center><INPUT type=submit name=btnG VALUE="{$ibforums->lang['google_do_search']}" style="font-size:10px"></center>
</td></tr>
</FORM>
</TABLE>
                     </td>
               </tr>           
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function navigation() {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['navigation']}</b></td>
           </tr>
           <tr>
                 <td class='row4' colspan='2'>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=portal">{$ibforums->lang['nav_home']}</a><br>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=idx">{$ibforums->lang['nav_forums']}</a><br>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Search&f=1">{$ibforums->lang['nav_search']}</a><br>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Members">{$ibforums->lang['nav_mlist']}</a><br>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Help">{$ibforums->lang['nav_help']}</a><br>
                           <a href="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?act=Downloads">{$ibforums->lang['tb_download']}</a>
                     </td>
               </tr>           
              </table>

             </td>
           </tr>
          </table>
<br>
EOF;
}

function latest_posts($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['latest_posts']}</b></td>
           </tr>
           <tr>
                 <td class='row4' colspan='2'>
                     {$data}
                     </td>
               </tr>           
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function stats_start() {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='2' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['board_stats']}</b></td>
           </tr>
EOF;
}

function stats_posts($text) {
global $ibforums;
return <<<EOF
                   <tr>
                     <td class='darkrow1' colspan='2'>{$ibforums->lang['board_stats']}</td>
                   </tr>
                   <tr>
                         <td class='row2' width='5%' valign='middle'><{F_STATS}></td>
                         <td class='row4' width="95%" align='left'>$text<br>{$ibforums->lang['most_online']}</td>
                   </tr>
EOF;
}

function stats_birthdays($birthusers="", $total="", $birth_lang="") {
global $ibforums;
return <<<EOF
        <tr>
           <td class='darkrow1' colspan='2'>{$ibforums->lang['birthday_header']}</td>
            </tr>
            <tr>
          <td class='row2' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='row4' width='95%'><b>$total</b> $birth_lang<br>$birthusers</td>
        </tr>
EOF;
}

function calendar_events($events = "") {
global $ibforums;
return <<<EOF
        <tr>
           <td class='darkrow1' colspan='2'>{$ibforums->lang['calender_f_title']}</td>
            </tr>
            <tr>
          <td class='row2' width='5%' valign='middle'><{F_ACTIVE}></td>
          <td class='row4' width='95%'>$events</td>
        </tr>
EOF;
}

function stats_active($active) {
global $ibforums;
return <<<EOF
        <tr>
           <td class='darkrow1' colspan='2'>$active[TOTAL] {$ibforums->lang['active_users']}</td>
            </tr>
            <tr>
          <td width="5%" class='row2'><{F_ACTIVE}></td>
          <td class='row4' width='95%'><b>{$active[GUESTS]}</b> {$ibforums->lang['guests']}, <b>$active[MEMBERS]</b> {$ibforums->lang['public_members']} <b>$active[ANON]</b> {$ibforums->lang['anon_members']} {$active[LINK]}<br>{$active[NAMES]}</td>
        </tr>
EOF;
}

function stats_end() {
global $ibforums;
return <<<EOF
              </table>
             </td>
           </tr>
          </table>
<br>
EOF;
}

function news($data) {
global $ibforums;
return <<<EOF
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>
               <!-- if you want an Icon, uncomment this line{$data['icon']} --> 
               {$data['title']}</b></td>
           </tr>
               <tr>
                 <td class='darkrow1'>{$ibforums->lang['postby']} <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['member_id']}'>{$data['member_name']}</a> @ {$data['start_date']} {$data['extra']}</td>
               </tr>
               <tr>
                   <td class='post1'>{$data['post_body']} {$data['post_body_extra']}</td>
               </tr>
               <tr>
                   <td class='row4'>{$ibforums->lang['comments']} {$data['replies']} :: <a href='{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}&act=ST&f={$data['forum_id']}&t={$data['tid']}'>{$ibforums->lang['viewcomments']}</a></td>
               </tr>
          </table>
             </td>
           </tr>
          </table>
EOF;
}

function poll($data) {
global $ibforums;
return <<<EOF
   <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
    <form action="{$ibforums->vars['board_url']}/index.{$ibforums->vars['php_ext']}?s={$ibforums->session_id}" method="post">
    <input type='hidden' name='act' value='Poll'>
    <input type='hidden' name='t' value='{$data['tid']}'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td colspan='3' class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$data['title']}, <font style="font-weight:normal;">{$data['description']}</b></td>
           </tr>

                <tr>
                  <td class='row1' valign='middle' align='left' width='35%'><b>{$ibforums->lang['p_choices']}</b></td>
                  <td class='row1' valign='middle' align='left' width='65%'><b>{$ibforums->lang['p_stats']}</b></td>
                </tr>
{$data['choices']}
                <tr>
                <td class='row2' align='center' colspan='2'>
{$data['poll_footer']}
                </td></tr></table>
                </td></tr>
     </form>
        </table>
<br>
EOF;
}

function poll_vote($data) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1' colspan='2'><INPUT type="radio" name="poll_vote" value="{$data['id']}">&nbsp;<b>{$data['choice']}</b></td>
    </tr>
EOF;
}

function poll_voted($data) {
global $ibforums;
return <<<EOF
    <tr>
    <td class='row1'><b>{$data['choice']}</b></td>
    <td class='row1'><img src='{$ibforums->vars['img_url']}/bar_left.gif' border='0' width='4' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar.gif' border='0' width='{$data['width']}' height='11' align='middle' alt=''><img src='{$ibforums->vars['img_url']}/bar_right.gif' border='0' width='4' height='11' align='middle' alt=''>&nbsp;[<B>{$data['votes']}</b>, {$data['percent']}%]</td>
    </tr>
EOF;
}

function WelcomePanel($data="") {
global $ibforums, $stats;
return <<<EOF
<table width="100%" align="center" border="0" cellspacing="1" cellpadding="0" class='tableborder'>
  <tr> 
    <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'> 
      <table width="100%" border="0" cellspacing="0" cellpadding="3">
        <tr> 
          <td><{CAT_IMG}></td>
          <td width="100%"><b>{$ibforums->lang['welcome_back']}, {$ibforums->member['name']}</b></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr> 
<td class="row4" width="{$data['width']}">{$data['avatar']}</td>
<td class="row4" width="70%" nowrap>{$ibforums->lang['it_is_now']} {$data['time']}.<br>
{$ibforums->lang['last_visited']} {$data['lastv']}.<br>
{$ibforums->lang['there_has_been']} {$data['posts_since']} {$ibforums->lang['posts_in']} {$data['topics_since']}<br>
{$ibforums->lang['topics_since_last_visit']}<br>
<a href="index.php?s={$ibforums->session_id}&act=Search&CODE=getnew">{$ibforums->lang['view_newposts']}</a></td>

<td class="row4" width="30%" nowrap>{$ibforums->lang['forum_stats']}<br>
{$ibforums->lang['mem_count']} {$data['stats']['MEM_COUNT']}, {$ibforums->lang['total_topics']} {$data['stats']['TOTAL_TOPICS']}<br>
{$ibforums->lang['total_replies']} {$data['stats']['TOTAL_REPLIES']}, {$ibforums->lang['total_posts']} {$data['stats']['TOTAL_POSTS']}<br>
{$ibforums->lang['newest_member']} <a href="{$ibforums->vars['board_url']}/index.php?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['stats']['LAST_MEM_ID']}">{$data['stats']['LAST_MEM_NAME']}</a><br>
{$ibforums->lang['top_thread_starter']} <a href="{$ibforums->vars['board_url']}/index.php?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['tt_id']}">{$data['tt_name']}</a> ({$data['tt_num']})<br>
{$ibforums->lang['top_poster']} <a href="{$ibforums->vars['board_url']}/index.php?s={$ibforums->session_id}&act=Profile&CODE=03&MID={$data['tp_id']}">{$data['tp_name']}</a> ({$data['tp_num']})<br></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br>
EOF;
}

function GuestPanel($data="") {
global $ibforums, $stats;
return <<<EOF
<table width="100%" align="center" border="0" cellspacing="1" cellpadding="0" class='tableborder'>
  <tr> 
    <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'> 
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr> 
          <td><{CAT_IMG}></td>
          <td width="100%" class="maintitle"><b>{$ibforums->lang['welcome_guest']} <a href="{$ibforums->vars['board_url']}/index.php?act=Reg&CODE=00">{$ibforums->lang['register']}</a> {$ibforums->lang['or']} <a href="{$ibforums->vars['board_url']}/index.php?act=Login&CODE=00">{$ibforums->lang['login']}</a>!</b></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td class='mainbg'>
      <table width="100%" border="0" cellspacing="1" cellpadding="4">
        <tr>
                  <td class="row4" width="64"><img src='{$ibforums->vars['board_url']}/html/avatars/IPB_Community_Pack/Green-haze.gif'></td>
                  <td class="row4" width="70%">{$ibforums->lang['it_is_now']} {$data['time']}.<br>
                    {$ibforums->lang['there_has_been']} {$data['posts_since']} {$ibforums->lang['posts_in']} {$data['topics_since']} {$ibforums->lang['topics_today']}<br>
                        <a href="index.php?s{$ibforums->session_id}=&act=Search&CODE=getactive">{$ibforums->lang['view_all_posts_today']}</a></td>
                  <td class="row4" width="30%" align="right">
                    <table border="0" cellspacing="0" cellpadding="1">
                          <tr><form action="index.php" method="post" name='LOGIN'><input type="hidden" name="CookieDate" value="1"><input type='hidden' name='act' value='Login'><input type='hidden' name='CODE' value='01'>
                            <td align="right">{$ibforums->lang['b_username']} </td>
                                <td><input type='text' size='20' maxlength='64' name='UserName' class='forminput'></td>
                          </tr>
                          <tr>
                            <td align="right">{$ibforums->lang['b_password']} </td>
                                <td><input type='password' size='20' name='PassWord' class='forminput'></td>
                          </tr>
                          <tr>
                            <td></td>
                                <td><input type="submit" name='submit' value="{$ibforums->lang['b_submit']}" class='forminput'></td>
                          </tr></form>
                        </table>
                  </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br>
EOF;
}

function render_portal($data) {
global $ibforums;
return <<<EOF
<table cellpadding=0 cellspacing='1' border='0' width='<{tbl_width}>' align='center'>
 <tr>
  <td width="21%" vAlign=top>
{$data['loginbox']}
{$data['navigation']}
{$data['forums_list']}
{$data['googlebar']}
{$data['new_posts']}
{$data['latest_posts']}
{$data['poll']}
{$data['old_news']}
{$data['top_posters']}
{$data['top_forums']}
{$data['new_members']}
{$data['stats']}
  </td>
  <td width="1%" >&nbsp;</td>
  <td width="75%" vAlign=top>
{$data['welcomepanel']}
{$data['latest_posts_big']}
{$data['new_posts_big']}

    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='tableborder' align='center'>
      <tr>
        <td>
          <table cellpadding='4' cellspacing='1' border='0' width='100%'>
           <tr>
             <td class='maintitle' background='{$ibforums->vars['img_url']}/tile_sub.gif'><b>{$ibforums->lang['latest_news']}</b></td>
           </tr>
           <tr>
                 <td class='row4' colspan='2'>
                     {$data['news']}
                     </td>
               </tr>           
              </table>
             </td>
           </tr>
          </table>

  </td>
<table cellpadding='0' cellspacing='0' border='0' width='<{tbl_width}>' align='center'>
    <tr>
        <td align='center'>{$ibforums->lang['copyrights']}</td>
    </tr>
</table>

EOF;
}

}
?>
