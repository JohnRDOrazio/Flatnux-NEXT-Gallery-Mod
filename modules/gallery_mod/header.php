<?php
/**
 * @package flatnux_section_gallery mod
 * @author of modded flatnux module John D'Orazio <donjohn.fmmi@gmail.com>  
 * @author of original flatnux module Alessandro Vernassa <speleoalex@gmail.com>
 * @copyright Copyright (c) 2011-2013
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * 
 * jQuery lightbox type plugin "Colorbox" (http://colorpowered.com/colorbox/) adapted for Flatnux Gallery module by John D'Orazio
 *  
 * AJAX multi-uploader with drag and drop (http://valums.com/ajax-upload/) adapted for Flatnux Gallery module by John D'Orazio
 * ["onAllComplete" event for AJAX multi-uploader (https://github.com/dvdplm/file-uploader/blob/d4ec0953e36b924ff9eda7bb17f93ce2a1189616/client/fileuploader.js)]  
 */
global $_FN;
//-----------------------------     config     -------------------------------->
$config=FN_LoadConfig("modules/gallery_mod/config.php");
$movie_extensions=$config['movie_extensions'];
$ThumbSize=$config['ThumbSize'];
$Num_Cols=$config['Num_Cols'];
$photodir=$config['photodir'];
$photogroup=$config['photogroup'];
$movie_extensions=$config['movie_extensions'];
$create_thumbs_dir=$config['create_thumbs_dir'];
$use_lightbox=$config['use_lightbox'];
//-----------------------------     config     -------------------------------->

$pathscript = "modules/gallery_mod";

// Check file permissions and correct if necessary
$perms = 0755;
$file = $_FN["siteurl"].$pathscript."/fileuploader.php";
if(fileperms($file) < $perms){ chmod($file,$perms); }

if($use_lightbox==1){
  $_FN['section_header_footer'] .= "
  <link rel=\"stylesheet\" href=\"$pathscript/lightbox.css\" type=\"text/css\" media=\"screen\" />
  <script src=\"$pathscript/prototype.js\" type=\"text/javascript\"></script>
  <script src=\"$pathscript/scriptaculous.js?load=effects,builder\" type=\"text/javascript\"></script>
  <script src=\"$pathscript/lightbox.js\" type=\"text/javascript\"></script>
  <script type=\"text/javascript\">
  	LightboxOptions.fileLoadingImage = '$pathscript/loading.gif';
  	LightboxOptions.fileBottomNavCloseImage = '$pathscript/closelabel.gif';
  </script>
  ";
}
if($use_lightbox==2){
?>
<script type="text/javascript">
  if (typeof jQuery == 'undefined') {  
      document.write('<scr'+'ipt type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"><'+'/script>');
  }
</script>
<?php
  $_FN['section_header_footer'] .= "
  <link rel=\"stylesheet\" href=\"$pathscript/colorbox.css\" type=\"text/css\" media=\"screen\" />
  <script src=\"$pathscript/colorbox/jquery.colorbox-min.js\" type=\"text/javascript\"></script>
  <script type=\"text/javascript\">
		wide = ($(window).width()-20)+'px';
		high = ($(window).height()-20)+'px';
    $(document).ready(function(){
			$(\"a[rel='jqlightbox']\").colorbox({
        maxWidth:wide,
        maxHeight:high,
        title:function(){
          var titl = $(this).attr('title');
          var url = $(this).attr('href');
          var desc = $(this).attr('data-desc');
          return '<span title=\"' + desc + '\">' +titl + '</span>' + '&nbsp;&nbsp;&nbsp;<a href=\"" . $_FN["siteurl"] . "modules/gallery_mod/download.php?filename=' + url + '" . (isset($_GET["dir"]) ? "&amp;dir=".$_GET["dir"] : "" ) . "\"><img src=\"modules/gallery_mod/download-icon.png\" title=\"Scarica questa immagine sul tuo computer\" alt=\"SCARICA\" /></a>';
        },
        current: '{current} di {total}',
        onComplete: function(){
          var el = $('.cboxPhoto');
          var desc = $(this).attr('data-desc');
          el.attr('title',desc);
        }
      });
		});
  </script>
  ";
}

if (FN_IsAdmin() || ($config['photogroup'] != "" && FN_UserInGroup($_FN['user'],$config['photogroup']))){
  /* Multi-file AJAX Uploader (http://valums.com/ajax-upload/) */
  echo "<link href=\"$pathscript/fileuploader.css\" rel=\"stylesheet\" type=\"text/css\">";
  echo "<script src=\"$pathscript/fileuploader.js\" type=\"text/javascript\"></script>";
  ?>
  <script type="text/javascript">        
	// in your app create uploader as soon as the DOM is ready		
	if(jQuery){	    $(document).ready(function(){	    	loadUploader(;)	    });      		}	else{
		checkdomready(loadUploader);
	}

	function loadUploader(){	      var uploader = new qq.FileUploader({	          element: document.getElementById('imageuploadform'),	          action: '<?php echo $_FN["siteurl"] ?>modules/gallery_mod/fileuploader.php',	          debug: true,	          onAllComplete: function(){ window.location.reload(true); }	      });               			}
	
	function checkdomready(func){
		func = func || function(){};
		if(typeof func != 'function'){ func = function(){} }
		var alreadyrunflag=0; //flag to indicate whether target function has already been run		
		if (document.addEventListener)
		  document.addEventListener("DOMContentLoaded", function(){alreadyrunflag=1; func();)}, false)
		else if (document.all && !window.opera){
		  document.write('<script type="text/javascript" id="contentloadtag" defer="defer" src="javascript:void(0)"><\/script>')
		  var contentloadtag=document.getElementById("contentloadtag")
		  contentloadtag.onreadystatechange=function(){
		    if (this.readyState=="complete"){ alreadyrunflag=1; func(); }
		  }
		}
		else if(/Safari/i.test(navigator.userAgent)){ //Test for Safari
			var _timer=setInterval(function(){
				if(/loaded|complete/.test(document.readyState)){ alreadyrunflag=1; clearInterval(_timer); func(); }
				}, 10);
		}
		window.onload=function(){
			setTimeout("if (!alreadyrunflag){func();}", 0)
		}		
	}  </script>
  <?php
}
?>
    