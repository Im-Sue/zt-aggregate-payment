<?php /*a:1:{s:49:"/www/wwwroot/ztpay/app/pay/view/qrcode/wxpay.html";i:1638116026;}*/ ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>商家收款</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        .wrap {
            padding: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .box-pay {
            width: 100%;
            padding: 0 40px;
            box-sizing: border-box;
            border-radius: 10px;
            background: linear-gradient(45deg, #38af53, #38af53);
        }

        .box-pay .title {
            box-sizing: border-box;
            line-height: 30px;
            text-align: center;
            font-size: 24px;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            color: #fff;
            letter-spacing: 2px;
            padding: 30px 10px 20px 10px;
            margin: 0;
        }

        .box-pay .input {
            padding: 0 10px;
            height: 60px;
        }

        .box-pay .input .money {
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            padding: 8px 15px;
            border-bottom: 1px solid #F0EFF4;
            position: relative;
            height: 60px;
        }

        .box-pay .input .money > img {
            width: 32px;
            height: 32px;
        }

        .box-pay .input .money > label {
            font-size: 16px;
            font-weight: 600;
        }

        .box-pay .input .money > input {
            width: 100%;
            height: 60px;
            line-height: 60px;
            outline: 0;
            border: 0;
            font-size: 42px;
            font-weight: bold;
            color: #fff;
            background: none;
            box-sizing: border-box;
            padding-left: 5px;
            text-align: center;
            padding-right: 37px;
        }

        .box-pay .input .money > input::-webkit-input-placeholder {
            /* placeholder颜色  */
            color: #fff;
            /* placeholder位置  */
            text-align: right;
        }

        .remark {
            font-size: 13px;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .remark .btn-edit-remark {
            padding-left: 10px;
            font-weight: bold;
        }

        .dialog-remark {
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 2;
            display: flex;
        }

        .dialog-remark .dialog-body {
            width: 80%;
            height: 167px;
            background: #fff;
            z-index: 2;
            position: relative;
            margin: auto;
            border-radius: 5px;
        }

        .dialog-remark p {
            font-weight: bold;
            text-align: center;
            padding: 5px 0 5px 0;
            color: #494949;
        }

        .dialog-remark .input {
            text-align: center;
            margin-bottom: 20px;
        }

        .dialog-remark .input > input {
            width: 85%;
            font-size: 16px;
            margin: auto;
            outline: 0;
            border: 0;
            line-height: 36px;
            background-color: #eee;
            padding-left: 10px;
            border-radius: 4px;
        }

        .dialog-remark .btn-remark-cancel {
            width: 50%;
            height: 44px;
            line-height: 44px;
            float: left;
            background: none;
            border: none;
            border-top: 1px solid #f0f0f0;
            font-weight: bold;
            font-size: 14px;
            outline: none;
            color: #494949;
        }

        .dialog-remark .btn-remark-confirm {
            width: 50%;
            height: 44px;
            line-height: 44px;
            float: left;
            background: none;
            border: none;
            border-top: 1px solid #f0f0f0;
            font-weight: bold;
            font-size: 14px;
            outline: none;
            border-left: 1px solid #f0f0f0;
            color: #38af53
        }

        .btn-pay {
            width: 70%;
            margin-left: 15%;
            margin-top: 110px;
            padding: 10px;
            background-color: #38af53;
            color: #fff;
            font-size: 18px;
            border-radius: 6px;
            border: none;
            outline: none;
        }
    </style>
</head>
<body>

<div class="wrap">
    <input type="hidden" id="user_id" name="user_id">
    <div class="box-pay">
        <p class="title"><?php echo htmlentities($title); ?></p>
        <div class="input" style="padding: 0 10px;">
            <div class="money">
                <img src="<?php echo mediaUrl('/static/pay/ic_money.png?r=2'); ?>">
                <input type="number" autocomplete="off" name="money" id="money" onkeyup="this.value=this.value.replace(/^([1-9]\d*(\.[\d]{0,2})?|0(\.[\d]{0,2})?)[\d.]*/g, '$1')" autofocus="autofocus">
            </div>
        </div>
        <div class="remark" id="btn-add-remark" onclick="remarkEdit()">
            <span>添加付款说明</span>
        </div>
        <div class="remark" id="remark-content" style="display:none;">
            <span id="txt-remark"></span>
            <span id="btn-edit-remark" class="btn-edit-remark" onclick="remarkEdit()">修改</span>
        </div>
    </div>
    <button type="submit" class="btn-pay" onclick="doPay()">微信付款</button>
</div>
<div class="dialog-remark" id="dialog-remark" style="display:none;">
    <div class="dialog-body">
        <p>添加付款说明</p>
        <div class="input">
            <input type="text" id="input-remark" autocomplete="off" name="money" autofocus="autofocus" placeholder="最多输入30个字">
        </div>
        <div>
            <button class="btn-remark-cancel" onclick="remarkCancel()">取消</button>
            <button class="btn-remark-confirm" onclick="remarkConfirm()">确定</button>
        </div>
    </div>
</div>
</body>

<script src="https://res.wx.qq.com/open/js/jweixin-1.3.2.js"></script>
<script src="<?php echo mediaUrl('/static/pay/jquery.min.js'); ?>"></script>
<script>

    /*var winHeight = $(window).height();  //获取当前页面高度
    $(window).resize(function () {
        var thisHeight = $(this).height();
        if ( winHeight - thisHeight > 140 ) {
            //键盘弹出
            $("#submit_btn").css("marginTop", 150);
        } else {
            //键盘收起
            $("#submit_btn").css("marginTop", 200);
        }
    });*/
    function doPay() {
        var openid = '<?php echo htmlentities($openid); ?>';
        //openid = 'on1YHwRGnnTbqI-URi3Wp2HLiLAs';
        var qrcode_id='<?php echo htmlentities($qrcode_id); ?>';
        var money = $("#money").val();
        if (money == '') {
            $("#money").focus();
            return false;
        }
        var remark = $('#input-remark').val();
        $.ajax({
            url: "<?php echo mobileUrl('/pay/qrcode/createOrder'); ?>",
            data: {
                qrcode_id: qrcode_id,
                openid: openid,
                total_fee: money,
                remark: remark,
                pay_type: 'wxpay'
            },
            type: 'post',
            success: function (res) {
                wx.chooseWXPay({
                    timestamp: res.jsApiParameters.timeStamp,
                    nonceStr: res.jsApiParameters.nonceStr,
                    package:res.jsApiParameters.package,
                    signType: res.jsApiParameters.signType,
                    paySign: res.jsApiParameters.paySign,
                    success: function (res) {
                        //alert("支付成功")
                        window.location.href = "<?php echo mobileUrl('/pay/qrcode/paySuccess', ['type'=>'wxpay']); ?>";
                    }
                });
            },
            error: function (error) {
                alert("网络错误");
                return false;
            }
        });
        return false;
    }

    var appid = '<?php echo htmlentities($signPackage["appId"]); ?>';
    var timestamp = '<?php echo htmlentities($signPackage["timestamp"]); ?>';
    var nonceStr = '<?php echo htmlentities($signPackage["nonceStr"]); ?>';
    var signature = '<?php echo htmlentities($signPackage["signature"]); ?>';
    wx.config({
        debug: false,
        appId: appid,
        timestamp: timestamp,
        nonceStr: nonceStr,
        signature: signature,
        jsApiList: [
            'checkJsApi',
            'updateAppMessageShareData',
            'hideOptionMenu',
            'showOptionMenu',
            'chooseWXPay'
        ]
    });
    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        wx.updateAppMessageShareData({
            title: '商家收款',
            desc: '<?php echo htmlentities($title); ?>',
            link: "<?php echo mobileUrl('/pay/qrcode/index', ['id' => $qrcode_id]); ?>",
            imgUrl: "<?php echo mediaUrl('/static/pay/wxpay_share.png', true); ?>"
        })
    });

    function remarkEdit() {
        $('#dialog-remark').show();
    }

    function remarkConfirm() {
        var remark = $('#input-remark').val();
        if (remark != '') {
            $('#dialog-remark').fadeOut(200, function () {
                $('#btn-add-remark').hide();
                $('#remark-content').show();
                $('#txt-remark').html(remark);
            });
        } else {
            $('#dialog-remark').hide();
            $('#remark-content').hide();
            $('#btn-add-remark').show();
        }
    }

    function remarkCancel() {
        $('#dialog-remark').fadeOut(200);
    }

    $("#input-remark").bind("input propertychange", function () {
        var text = $("#remark").val();
        var textlen = $("#remark").val().length;
        if (textlen > 30) {
            var lenText = text.substring(0, 30)
            $("#remark").val(lenText)
        }

    });
</script>
</html>