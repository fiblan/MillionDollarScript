<?php
/**
 * @version		$Id: category.inc.php 157 2012-10-04 15:09:35Z ryan $
 * @package		mds
 * @copyright	(C) Copyright 2010 Ryan Rhode, All rights reserved.
 * @author		Ryan Rhode, ryan@milliondollarscript.com
 * @license		This program is free software; you can redistribute it and/or modify
 *		it under the terms of the GNU General Public License as published by
 *		the Free Software Foundation; either version 3 of the License, or
 *		(at your option) any later version.
 *
 *		This program is distributed in the hope that it will be useful,
 *		but WITHOUT ANY WARRANTY; without even the implied warranty of
 *		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *		GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License along
 *		with this program;  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *		Million Dollar Script
 *		A pixel script for selling pixels on your website.
 *
 *		For instructions see README.txt
 *
 *		Visit our website for FAQs, documentation, a list team members,
 *		to post any bugs or feature requests, and a community forum:
 * 		http://www.milliondollarscript.com/
 *
 */


##########################################################

function format_cat_translation_table ($cat) {

	global $AVAILABLE_LANGS;

	//$sql = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name AS NAME FROM categories LEFT JOIN cat_name_translations ON categories.category_id = cat_name_translations.category_id WHERE lang is NULL";

	foreach  ($AVAILABLE_LANGS as $key => $val) {
		$sql = "SELECT categories.category_id, categories.category_name, lang FROM cat_name_translations, categories WHERE categories.category_id=cat_name_translations.category_id AND categories.category_id='$cat' AND lang='$key' ";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());
		//$row = mysql_fetch_row($result);
		if (mysql_num_rows($result)==0) {
			$cat_row = get_category($cat);
			$sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES ('".$cat."', '".$key."', '".addslashes($cat_row['category_name'])."')";
			//echo "<b>$sql</b>";
			 mysql_query($sql) or die (mysql_error().$sql);

			
		// echo "$sql<br>";
		}


	
	}
	$search_set = get_search_set($cat, $cat);
	$sql = "update categories set search_set='$search_set' where category_id='$cat'";
	mysql_query($sql) or die (mysql_error().$sql);

	$query ="SELECT * FROM categories WHERE parent_category_id='$cat' ";
	$result = mysql_query ($query) or die(mysql_error().$query);  
	while ($row= mysql_fetch_array($result)) {
		format_cat_translation_table ($row['category_id']);
	}

}

##################################################

function update_category_cache($cat_id) {

	$f_id = 1;

	require_once('codegen_functions.php');
	for ($f_id=1; $f_id<6; $f_id++){
		generate_category_cache($cat_id, $lang, $f_id);
	}

	$query ="SELECT category_id FROM categories WHERE parent_category_id='$cat_id' ";
	$result = mysql_query ($query) or die(mysql_error().$query);  
	while ($row= mysql_fetch_array($result)) {
		update_category_cache ($row['category_id']);
	}


}

##################################################
# Globals
$withSubCat =0;
$s=0;
$form_id;
##################################################

# show all categories that are the children

function showAllCat($child, $cols, $subCat, $lang, $f_id)
{
   global $withSubCat, $catName, $form_id, $f2;
   # initialise the global subcat flag
   $withSubCat = $subCat;
   $form_id = $f_id;

   # query to get all the nodes that are the 
   # children of child id

    $query = "SELECT categories.category_id, categories.category_name, lang,  cat_name_translations.category_name, obj_count, allow_records  FROM categories LEFT JOIN cat_name_translations ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='$child' AND (lang='".$_SESSION['MDS_LANG']."') and form_id='$form_id' ORDER BY list_order , NAME ";

	//echo "$query";

   $x=0;
   # do the query
   $result = mysql_query ($query) or die($query. mysql_error());
   while ($row = mysql_fetch_row($result)) {
      $cats[] = $row;
      $x++;
      if ($x==$cols) {
         showRow($cats);
         unset($cats); # clear array
         $x=0;
      } 
   }
   # show the remaining cats
   showRow($cats);

}

############################################################
function showRow ($cats) {
      echo "<tr>";

   for ($x=0; $x < count($cats); $x++) {
      showCat($cats[$x]);
   }
      echo "</tr>";


}

#######################################
define ('CAT_MOD_REWRITE', 'YES');
function cat_url_write($cat, $name) {
	
	if (CAT_MOD_REWRITE=='YES') {
		$name = ereg_replace("[ '\"\.&/]+", "_", $name);
		//$name = ereg_replace ("_$", "", $name);
		$name = urlencode($name);
		return BASE_HTTP_PATH."jobs/category/$name.html";

	} else {

		return htmlentities($_SERVER['PHP_SELF'])."?cat=$cat";

	}

}

function get_cat_id_from_url($cat_name) {
	
	$cat_name = str_replace('_', '%', $cat_name);
	$cat_name = ereg_replace("\.html", "", $cat_name);
	$sql = "select category_id from categories where category_name like '$cat_name' ";
	//echo $sql;
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);
	return $row['category_id'];

}

############################################################

function showCat ($cat) {
   global $withSubCat;
   global $MODE;

   echo "<td valign=top width='33%'>";
   echo '<IMG alt="&gt;" src="images/arrow.gif" width="6" height="9" border="0" alt="">';
   echo "<A HREF=\"".htmlentities($_SERVER['PHP_SELF'])."?cat=$cat[0]\"><span class='cat_heading'>$cat[3]</span></A>"; //echo " (ID: ". ($cat[0]).") ";
	if  ($cat[5]=='N') echo "<b>&#8224;</b>";
	if ($MODE = 'ADMIN') {

   ?>

   <a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $cat[0]?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a>

   <a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $cat[0];?>&cat=<?php echo $cat[0];?>">
   <IMG alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit"/>
   </a>

   <?php

	} else {



	}

	?>

   <?php
   if ($withSubCat) {
      showSubcat($cat[0]);
   }
   echo "</td>";


}

#################################################################################

function showSubcat ($c) {

	global $f2;

   //$query = "SELECT t1.category_id, t1.category_name, t2.category_name, obj_count, allow_records FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND parent_category_id='$c' and t2.lang='".$_SESSION['MDS_LANG']."' order by t1.list_order,  t2.category_name ASC ";
   $query = "SELECT categories.category_id, categories.category_name, cat_name_translations.category_name, obj_count, allow_records FROM categories, cat_name_translations WHERE categories.category_id=cat_name_translations.category_id AND parent_category_id='$c' and cat_name_translations.lang='".$_SESSION['MDS_LANG']."' order by categories.list_order,  cat_name_translations.category_name ASC ";
   
   $result = mysql_query ($query ) or die(mysql_error());

   $x=0;
   echo "<br><div style='margin-left: 20px;'>";
   while ($row = mysql_fetch_row($result)) {
      $x++;
      if ($x > SHOW_SUBCATS) break;
	  echo $row[2];
	  if  ($row[4]=='N') echo "<b>&#8224;</b>";
	  
    //echo "<A HREF=".$_SERVER['PHP_SELF']."?cat=$row[0]><font color=#0000FF>$row[2]</font></A> "//;echo "<small>(ID: ". ($row[0]).")</small>";

	  

   ?>

   <a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $row[0]?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a>

   <a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $row[0];?>&cat=<?php echo $row[0];?>">
   <IMG src="edit.gif" width="16" height="16" border="0" alt="Edit">
   </a><br>

   <?php

	

   }
   if ($x > SHOW_SUBCATS)
   echo "[<a href='".htmlentities($_SERVER['PHP_SELF'])."?cat=$c'><b>More...</b></a>]</div>";
   else
   echo "</div>";


}



#################################################################
function getPath($c) {

   $p = findPath($c, "");
   return $p;
}

###############################################################

function get_search_set($c, $path) {

	 # query that will get all the child nodes;
	$query ="SELECT category_id, category_name FROM categories WHERE parent_category_id='$c' order by list_order , category_name  ";
	$result = mysql_query ($query) or die(mysql_error().$query);  
	while ($row = mysql_fetch_row($result)) {
		$path = "$path,$row[0]";
		$path = get_search_set ( $row[0], $path);
	}
   // no more trees returned

   return $path;

 
}

###############################################################
# Display the path. Recursive function.
#
function findPath($c, $path) {

	global $f2;
	//$query = "SELECT t1.category_name, t1.parent_category_id, t2.category_name FROM categories as t1, cat_name_translations as t2 WHERE t1.category_id=t2.category_id AND t1.category_id='$c' AND t2.lang = '".$_SESSION['MDS_LANG']."' ";
	$query = "SELECT categories.category_name, categories.parent_category_id, cat_name_translations.category_name FROM categories, cat_name_translations WHERE categories.category_id=cat_name_translations.category_id AND categories.category_id='$c' AND cat_name_translations.lang = '".$_SESSION['MDS_LANG']."' ";
//echo $query;
	$result = mysql_query($query) or die("<b>$query</b>".mysql_error());
	if (mysql_num_rows($result)>0) {
		$row = mysql_fetch_row($result);

		if ($path == "") {
			 $path = "  $row[2]  "; // leaf
		} else {
			$path = " <A href=\"".htmlentities($_SERVER['PHP_SELF'])."?cat=$c\">$row[2]</a> -&gt;  "; // stem
		}
		
		$path = findPath($row[1], $path).$path;   
		return $path;
	  
	}
 
}



###############################################################

function getCatName($c) {
	if (!$c) return false;
   $query = "SELECT category_name FROM cat_name_translations WHERE category_id ='$c' and lang='".$_SESSION['MDS_LANG']."' ";
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   return $row[0];
}

###############################################################

function getCatParent($c) {
   $query = "SELECT parent_category_id FROM categories WHERE category_id ='$c'";
   $result = mysql_query($query) or die(mysql_error().$query);
   $row = mysql_fetch_row($result);
   return $row[0];
}

###################################################################
function showCatOptions ( $node, $path) {

	
   # query that will get all the child nodes;
$query ="SELECT category_id, category_name FROM categories WHERE parent_category_id=$node order by list_order ,  category_name  ";
   $result = mysql_query ($query) or die(mysql_error());  
      while ($row = mysql_fetch_row($result)) {
         $prev = $path;
         $path = "$path -- $row[1]";
         echo "<option value=$row[0]>$path</option>\n";
         showCatOptions ( $row[0], $path);
         $path = $prev;
      }
   // no more trees returned
   return;

}



##########################################################

function add_new_cat_form ($parent) {


?>
<hr>

<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
Add a new category here:<br>
<input type="text" name="new_cat" size="20">
<input type="checkbox" name="allow_records" value="ON" id="id01" checked> <label for="id01">Allow records to be added to this category.</label><br>
<?php
echo "<input type='hidden' name='cat' value='$parent'>";

?>
<input type='submit' value="Add"><br>

<?php


}

###################################################################

function add_cat ( $catname, $parent, $form_id, $allow_records) {
	global $f2;

   #$id = db_generate_id("category_id", "categories");
   
   $id = db_generate_id_fast("category_id", "categories");

   $query = "INSERT INTO categories (category_id, category_name,
parent_category_id, form_id, allow_records) VALUES ($id, '$catname', $parent, $form_id, '$allow_records')";
  // echo "doing query, id is: $query";
   $result = mysql_query($query) or die($query.mysql_error());

   $cat_id = mysql_insert_id ();

   $sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES (".$id.", '".$_SESSION['MDS_LANG']."', '".$catname."')";
	$result = mysql_query($sql) or die (mysql_error().$sql);

	//echo "Updated to <b>$catname</b> ($sql)<br>";

  // $sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES (".$category_id.", '".$_SESSION['MDS_LANG']."', '".$new_name."')";
	//mysql_query($sql) or die (mysql_error());


}


###################################################################

function del_cat_recursive ($category_id) {
	
	$query ="SELECT * FROM categories WHERE category_id='$category_id' ";
	$result = mysql_query ($query) or die (mysql_error().$query);
	$row = mysql_fetch_array($result);
	if (($row['obj_count'] > 0) && ($_REQUEST['confirm']=='')) {
		
		return -$row['obj_count'];

	}

	$query ="DELETE	FROM categories WHERE category_id='$category_id' ";
    mysql_query ($query) or die(mysql_error().$query);

	$query ="DELETE	FROM cat_name_translations WHERE category_id='$category_id' ";
    mysql_query ($query) or die(mysql_error().$query);
	
	$query ="SELECT * FROM categories WHERE parent_category_id='$category_id' ";
	$result = mysql_query ($query) or die(mysql_error().$query);  
	while ($row= mysql_fetch_array($result)) {
		del_cat_recursive ($row['category_id']);
	}

   
}



###################################################################

function get_category($category_id) {
	global $f2;

	$sql = "select * FROM cat_name_translations, categories  ".
		   "WHERE cat_name_translations.category_id=categories.category_id AND categories.category_id='$category_id' and lang='".$_SESSION['MDS_LANG']."'";

	$result = mysql_query($sql) or die (mysql_error());
	return mysql_fetch_array($result);


}





######################################################
function build_ad_count ($cat) {

	//include ("../include/posts.inc.php");

	// get number of posts for this category
	$sql = search_category_for_ads($cat);
	$sql = "SELECT * FROM ads WHERE 1=1 $sql ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$count = mysql_num_rows($result);
	$c_row = get_category($cat);
	//echo "".$c_row['category_name']." --&gt; $count<br>";
	
	// are there more cats?
	$sql = "SELECT * FROM categories WHERE parent_category_id='$cat' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);

	while ($row = mysql_fetch_array($result)) {
		$count = $count + build_ad_count($row['category_id']);
	}

	$sql = "UPDATE categories SET obj_count='$count' WHERE category_id='$cat' AND form_id=3 ";
	mysql_query ($sql) or die (mysql_error().$sql);

	return $count;

}

##########################################################

# show all categories that are the children

function getCatStruct($cat_id, $lang, $f_id) {
	global $CACHE_ENABLED, $f2;
  
	// $category_table = array();

	if ($cat_id==false) $cat_id='0';

	if ($CACHE_ENABLED=='YES') {
		$dir = dirname(__FILE__);
		$dir = preg_split ('%[/\\\]%', $dir);
		$blank = array_pop($dir);
		$dir = implode('/', $dir);
		if (file_exists("$dir/cache/cat_f".$f_id."_c".$cat_id."_cache.inc.php")) {

			include ("$dir/cache/cat_f".$f_id."_c".$cat_id."_cache.inc.php");
			return $category_table[$_SESSION['MDS_LANG']];
		}
	}
  

   # query to get all the nodes that are the 
   # children of child id

    $query = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name, obj_count  FROM categories LEFT JOIN cat_name_translations  ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='$cat_id' AND (lang='".$lang."') and form_id=$f_id ORDER BY list_order, category_name ";

	//echo "$query";

   $x=0;
   
   $result = mysql_query ($query) or die($query. mysql_error());
   $i=0;
   while ($row = mysql_fetch_row($result)) {
	   //$children = array();
	   $children = getCategoryChildrenStruct($row[0], $lang, $f_id);
	   $category_table[$i]['category_id'] = $row[0];
	   $category_table[$i]['category_parent_id'] = $cat_id;
	   $category_table[$i]['category_type'] = "PARENT";
	   $category_table[$i]['category_name'] = $row[3];
	   $category_table[$i]['category_obj_cnt'] = $row[4];
	   $category_table[$i]['category_children'] = $children;
	   $category_table[$i]['category_child_cnt'] = sizeof($children);	   
		$i++;
	   //echo $row[3]." ";
     
   }

   return $category_table;
   

}


###################################################################################

function getCategoryChildrenStruct($cat_id, $lang, $f_id) {

	$children = array();

	 $query = "SELECT categories.category_id, categories.category_name, lang, cat_name_translations.category_name AS NAME, obj_count  FROM categories LEFT JOIN cat_name_translations  ON categories.category_id=cat_name_translations.category_id WHERE parent_category_id='$cat_id' AND (lang='".$lang."') and form_id='$f_id' ORDER BY list_order, NAME ASC "; // removed: obj_count DESC,

	 $result = mysql_query ($query) or die($query. mysql_error());

	$i=0;
	 while ($row = mysql_fetch_row($result)) {
	   $children[$i]['category_id'] = $row[0];
	   $children[$i]['category_type'] = "CHILD";
	   $children[$i]['category_name'] = $row[3];
	   $children[$i]['category_obj_cnt'] = $row[4];
	   $i++;

	 }

	 return $children;



}
###########################################
# Display category structure.
# The following function will break up the array
# into equal portions, and arrange them into columns
function display_categories($cats) {
	global $label;

	if (func_num_args() > 1) {
		$COLS = func_get_arg(1);
	} else {
		$COLS = 2;
	}

	$parents = (sizeof($cats));
	$max = ceil ($parents / $COLS); // how many cats per column

	$width = 100 / $COLS;

	$index=0;

	?>
	<table align="center" border="0" cellpadding="5" cellspacing="0" width="100%"  >
	<tr>
	<?php

	for ($c = $COLS; $c > 0; $c--) {
		echo "<td valign='top' width='$width%' bgcolor=''>";
		$max = ceil ($parents / $c);
		for ($i = 0; $i < $max; $i++) {
			$parents--;
			//echo "yeah...max($max) ($parents / $c)";
			echo '<IMG src="images/arrow.gif" width="6" height="9" border="0" alt=""/>';
			echo "<A HREF=\"".cat_url_write($cats[$index]['category_id'], $cats[$index]['category_name'])."\"><span class='cat_heading'>";
			echo $cats[$index]['category_name'];
			
			echo "</span></a> ";
			if (CAT_SHOW_OBJ_COUNT=='YES') {
				echo "<small>(".$cats[$index]['category_obj_cnt'].")</small>";
			}
			echo "<br>";
			if  ((FORMAT_SUB_CATS=='YES') && ($_REQUEST['cat']=='')) {
				display_sub_cats_table($cats, $index);
			} else {
				display_sub_cats_compact($cats, $index);

			}
			$index++;

		}
		echo "</td>";
	}

	?>
	</tr>
	</table>
	<?php


}



##################################

function display_sub_cats_table(&$cats, $index) {
	global $label;
	
	//print_r ($cats);

	$children = $cats[$index]['category_children'];
	$space = "";

	echo "<div class='cat_subcategory'>";
	echo "<table border='0' width='100%'>";
	$j=0;
	$sub_width = 100 / SUB_CATEGORY_COLS;
	for ($x=0; $x < $cats[$index]['category_child_cnt']; $x++) {
		if (CAT_NAME_CUTOFF == "YES") {
			//if (strlen($children[$x]['category_name']) > CAT_NAME_CUTOFF_CHARS) {
				$children[$x]['category_name'] = truncate_html_str($children[$x]['category_name'], CAT_NAME_CUTOFF_CHARS, $trunc_str_len);
			//}
			//echo "river";
		}
		if ($j==0) { echo "<tr>"; $tr_open=true;}
		$j++;
		echo "<td valign='top' width='33%' >";
		echo $space."<a href='".cat_url_write($children[$x]['category_id'], $children[$x]['category_name'])."'>".$children[$x]['category_name']."</a>";
		if (CAT_SHOW_OBJ_COUNT=='YES') {
			echo "<small> (".$children[$x]['category_obj_cnt'].")</small>";
		}
		//$space = " &nbsp; ";
		if ($x >= SHOW_SUBCATS) {
			echo " &nbsp; [<a href='".cat_url_write($cats[$index]['category_id'], $cats[$index]['category_name'])."'><b>".
				$label["category_expand_more"]."</b></a>] ";
			break;
		}
		echo "</td>";
		if ($j>=SUB_CATEGORY_COLS) {echo "</tr>"; $j=0; $tr_open=false;}
	}
	if (($j < SUB_CATEGORY_COLS) && ($tr_open)) { // render the remaining cells
		for ($j=$j; $j < SUB_CATEGORY_COLS; $j++) {
			echo "<td>&nbsp;</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "</div>";


}

######################################

function display_sub_cats_compact(&$cats, $index) {
	global $label;
	$children = $cats[$index]['category_children'];
			$space = "";
			echo "<div class='cat_subcategory'>";
			for ($x=0; $x < $cats[$index]['category_child_cnt']; $x++) {
				//echo CAT_NAME_CUTOFF;
				if (CAT_NAME_CUTOFF == "YES") {
					//if (strlen($children[$x]['category_name']) > CAT_NAME_CUTOFF_CHARS) {
						$children[$x]['category_name'] = truncate_html_str($children[$x]['category_name'], CAT_NAME_CUTOFF_CHARS, $trunc_str_len);
					//}
					//echo "river";
				}
				echo $space."<a href='".cat_url_write($children[$x]['category_id'], $children[$x]['category_name'])."'>".$children[$x]['category_name']."</a>";
				if (CAT_SHOW_OBJ_COUNT=='YES') {
					echo "<small>(".$children[$x]['category_obj_cnt'].")</small>";
				}
				$space = " &nbsp; ";
				if ($x >= SHOW_SUBCATS) {
					echo " &nbsp; [<a href='".cat_url_write($cats[$index]['category_id'], $cats[$index]['category_name']). "'><b>".$label["category_expand_more"]."</b></a>] ";
					break;
				}
			}
			echo "</div>";


}

?>
