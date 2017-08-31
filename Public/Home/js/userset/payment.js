$(".province").bind("change",function(){
    var id = $(this).val();
    $.ajax({
        url : 'region_ajax',
        dataType : "json",
        type : 'post',
        data : {id:id},
        success:function(data){
	        $('.city option').remove(); 
	        for (var i = 0; i < data.length; i++) {                                                                                                                                   
	            $('.city').append('<option value ="'+data[i].id+'">'+data[i].name+'</option>');
	        };       
	        $(".city").trigger("change");            
        },
        error:function(){
            return false;
        },
    });
})
$(".city").bind("change",function(){
    var id = $(this).val();
    $.ajax({
        url : 'region_ajax',
        dataType : "json",
        type : 'post',
        data : {id:id},
        success:function(data){
	        $('.area option').remove(); 
	        for (var i = 0; i < data.length; i++) {                                                                                                                                   
	            $('.area').append('<option value ="'+data[i].id+'">'+data[i].name+'</option>');
	        };                
        },
        error:function(){
            return false;
        },
    });
})

function check(){
	var company_name = $('#company_name').val();
	var province = $('#province').val();
	var city = $('#city').val();
	var area = $('#area').val();
	var address = $('#address').val();
	var apply_name = $('#apply_name').val();
	var applytel = $('#applytel').val();
	var respon_name = $('#respon_name').val();
	var respontel = $('#respontel').val();
	var estimate = $('input[name=estimate]:checked').val();
	var remarks = $('#remarks').val();
	if (company_name == '') {
		layer.tips('请填写公司名称!','#company_name');
		return false;
	};
	if (province == '') {
		layer.tips('请选择地区!','#province');
		return false;
	};
	if (city == '') {
		layer.tips('请选择地区!','#city');
		return false;
	};
	if (area == '') {
		layer.tips('请选择地区!','#area');
		return false;
	};
	if (address == '') {
		layer.tips('请填写详细地址!','#address');
		return false;
	};
	if (apply_name == '') {
		layer.tips('请填写申请人!','#apply_name');
		return false;
	};
	if (applytel == '') {
		layer.tips('请填写申请人联系电话!','#applytel');
		return false;
	};
	if(!/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/.test(applytel)){
        layer.tips('请输入正确的手机号', '#applytel');
       return false;
    };
	if (respon_name == '') {
		layer.tips('请填写对账人!','#respon_name');
		return false;
	};
	if (respontel == '') {
		layer.tips('请填写对账人联系电话!','#respontel');
		return false;
	};
	if(!/^1[34578]\d{9}$/.test(respontel)){
        layer.tips('请输入正确的手机号', '#respontel');
       return false;
    };
	if (estimate == undefined) {
		layer.tips('请选择每月采购金额!','#estimate',{tips:3});
		return false;
	};
	if (remarks == '') {
		layer.tips('请填写备注!','#remarks');
		return false;
	};
    return true;
}