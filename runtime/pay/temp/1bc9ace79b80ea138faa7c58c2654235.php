<?php /*a:1:{s:49:"/www/wwwroot/ztpay/app/pay/view/qrcode/error.html";i:1636736552;}*/ ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>商家收款</title>
</head>
<body >
<div class="wrap" style="text-align: center;">
    <img style="margin: 140px auto 40px;" class="success_logo" src="<?php echo mediaUrl('/static/pay/ic_error.png'); ?>" width="100px;" />
    <div class="money" style="margin: 10px;font-size: 24px;"><?php echo htmlentities($message); ?></div>
    <div class="line"></div>
</div>

</body>
</html>