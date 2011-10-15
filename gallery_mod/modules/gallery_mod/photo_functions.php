<?php
/**
 * @package flatnux_module_gallery
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');
/**
 * ricava la lista delle foto presenti in una cartella
 * @param string directory
 * @param string extensions 
 */
function FNGALLERY_GetFileList($dir,$extensions)
{
	global $_FN;
	if (!is_dir($dir))
	{
		return false;
	}
	$handle_dir=opendir($dir);
	$list_images=array();
	$exts=explode(",",strtolower($extensions));
	while (false !== ($file=readdir($handle_dir)))
	{
		if (!is_dir($dir . "/" . $file) && in_array(strtolower(FN_GetFileExtension($file)),$exts))
		{
			$tmp=array();
			$tmp['url']="";
			if (FN_GetFileExtension($file) == "link")
			{
				$tmp['url']=file_get_contents($dir . "/" . $file);
			}
			$filetitle=$dir . "/" . $file . "." . $_FN['lang'] . ".title.txt";
			$filedesc=$dir . "/" . $file . "." . $_FN['lang'] . ".txt";
			$filethumb=FNGALLERY_GetThumb($dir . "/" . $file);
			$tmp['file']=$dir . "/" . $file;
			$tmp['title']="";
			$tmp['description']="";
			if (file_exists($filetitle))
				$tmp['title']=htmlspecialchars(file_get_contents($filetitle));
			if (file_exists($filedesc))
				$tmp['description']=htmlspecialchars(file_get_contents($filedesc));
			$tmp['thumb']=$filethumb;
			$list_images[]=$tmp;
		}
	}
	closedir($handle_dir);
	sort($list_images);
	return $list_images;
}
/**
 *
 * @global type $_FN
 * @param type $filename
 * @return string 
 */
function FNGALLERY_GetThumb($filename)
{
	global $_FN;
	$path=substr($filename,0,strrpos($filename,"/"));
	$file_thumb=substr($filename,strrpos($filename,"/"),strlen($filename));
	$filenamethumb=$path . "/" . "thumbs" . "/" . $file_thumb;
	if (file_exists($filenamethumb))
	{
		return $filenamethumb;
	}
	if (file_exists($filenamethumb . ".gif"))
	{
		return $filenamethumb . ".gif";
	}
	if (file_exists($filenamethumb . ".png"))
	{
		return $filenamethumb . ".png";
	}
	if (file_exists($filenamethumb . ".jpg"))
	{
		return $filenamethumb . ".jpg";
	}
	if (file_exists($filenamethumb . ".jpeg"))
	{
		return $filenamethumb . ".jpeg";
	}
	if (file_exists($filenamethumb . ".GIF"))
	{
		return $filenamethumb . ".GIF";
	}
	if (file_exists($filenamethumb . ".PNG"))
	{
		return $filenamethumb . ".PNG";
	}
	if (file_exists($filenamethumb . ".JPG"))
	{
		return $filenamethumb . ".JPG";
	}
	if (file_exists($filenamethumb . ".JPEG"))
	{
		return $filenamethumb . ".JPEG";
	}
	if (FNGALLERY_IsVideo($filename))
		return ($_FN['siteurl'] . ("modules/gallery_mod/video.png"));
	else
		return $filenamethumb . ".jpg";
}
/**
 *
 * @param string $filename
 * @return bool 
 */
function FNGALLERY_IsVideo($filename)
{
//-----------------------------     config     -------------------------------->
	global $_FN;
	$config=FN_LoadConfig("modules/gallery_mod/config.php");
	$photodir=$config['photodir'];
	$movie_extensions=$config['movie_extensions'];
	$ThumbSize=$config['ThumbSize'];
	$Num_Cols=$config['Num_Cols'];
	$photogroup=$config['photogroup'];
	$movie_extensions=$config['movie_extensions'];
	$create_thumbs_dir=$config['create_thumbs_dir'];
	$use_lightbox=$config['use_lightbox'];
//-----------------------------     config     --------------------------------<
	$exts=explode(",",strtolower($movie_extensions));
	if (in_array(strtolower(FN_GetFileExtension($filename)),$exts))
		return true;
	else
		return false;
}
?>