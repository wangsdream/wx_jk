<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<body>
	<div class="basic_xinxi">
        <ul>
            <li class="cl">
                <div class="left">语音录入</div>
                <div class="right">
                    <input type="button" id="record" class="record" style="background:url(./voice_n_1.png) no-repeat center ; background-size:cover;">
                </div>
            </li>
        </ul>
    </div>
    <input type="hidden" id="audio_id" name="audio_id" class="audio_id" value="">
    <div class="basic_xinxi miaoshu" id="miaoshu" style="margin-top:10px;display:none">
        <ul>
        	<li class="cl">
                <div class="left">语音描述</div>
                    <div class="right">
                        <button id="playrecord">
                            播放
                        </button>
                        <input id="playVoice" type="hidden" value="" >
                    </div>
                </li>
        </ul>
    </div>
    <div id="voice-input" style="position: fixed;left: 0.91rem;top: 20%;width: 5.38rem;height: 6.98rem;background-size: 100% 100%;display:none">
        <img src="./voice-index.gif">
        wangzhen123
    </div>
	<script>
    var btnRecord = $('#record');
    var startTime = 0;
    var endTime = 0;
    var recordTimer = null;
    var record_delay = 500;
    var record_time = 1 * 1000; //最短录音时间阈值
    var button_state = 'none'; //按钮状态
    var voice_state = 'none'; //状态：
    
    wx.config({
        debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: 'appid', // 必填，公众号的唯一标识
        timestamp:'time', // 必填，生成签名的时间戳
        nonceStr: 'str', // 必填，生成签名的随机串-
        signature: 'sign',// 必填，签名-
        jsApiList: [
            "startRecord",
            "stopRecord",
            "onVoiceRecordEnd",
            "playVoice",
            "stopVoice",
            "onVoicePlayEnd",
            "uploadVoice",
        ] // 必填，需要使用的JS接口列表
    });
    
    wx.ready(function () {
        voice_state = "ready";
        stopRecord();
        $('#record').on('touchstart', function(event) {
            event.preventDefault();
            button_state = "buttondown";
            //按下按钮
            if(voice_state == 'ready'){
                startTime = new Date().getTime();
                // 延时后录音，避免误操作
                if(recordTimer){clearTimeout(recordTimer);}
                recordTimer = setTimeout(function () {
                    //开始录音
                    voice_state = 'starting_recording';
                    wx.startRecord({
                        success: function (res) {
                            if(button_state !== 'buttoncancel'){
                                //录音中
                                $("#voice-input").css("display","");
                                $(".record").css("background-image", "url(./voice_n_2.png)");
                                voice_state = 'recording';
                                wx.onVoiceRecordEnd({
                                    complete: function(res){
                                        RecordEnd();
                                    }
                                });
                                recordTimer = setTimeout(function(){
                                    voice_state = 'canstop_recode';
                                }, record_delay)
                            }else{
                                voice_state = 'buttoncancel';
                                setTimeout(stopRecord, record_delay);
                            }
                        },
                        fail: function(res){
                            alert('录音失败,请重试');
                            voice_state = 'ready';
                        },
                        cancel: function () {
                            alert('用户取消权限');
                            voice_state = 'ready';
                        }
                    });

                }, record_delay);
            }
        }).on('touchend', function(event) {
            //tishi('松开按钮' + voice_state);
            button_state = 'buttonup';
            RecordEnd(event);
            event.preventDefault();
            return false;
        }).on('touchcancel', function(event){
            button_state = 'buttoncancel';
            RecordEnd(event);
            event.preventDefault();
        })
   		
        document.querySelector('#playrecord').onclick = function() {
            var playlocalId = $('#playVoice').val();
            wx.playVoice({
                localId: playlocalId
            });
        };

        //注册微信播放录音结束事件【一定要放在wx.ready函数内】
        wx.onVoicePlayEnd({
            success: function (res) {
                stopWave();
            }
        });
    });
    wx.error(function(res){
            alert(res);
    });
    
    //判断按钮的分别状态进行调用不同的方法
    function RecordEnd(e){
        //结束录音
        if(recordTimer){
            clearTimeout(recordTimer);
            recordTimer == null;
        }
        endTime = new Date().getTime();
        if(button_state == 'buttoncancel'){
            //取消 and 授权
            voice_state = 'stoping_recode';
            setTimeout(stopRecord, record_delay)
        }else if(voice_state == 'starting_recording'){
            // 开始录音和录音开始成功之间
            voice_state = 'stoping_recode';
            setTimeout(stopRecordAndTranslateVoice, record_delay)
        }else if(voice_state == 'recording'){
            // 已经开始录音
            voice_state = 'stoping_recode';
            setTimeout(stopRecordAndTranslateVoice, record_delay)
        }else if(voice_state == 'canstop_recode'){
            // 已经开始录音
            voice_state = 'stoping_recode';
            setTimeout(stopRecordAndTranslateVoice)
        }
    }

    function stopRecordAndTranslateVoice(){
        wx.stopRecord({
            success: function(res){
                if((endTime - startTime) < record_time){
                    alert('录音时间太短');
                    voice_state = 'ready';
                }else{
                    voice_state = 'ready';
                    var localId = res.localId;
                    uploadVoice(localId);
                }
            },
            fail: function(res){
                alert('结束录音失败' + JSON.stringify(res));
                voice_state = 'ready';
            }
        })
    }
    
    //调用微信语音停止方法
    function stopRecord(){
        $("#voice-input").css("display",'none');
        $(".record").css("background-image", "url(./voice_n_2.png)");
        wx.stopRecord({
            success: function(res){
                voice_state = 'ready';
            },
            fail: function(res){
                voice_state = 'ready';
            }
        })
    }
    
    //上传语音方法
    function uploadVoice(localId){
        $('#playVoice').val(localId);
        $("#miaoshu").css("display","");
        $("#voice-input").css("display",'none');
        $(".record").css("background-image", "url(./voice_n_2.png)");
        wx.uploadVoice({
            localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
            isShowProgressTips: 1, // 默认为1，显示进度提示
            success: function (res) {
                var serverId = res.serverId; // 返回音频的服务器端ID
                $.ajax({
                    url: '后台处理文件',
                    type: 'post',
                    data: {serverId: serverId, token: token},
                    dataType: "json",
                    success: function (data) {
                        alert(data.msg);
                    },
                    error: function (xhr, errorType, error) {
                        console.log(error);
                    }
                });
            }
        });
    }
</script>
</body>
</html>