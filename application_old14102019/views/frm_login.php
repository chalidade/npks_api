<form id="frm_login" name="frm_login">
<div align="center" style="width:100%;">
	<img src="img/content/logo.png" width="150">
</div>
<table style="font-size:12px; color:#000;">
    <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-weight:bold;">Username</td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td>
        <input name="frm_login_username" id="frm_login_username" type="text" style="width:260px;" require="true" post="true" error="Username tidak boleh kosong" tabindex="1"/>
        <span id="val_frm_login_username" class="validate" style="margin-left:15px;"></span>
        </td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-weight:bold;">Password</td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td>
        <input name="frm_login_password" id="frm_login_password" type="password" style="width:260px;" require="true" post="true" error="Password tidak boleh kosong" tabindex="2"/>
        <span id="val_frm_login_password" class="validate" style="margin-left:15px;"></span>
        </td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td style="font-size:12px; font-weight:bold;">SECURITY CODE</td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td valign="middle">
                    <div style="border:1px solid #999; background:#FFF;" align="center" id="cap">
                        <img src="mod/asset/get_captcha.php" alt="" id="captcha" style="cursor:pointer;" onclick="change_captcha()" title="Click to refresh"/>
                    </div>
                    </td>
                </tr>
            </table><button type="button" style="margin-top:3px;" onclick="change_captcha()"> &nbsp;REFRESH</button>
        </td>
    </tr>
    <tr>
        <td width="20">&nbsp;</td>
        <td>
        <input name="frm_login_sec_code" id="frm_login_sec_code" type="text" style="width:260px;" require="true" post="true" error="Security Code tidak boleh kosong" tabindex="3"/>
        <span id="val_frm_login_sec_code" class="validate" style="margin-left:15px;"></span>
        </td>
    </tr>
    <tr>
        <td width="20" height="40">&nbsp;</td>
        <td>
        <button style="background-color:#ff6b05; color:#FFF; font-weight:bold;" type="submit" name="frm_login_submit" id="frm_login_submit" tabindex="4"> &nbsp;LOGIN</button> &nbsp;
        <button style="background-color:#ff6b05; color:#FFF; font-weight:bold;" type="reset" name="frm_login_reset" id="frm_login_reset"> &nbsp;RESET</button> &nbsp;
        <button style="background-color:#ff6b05; color:#FFF; font-weight:bold;" name="frm_lupa_password" id="frm_lupa_password"> &nbsp;FORGOT PASSWORD</button>
        <br /><br />
        <span id="verify"></span>
        </td>
    </tr>
</table>
<input type="hidden" id="val_frm_login" name="val_frm_login"/>
</form>         
<script>
$("#frm_login_username").focus();

function change_captcha(){
    document.getElementById('captcha').src="mod/asset/get_captcha.php?rnd=" + Math.random();
}	

// $("#frm_login_sec_code").click(function(){
//     alert(document.getElementById('captcha').text);
// });		

$("#frm_lupa_password").click(function(){
            $("#login_div").remove();
            c_div('login_div', '<div style="width:100%;" align="right"><a class="close" style="background-image:url(img/content/close.png); position:absolute; right:25px; top:17px; cursor:pointer; height:35px; width:35px;"></a></div><br><div style="color:#fff; background:url(img/content/white-med.png) no-repeat; height:450px; width:410px;"><div style="padding:30px 20px 10px 20px;" id="login_div_content"></div></div></div>');
            $("#login_div").overlay({effect: 'apple', load: true, top: 110, mask: '#000', oneInstance: false});
            $("#login_div_content").load('tpl/frm_lupa_password.php');
    });

//alert(document.getElementById('captcha').val());
$("#frm_login").submit(function(){
		if($("#frm_login_username").val().length>0&&$("#frm_login_password").val().length>0&&$("#frm_login_sec_code").val().length>0){
			$("#verify").html('<img src="img/content/loading.gif" style="margin-bottom:-3px;"> &nbsp;Verifikasi Userlogin...');
			$.post("mod/exec/exec_login.php",{frm_login_username:$("#frm_login_username").val(), frm_login_password:$("#frm_login_password").val(), frm_login_sec_code:$("#frm_login_sec_code").val()} ,function(data)
			{	
				if(data=='true'){
					document.location = 'index.php';		
                }else if(data == 'falscaptcha'){
					$("#verify").html('<img src="img/icon/icon-cancel.png" style="margin-bottom:-3px;"> &nbsp;Captcha Tidak Sesuai');
                }else{
                    $("#verify").html('<img src="img/icon/icon-cancel.png" style="margin-bottom:-3px;"> &nbsp;Username atau Password Salah');			
				}
			});
		}else{
			$("#verify").html('<img src="img/icon/icon-cancel.png" style="margin-bottom:-3px;"> &nbsp;Silahkan Lengkapi Data');	
		}
        return false;
    });

</script>   
