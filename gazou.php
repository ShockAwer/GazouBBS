<?php
/*************************************
 * GazouBBS by ToR
 *
 * Translated by J.R.
  *
  * http://php.s3.to/
  *
  * Image Upload BBS.
  *
  * Prepare a directory img for storage and set it to 777.
  * Prepare an empty log file imglog.log and set it to 666.
  * Some servers cannot be uploaded.
  *
  * 2001/09/27 v2.4 change image save name from local to time name, paging
  * 2001/10/31 v3.0 Rebuild. Created a posting page for administrators. Forms can be separated.

  **************************************/
//---- setting --------
define(LOGFILE, 'imglog2.log'); //log file name
define(PATH, '. /img/'); //image storage directory . /???? /?

define(TITLE, 'GazouBBS'); //title (<title> and TOP)
define(HOME, '/'); //link to "Home

define(MAX_KB, '100'); //posting capacity limit KB (up to 2M by php setting)
define(MAX_W, '250'); //posting size width (more than this, reduce width)
define(MAX_H, '250'); //post size height

define(PAGE_DEF, '7'); //posts to display on one page
define(LOG_MAX, '200'); //log max lines

define(ADMIN_PASS, '0123'); //admin path
define(CHECK, 0); //admin approval before displaying images? yes=1 no=0
define(SOON_ICON, 'soon.jpg'); // 

define(BUNRI, 0); //separate submission form?

define(PHP_SELF, $PHP_SELF); //name of this script

/* Header */
function head(&$dat){
  $dat.='
<html><head>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=Shift_JIS">
<STYLE TYPE="text/css">
<!--
body,tr,td,th { font-size:10pt }
a:hover { color:#DD0000; }
span { font-size:20pt }
small { font-size:8pt }
-->
</STYLE>
<title>'.TITLE.'</title></head>
<body bgcolor="#FFFFEE" text="#800000" link="#0000EE" vlink="#0000EE">
<p align=right>
[<a href="'.HOME.'" target="_top">Home</a>]
[<a href="'.PHP_SELF.'?mode=admin">Admin</a>]
<p align=center>
<font color="#800000" face="MS PGothic" size=5>
<b><SPAN>'.TITLE.'</SPAN></b></font>
<hr width="90%" size=1>
';
}
/* Submission Form */
function form(&$dat,$admin=""){
  $maxbyte = MAX_KB * 1024;
  if($admin){
    $hidden = "<input type=hidden name=admin value=\"".ADMIN_PASS."\">";
    $msg = "<h4>HTML tags are allowed.</h4>";
  }
  $dat.='
<center>'.$msg.'
<form action="'.PHP_SELF.'" method="POST" enctype="multipart/form-data">
<input type=hidden name=mode value="regist">
'.$hidden.'
<input type=hidden name="MAX_FILE_SIZE" value="'.$maxbyte.'">
<table cellpadding=1 cellspacing=1>
<tr>
  <td bgcolor=#eeaa88><b>Name</b></td>
  <td><input type=text name=name size="28"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>E-mail</b></td>
  <td><input type=text name=email size="28"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Title</b></td>
  <td>
    <input type=text name=sub size="35">
    <input type=submit value="Send"><input type=reset value="Reset">
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Body</b></td>
  <td><textarea name=com cols="48" rows="4" wrap=soft></textarea>
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>URL</b></td>
  <td><input type=text name=url size="63" value="http://"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>File</b></td>
  <td><input type=file name=upfile size="35"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Delkey</b></td>
  <td>
    <input type=password name=pwd size=8 maxlength=8 value="">
    <small>(For deletion of posts. Alphanumeric characters, up to 8 characters)</small>
  </td>
</tr>
<tr><td colspan=2>
<small>
<LI>Supported ： GIF, JPG, PNG<br>
<LI>Some browsers may not upload properly.<br>
<LI>The maximum size of submitted images is '.MAX_KB.' KB.<br>
<LI>Anything larger than '.MAX_W.'x'.MAX_H.' gets thumbnailed. 
</small>
</td></tr></table></form></center>
<hr>
  ';
}
/* 記事部分 */
function main(&$dat, $page){
  $line = file(LOGFILE);
  $st = ($page) ? $page : 0;

  for($i = $st; $i < $st+PAGE_DEF; $i++){
    if($line[$i]=="") continue;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pwd,$ext,$w,$h,$time,$chk) = explode(",", $line[$i]);
    // URLとメールにリンク
    if($url)   $url = "<a href=\"http://$url\" target=_blank>Link</a>";
    if($email) $name = "<a href=\"mailto:$email\">$name</a>";
    $com = eregi_replace("(^|/>)(&gt;[^<]*)", "\\1<font color=789922>\\2</font>", $com);
    // 画像ファイル名
    $img = PATH.$time.$ext;
/* Feel free to change ["] to [\"]. */
    // <imgタグ作成
    $imgsrc = "";
    if($ext && is_file($img)){
      $size = ceil(filesize($img) / 1024);//Size display on alt
      if(CHECK && $chk != 1){//Unapproved
        $imgsrc = "<img src=".SOON_ICON.">";
      }elseif($w && $h){//Approved
        $imgsrc = "<a href=\"".$img."\" target=_blank><img src=".$img."
			border=0 align=left width=$w height=$h hspace=20 alt=\"".$size." KB\"></a>";
      }else{//それ以外
        $imgsrc = "<a href=\"".$img."\" target=_blank><img src=".$img."
			border=0 align=left hspace=20 alt=\"".$size." KB\"></a>";
      }
    }
    // Main Creation
    $dat.="No.$no <font color=#cc1105 size=+1><b>$sub</b></font><br> ";
    $dat.="Name <font color=#117743><b>$name</b></font> Date $now &nbsp; $url";
    $dat.="<p><blockquote>$imgsrc $com</blockquote><br clear=left><hr>\n";

    $p++;
    clearstatcache();//Clear file stat
  }
  $prev = $st - PAGE_DEF;
  $next = $st + PAGE_DEF;
  // page break processing
  $dat.="<table align=left><tr>\n";
  if($prev > 0){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$prev>";
    $dat.="<input type=submit value=\"Previous\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  if($p >= PAGE_DEF && count($line) > PAGE_DEF){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$next>";
    $dat.=" <input type=submit value=\"Next\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  $dat.="</td>\n</tr></table>\n";
}
/* Footer */
function foot(&$dat){
  $dat.='
<table align=right><tr>
<td nowrap align=center><form action="'.PHP_SELF.'" method=POST>
<input type=hidden name=mode value=usrdel>
"Post deleted"<br>
No. <input type=text name=no size=3>
Deleted Post<input type=password name=pwd size=4 maxlength=8>
<input type=submit value="Delete">
</form></td>
</tr></table><br clear=all>
<center><P><small><!-- GazouBBS v3.0 -->
- <a href="http://php.s3.to" target=_top>GazouBBS</a> -
</small></center>
</body></html>
  ';
}
/* Check posts */
function regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name){
  global $REQUEST_METHOD;

  if($REQUEST_METHOD != "POST") error("Doesn't work, no submit.");
  // Lack of...
  if(!$name||ereg("^[ |　|]*$",$name)) error("No name"); 
  if(!$com||ereg("^[ |　|\t]*$",$com)) error("No body"); 
  if(!$sub||ereg("^[ |　|]*$",$sub))   $sub="（Untitled）"; 
  if(strlen($com) > 1000) error("Your comment's too long！");

  $line = file(LOGFILE);
  // 時間とホスト取得
  $tim = time();
  $host = gethostbyaddr(getenv("REMOTE_ADDR"));
  // 連続投稿チェック
  list($lastno,,$lname,,,$lcom,,$lhost,,,,,$ltime,) = explode(",", $line[0]);
  if(RENZOKU && $host == $lhost && $tim - $ltime < RENZOKU)
    error("Don't spam!");
  // No., path, time and URL format
  $no = $lastno + 1;
  $pass = ($pwd) ? substr(md5($pwd),2,8) : "*";
  $now = gmdate("Y/m/d(D) H:i",$tim+9*60*60);
  $url = ereg_replace("^http://", "", $url);
  //text formatting
  $name = CleanStr($name);
  $email= CleanStr($email);
  $sub  = CleanStr($sub);
  $url  = CleanStr($url);
  $com  = CleanStr($com);
  // Uniformity of line feed characters. (????)
  $com = str_replace( "\r\n",  "\n", $com); 
  $com = str_replace( "\r",  "\n", $com);
  // One continuous blank line
  $com = ereg_replace("\n((　| )*\n){3,}","\n",$com);
  $com = nl2br($com);										//Substitute <br> before the newline character
  $com = str_replace("\n",  "", $com);	//\erase ɑn from the string。
  // Don't double post!
  if($name == $lname && $com == $lcom)
    error("Oops, double post -<br><br><a href=$PHP_SELF>Reload");
  // Log lines over
  if(count($line) >= LOG_MAX){
    for($d = count($line)-1; $d >= LOG_MAX-1; $d--){
      list($dno,,,,,,,,,$ext,,,$dtime,) = explode(",", $line[$d]);
      if(is_file(PATH.$dtime.$ext)) unlink(PATH.$dtime.$ext);
      $line[$d] = "";
    }
  }
  // Upload error
  if($upfile != "none"){
    $dest = PATH.$upfile_name;
    copy($upfile, $dest);
    if(!is_file($dest)) error("Upload failed. The server may cannot upload this file.");
    $size = getimagesize($dest);
    $W = $size[0];
    $H = $size[1];
    rename($dest,$tim.$size[2]);
    // Thumbnailing
    if($W > Max_W || $H > Max_H){
      $W2 = Max_W / $W;
      $H2 = Max_H / $H;

      ($W2 < $H2) ? $key = $W2 : $key = $H2;

      $W = $W * $key;
      $H = $H * $key;
    }
    $mes = "Uploading $upfile_name was a success<br><br>";
  }
  $chk = (CHECK) ? 0 : 1;//Check

  $newline = "$no,$now,$name,$email,$sub,$com,$url,$host,$pass,.$size[2],$W,$H,$tim,$chk,\n";

  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, $newline);
  fputs($fp, implode('', $line));
  fclose($fp);

  echo "$msg Wait to be redirected";
  echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=".PHP_SELF."?\">";
}
/* Formatting */
function CleanStr($str){
  global $admin;

  $str = trim($str);//Leading and trailing whitespace removal
  if (get_magic_quotes_gpc()) {//Delete 
    $str = stripslashes($str);
  }
  if($admin!=ADMIN_PASS){//管理者はタグ可能
    $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');//Tag ban (????????)
    $str = str_replace("&amp;", "&", $str);//Specials characters
  }
  return str_replace(",", "&#44;", $str);//Convert commas
}
/* ユーザー削除 */
function usrdel($no,$pwd){
  if($no == "" || $pwd == "") error("Deletion No. or password has not been entered.");

  $line = file(LOGFILE);
  $flag = FALSE;

  for($i = 0; $i<count($line); $i++){
    list($dno,,,,,,,,$pass,$dext,,,$dtim,) = explode(",", $line[$i]);
    if($no == $dno && substr(md5($pwd,2,8)) == $pass){
      $flag = TRUE;
      $line[$i] = "";			//パスワードがマッチした行は空に
      $delfile = PATH.$dtim.$dext;	//削除ファイル
      break;
    }
  }
  if(!$flag) error("Cannot find the post in question or your password is incorrect.");
  // ログ更新
  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, implode('', $line));
  fclose($fp);

  if(is_file($delfile)) unlink($delfile);//Full deletion
}
/* パス認証 */
function valid($pass){
  if($pass && $pass != ADMIN_PASS) error("Invalid");

  head($dat);
  echo $dat;
  echo "[<a href=\"".PHP_SELF."\">Home</a>]\n";
  echo "<table width='100%'><tr><th bgcolor=#E08000>\n";
  echo "<font color=#FFFFFF>Admin</font>\n";
  echo "</th></tr></table>\n";
  echo "<p><form action=\"".PHP_SELF."\" method=POST>\n";
  // ログインフォーム
  if(!$pass){
    echo "<center><input type=radio name=admin value=del checked>Delete ";
    echo "<input type=radio name=admin value=post>admin post<p>";
    echo "<input type=hidden name=mode value=admin>\n";
    echo "<input type=password name=pass size=8>";
    echo "<input type=submit value=\" Submit \"></form></center>\n";
    die("</body></html>");
  }
}
/* 管理者削除 */
function admindel($delno,$chkno){
  global $pass;

  if($chkno || $delno){
    $line = file(LOGFILE);
    $find = FALSE;
    for($i = 0; $i < count($line); $i++){
      list($no,$now,$name,$email,$sub,$com,$url,
           $host,$pw,$ext,$w,$h,$tim,$chk) = explode(",",$line[$i]);
      if($chkno == $no){//画像チェック$chk=1に
        $find = TRUE;
        $line[$i] = "$no,$now,$name,$email,$sub,$com,$url,$host,$pw,$ext,$w,$h,$tim,1,\n";
        break;
      }
      if($delno == $no){//削除の時は空に
        $find = TRUE;
        $line[$i] = "";
        break;
      }
    }
    if($find){//ログ更新
      $fp = fopen(LOGFILE, "w");
      flock($fp, 2);
      fputs($fp, implode('', $line));
      fclose($fp);
    }
  }
  // 削除画面を表示
  echo "<input type=hidden name=mode value=admin>\n";
  echo "<input type=hidden name=admin value=del>\n";
  echo "<input type=hidden name=pass value=\"$pass\">\n";
  echo "<center><P>Check the checkboxes of the posts you wish to delete and press the Delete button.\n";
  echo "<P><table border=1 cellspacing=0>\n";
  echo "<tr bgcolor=6080f6><th>Delete</th><th>No</th><th>date</th><th>title</th>";
  echo "<th>submit</th><th>body</th><th>IP</th><th>file<br>(Bytes)</th>";
  if(CHECK) echo "<th>Image<br>Permission</th>";
  echo "</tr>\n";

  $line = file(LOGFILE);

  for($j = 0; $j < count($line); $j++){
    $img_flag = FALSE;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pw,$ext,$w,$h,$time,$chk) = explode(",",$line[$j]);
    // フォーマット
    list($now,$dmy) = split("\(", $now);
    if($email) $name="<a href=\"mailto:$email\">$name</a>";
    $com = str_replace("<br />"," ",$com);
    $com = htmlspecialchars($com, ENT_QUOTES, 'Shift_JIS');
    if(strlen($com) > 40) $com = substr($com,0,38) . " ...";
    // 画像があるときはリンク
    if($ext && is_file(PATH.$time.$ext)){
      $img_flag = TRUE;
      $clip = "<a href=\"".PATH.$time.$ext."\" target=_blank>".$time.$ext."</a>";
      $size = filesize(PATH.$time.$ext);
      $all += $size;			//合計計算
    }else{
      $clip = "";
      $size = 0;
    }
    $bg = ($j % 2) ? "d6d6f6" : "f6f6f6";//背景色

    echo "<tr bgcolor=$bg><th><input type=checkbox name=del value=\"$no\"></th>";
    echo "<th>$no</th><td><small>$now</small></td><td>$sub</td>";
    echo "<td><b>$name</b></td><td><small>$com</small></td>";
    echo "<td>$host</td><td align=center>$clip<br>($size)</td>\n";

    if(CHECK){//画像チェック
      if($img_flag && $chk == 1){
        echo "<th><font color=red>OK</font></th>";
      }elseif($img_flag && $chk != 1) {
        echo "<th><input type=checkbox name=chk value=$no></th>";
      }else{
        echo "<td><br></td>";
      }
    }
    echo "</tr>\n";
  }
  if(CHECK) $msg = "or alloe";

  echo "</table><p><input type=submit value=\"Delete $msg\">";
  echo "<input type=reset value=\"リセット\"></form>";

  $all = (int)($all / 1024);
  echo "These files take up : <b>$all</b> KB. <b>Wow!</b>";
  die("</center></body></html>");
}
/* エラー画面 */
function error($mes){
  global $upfile_name;

  if(is_file(PATH.$upfile_name)) unlink(PATH.$upfile_name);

  head($dat);
  echo $dat;
  echo "<br><br><hr size=1><br><br>
        <center><font color=red size=5><b>$mes</b></font></center>
        <br><br><hr size=1>";
  die("</body></html>");
}
/*-----------Main-------------*/
switch($mode){
  case 'regist':
    regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name);
    break;
  case 'admin':
    valid($pass);
    if($admin=="del") admindel($del,$chk);
    if($admin=="post"){
      echo "</form>";
      form($post,1);
      echo $post;
      die("</body></html>");
    }
    break;
  case 'usrdel':
    usrdel($no,$pwd);
    break;
  default:
    head($buf);
    if(!BUNRI) form($buf);
    main($buf,$page);
    foot($buf);
    echo $buf;
}
?>
