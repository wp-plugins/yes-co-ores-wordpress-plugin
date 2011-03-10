jQuery(document).ready( function($) {
	
//	//$('body').html('leeg!');
	
});

function testMCP()
{
	jQuery('#yog_test_connection').nextAll('img').show();
	jQuery('#yog_test_connection').nextAll('span').html('Verbinding maken met MCP kanaal');
	
	jQuery.post("/wp-admin/admin-ajax.php", {url:jQuery('[name=yog_mcp_url]').val(),user:jQuery('[name=yog_user]').val(),password:jQuery('[name=yog_password]').val(),action:"testmcp", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			jQuery('#yog_test_connection').nextAll('img').hide();
			jQuery('#yog_test_connection').nextAll('span').html(msg);
		});
}

function removeUUID()
{
	jQuery('#yog_remove_uuid').nextAll('img').show();
	jQuery('#yog_remove_uuid').nextAll('span').html('Bezig met verwijderen');
	
	jQuery.post("/wp-admin/admin-ajax.php", {action:"removeuuid", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			jQuery('[name=yog_uuid]').val('');
			jQuery('#yog_remove_uuid').nextAll('img').hide();
			jQuery('#yog_remove_uuid').nextAll('span').html(msg);
			jQuery('#koppelingsstatus').html('Niet actief');
		});
}

function forceSync()
{
	jQuery('#yog_force_sync').nextAll('img').show();
	jQuery('#yog_force_sync').nextAll('span').html('Bezig met synchroniseren');
	
	jQuery.post("/wp-admin/admin-ajax.php", {action:"forcesync", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			jQuery('#yog_force_sync').nextAll('img').hide();
			jQuery('#yog_force_sync').nextAll('span').html(msg);
		});
}

function addKoppeling(secret)
{
	jQuery('#yog_add_koppeling').hide();
	jQuery('#yog_add_koppeling').nextAll('img').show();
	jQuery.post("/wp-admin/admin-ajax.php", {activatiecode:secret, action:"addkoppeling", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			//alert(msg);
			jQuery('#laatstekoppeling').before(msg);
			jQuery('#yog_add_koppeling').nextAll('img').hide();
			jQuery('#yog_add_koppeling').show();
		});
}
function removeVideo(uuid,postID,element)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"removevideo", id:uuid, postid:postID, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			element.fadeOut();
		});
}
function addVideo(ipostID,ititel,itype,iurl)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"addvideo", postid:ipostID, titel:ititel, type:itype, url:iurl, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			jQuery('#laatste_video').before('<tr><td>'+ititel+'</td><td><a href="http://'+iurl+'">'+iurl+'</a></td><td><input type="button" class="button-primary" onclick="removeVideo(\''+msg+'\',\''+ipostID+'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td></tr>');
			jQuery('#video_titel').val('');
			jQuery('#video_url').val('');
		});
}
function removeLink(uuid,postID,element)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"removelink", id:uuid, postid:postID, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			element.fadeOut();
		});
}
function addLink(ipostID,ititel,itype,iurl)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"addlink", postid:ipostID, titel:ititel, type:itype, url:iurl, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			jQuery('#link_tabel').after('<tr><td>'+itype+'</td><td>'+ititel+'</td><td><a href="http://'+iurl+'">'+iurl+'</a></td><td><input type="button" class="button-primary" onclick="removeLink(\''+msg+'\',\''+ipostID+'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td></tr>');
			jQuery('#link_titel').val('');
			jQuery('#link_type').val('');
			jQuery('#link_url').val('');
		});
}
function removeDocument(uuid,postID,element)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"removedocument", id:uuid, postid:postID, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			element.fadeOut();
		});
}
function addDocument(ipostID,ititel,itype,iurl)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"adddocument", postid:ipostID, titel:ititel, type:itype, url:iurl, 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			alert(msg);
			jQuery('#laatste_document').before('<tr><td>'+itype+'</td><td>'+ititel+'</td><td><a href="http://'+iurl+'">'+iurl+'</a></td><td><input type="button" class="button-primary" onclick="removeDocument(\''+msg+'\',\''+ipostID+'\', jQuery(this).parent().parent() );" value="Verwijderen" style="margin-left: 5px;"></td></tr>');
			jQuery('#document_titel').val('');
			jQuery('#document_type').val('');
			jQuery('#document_url').val('');
		});
}

function toggleHome(element)
{
	jQuery.post("/wp-admin/admin-ajax.php", {action:"togglehome", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			element.next().html(msg);
		});
}

function verwijderKoppeling(secret,element)
{
	jQuery.post("/wp-admin/admin-ajax.php", {activatiecode:secret, action:"removekoppeling", 'cookie': encodeURIComponent(document.cookie)},
		function(msg)
		{
			element.parent().parent().parent().slideUp(function(){ element.parent().parent().parent().remove(); });
		});	
}



