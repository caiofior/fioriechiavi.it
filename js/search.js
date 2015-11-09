$("#searchButton").hide();
$("#searchContent").on("click","#paginationContainer a.pageSelector",function(e){
    $("#start").val($(this).data("page")).trigger("change",{dontReset:true});
    e.preventDefault();
});
$("#emtpytaxasearch").click(function(e){
     $("#taxasearch").val("");
     $("#taxasearchid").val("");
     $("#text").trigger("change",{dontReset:true});
     e.preventDefault();
});
$( "#taxasearch" ).autocomplete({
      source: "?action=autocomplete",
      select: function( e, ui ) {
         $("#taxasearch").val(ui.item.label);
         $("#taxasearchid").val(ui.item.value);
         $("#taxasearch").trigger("change",{dontReset:true});
         e.preventDefault();
      },
      change: function (e, ui) {
          if (!ui.item)
              $(this).val("");
      }
});
$("#searchForm").on("click","a.showNext",function (e) {
    var el = $(this).next();
    el.toggle();
    if(!el.is(":visible")) {
        el.find("input[type=hidden]").val(1);
        el.find("option").attr("selected","selected");
        el.find("div select").trigger("change",{dontReset:true});
    }
    e.preventDefault();
});
$("#searchForm").on("click","a.selectAll",function (e) {
    $(this).parent().find("div input[type=hidden]").val(1);
    $(this).parent().find("div select option").attr("selected","selected");
    $(this).parent().find("div select").trigger("change",{dontReset:true});
    e.preventDefault();
});
$("#searchForm").on("change","input[type!=hidden],select",function (e,data) {
    if (
            typeof data !== 'object' ||
            !data.hasOwnProperty("dontReset") || 
            data.dontReset !== true
            ) {
        $("#start").val(0);
    }
    if($(this).prop("tagName") == "SELECT") {
        var val=1;
        if ($(this).children("option").size() != $(this).children("option:selected").size()) {
            val=0;
        }
        $(this).siblings("input[type=hidden]").val(val);
    }
    $.ajax({
        url: $(this).parents("form").first().attr("action")+"?action=search",
        data: $(this).parents("form").first().serializeArray(),
        method:"post",
        async : false,
        success : function (data) {
            $.each(data,function(name,value) {
                $("#"+name).html(value);    
            });
        }
    });  
});