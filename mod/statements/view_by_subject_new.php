<?php
   require("../../../inf/include/stuff.php");
   session_start();
 ?>
 <html>
 <head>
 <title>Каталог по темам</title>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <meta name="Robots" content="NOINDEX,FOLLOW">
 <link href="http://informatics.mccme.ru/inf/style/newstyle.css" rel="stylesheet" type="text/css">
<style type="text/css">@import url(http://informatics.mccme.ru/moodle/mod/statements/statements_theme.css);</style><style type="text/css">@import url(http://informatics.mccme.ru/moodle/mod/statements/polygon.css);</style>
 <?php
   require("../../../inf/include/viewing_new.php");
 
   function print_to_root($db,$node) {
     $query="SELECT parent_id,name FROM ".table_prefix."subjects WHERE subject_id='".mysql_escape_string(our2db($node))."'";
     $res=mysql_query($query);
     $item=mysql_fetch_array($res);
     if ($item)
       $parent=db2our($item[0]);
     else
       $parent="0";
     if ($parent!="0") {
       print_to_root($db,$parent);
       print(" &gt;&gt; ");
     }
     $title=db2our($item[1]);
    //   if ($node!=1) 
         $href="view_by_subject_new.php?parent=".$node;
    //   else
    //     $href="index.php";
 ?>
 <a class="componentboxheaderlink" href="<?php print($href);?>"><?php print(displayour($title)); ?></a>
 <?php
   }
 
   function print_children($db,$parent) {
     $query="SELECT subjects.subject_id,subjects.name,COUNT(DISTINCT problems.problem_id) FROM ".table_prefix."subjects AS subjects LEFT JOIN ".table_prefix."problem_subjects AS problem_subjects ON (problem_subjects.subject_id=subjects.subject_id) LEFT JOIN ".table_prefix."problems as problems ON (problem_subjects.problem_id=problems.problem_id ".get_current_problem_filter()." ".get_showing_filter($db).") WHERE (0=0) AND parent_id='".mysql_escape_string(our2db($parent))."' GROUP BY subjects.subject_id ORDER BY subjects.order_value ASC";
     $res=mysql_query($query);
     $first=true;
     while ($item=mysql_fetch_array($res)) {
       //if ($item[2]>0) 
	{
         if ($first) {
           $first=false;
 ?>
 <ul class="componentboxlist">
 <?php
         }
 ?>
 <li><a class="componentboxlink" href="view_by_subject_new.php?parent=<?php print(displaydb($item[0])); ?>"><?php print(displaydb($item[1])); ?></a> (<?php print(displaydb($item[2])); ?> задач<?php print(get_plural_ending($item[2])); ?>)
 <?php
       }
     }
     if (!$first) {
 ?>
 </ul>
 <?php
     }
   }
   
   $related_sources_kinds=array(
     "chapter_subject_id",
     "paragraph_subject_id",
     "subject_id");
     
   function print_related_sources($db,$parent) {
     global $related_sources_kinds;
     if ($parent==get_undefined_subject($db)) return;
     $first=true;
     $query="SELECT source_id FROM ".table_prefix."sources_attributes WHERE ((0=1)";
     foreach ($related_sources_kinds as $rsk) {
       $query.=" OR name='".mysql_escape_string(our2db($rsk))."'";
     }
     $query.=") AND value='".mysql_escape_string(our2db($parent))."' AND kind=3";
     $res=mysql_query($query);
     while ($item=mysql_fetch_array($res)) {
       $query2="SELECT COUNT(*) FROM ".table_prefix."problem_sources as problem_sources, ".table_prefix."problems as problems WHERE problem_sources.problem_id=problems.problem_id AND problem_sources.source_id='".mysql_escape_string($item[0])."'".get_showing_filter($db);
       $res2=mysql_query($query2);
       $item2=mysql_fetch_array($res2);
       if (((int) $item2[0])>0) {
         if ($first) {
           print("Материалы по этой теме:\n<ul class=\"componentboxlist\">\n");
           $first=false;
         }
         print("<li><a class=\"componentboxlink\" href=\"view_by_source_new.php?parent=".displaydb($item[0])."\">");
         print_exact_source_reference($db,db2our($item[0]));
         print("</a>\n");
       }
     }
     if (!$first) {
       print("</ul>\n<hr>\n");
     }
   }
 
   function print_related_articles($db,$parent) {
     global $article;
     if (isset($article)&&($article!="")) {
 ?>
 <a class="componentboxlink" href="view_by_subject_new.php?parent=<?php print(addslashes($parent)); ?>">Вернуться к задачам</a>
 <hr><p>
 <?php
     } else {
       $query="SELECT value FROM ".table_prefix."subjects_attributes as subjects_attributes WHERE subject_id='".mysql_escape_string($parent)."' AND name='reference'";
       $res=mysql_query($query);
       $first=true;
       while ($item=mysql_fetch_array($res)) {
         if ($first) {
           print("Ссылки по теме:<br>");
           $first=false;
         }
         $ref=$item[0];
         $at=strpos($ref,";");
         if ($at===false) {
           $href=$ref;
           $title=$ref;
         } else {
           $href=substr($ref,0,$at);
           $title=substr($ref,$at+1);
         }
 ?>
 <a target="_blank" class="componentboxlink" href="<?php print(displayour($href)); ?>"><?php print(displayour($title)); ?></a><br>
 <?php
       }
       if (!$first) {
         print("<hr><p>");
       }
     }
   }
   
   function print_header_box($db,$parent) {
 ?>
 <div class="componentbox">
 <div class="componentboxheader">
 <?php
   print_box_hiders("header",0);
 ?>
 
 <?php
   print_to_root($db,$parent);
 ?>
 </div>
 <div class="componentboxcontents" id="header_box_contents" <?php print_box_visibility_style(0,false,1); ?>>
 <?php
   //print_related_articles($db,$parent);
   //print_related_sources($db,$parent);
   print_children($db,$parent);
 ?>
 </div>
 </div>
 <?php
   }
   
 ?>
 </head>
 
 <body>
 <?php
   require("../../../inf/include/db_connect.php");
   if ($db) {
     require("../../../inf/include/header_new.php");
     process_basket();
     if (isset($_REQUEST["parent"])) {
       $parent=$_REQUEST["parent"];
       if (!check_if_subject_exists($db,$parent))
         $parent=get_undefined_subject($db);
     } else {
       $parent=get_undefined_subject($db);
     }
 
     $nobasket=(count(get_basket())==0);
 ?>
 <table class="viewingtable">
 <tr class="viewingtablecell">
 <td class="viewingtablecell" colspan=<?php print($nobasket?1:2); ?>>
 <?php
     print_header_box($db,$parent);
 //    print_filter_box($db,get_current_url());
 ?>
 </td>
 </tr>
 <tr class="viewingtablecell">
 <?php
   if (!$nobasket) {
 ?>
 <td class="viewingtablecell" width="20%"><?php
 //    print_links_box($db);
     print_basket_box($db,get_current_url());
 //    print_about_box($db);
 ?></td>
 <?php
   }
 ?>
 <td class="viewingtablecell"><?php
     $boxquery="SELECT problems.problem_id,problems.problem_id FROM ".table_prefix."problem_subjects as problem_subjects, ".table_prefix."problems as problems, ".table_prefix."subjects as subjects WHERE (0=0) ".get_showing_filter($db)." AND problem_subjects.subject_id='".mysql_escape_string($parent)."' AND problem_subjects.problem_id=problems.problem_id ".get_current_problem_filter()." AND problem_subjects.subject_id=subjects.subject_id GROUP BY problems.problem_id ORDER BY problem_subjects.order_value, problems.difficulty, problems.problem_id";
     print_problems_box($db,$boxquery,"view_by_subject_new.php?parent=".displayour($parent));
 ?></td>
 </tr>
 </table>
 <?php
     require("../../../inf/include/footer_new.php");
     require("../../../inf/include/db_finish.php");
   }
 ?>
 </body>
 </html>