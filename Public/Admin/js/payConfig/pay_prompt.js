/**
 * 
 */
var rule = {
	pay_account : {
		required : true,
		NumberAndEnglish : true,
	},

	mchid : {
		required : true,
		number   : true,
	},
	
	pay_key : {
		required : true,
		NumberAndEnglish : true,
		lengthPass       : true,
	},
	public_key : {
		required : true,
	},
	
	private_key :{
		required : true,
	}
};


var msg = {}

var i ;
for (i in prompt) {
	msg[i] = {required : prompt[i], number : '请输入数字'};
}

