$("#searchButton").hide();
$("#searchContent").on("click","#paginationContainer a.pageSelector",function(e){
    $("#start").val($(this).data("page")).trigger("change",{dontReset:true});
    e.preventDefault();
});
$("#emtpytaxasearch").click(function(e){
     $("#taxasearch").val("");
     $("#taxasearchid").val("");
     $("#taxasearchid").trigger("change",{dontReset:true});
     e.preventDefault();
});
$( "#taxasearch" ).autocomplete({
      source: "?action=autocomplete",
      select: function( e, ui ) {
         $("#taxasearch").val(ui.item.label);
         $("#taxasearchid").val(ui.item.value);
         $("#taxasearchid").trigger("change",{dontReset:true});
         e.preventDefault();
      },
      change: function (e, ui) {
          if (!ui.item)
              $(this).val("");
      }
});
$("#searchForm").on("click","a.showNext",function (e) {
    $(this).next().toggle();
    e.preventDefault();
});
$("#searchForm").on("click","a.selectAll",function (e) {
    $(this).parent().find("div select option").attr("selected","selected");
    $(this).parent().find("div select").trigger("change",{dontReset:true});
    e.preventDefault();
});
$("#searchForm").on("change","input,select",function (e,data) {
    if (
            typeof data !== 'object' ||
            !data.hasOwnProperty("dontReset") || 
            data.dontReset !== true
            ) {
        $("#start").val(0);
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