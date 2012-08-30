<?php
/**
 * @package flatnux_section_gallery
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2005
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * 
 */
if (strpos ( strtolower ( $_SERVER ['SCRIPT_NAME'] ), strtolower ( basename ( __FILE__ ) ) ))
{
	header ( "Location: ../../index.php" );
	die ( "..." );
}

global $_FN, $photodir;
$DEFDIR = $_FN ['datadir'] . "/$photodir";
$dir = getparam ( "dir", PAR_GET, SAN_FLAT );
if (is_admin () == true)
{
	/*	echo "\n<form  method=\"post\" action=\"?mod=".$_FN['vmod']."&amp;dir=$dir\" >";
	    echo _NEWFOLDER.":&nbsp;<input  size=\"20\"  type=\"text\" name=\"sectionname\" />";
	    echo "\n<input type=\"submit\"  class=\"submit\" name=\"newdir\" value=\""._CREA."\" />";
		echo "\n</form>";*/
	echo "<br /><a href=\"?mod={$_FN['mod']}&amp;f=1&amp;dir=$dir\">refresh thumbs</a><br /><br />";
}
?>