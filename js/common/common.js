/**
 * Async update of element
 */
$("*[data-update]").click(function (e) {
   $("#ajaxLoader").show();
   content = $(this).attr("data-update");
   if ($(this).attr("data-content") != "")
      content = $(this).attr("data-content");
   data = $("form").first().serializeArray();
   data.push({"name":"xhrUpdate" ,"value": "1"});
   data.push({"name":"update" ,"value": $(this).attr("data-update")});
   data.push({"name":"content" ,"value": content});
   request = $.ajax({
      url: $("form").first().attr("action"),
      type: "POST",
      data: data,
      dataType: "json",
      success: function (data) {
          $.each(data, function (elementId, content) {
             $("#"+elementId).empty().append(content);
          });
          $("#ajaxLoader").hide();
      },
      error: function (jqXHR, textStatus,errorThrown) {
         $("#ajaxLoader").hide();
         console.error(textStatus+" "+errorThrown);
      }
   });
   e.preventDefault();
});
/**
 * Async form validation
 */
$("input:submit").click(function (e) {
   $("#ajaxLoader").show();
   $(".notValid").removeClass("notValid");
   $(".errorMessage").remove();
   status = false;
   form = $(this).parents("form").first();
   data = form.serializeArray();
   data.push({"name":"xhrValidate" ,"value": "1"});
   request = $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: data,
      async: false,
      dataType: "json",
      success: function (data) {
         
          objectData = Object.keys(data);
          if (objectData.length == 1 && objectData[0] == "validMessage") {
             message = data.validMessage;
             if (message == true)
                status = true;
             else {
               $(form).find("input:submit").after("<div class=\"validMessage\">"+data.validMessage+"</div>");
            }
          }
          else {
            $.each(data, function (elementId, content) {
               try {
                  el = $("#"+elementId);
               } catch (err) {}
               if (el.length > 0 ) {
                  el.addClass("notValid").focus().after("<div class=\"errorMessage\">"+content+"</div>");
               }
            });
          }
          $("#ajaxLoader").hide();
      },
      error: function (jqXHR, textStatus,errorThrown) {
         $("#ajaxLoader").hide();
         console.error(textStatus+" "+errorThrown);
      }
   });
   if (status == "false") e.preventDefault();
});
/**
 * Class blank redirect to blank page
 */
$("a.blank").attr('target','_blank');
/**
 * Gets datatable metadata
 * @param {type} el
 * @returns {Array|metadata}
 */
function getDatatableMetadata(el) {
   metadata= [];
   $(el).find("thead > tr > th").each(function(idEl,el){
      attributValues=new Object();
      $.each(el.attributes, function(idAtt,value){
         name = value.name;
         if(name.match(/^data/)) {
            name = name.replace('data-','');
            if (name == 'assorting')
               name = 'asSorting';
            else
               name = name.substr(0,1)+name.substr(1,1).toUpperCase()+name.substr(2);
            value = value.value;
            if (value == "false")
               value = false;
            attributValues[name]=value;
         }
      });
      attributValues["aTargets"]=[idEl];
      metadata.push(attributValues);
   });
   return metadata;
}
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');
