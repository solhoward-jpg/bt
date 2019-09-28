<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>Bituex Pay 支付案例</title>
		<link href="https://cdn.bootcss.com/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">
			<div class="card center-block mt-6">
				<div class="card-body">
					<h5 class="card-title">Bituex Pay 支付案例</h5>
					<h6 class="card-subtitle mb-6 text-danger">资金将进入商户账户。正式使用请在服务器端使用私钥进行签名。</h6>
					<form class="">
					  <div class="form-group col-sm-6 mb-6">
						<label for="mid">商户号</label>
						<input type="text" class="form-control" id="mid" value="M00000088" placeholder="必填，通过商户后台获得">
					  </div>
					  <div class="form-group col-sm-6 mb-2">
						<label for="access_key_secret">私钥</label>
						<input type="text" class="form-control" id="access_key_secret" value="0e060d3d150e42ff939569bb5d348f26" placeholder="必填，通过商户后台获得">
					  </div>
					  <div class="form-group col-sm-6 mb-2">
						<label for="subject">商品名称</label>
						<input type="text" class="form-control" id="subject" value="购买100积分" placeholder="必填">
					  </div>
					  <div class="form-group col-sm-6 mb-6">
						<label for="body">商品描述</label>
						<input type="text" class="form-control" id="body" value="123" placeholder="选填">
					  </div>
					  <div class="form-group col-sm-6 mb-6">
						<label for="out_trade_no">订单号</label>
						<input type="text" class="form-control" id="out_trade_no" value="201905150129093384743" placeholder="必填，同商户需唯一">
						</div>
					  <div class="form-group col-sm-6 mb-6">
						<label for="quantity">支付数量（目前只支持USDT、CNY）</label>
						<div class="input-group">
							<input type="text" class="form-control" id="quantity" value="50000" placeholder="必填">
							<select class="form-control" id="currency_type" style="width:80px;">
								
									<option value="CNY">CNY</option>
							</select>
							</div>
					  </div>
					  <div class="form-group col-sm-6 mb-6">
						<label for="coin">商户收款币种（目前只支持USDT）</label>
						<input type="text" class="form-control" id="coin" value="USDT" readonly="readonly" placeholder="必填，目前只支持USDT">
					  </div>
					  <div class="form-group col-sm-6 mb-6">
						<label for="timestamp">时间（UTC+0时间，当前时间1分钟内）</label>
						<input type="text" class="form-control" id="timestamp" value="<?php date('Y-m-d H:i:s',time()-8*3600)?>" placeholder="必填，格式: 2019-05-08 12:12:12">
					  </div>
					  <div class="form-group col-sm-6 mb-2">
						<label for="notify_url">通知地址</label>
						<input type="text" class="form-control" id="notify_url" value="https://pay.bituex.com/openapi/pay/deposit/address" placeholder="必填，将通过此接口通知订单支付状态">
					  </div>
					  <div class="form-group col-sm-6 mb-2">
						<label for="return_url">跳转地址</label>
						<input type="text" class="form-control" id="return_url" value="https://pay.bituex.com" placeholder="必填，支付成功后的跳转地址">
					  </div>
					  <div class="form-group col-sm-6 mb-2">
						<button type="button" id="goPay" class="btn btn-primary">支付</button>
					  </div>
					</form>
				</div>
			</div>
		</div>
		<script src="https://cdn.bootcss.com/jquery/3.4.1/jquery.min.js"></script>
		<script src="https://cdn.bootcss.com/twitter-bootstrap/4.3.1/js/bootstrap.min.js"></script>
		<script src="https://cdn.jsdelivr.net/gh/emn178/js-md5/build/md5.min.js"></script>
		<script src="https://cdn.bootcss.com/moment.js/2.24.0/moment.min.js"></script>
		<script>
			// 将对象转换成url参数
			var parseParam=function(param, key){
			  var paramStr="";
			  if(param instanceof String||param instanceof Number||param instanceof Boolean){
			//     paramStr+="&"+key+"="+encodeURIComponent(param);
				paramStr+="&"+key+"="+param;
			  }else{
			    $.each(param,function(i){
			      var k=key==null?i:key+(param instanceof Array?"["+i+"]":"."+i);
			      paramStr+='&'+parseParam(this, k);
			    });
			  }
			  return paramStr.substr(1);
			};
			var uuid = function() {
				var s = [];
				var hexDigits = "0123456789abcdef";
				for (var i = 0; i < 36; i++) {
					s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
				}
				s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
				s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
				s[8] = s[13] = s[18] = s[23] = "-";
			 
				var uuid = s.join("");
				return uuid;
			}
			$(function() {
				$("#timestamp").val(moment(new Date()).utc().format('YYYY-MM-DD HH:mm:ss'));
				$("#out_trade_no").val(uuid().toString().replace(/-/g, ''));
				$("#goPay").click(function() {
					
					var coin = $("#coin").val();
					var body = $("#body").val();
					var mid = $("#mid").val();
					var notify_url = $("#notify_url").val();
					var out_trade_no = $("#out_trade_no").val();
					var quantity = $("#quantity").val();
					var currency_type = $("#currency_type").val();
					var return_url = $("#return_url").val();
					var subject = $("#subject").val();
					var timestamp = $("#timestamp").val();
					var access_key_secret = $("#access_key_secret").val();
					
					//1.按照字典顺序构造参数对象
					var params = {
						body: body,
						coin: coin,
						currency_type: currency_type,
						mid: mid,
						notify_url: notify_url,
						out_trade_no: out_trade_no,
						quantity: quantity,
						return_url: return_url,
						subject: subject,
						timestamp: timestamp,
						user_tag: '11',
						access_key_secret: access_key_secret,
					};
					
					var urlParams = parseParam(params);
					console.log(urlParams);
					var sign = md5(urlParams);
					console.log(urlParams);
					console.log(sign);
					
					delete params.access_key_secret;
					params.sign = sign;
					var urlParamsNoSecret = parseParam(params);
					
					window.location.href = "http://www.bituex.com/openapi/gateway?" + urlParamsNoSecret;
				});
			});
		</script>
	</body>
</html>
