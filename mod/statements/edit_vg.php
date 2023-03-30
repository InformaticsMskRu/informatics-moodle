<?php
     
global $problem_id;
global $contest_id;


require_once('../../config.php');
require_once('lib.php');
require_once('../../../inf/include/stuff.php');

$chapterid  = optional_param('chapterid', 0, PARAM_INT); // Chapter ID
$rank    = optional_param('rank', 0, PARAM_INT);

require_login();

if(isadmin() || $_GET["analysis"] || $_GET["subject"]) {
   function print_option_subtree($root,$indent,$selected) {
     $query="SELECT subject_id,name,description FROM ps_subjects WHERE parent_id='".mysql_escape_string($root)."' ORDER BY order_value ASC";
     $res=mysql_query($query);
     while ($item=mysql_fetch_array($res)) {
       if ($item["subject_id"]==$selected) $selmark=" selected"; else $selmark="";
       print($indent."<option value=\"".$item["subject_id"]."\"".$selmark.">".$indent.$item["subject_id"].". ".$item["name"]." (".$item["description"].")\n");
       $query="SELECT subject_id,name,description FROM ps_subjects WHERE parent_id='".mysql_escape_string($item["subject_id"])."' ORDER BY order_value ASC";
       $res2=mysql_query($query);
       $item2=mysql_fetch_array($res2);
       if ($item2) {
         print_option_subtree($item["subject_id"],$indent."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$selected);
       }
     }
   }
   
   function print_subject_select($value) {
     echo "<select name='subjects[]' size='1'>";
     if($value==0) $selmark="selected"; else $selmark="";      
     print("<option value=0 ".$selmark.">---</option>\n");
     print_option_subtree(0,"&nbsp;&nbsp;",$value);
     print("</select>");
  } 
include("edit_problem.php");
	} else {
	  	if($_POST["analysis"]) {
			include("suggest_analysis.php");
			die;
		} else {
		  	if($_POST['subjects']) {
				include("suggest_subjects.php");
				die;
			} else {
			error("You must me Administrator to edit problems");
			}
		}
	}
?>
