//Load  
function Load() {
    $("<div class=\"datagrid-mask\"></div>").css({ display: "block", width: "100%", height: $(window).height() }).appendTo("body");
    $("<div class=\"datagrid-mask-msg\" style=\"font-size:12px;\"></div>").html("正在执行，请稍候...").appendTo("body").css({ display: "block", left: ($(document.body).outerWidth(true) - 190) / 2, top: ($(window).height() - 45) / 2 });
}

//hidden Load  
function dispalyLoad() {
    $(".datagrid-mask").remove();
    $(".datagrid-mask-msg").remove();
}