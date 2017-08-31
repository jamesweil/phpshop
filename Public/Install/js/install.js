/**
 * 
 */
 function gonext(url){
    window.location.href=url;
}
(function (w){
    $.ajaxSetup ({ cache: false });
    this.reloads = function(n, data, url) {
        var installURL =  url +"/n/"+n+'.html';
        $.ajax({
            type: "POST",		
            url: installURL,
            data: data,
            dataType: 'json',
            beforeSend:function(){
            },
            success: function(msg){
                if(msg.n=='999999'){
                    $('#dosubmit').attr("disabled",false);
                    $('#dosubmit').removeAttr("disabled");				
                    $('#dosubmit').removeClass("nonext");
                    setTimeout(function (){
                    	gonext(WHAT_URD);
                    },2000);
                }
                if(msg.n>=0){
                    $('#loginner').append(msg.msg);	
                    var n = msg.n;
                    InstallDb.reloads(n, data, url);
                }else{
                    alert(msg.msg);
                }			 
            },
            error : function(msg)
            {
                console.log(msg);
            }
        });
    }
    w.InstallDb = this;
})(window);

InstallDb.reloads(-1, data, INSTALL_DB);
