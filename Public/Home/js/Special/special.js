//验证申请加盟
function check(){
    var applicant = $('#applicant').val();
    var tel = $('#tel').val();
    var age = $('#age').val();
    var email = $('#email').val();
    var province = $('#province').val();
    var city = $('#city').val();
    var area = $('#area').val();
    var address = $('#address').val();
    var fax = $('#fax').val();
    var qq = $('#qq').val();
    var remark = $('#remark').val();
    if(applicant == ''){
    layer.tips('请填写申请人!', '#applicant');
    return false;
    }
    if(tel == ''){
    layer.tips('请填写联系手机号!', '#tel');
    return false;
    }
    if(!/^1[34578]\d{9}$/.test(tel)){
  		layer.tips('请输入正确的手机号!', '#tel');
		return false;
	}
    if(age == ''){
        layer.tips('请填写年龄!', '#age');
        return false;
    }
    if(email == ''){
	    layer.tips('请填写联系邮箱!', '#email');
	    return false;
    }
    if(!/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/.test(email)){
        layer.tips('请输入正确的邮箱!', '#email');
        return false;
    }
    if(province == "请选择省份"){
        return false;
    }
    if(city == '请选择城市'){
        return false;
    }
    if(area == '请选择地区'){
        return false;
    }
    if(address == ''){
        layer.tips('请填写详细地址!', '#address');
        return false;
    }
    if(fax == ''){
        layer.tips('请填写传真!', '#fax');
        return false;
    }
    if(qq == ''){
        layer.tips('请填写QQ号码!', '#qq');
        return false;
    }
    if(remark == ''){
        layer.tips('请填写备注说明!', '#remark');
        return false;
    }
    return true;
}

// 配送方式选择

