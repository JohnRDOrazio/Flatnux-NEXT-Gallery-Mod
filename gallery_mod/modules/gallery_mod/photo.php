<?php
/**
 * @package flatnux_section_gallery
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2005
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * 
 */
global $movie_extensions;
global $_FN;
$relroot = "";
while ( !file_exists($relroot . "include/flatnux.php"))
{
	$relroot .= "../";
}
chdir($relroot);
require_once "include/flatnux.php";
require_once("modules/gallery_mod/photo_functions.php");
FN_LoadConfig("modules/gallery_mod/config.php");
echo "<body style=\"margin:0px;padding:0px;background-color:#a0a0a0\">";
$_foto = FN_GetParam("foto",$_GET,"html");
echo "</body></html>";
if ( strstr($_foto,"..") )
	die(FN_i18n("error"));
FNGALLERY_PopupWindow($_foto);
/**
 *
 * @global array $_FN
 * @param string $_foto 
 */
function FNGALLERY_PopupWindow($_foto)
{
	global $_FN;
	FN_LoadConfig("modules/gallery_mod/config.php");
	$getdir = FN_GetParam("dir",$_GET,"html");
	$folder = dirname($getdir . $_foto);
	$photos = FNGALLERY_GetFileList($folder,"jpg,jpeg,gif,bmp,png,JPG,JPEG,GIF,BMP,PNG");
	$next = $photos[0]['file'];
	for ( $i = 0;isset($photos[$i]);$i++  )
	{
		if ( basename($photos[$i]['file']) == basename($_foto) )
		{
			if ( isset($photos[$i + 1]['file']) )
			{
				$next = $photos[$i + 1]['file'];
				break;
			}
			else
			{
				$next = $photos[0]['file'];
			}
		}
	}
	$next = urlencode($next);
	echo "<div style=\"text-align:center;position:relative;margin-top:10px;\">";
	if ( FNGALLERY_IsVideo($_foto) )
	{
		$thumbs = FNGALLERY_GetThumb($_foto);
		echo "<a  href=\"{$_FN['siteurl']}/$_foto\"><img  alt=\"\" border=\"0\" src=\"$thumbs\"><br /><b>Scarica</b></a><br /><br /><a  href=\"javascript:window.close();\">" . FN_i18n("close") . "</a>";
	}
	else
		echo "<a title=\"$next\" href=\"?mod={$_FN['mod']}&amp;foto=$next\"><img  alt=\"" .FN_i18n("next")  . "\" title=\"" .FN_i18n("next")  . "\"  border=\"0\"  src=\"{$_FN['siteurl']}/$_foto\" /></a>";
	echo "</div>";
}
?>