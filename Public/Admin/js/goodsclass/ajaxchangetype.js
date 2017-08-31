
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------
$(function() {
    var bFlag = false;//用来js点击事件反应完成后，才执行第二次点击的事件
    // 是否热卖单单品
    $(".hot-control").on('click', function () {
        var hot_sing = $(this);
        ajaxChangePic(hot_sing,"hot_single");
    });

    // 是否打印耗材推荐
    $(".printing-control").on('click', function () {
        var printing = $(this);
        ajaxChangePic(printing,"is_printing");
    });

    // 是否办公硬件推荐
    $(".hardware-control").on('click', function () {
        var hardware = $(this);
        ajaxChangePic(hardware,"is_hardware");
    });

    /**
     * 手动切换单击项是否推荐
     * @param _this 单击项
     * @param changType 数据库需要改变的字段
     */
    function ajaxChangePic(_this,changType){
        if(bFlag == true)return;
        bFlag = true;
        var id = _this.attr("data-id");
        //获取域名
        var common = $("#common").val();
        var data_flag = _this.attr("data-flag");
        var data = {id:id,data_flag:data_flag,type:changType};
        var url = AJAX_CHAGE_TYPE ;
        $.getJSON(url,data,function(json){
            if(json == "no"){
                _this.attr("src",common+"/img/cancel.png");
                _this.attr("data-flag","false");
                bFlag = false;
            }else {
                _this.attr("src", common+"/img/yes.png");
                _this.attr("data-flag", "true");
                bFlag = false;
            }
        });
    }

});


