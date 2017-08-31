jQuery(function($) {
var timer;
function button1_click(event)
{
     $(".slide").css("visibility","hidden");
     $("#image1").css("visibility","visible");
     $("#image1").css("opacity","0");
    $("#image1").animate({"opacity":1},500, "linear", null);
     $("ul.buttons li").removeClass("active");
    $("#image1").animate({"opacity":1},500, "linear", null);
     $("#button1").addClass("active");
     clearTimeout(timer);
     timer = setTimeout(eval("button2_click"),"5000");
    $("#image1").animate({"opacity":1},500, "linear", null);
}

function button2_click(event)
{
     $(".slide").css("visibility","hidden");
     $("#image2").css("visibility","visible");
     $("#image2").css("opacity","0");
    $("#image2").animate({"opacity":1},500, "linear", null);
     $("ul.buttons li").removeClass("active");
    $("#image2").animate({"opacity":1},500, "linear", null);
     $("#button2").addClass("active");
     clearTimeout(timer);
     timer = setTimeout(eval("button3_click"),"5000");
    $("#image2").animate({"opacity":1},500, "linear", null);
}

function button3_click(event)
{
     $(".slide").css("visibility","hidden");
     $("#image3").css("visibility","visible");
     $("#image3").css("opacity","0");
    $("#image3").animate({"opacity":1},500, "linear", null);
     $("ul.buttons li").removeClass("active");
    $("#image3").animate({"opacity":1},500, "linear", null);
     $("#button3").addClass("active");
     clearTimeout(timer);
     timer = setTimeout(eval("button1_click"),"5000");
    $("#image3").animate({"opacity":1},500, "linear", null);
}

function OnLoad(event)
{
     clearTimeout(timer);
     timer = setTimeout(eval("button2_click"),"5000");
}

$('#button1').bind('mouseover', button1_click);

$('#button2').bind('mouseover', button2_click);

$('#button3').bind('mouseover', button3_click);

OnLoad();

});


