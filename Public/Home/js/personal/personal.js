// $(function(){
//     $('#expand').click(function(){
//     	alert(11);
//         // $.ajax({
//         //      type: "POST",
//         //      url: "{:U('Personal/order_myorder')}",
//         //      data: {limit:20},
//         //      dataType: "text",
//         //      success: function(data){
//         //      	alert(2);
//         //       //    $('#resText').empty();   //清空resText里面的所有内容
//         //       //    var html = ''; 
//         //       //    $.each(data, function(commentIndex, comment){
//         //       //          html += '<div class="comment"><h6>' + comment['username']
//         //       //                    + ':</h6><p class="para"' + comment['content']
//         //       //                    + '</p></div>';
//         //       //    });
//         //       //    $('#resText').html(html);
//         //       // }
//         // });
//     });
// });
function expand(){
	$.ajax({
        type: "POST",
        url: 'myorder_ajax',
        data: {limit:20},
        dataType: "json",
        success: function(data){
        	if (data == 0) {
                alert('查询失败');
        	}else{
            	$('.more').empty();   //清空resText里面的所有内容
	            var html = ''; 
	            for(var i in data){
			        var user_name=data[i].user_name;
			        var images = data[i].images;
			        var create_time = data[i].create_time;
			        var order_status = data[i].order_status;
			        var id = data[i].id;
			        var price_sum = data[i].price_sum;

			        html='<div class="imgParent fl">'+sname+'</td><td>'+snum+'</td></tr>';
			        
			    }
	            $('.more').html(html);
        	}
        }
    });
// }