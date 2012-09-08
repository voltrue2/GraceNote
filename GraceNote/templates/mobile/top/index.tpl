<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<html>
<head>
{if $QUERIES.TITLE}
<title>{$QUERIES.TITLE}</title>
{/if}
<link rel="stylesheet" type="text/css" href="/css/base.css{mtime path=CSS|base.css}" />
{if $AUTO_CSS}
<link rel="stylesheet" type="text/css" href="/css/{$AUTO_CSS}" />
{/if}
<script type="text/javascript" src="/js/Basic.js{mtime path=JS|Basic.js}"></script>
<script type="text/javascript" src="/js/PhotoGallery.src.js{mtime path=JS|PhotoGallery.src.js}"></script>
</head>
<body>
Mobile template<br />
Device : {$DEVICE}<br />
User Agent : {$USER_AGENT}<br />
{if $QUERIES}
QUERIES : <br />
{foreach from=$QUERIES key=i item=val}
{$i} = {$val}<br />
{/foreach}
{/if}
<br />
Top Page Contents go <strong><u>HERE</u></strong>
<br />
<br />
<form  enctype="multipart/form-data" method="POST">
	<input type="hidden" name="UPLOAD" value="true" />
	CSV Upload: <input type="file" name="CSV" value="" />
	<input type="submit" value="Upload" />
</form>
<form method="POST">
	Page Title: <input type="text" name="TITLE" value="" />
	<input type="submit" value="Submit" />
</form>
<br />
{literal}
<script type="text/javascript">
var p;
var list = new Array();
list[0] = new Array();
list[1] = new Array();
list[2] = new Array();
list[3] = new Array();
list[4] = new Array();
list[5] = new Array();
list[6] = new Array();
list[7] = new Array();
list[8] = new Array();
list[0].image = 'http://www.localwin.com/julie/system/files/lu10/french_cuisine.jpg';
list[0].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[0].thumb = 'http://img.foodnetwork.com/FOOD/2007/09/21/thanks_goodeatsroastturkey_lead.jpg';
list[1].image = 'http://dianasneighborhood.files.wordpress.com/2009/07/picnic-in-the-park-nanda.jpg?w=500&h=375';
list[1].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[1].thumb = 'http://dianasneighborhood.files.wordpress.com/2009/07/picnic-in-the-park-nanda.jpg?w=500&h=375';
list[2].image = 'http://www.dubrovnik-apartments.com/weblog/images/cuisine.JPG';
list[2].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[2].thumb = 'http://img.foodnetwork.com/FOOD/2008/12/23/FNmag_Slow-Cooker-Pork-Ta_s4x3_lead.jpg';
list[3].image = 'http://www.reallygoodfood.org/wp-content/uploads/2009/10/Nougatine-Jean-Georges-@-NYC-0142-500x357.jpg';
list[3].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[3].thumb = 'http://www.reallygoodfood.org/wp-content/uploads/2009/10/Nougatine-Jean-Georges-@-NYC-0142-500x357.jpg';
list[4].image = 'http://www.friedchillies.com/images/gallery/MF1.jpg';
list[4].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[4].thumb = 'http://www.friedchillies.com/images/gallery/MF1.jpg';
list[5].image = 'http://www.friedchillies.com/images/gallery/5/L_SweetSourCrabs(C).jpg';
list[5].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[5].thumb = 'http://www.friedchillies.com/images/gallery/5/L_SweetSourCrabs(C).jpg';
list[6].image = 'http://config.terracedowns.co.nz/Shared/Images/PageContent/Terrace%20Downs%20Signature%20High%20Country%20Cuisine.jpg';
list[6].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[6].thumb = 'http://gomiamicard.com/blog/files/2008/02/miami-food-network-party.jpg';
list[7].image = 'http://img.foodnetwork.com/FOOD/2010/04/13/GC_good-eats-meatloaf_s4x3_lg.jpg';
list[7].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[7].thumb = 'http://img.foodnetwork.com/FOOD/2010/04/13/GC_good-eats-meatloaf_s4x3_lg.jpg';
list[8].image = 'http://chicagoist.com/attachments/chicagoist_laura/2007_8_mexican.jpg';
list[8].caption = '{/literal}{if $QUERIES.TITLE}<strong>{$QUERIES.TITLE}</strong>{/if}{literal}';
list[8].thumb = 'http://chicagoist.com/attachments/chicagoist_laura/2007_8_mexican.jpg';
function init(obj){
	var params = {
		auto_loop:true,
		loop_speed:10,
		preloader:'/img/preloaders/preloader.gif', 
		list_length:list.length, 
		//outline_color:'http://images2.layoutsparks.com/1/186845/white-christmas-snow-falling.gif',
		outline_color:'#666666', 
		gap:1,
		//canvas_color:'http://images2.layoutsparks.com/1/114740/kiss-rain-falling-animated.gif', 
		//canvas_color:'http://images2.layoutsparks.com/1/98631/pretty-rose-petals-falling.gif',
		canvas_color:'#EFEFEF',
		thumb_color:'#FFFFFF', 
		width:500, 
		height:450, 
		display_height:409, 
		x:400, 
		y:220,
		position:'absolute',
		caption_x:2,
		caption_y:384,
		caption_font:'Verdana'
	};
	p = new PhotoGallery(obj, list, params);
};
$('#photo_gallery').ready(init);
</script>
{/literal}

<div id="photo_gallery"></div>

</body>
</html>