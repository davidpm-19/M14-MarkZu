
var viewrotationenabled=false;var currentviewnum=0;var currentviewid="";var viewrotationspeed=10;var currentviewtitle="";var currentviewurl="";$(document).ready(function(){$('.manage-views').click(function(){pause_view_rotation();});$("#addtomyviews a").click(function(){show_throbber();var theurl=$('#maincontentframe').contents()[0].URL;hide_throbber();var content="<div id='popup_header'><b>"+_("Add View")+"</b></div><div id='popup_data'><p style='margin-top: 10px;'>"+_('Use this to add what you see on the screen to your views page.')+"</p></div><form id='addview_form' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='addview'><input type='hidden' name='url' value='"+theurl+"'><label for='addviewTitleBox'>"+_('View Title')+"</label><div class='popup-form-box'><input type='text'  name='title' id='addviewTitleBox' value='' style='width: 100%;' class='form-control'></div><div id='addviewFormButtons'><button type='submit' class='btn btn-sm btn-primary' name='submitButton' id='submitAddViewButton'>"+_('Submit')+"</button></div></form>";hide_throbber();set_popup_content(content);display_popup();$("#addview_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#popup_container").each(function(){this.origHTML=this.innerHTML;this.innerHTML="<div id='popup_header'><b>"+_('Please Wait')+"</b></div><div id='popup_data'><p>"+_('Processing')+"...</p><i class='fa fa-spinner fa-pulse'></i></div>";});},success:function(msg){$("#popup_container").each(function(){$(this).html("<div id='popup_header'><b>"+_('View Added')+"</b></div><div id='popup_data'><p>"+_('Success! Your view was added to your views page.')+"</p></div>");fade_popup("green");});},error:function(msg){$("#popup_container").each(function(){$(this).html("<div id='popup_header'><b>"+_('Error')+"</b></div><div id='popup_data'><p>"+_('An error occurred processing your request.')+"</p></div>");});}});return false;});});$("#myviewsquickview").click(function(){start_view_rotation(0);return;});$("a.rotatemyviewslink").click(function(){if(viewrotationenabled==true){stop_view_rotation();}else{var x=currentviewnum;var y=currentviewid;start_view_rotation(currentviewnum+1);}
return;});$("#myviewsinit").each(function(){var x=1;});$(".remove-view").click(function(){var viewid=$(this).parent().data('id');var title=$(this).parent().data('title');$(this).blur();if(!confirm(_("You are about to delete the view")+":\n"+title)){return false;}
get_ajax_data("deleteviewbyid",viewid);update_views_menu();var content="<div id='popup_header' style='padding-bottom: 5px;'><b>"+_('View Deleted')+"</b></div><div id='popup_data'><p>"+_('View has been removed.')+"</p></div>";$(this).parents('tr').remove();set_child_popup_content(content);display_child_popup();fade_child_popup("red");});$("#myviewspeedslider").each(function(){var result=get_ajax_data("getusermeta","view_rotation_speed");if(result!=""){viewrotationspeed=parseInt(result);}
if(viewrotationspeed<10){viewrotationspeed=10;}
var rotation_enabled=get_ajax_data("getusermeta","view_rotation_enabled");if(rotation_enabled!=""&&rotation_enabled==1){start_view_rotation(0);}else{show_new_view(0);}
$(this).slider({value:viewrotationspeed,step:5,min:10,max:60,slide:function(event,ui){},change:function(event,ui){show_throbber();viewrotationspeed=ui.value;pause_view_rotation();resume_view_rotation(currentviewnum+1);hide_throbber();var optsarr={"keyname":"view_rotation_speed","keyvalue":viewrotationspeed,"autoload":false};var opts=JSON.stringify(optsarr);var result=get_ajax_data("setusermeta",opts);}});});$(".btn-view-pr").click(function(){show_throbber();pauseresume_view_rotation();hide_throbber();});$("#myviewoverlay").click(function(){if(viewrotationenabled==true){pause_view_rotation();}});$(".edit-view").click(function(){var id=$(this).parent().data('id');whiteout();show_throbber();var view=JSON.parse(get_ajax_data("getviewbyid",id));var theurl=view.url;var thetitle=view.title;var content="<div id='popup_header' style='margin-bottom: 10px;'><b>"+_('Edit View')+"</b></div><div id='popup_data'></div><form id='editview_form' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='updateview'><input type='hidden' name='id' value='"+id+"'><div class='popup-form-box'><div><label for='addviewTitleBox'>"+_('View Title')+"</label></div><input type='text' size='30' name='title' id='addviewTitleBox' value='"+thetitle+"' class='form-control'></div><div class='popup-form-box'><div><label for='addviewURLBox'>"+_('URL')+"</label></div><input type='text' size='30' name='url' id='addviewURLBox' class='form-control' value='"+theurl+"'></div><div id='addviewFormButtons'><input type='submit' class='btn btn-sm btn-primary' name='submitButton' value='"+_('Submit')+"' id='submitAddViewButton'></div></form>";hide_throbber();set_child_popup_content(content);display_child_popup();$("#editview_form").submit(function(){var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#child_popup_container").each(function(){this.origHTML=this.innerHTML;this.innerHTML="<div id='popup_header' style='padding-bottom: 5px;'><b>"+_('Please Wait')+"</b></div><div id='popup_data'><p>"+_('Processing')+"...</p><i class='fa fa-spinner fa-pulse'></i></div>";resize_child_popup();});},success:function(msg){clear_whiteout();$("#child_popup_container").each(function(){$(this).html("<div id='popup_header' style='padding-bottom: 5px;'><b>"+_('View Changes Saved')+"</b></div><div id='popup_data'><p>"+_('Success! Your view was updated successfully.')+"</p></div>");resize_child_popup();fade_child_popup("green");update_views_menu();location.reload();});},error:function(msg){$("#child_popup_container").each(function(){this.innerHTML="<div id='popup_header' style='padding-bottom: 5px;'><b>"+_('Error')+"</b></div><div id='popup_data'><p>"+_('An error occurred processing your request.')+"</p></div>";});}});return false;});});$("a.addnewviewlink").click(function(){if($("#popup_layer").css("visibility")!=="hidden"){return;}
show_throbber();var inrotation=false;if(viewrotationenabled==true){inrotation=true;}
var theurl="";var content="<div id='popup_header' style='margin-bottom: 10px;'><b>"+_('Add View')+"</b></div><div id='popup_data'></div><form id='addview_form' style='margin: 0;' method='get' action='"+ajax_helper_url+"'><input type='hidden' name='cmd' value='addview'><div class='popup-form-box'><label for='addviewTitleBox'>"+_('View Title')+"</label><input type='text' size='30' name='title' id='addviewTitleBox' value='' class='form-control'></div><div class='popup-form-box'><label for='addviewURLBox'>"+_('URL')+"</label><input type='text' size='30' name='url' id='addviewURLBox' class='form-control' value='"+theurl+"'><span class='subtext'>Include the protocol when specifying a URL.</span><br><span class='subtext'>Example: <b>https://</b>www.nagios.com</span></div><div id='addviewFormButtons'><input type='submit' class='btn btn-sm btn-primary' name='submitButton' value='"+_('Submit')+"' id='submitAddViewButton'></div></form>";hide_throbber();set_popup_content(content);center_popup();display_popup();$("#addview_form").submit(function(){hide_throbber();var params={};$(this).find(":input, :password, :checkbox, :radio, :submit, :reset").each(function(){params[this.name||this.id||this.parentNode.name||this.parentNode.id]=this.value;});params["nsp"]=nsp_str;$.ajax({type:"POST",url:this.getAttribute("action"),data:params,beforeSend:function(XMLHttpRequest){$("#popup_container").each(function(){this.origHTML=this.innerHTML;this.innerHTML="<div id='popup_header' style='margin-bottom: 5px;'><b>"+_('Please Wait')+"</b></div><div id='popup_data'><p>"+_('Processing')+"...</p><i class='fa fa-spinner fa-pulse'></i></div>";resize_popup();});},success:function(msg){$("#popup_container").each(function(){$(this).html("<div id='popup_header' style='margin-bottom: 5px;'><b>"+_('View Added')+"</b></div><div id='popup_data'><p>"+_('Success! Your view was added to your views page.')+"</p></div>");resize_popup();fade_popup("green");});},error:function(msg){$("#popup_container").each(function(){$(this).html("<div id='popup_header' style='margin-bottom: 5px;'><b>"+_('Error')+"</b></div><div id='popup_data'><p>"+_('An error occurred processing your request.')+"</p></div>");});}});if(inrotation==true){pause_view_rotation();resume_view_rotation(currentviewnum);}else{show_new_view(currentviewnum);}
return false;});});$("#leftnav").on('click','a.mymenulink',function(){stop_view_rotation();var theparent=this.parentNode;var url=this.href;var title=this.innerHTML;var rawid=this.id;var id=rawid.substr(5);var num=parseInt(get_ajax_data("getviewnumfromid",id));currentviewnum=num;fill_view_screen(title,url,id);return;});});function highlight_views_menu_item(id)
{$("#"+id).addClass("activemenulink");}
function update_views_menu()
{var html=get_ajax_data("getviewsmenuhtml","");if(inframe){$('ul#myviewsmenu',parent.document).html(html);}else{$("ul#myviewsmenu").html(html);}}
function fill_view_screen(title,url,id)
{update_views_menu();highlight_views_menu_item("view-"+id);$("#trashview").css("visibility","visible");$("#editview").css("visibility","visible");var thetarget=$("#myviewsviewtitle");$("#myviewsviewtitle")[0].innerHTML=title;$("#maincontentframe").attr({src:url});currentviewid=id;currentviewurl=url;currentviewtitle=title;}
function show_new_view(num)
{var viewdata=get_ajax_data("getviewbynum",num);eval('var viewobj='+viewdata);fill_view_screen(viewobj.title,viewobj.url,viewobj.id);currentviewnum=num;}
function stop_view_rotation()
{$(this).stopTime("viewrotationtimer");$('.viewspeed-slider-box').hide();viewrotationenabled=false;$("a.rotatemyviewslink")[0].innerHTML="<span><i class='fa fa-play'></i></span>"+_("Start Rotation");current_time=0;clearInterval(current_timer_id);current_time=viewrotationspeed;$('#current-time').html('');$('.rotation-stopped').show();$('.btn-view-pr').html('<i class="fa fa-stop"></i>');$('.btn-view-pr').prop('disabled',true);var optsarr={"keyname":"view_rotation_enabled","keyvalue":0,"autoload":false};var opts=JSON.stringify(optsarr);var result=get_ajax_data("setusermeta",opts);}
function start_view_rotation(num)
{start_timer_to_next_rotation();show_new_view(num);$(this).stopTime("viewrotationtimer");$(this).everyTime(viewrotationspeed*1000,"viewrotationtimer",function(i){currentviewnum+=1;show_new_view(currentviewnum);start_timer_to_next_rotation();});$('.rotation-stopped').hide();$('.btn-view-pr').attr('data-original-title',_('Pause rotation'));$('.btn-view-pr').html('<i class="fa fa-pause"></i>');$('.btn-view-pr').prop('disabled',false);$(".viewspeed-slider-box").show();viewrotationenabled=true;$("a.rotatemyviewslink")[0].innerHTML="<span><i class='fa fa-stop'></i></span>"+_("Stop Rotation");var optsarr={"keyname":"view_rotation_enabled","keyvalue":1,"autoload":false};var opts=JSON.stringify(optsarr);var result=get_ajax_data("setusermeta",opts);}
function pause_view_rotation()
{$(this).stopTime("viewrotationtimer");$('.btn-view-pr').attr('data-original-title',_('Resume rotation'));$('.btn-view-pr').html('<i class="fa fa-play"></i>');viewrotationenabled=false;clearInterval(current_timer_id);current_time=viewrotationspeed;}
function resume_view_rotation(newviewnum)
{start_view_rotation(newviewnum);$('.btn-view-pr').attr('data-original-title',_('Pause rotation'));$('.btn-view-pr').html('<i class="fa fa-pause"></i>');viewrotationenabled=true;}
function pauseresume_view_rotation()
{if(viewrotationenabled==false){resume_view_rotation(currentviewnum+1);}else{pause_view_rotation();}}
var current_timer_id=0;var current_time=0;function start_timer_to_next_rotation()
{if(current_timer_id!=0){clearInterval(current_timer_id);current_time=viewrotationspeed;}
if(current_time==0){current_time=viewrotationspeed;}
$('#current-time').html(current_time);current_timer_id=setInterval(run_current_timer,1000);}
function run_current_timer()
{current_time--;$('#current-time').html(current_time);}