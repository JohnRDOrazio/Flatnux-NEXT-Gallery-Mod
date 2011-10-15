<?php
/**
 * @package flatnux_module_gallery
 * @author Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 *
 */
defined('_FNEXEC') or die('Restricted access');
global $_FN;
require_once ("modules/gallery_mod/photo_functions.php");
global $ThumbSize,$Num_Cols,$movie_extensions,$_FN,$photogroup,$use_lightbox,$create_thumbs_dir;
//-----------------evito di navigare in altre cartelle ------------------------<
//-----------------------------     config     -------------------------------->
$config=FN_LoadConfig("modules/gallery_mod/config.php");
$photodir=$config['photodir'];
//dprint_r($config);
$movie_extensions=$config['movie_extensions'];
$ThumbSize=$config['ThumbSize'];
$Num_Cols=$config['Num_Cols'];
$photogroup=$config['photogroup'];
$create_thumbs_dir=$config['create_thumbs_dir'];
$use_lightbox=$config['use_lightbox'];
//-----------------------------     config     -------------------------------->
$dir=FN_GetParam("dir",$_GET,"html");
$getdir=FN_GetParam("dir",$_GET,"html");
//-----------------evito di navigare in altre cartelle ------------------------>
if ($dir != "" && (strstr($dir,".") || !is_dir($_FN['datadir'] . "/$photodir/$dir")))
{
	die("error");
}
$PATH_GALLERY=$_FN['datadir'] . "/$photodir";
//--------------- inizializzazione gallery ------------------------------------>
if (!file_exists($PATH_GALLERY))
{
	FN_MkDir($PATH_GALLERY);
	if (FN_IsWritable($PATH_GALLERY))
	{
		$files=glob("modules/gallery_mod/install/*");
		foreach ($files as $file)
		{
			FN_Copy($file,$PATH_GALLERY);
		}
	}
}
//--------------- inizializzazione gallery ------------------------------------<
//------------- se il gruppo foto non esiste lo creo -------------------------->
if ($photogroup != "")
{
	$table=new XMLTable("fndatabase","groups",$_FN['datadir']);
	$grouplist=$table->GetRecordByPk($photogroup);
	if (!$grouplist)
		$table->InsertRecord(array("groupname"=>$photogroup));
}
//------------- se il gruppo foto non esiste lo creo --------------------------<
//----------------------------------NEW FOLDER---- ---------------------------->
if (FNGALLERY_IsGalleryAdmin() && isset($_POST ['sectionname']))
{
	$dirtomake=FN_GetParam("sectionname",$_POST,"flat");
	if ($dirtomake != "")
	{
		//die ("$PATH_GALLERY/$dir/" . $dirtomake);
		if (file_exists("$PATH_GALLERY/$dir/" . $dirtomake))
		{
			FNGALLERY_PopupWindow("$PATH_GALLERY/$dir/" . $dirtomake . ":<br />" . FN_i18n("a file already exists with this name"));
		}
		else
		{
			FN_MkDir("$PATH_GALLERY/$dir/" . $dirtomake);
		}
	}
}
//----------------------------------NEW FOLDER --------------------------------<
//----------------------------------NEW PHOTO  -------------------------------->
if (FNGALLERY_IsGalleryAdmin() && isset($_FILES ['newfile']))
{
	$fileToOpen="$PATH_GALLERY/$dir";
	$file_clean=str_replace("\\",'',$_FILES ['newfile'] ['name']);
	if ($file_clean != "")
	{
		if (FNGALLERY_IsImage($_FILES ['newfile'] ['tmp_name']) || FNGALLERY_IsVideo($file_clean) || FN_GetFileExtension($file_clean) == "link")
		{
			if (!move_uploaded_file($_FILES ['newfile'] ['tmp_name'],$fileToOpen . "/" . $file_clean))
			{
				FN_Alert(_FILENOTCREATED);
			}
			else
			{
			}
			FNGALLERY_RefreshThumbs($fileToOpen);
		}
		else
		{
			echo ("<div>" . FN_i18n("the file you uploaded is not an image") . "</div>");
		}
	}
}
//----------------------------------NEW PHOTO  --------------------------------<
//----------------------------------DEL PHOTO  -------------------------------->
$delfile=FN_GetParam("delfile",$_GET,"flat");
if (FNGALLERY_IsGalleryAdmin() && $delfile != "")
{
	$delfile=basename($delfile);
	$fileToOpen="$PATH_GALLERY/$dir/$delfile";
	if (file_exists($fileToOpen))
	{
		unlink($fileToOpen);
		FNGALLERY_RefreshThumbs("$PATH_GALLERY/$dir");
	}
}
//----------------------------------DEL PHOTO  --------------------------------<
//----------------------------------DEL FOLDER  ------------------------------->
$deldir=FN_GetParam("deldir",$_GET,"flat");
if (FNGALLERY_IsGalleryAdmin() && $deldir != "")
{
	if (!strstr("..",$deldir))
	{
		$fileToOpen="$PATH_GALLERY/$deldir";
		if (file_exists($fileToOpen) && is_dir($fileToOpen))
		{
			FN_RemoveDir($fileToOpen);
		}
	}
}
//----------------------------------DEL FOLDER  -------------------------------<
//------------------------------- javascript window --------------------------->
$photos=FNGALLERY_GetFileList("$PATH_GALLERY/$dir","jpg,jpeg,gif,bmp,png");
$photos=FN_ArraySortByKey($photos,"file");
$videos=FNGALLERY_GetFileList("$PATH_GALLERY/$dir","$movie_extensions");
$links=FNGALLERY_GetFileList("$PATH_GALLERY/$dir","link");
$dirs=FNGALLERY_GetListDir("$PATH_GALLERY/$dir");
echo '
<script  type="text/javascript">
<!-- 
function popup(nomefile, larghezza, altezza, x, y) {win_popup = window.open(nomefile,"popup","toolbar= 0,location= 0,directories= 0,status= 0,menubar= 0,scrollbars= 1,resizable= 1,copyhistory= 0,width=" + larghezza + ",height=" + altezza);
if(x && y); {x = parseInt(x);
y = parseInt(y);
win_popup.moveTo(x, y); } }
';
if (FNGALLERY_IsGalleryAdmin())
{
	echo '
function divtoinput(obj)
{
	var obj_title=obj.innerHTML;
	obj.removeAttribute("onclick");
	obj.innerHTML="<input style=\"text-align:center\" type=\"text\" name=\""+obj.id+"\" value=\""+obj_title+"\" />";
	document.getElementById("x__btn_savephotos").type="submit";		
}
function toinput(id)
{
	var obj_title=document.getElementById("galleryid_"+id)
	document.getElementById("galleryid_"+id).removeAttribute("onclick");
	document.getElementById("d_"+"galleryid_"+id).removeAttribute("onclick");
	obj_title.innerHTML="<input style=\"text-align:center\" type=\"text\" name=\""+obj_title.id+"\" value=\""+document.getElementById("x__"+obj_title.id).innerHTML+"\" />";
	olddesc=document.getElementById("x__d_"+obj_title.id).innerHTML;
	document.getElementById("d_"+obj_title.id).innerHTML="<textarea name=\"d_"+obj_title.id+"\">"+olddesc+"</textarea>";
	document.getElementById("x__btn_savephotos").type="submit";
}
function titletoinput(obj)
{
	try{
	var olddesc=document.getElementById("desc_"+obj.id).innerHTML;
	var oldtitle=document.getElementById("title_"+obj.id).innerHTML;
	document.getElementById("x_title_"+obj.id).innerHTML=
	"<input type=\"text\" name=\"title_"+obj.id+"\" value=\"" + oldtitle + "\" />&nbsp;";
	document.getElementById("desc_" + obj.id).innerHTML=
	"<input value=\""+olddesc+"\"  type=\"text\" name=\"desc_"+obj.id+"\"  />";
	document.getElementById("x__btn_savephotos").type="submit";
	}
	catch(e)
	{}
}
';
}
echo '
// -->
</script>
';
//------------------------------- javascript window ---------------------------<
//------------------------------- change title      --------------------------->
if (FNGALLERY_IsGalleryAdmin() && isset($_POST ["ffmaintitle"]))
{
	//dprint_r($_POST);
	$newtitle=htmlspecialchars(FN_GetParam("ffmaintitle",$_POST,"flat"));
	$filetoopen="$PATH_GALLERY/$dir/title." . $_FN['lang'] . ".txt";
	$fileh=fopen($filetoopen,"w");
	fwrite($fileh,$newtitle);
	fclose($fileh);
}
//------------------------------- change title      ---------------------------<
echo FNGALLERY_PrintNavBar();
if (FNGALLERY_IsGalleryAdmin())
	echo "<form action=\"?mod=" . $_FN['mod'] . "&amp;dir=$dir\" enctype=\"multipart/form-data\" method=\"post\">";
$title="";
if (file_exists("$PATH_GALLERY/$dir/title." . $_FN['lang'] . ".txt"))
{
	$title=htmlentities(file_get_contents("$PATH_GALLERY/$dir/title." . $_FN['lang'] . ".txt"));
}
$ok="";
if (FNGALLERY_IsGalleryAdmin())
{
	$ok="onclick=\"divtoinput(this)\"";
}
echo "<div style=\"text-align:center\" id=\"ffmaintitle\" $ok >" . ($title) . "</div>";
if (FNGALLERY_IsGalleryAdmin() && $title == "")
{
	echo "<img style=\"cursor:pointer\" onclick=\"divtoinput(document.getElementById('ffmaintitle'))\" style=\"cursor:pointer;border:0px;\" src=\"" . FN_FromTheme("images/modify.png") . "\"  alt=\"\" title=\"" . FN_i18n("modify") . "\" />";
}
//------------------------------- folders-------------------------------------->
//------------------------------- folders-------------------------------------->
$iddir=0;
foreach ($dirs as $curdir)
{
	$dir=$curdir ['dir'];
	$dirtitle=$curdir ['title'];
	$dirdesc=$curdir ['description'];
	$basedir=FN_GetParam("dir",$_GET,"flat");
	FNGALLERY_MakeDirThumb($basedir,$dir,$PATH_GALLERY,false);
	$tonclick="";
	//----cambio titolo interno e nome cartella --->
	if (FNGALLERY_IsGalleryAdmin() && isset($_POST ["desc_iddir$iddir"]))
	{
		$dirdesc=$newdesc=(FN_GetParam("desc_iddir$iddir",$_POST,"flat"));
		$dirtitle=$newtitle=(FN_GetParam("title_iddir$iddir",$_POST,"flat"));
		$fileh=fopen($PATH_GALLERY . "/" . $curdir ['dir'] . "/title." . $_FN['lang'] . ".txt","w");
		fwrite($fileh,$newdesc);
		fclose($fileh);
		FN_SetFolderTitle($PATH_GALLERY . "/" . $curdir ['dir'] . "/",$newtitle,$_FN['lang']);
	}
	//----cambio titolo interno e nome cartella ---<
	echo "\n<div>";
	////-------anteprima cartella o icona-------------------->
	$img=FNGALLERY_GetThumb("$PATH_GALLERY$dir");
	if ($create_thumbs_dir == 1 && $img != false)
	{
		echo "<a href=\"" . FN_RewriteLink("?mod=" . $_FN['mod'] . "&amp;dir=$dir") . "\" ><img style=\"padding:5px;vertical-align:middle;border:0px;\" alt=\"\" src=\"$img\"  /></a>&nbsp;";
	}
	else
		echo "<img style=\"border:0px;\" alt=\"\" src=\"" . FN_FromTheme("images/subsection.png") . "\"  />&nbsp;";
	////-------anteprima cartella o icona--------------------<
	echo "<b id=\"x_title_iddir$iddir\" ><a href=\"" . FN_RewriteLink("?mod=" . $_FN['mod'] . "&amp;dir=$dir") . "\" id=\"title_iddir$iddir\">$dirtitle</a></b>";
	echo "&nbsp;<em><span id=\"desc_iddir$iddir\">" . $dirdesc . "</span></em>"; // descrizione
	if (FNGALLERY_IsGalleryAdmin())
	{
		echo "&nbsp;<img id=\"iddir$iddir\" style=\"cursor:pointer\" onclick=\"titletoinput(this)\" style=\"cursor:pointer;border:0px;\" src=\"" . FN_FromTheme("images/modify.png") . "\" alt=\"\" title=\"" . FN_i18n("modify") . "\" />";
	}
	//---icona cancella --->
	if (FNGALLERY_IsGalleryAdmin())
	{
		$cdir=FN_GetParam("dir",$_GET,"flat");
		echo "<a href=\"javascript:check('?mod={$_FN['mod']}&amp;dir=$cdir&amp;deldir=$dir')\"><img style=\"border:0px;\" src=\"" . FN_FromTheme("images/delete.png") . "\"  alt=\"\" title=\"" . FN_i18n("delete folder") . "\" /></a>";
	}
	//---icona cancella ---<
	echo "</div>";
	$iddir++;
}
//------------------------------- folders--------------------------------------<
//------------------------------- folders--------------------------------------<
//---------------------------------links -------------------------------------->
if (count($links) > 0)
{
	foreach ($links as $curlink)
	{
		$url=$curlink ['url'];
		$dirtitle=$curlink ['url'];
		echo "\n<div><img style=\"border:0px;\" alt=\"\" src=\"" . FN_FromTheme("images/mime/web.png") . "\" />.<b><a  onclick=\"window.open(this.href);return false;\"  href=\"$url\" >$dirtitle </a></b> " . $curlink ['title'] . "</div>";
	}
}
//---------------------------------links --------------------------------------<
//---------------------------------photos ------------------------------------->
FNGALLERY_PrintFiles($photos);
//---------------------------------photos ------------------------------------->
//---------------------------------videos ------------------------------------->
FNGALLERY_PrintFiles($videos);
//---------------------------------videos -------------------------------------<
if (FNGALLERY_IsGalleryAdmin())
{
	echo "<input id=\"x__btn_save\" class=\"submit\" type=\"submit\" value=\"" . FN_i18n("save") . "\" />";
  echo "</form>";
	// invio nuovo file -------------->
	$maxsize1=ini_get("upload_max_filesize");
	$maxsize=str_replace("M","000000",$maxsize1);
  echo "<div style=\"width:350px;margin:10px auto 0px auto;text-align:center;\">" . FN_i18n("send new image") . " (max " . $config["max_uploadlimit"] . "M):<br /><span style=\"font-size:.8em;font-style:italic;\">DRAG & DROP enabled on capable browsers!</span></div>";
?>
	<div id="imageuploadform" style="width:500px;margin:0px auto;border:groove 1px DarkRed;padding:20px;">		
		<noscript>			
			<!-- <p>Please enable JavaScript to use file uploader.</p> -->
			<!-- or put a simple form for upload here -->
    	<?php 
        echo "<form action=\"?mod=" . $_FN['mod'] . "&amp;dir=$dir\" enctype=\"multipart/form-data\" method=\"post\">"; 
      	echo "<div>
      		<br />
      		<fieldset><legend>" . FN_i18n("send new image") . " (max  $maxsize1):</legend>";
      	echo "<input type=\"file\" name=\"newfile\" />";
      	echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$maxsize\" />";
      	// invio nuovo file --------------<
      	echo "
      		</fieldset>
      		<br />
      		<input id=\"x__btn_savephotos\" class=\"submit\" type=\"submit\" value=\"" . FN_i18n("save") . "\" />
          </form>";
      ?>
		</noscript>         
	</div>
<?php
	// invio nuovo file --------------<
}
if (FNGALLERY_IsGalleryAdmin())
{
	echo "<form  method=\"post\" action=\"?mod=" . $_FN['mod'] . "&amp;dir=$getdir\" ><div><br /><br />";
	echo FN_i18n("new folder") . ":&nbsp;<input  size=\"20\"  type=\"text\" name=\"sectionname\" />";
	echo "\n<input type=\"submit\"  class=\"submit\" name=\"newdir\" value=\"" . FN_i18n("create") . "\" />";
	echo "\n</div></form>";
}
/**
 *
 * @global array $_FN
 * @staticvar int $galleryid
 * @param array $photos 
 */
function FNGALLERY_PrintFiles($photos)
{
//-----------------------------     config     -------------------------------->
	global $_FN;
	$config=FN_LoadConfig("modules/gallery_mod/config.php");
	$photodir=$config['photodir'];
	$movie_extensions=$config['movie_extensions'];
	$ThumbSize=$config['ThumbSize'];
	$Num_Cols=$config['Num_Cols'];
	$photogroup=$config['photogroup'];
	$create_thumbs_dir=$config['create_thumbs_dir'];
	$use_lightbox=$config['use_lightbox'];
//-----------------------------     config     --------------------------------<
	$dir=FN_GetParam("dir",$_GET,"flat");
	static $galleryid=0;
	if (count($photos) > 0)
	{
		$cols=1;
		$row=1;
		$close=true;
		echo "<table cellpadding=\"1\" cellspacing=\"1\" width=\"100%\" border=\"0\" style=\"text-align:center\">";
		foreach ($photos as $curphoto)
		{
			FNGALLERY_CreateThumb($curphoto ['file'],$ThumbSize,false);
			if ($cols == 1)
			{
				echo "<tr>";
				$close=false;
			}
			if (FNGALLERY_IsImage($curphoto ['file']))
			{
				list ( $l,$h,$type,$attr )=getimagesize($curphoto ['file']);
			}
			else
			{
				$l=100;
				$h=100;
			}
			$l += 30;
			$h += 30;
			echo "<td style=\"text-align:center\">";
			$tonclick="";
			if (FNGALLERY_IsGalleryAdmin())
			{
				if (isset($_POST ["galleryid_$galleryid"]))
				{
					//cambio del titolo
					$newtitle=(FN_GetParam("galleryid_$galleryid",$_POST,"flat"));
					$newdesc=(FN_GetParam("d_galleryid_$galleryid",$_POST,"flat"));
					$filetitle=$curphoto ['file'] . "." . $_FN['lang'] . ".title.txt";
					$filedesc=$curphoto ['file'] . "." . $_FN['lang'] . ".txt";
					if (file_exists($filetitle) && !is_writable($filetitle))
					{
						echo "error: $filetitle is radonly";
					}
					else
					{
						$fileh=fopen($filetitle,"w");
						fwrite($fileh,$newtitle);
						fclose($fileh);
						$fileh=fopen($filedesc,"w");
						fwrite($fileh,$newdesc);
						fclose($fileh);
						$curphoto ['title']=htmlspecialchars($newtitle);
						$curphoto ['description']=htmlspecialchars($newdesc);
					}
				}
				$tonclick="onclick='toinput(\"$galleryid\");'";
			}
			echo "<div id=\"galleryid_$galleryid\" $tonclick  >" . "<div style=\"text-align:center\" id=\"x__galleryid_$galleryid\">" . $curphoto ['title'] . "</div>";
			if (FNGALLERY_IsGalleryAdmin() && $curphoto ['title'] == "")
			{
				echo "<img style=\"cursor:pointer;border:0px;\" src=\"" . FN_FromTheme("images/modify.png") . "\"  alt=\"\" title=\"" . FN_i18n("modify") . "\" />";
			}
			echo "</div>";
			if ($use_lightbox == 1 && !FNGALLERY_IsVideo($curphoto ['file']))
				echo "<a rel=\"lightbox[roadtrip]\" href=\"" . $curphoto ['file'] . "\">";
      elseif ($use_lightbox == 2 && !FNGALLERY_IsVideo($curphoto ['file']))
				echo "<a rel=\"jqlightbox\" title=\"". $curphoto ["title"] ."\" href=\"" . $curphoto ["file"] . "\" data-desc=\"" . $curphoto["description"] . "\">";
			else
				echo "<a onclick=\"popup('modules/gallery_mod/photo.php?mod=" . $_FN['mod'] . "&amp;foto=" . $curphoto ['file'] . "&lang=" . $_FN['lang'] . "', $l,$h, 0, 0); return false;\" href=\"" . $curphoto ['file'] . "\">";
			echo "<img  style=\"border:0px;\" src=\"" . $curphoto ['thumb'] . "\" alt=\"\" />";
			echo "</a>";
			if (FNGALLERY_IsGalleryAdmin())
				echo "<br /><a  href=\"javascript:check('?mod={$_FN['mod']}&amp;dir=$dir&amp;delfile=" . basename($curphoto ['file']) . "')\"><img style=\"height:16px;border:0px;\"   src=\"" . FN_FromTheme("images/delete.png") . "\" alt=\"\" title=\"" . FN_i18n("delete") . "\" /></a>";
			echo "<div $tonclick id=\"d_galleryid_$galleryid\"  >" . "<div style=\"text-align:center\" id=\"x__d_galleryid_$galleryid\">" . FN_Tag2Html($curphoto ['description']) . "</div>";
			if (FNGALLERY_IsGalleryAdmin() && $curphoto ['description'] == "")
			{
				echo "<img style=\"cursor:pointer;border:0px;\" src=\"" . FN_FromTheme("images/modify.png") . "\"  alt=\"\" title=\"" . FN_i18n("modify") . "\" />";
			}
			echo "</div></td>";
			if ($cols == $Num_Cols)
			{
				echo "</tr>";
				$cols=0;
				$close=true;
				$row++;
			}
			$galleryid++;
			$cols++;
		}
		if (!$close)
		{
			if ($row > 1)
				while ($cols != $Num_Cols + 1)
				{
					echo "<td>&nbsp;</td>";
					$cols++;
				}
			echo "</tr>";
		}
		echo "</table>";
	}
}
/**
 *
 * @global array $_FN
 * @param type $dir
 * @return type 
 */
function FNGALLERY_GetListDir($dir)
{
//-----------------------------     config     -------------------------------->
	global $_FN;
	$config=FN_LoadConfig("modules/gallery_mod/config.php");
	$photodir=$config['photodir'];
//-----------------------------     config     -------------------------------->	
	$handle_dir=opendir($dir);
	$list_dirs=array();
	while (false !== ($file=readdir($handle_dir)))
	{
		if ($file != "." && $file != ".." && $file != "thumbs" && is_dir($dir . "/" . $file))
		{
			$tnp=array();
			$filetitle=$dir . "/" . $file . "/title." . $_FN['lang'] . ".txt";
			$tmp ['title']=FN_GetFolderTitle($dir . "/$file/");
			$tmp ['description']="";
			if (file_exists($filetitle))
				$tmp ['description']=file_get_contents($filetitle);
			$s="^" . $_FN['datadir'] . "/$photodir/";
			$tmp ['dir']=preg_replace("/" . str_replace('/','\\/',$s) . "/s","","$dir" . "/" . $file);
			$list_dirs []=$tmp;
		}
	}
	sort($list_dirs);
	return $list_dirs;
}
/**
 *
 * @global array $_FN
 */
function FNGALLERY_PrintNavBar()
{
	global $_FN;
//-----------------------------     config     -------------------------------->
	$config=FN_LoadConfig("modules/gallery_mod/config.php");
	$photodir=$config['photodir'];
//-----------------------------     config     -------------------------------->	
	$tmp=$_FN['sectionvalues']['title'];
	echo FN_i18n("position") . ": <a href=\"" . FN_RewriteLink("?mod=" . $_FN['mod']) . "\" >$tmp</a>";
	$path=FN_GetParam("dir",$_GET,"flat");
	//$path = FN_GetParam($path, PAR_NULL, "html");
	$path=explode("/",$path);
	$link="";
	foreach ($path as $section)
	{
		if ($section != "")
		{
			$link .= "/$section";
			$tmp=FN_GetFolderTitle($_FN['datadir'] . "/$photodir/" . $link);
			echo " -&gt; <a href=\"" . FN_RewriteLink("?mod=" . $_FN['mod'] . "&amp;dir=$link") . "\" >$tmp</a>";
		}
	}
}
/**
 *
 * @global string $_FN
 * @global type $photogroup
 * @return type 
 */
function FNGALLERY_IsGalleryAdmin()
{
	global $_FN;
	$config=FN_LoadConfig("modules/gallery_mod/config.php");
	if (FN_IsAdmin() || ($config['photogroup'] != "" && FN_UserInGroup($_FN['user'],$config['photogroup'])))
		return true;
	else
		return false;
}
/**
 *
 * @param string $filename
 * @param int $max
 * @param bool $force_thumb
 * @param int $max_h
 * @param int $max_w
 */
function FNGALLERY_CreateThumb($filename,$max = 100,$force_thumb = false,$max_h = "",$max_w = "")
{
	if ($max_h == "")
		$max_h=$max;
	if ($max_w == "")
		$max_w=$max;
	if (!function_exists("getimagesize"))
	{
		echo "<br />" . "GD library is required";
		return;
	}
	$new_height=$new_width=0;
	if (!file_exists($filename))
	{
		echo "non esiste";
		return;
	}
	if (!getimagesize($filename))
	{
		return;
	}
	list ( $width,$height,$type,$attr )=getimagesize($filename);
	$path=dirname($filename) . "/thumbs";
	$file_thumb=$path . "/" . basename($filename);
	if (!file_exists($path))
	{
		mkdir($path);
	}
	if (!file_exists($path))
	{
		echo "error make dir";
		return;
	}
	if (!is_dir($path))
	{
		echo "<br />$path not exists";
	}
	$new_height=$height;
	$new_width=$width;
	if ($width >= $max_w)
	{
		$new_width=$max_w;
		$new_height=intval($height * ($new_width / $width));
	}
	//se troppo alta
	if ($new_height >= $max_h)
	{
		$new_height=$max_h;
		$new_width=intval($width * ($new_height / $height));
	}
	// se l' immagine e gia piccola
	if ($width <= $max_w && $height <= $max_h)
	{
		$new_width=$width;
		$new_height=$height;
	}
	// Load
	$thumb=imagecreatetruecolor($new_width,$new_height);
	$white=imagecolorallocate($thumb,255,255,255);
	$size=getimagesize($filename);
	switch ($size [2])
	{
		case 1 :
			$source=ImageCreateFromGif($filename);
			break;
		case 2 :
			$source=ImageCreateFromJpeg($filename);
			break;
		case 3 :
			$source=ImageCreateFromPng($filename);
			break;
		default :
			$tmb=null;
			$size [0]=$size [1]=150;
			$source=ImageCreateTrueColor(150,150);
			$rosso=ImageColorAllocate($tmb,255,0,0);
			ImageString($tmb,5,10,10,"Not a valid",$rosso);
			ImageString($tmb,5,10,30,"GIF, JPEG or PNG",$rosso);
			ImageString($tmb,5,10,50,"image.",$rosso);
	}
	// Resize
	imagefilledrectangle($thumb,0,0,$width,$width,$white);
	imagecopyresampled($thumb,$source,0,0,0,0,$new_width,$new_height,$width,$height);
	// Output
	$file_to_open=$file_thumb;
	//forzo estensione jpg
	imagejpeg($thumb,$file_to_open . ".jpg");
}
/**
 *
 * @global string $_FN
 * @param string $targetthumb
 * @param array $listfiles 
 */
function FNGALLERY_CreateIcon($targetthumb,$listfiles)
{
	global $_FN;
	$image_quality=8;
	$n=count($listfiles);
	if ($n > 4)
		$n=4;
	$maxwidth=58;
	$maxheight=40;
	$newwidth=144;
	$newheight=108;
	$dimg=ImageCreateTrueColor($newwidth,$newheight);
	$bianco=ImageColorAllocate($dimg,255,255,255);
	$arancio=ImageColorAllocate($dimg,254,184,22);
	$giallo=ImageColorAllocate($dimg,255,243,140);
	ImageFilledRectangle($dimg,0,0,143,107,$bianco);
	// background color orange
	ImageFilledEllipse($dimg,24,7,14,14,$arancio);
	ImageFilledEllipse($dimg,54,7,14,14,$arancio);
	ImageFilledRectangle($dimg,24,0,54,7,$arancio);
	ImageFilledEllipse($dimg,5,13,10,10,$arancio);
	ImageFilledEllipse($dimg,138,13,10,10,$arancio);
	ImageFilledEllipse($dimg,5,102,10,10,$arancio);
	ImageFilledEllipse($dimg,138,102,10,10,$arancio);
	ImageFilledRectangle($dimg,5,7,138,107,$arancio);
	ImageFilledRectangle($dimg,0,13,143,102,$arancio);
	//over yellow
	ImageFilledEllipse($dimg,26,9,14,14,$giallo);
	ImageFilledEllipse($dimg,52,9,14,14,$giallo);
	ImageFilledRectangle($dimg,26,2,52,9,$giallo);
	ImageFilledEllipse($dimg,7,15,10,10,$giallo);
	ImageFilledEllipse($dimg,136,15,10,10,$giallo);
	ImageFilledEllipse($dimg,7,100,10,10,$giallo);
	ImageFilledEllipse($dimg,136,100,10,10,$giallo);
	ImageFilledRectangle($dimg,7,9,136,105,$giallo);
	ImageFilledRectangle($dimg,2,15,141,100,$giallo);
	ImageColorTransparent($dimg,$bianco);
	for ($j=0; $j < $n; $j++)
	{
		if ($n == 0)
			break;
		$nome=$listfiles [$j];
		$sfile=$listfiles [$j];
		if (FNGALLERY_IsVideo($sfile))
			continue;
		$size=getimagesize($sfile);
		switch ($size [2])
		{
			case 1 :
				$simg=ImageCreateFromGif($sfile);
				break;
			case 2 :
				$simg=ImageCreateFromJpeg($sfile);
				break;
			case 3 :
				$simg=ImageCreateFromPng($sfile);
				break;
		}
		$currwidth=imagesx($simg);
		$currheight=imagesy($simg);
//-----------set the dimensions of the thumbnail ------------------------------>
		if (($currwidth > $maxwidth) or ($currheight > $maxheight))
		{
			if ($currheight > $currwidth)
			{
				$zoom=$maxheight / $currheight;
				$newheight=$maxheight;
				$newwidth=$currwidth * $zoom;
			}
			else
			{
				$zoom=$maxwidth / $currwidth;
				$newwidth=$maxwidth;
				$newheight=$currheight * $zoom;
			}
		}
		else
		{
			$newwidth=$currwidth;
			$newheight=$currheight;
		}
		$distor=($maxwidth - $newwidth) / 2;
		$distver=($maxheight - $newheight) / 2 + 2;
//-----------set the dimensions of the thumbnail ------------------------------<
		switch ($j)
		{
			case 0 :
				$distor=$distor + 9;
				$distver=$distver + 14;
				ImageCopyResampled($dimg,$simg,$distor,$distver,0,0,$newwidth,$newheight,$currwidth,$currheight);
				break;
			case 1 :
				$distor=$distor + 77;
				$distver=$distver + 14;
				ImageCopyResampled($dimg,$simg,$distor,$distver,0,0,$newwidth,$newheight,$currwidth,$currheight);
				break;
			case 2 :
				$distor=$distor + 9;
				$distver=$distver + 60;
				ImageCopyResampled($dimg,$simg,$distor,$distver,0,0,$newwidth,$newheight,$currwidth,$currheight);
				break;
			case 3 :
				$distor=$distor + 77;
				$distver=$distver + 60;
				ImageCopyResampled($dimg,$simg,$distor,$distver,0,0,$newwidth,$newheight,$currwidth,$currheight);
				break;
		}
		ImageDestroy($simg);
	}
	@imagepng($dimg,$targetthumb,$image_quality);
	imagedestroy($dimg);
}
/**
 *
 * @paramstring $basedir
 * @param string $dir
 * @param string $PATH_GALLERY
 * @param boolean $force 
 */
function FNGALLERY_MakeDirThumb($basedir,$dir,$PATH_GALLERY,$force = false)
{
	if (FN_IsAdmin() && isset($_GET ['f']))
		$force=true;
//---------------------------------preview------------------------------------->
	$namedir=basename($dir);
	if (!file_exists("$PATH_GALLERY$basedir/thumbs"))
	{
		mkdir("$PATH_GALLERY$basedir/thumbs");
	}
	$targetthumb="$PATH_GALLERY$basedir" . "/thumbs/$namedir.png";
	if (!file_exists($targetthumb) || $force)
	{
		FNGALLERY_RefreshThumbs("$PATH_GALLERY$dir");
	}
//---------------------------------preview-------------------------------------<
}
/**
 *
 * @param string $fileToOpen
 * @return string 
 */
function FNGALLERY_RefreshThumbs($fileToOpen)
{
	if (FNGALLERY_IsVideo($fileToOpen))
		return;
	//	dprint_r($fileToOpen);
	$namedir=basename($fileToOpen);
	$basedir=dirname($fileToOpen);
	$targetthumb="$basedir" . "/thumbs/$namedir.png";
	$files=FNGALLERY_GetFileList("$fileToOpen","jpg,gif,png,jpeg");
	$listfiles=array();
	foreach ($files as $f)
	{
		$listfiles []=$f ['file'];
	}
	FNGALLERY_CreateIcon($targetthumb,$listfiles);
}
/**
 *
 * @param string $filename
 * @return bool 
 */
function FNGALLERY_IsImage($filename)
{
	if (false != getimagesize($filename))
	{
		return true;
	}
	return false;
}
/**
 *
 * @param string $message 
 */
function FNGALLERY_PopupWindow($message)
{
	echo "<div style=\"position:absolute;left:50%;top:10px;\"><div onclick = \"this.style.display='none'\" style=\"text-align:center;font-size:12px;line-height:14px;padding:10px;left:-200px;background-color:#ffffff;color:#000000;border:1px solid red;cursor:pointer;position:absolute;width:400px;height:100px;margin:auto;\">";
	echo $message;
	echo "</div></div>";
}
?>