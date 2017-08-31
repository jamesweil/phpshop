
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
    //获取分页p值  初始化为1
    var current_page = 1;
    $("body").on('click', '.pagination  a', function() {
        current_page = $(this).attr('data-p')
    });

    //当前页导出
    $(".current-export").on('click',function(){
        //电话
        var tel = $("#input-mobile").val();
        //email
        var email_value = $("#input-email").val();
        var page = current_page;
        var data = {mobile:tel, email: email_value,p:page};
        var url = EXCEL_URL;
        var tj_value = JSON.stringify(data);
        submitForm(url,tj_value);
    });

    /**
     * js模拟表单get提交
     * @param action url地址
     * @param params 要传递的值
     */
    function submitForm(action, params) {
        var form = $("<form></form>");
        form.attr('action', action);
        form.attr('method', 'get');
        form.attr('target', '_self');
        var input1 = $("<input type='hidden' name='tj_value' value='' />");
        input1.attr('value', params);
        form.append(input1);
        form.appendTo("body");
        form.css('display', 'none');
        form.submit();
    }
});
