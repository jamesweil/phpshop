$(function() {
    $.fn.extend({
        //点击选项卡   abtnClass 操作按钮class  conClass 内容添加class
        clickTab: function (aBtnClass, conClass) {
            //默认值
            var json = {};
            json.aBtnClass = aBtnClass || 'active';
            json.conClass = conClass || 'active';
            $(this).each(function (index, obj) {
                var aBtn = $(this).find('.tab-pane li');
                var aCon = $(this).find('.tab-content-wrap .con-main');
                aBtn.eq(0).addClass(json.aBtnClass);
                aCon.eq(0).addClass(json.conClass);
                aBtn.on('click', function () {
                    aBtn.removeClass(json.aBtnClass).eq($(this).index()).addClass(json.aBtnClass);
                    aCon.removeClass(json.conClass).eq($(this).index()).addClass(json.conClass);
                });
            });
        }
    });
    $('.click-tab-mrap').clickTab('active','active');
})