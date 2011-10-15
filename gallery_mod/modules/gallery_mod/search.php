<?php
/**
 * @package flatnux_module_news
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2003-2005
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * 
 */
defined('_FNEXEC') or die('Restricted access');
/**
 *
 * @global array $_FN
 * @param array $tosearch_array
 * @param string $method
 * @param array $sectionvalues
 * @param int $maxres
 * @return array 
 */
function FNSEARCH_module_gallery($tosearch_array,$method,$sectionvalues,$maxres)
{
	global $_FN;
	$results=array();
	global $_FN;
	$config = FN_LoadConfig("modules/gallery_mod/config.php",$sectionvalues['id']); //load config in section
	//dprint_r($config);
	$where="{$_FN['datadir']}/{$config['photodir']}";
	if (!file_exists($where))
	{
		return;
	}
	$res=array();
	$handle=opendir($where);
	$filelist=array();
	$filelist = FN_ListDir("{$_FN['datadir']}/{$config['photodir']}");
	$cont = 0;
	foreach  ($filelist as $file)
	{
		$found=false;
		if (!($file == "." or $file == ".."))
		{
			$title=null;
			if (is_dir("{$_FN['datadir']}/{$config['photodir']}/$file/"))
			{
				$title=FN_GetFolderTitle("{$_FN['datadir']}/{$config['photodir']}/$file/",$file);
				$files = glob ("{$_FN['datadir']}/{$config['photodir']}/$file/*.txt");
				$desctiption = "";
				foreach ($files as $filec)
				{
					$desctiption .= "\n".file_get_contents($filec);
				}
				//echo " <b>$title</b> ";
				foreach ($tosearch_array as $q)
				{
					$found=false;
					if (stristr($title." ".$desctiption,$q))
					{
						$found=true;
						$haveresult=1;
					}
					else
					{
						if ($method == "AND")
						{
							$found=false;
							break;
						}
					}
				}
			}
			if ($found == true)
			{
				$module = $sectionvalues['id'];
				$res[$cont]="<img border=\"0\ alt=\"$module\" src=\"" . FN_FromTheme("images/icons/image.png") . "\" /> <a href=?mod=$module&amp;dir=/$file>$title</a>";
				$link = FN_RewriteLink("index.php?mod=$module&amp;dir=/$file");
				$results[$cont]['link'] = $link;
				$results[$cont]['title'] = $sectionvalues['title'].": ". $title ;
				$results[$cont]['text'] = substr(strip_tags($title . " ".$desctiption), 0, 100);
				$cont++;
			}
		}
	}
	closedir($handle);
	return $results;
}
?>