<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:800px;overflow:hidden;margin:0 auto;background-color: #FFFFFF;">
            <tbody>
                <tr>
    <td><div style="height:1px;width: 90%;background-color:#49433b!important;margin: 10px auto 10px auto;"></div>
</td></tr>
            </tbody>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:800px;overflow:hidden;margin:0 auto;background-color: #FFFFFF;">
            <tbody>
<tr style="text-align: center;">
                    <td bgcolor="#ffffff" align="left" style="border-radius: 0px 0px 4px 4px; color: #49433B; font-family: 'Arial', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <!-- <p style="margin: 0;">Cheers,<br>{{config('app.name')}} Team</p> -->
                        <div class="mpaddingl">
                            <div style="text-align:center;width:100%;">
                                <p style="margin-top: 0 !important;">Follow us at:</p>
                                <div class="followusico">
                                    <div style="max-height: 32px;margin-right:10px;float: left;">
                                        <a href="https://www.facebook.com/Shilladutyfree.sg" style="max-height:50px;">

                                            <div class="iconsocial">
                                                <!-- <img src="{{asset('build/email/fb.png')}}" class="mediaicosocial"> -->

                                                <!-- for outlook logo code -->
                                                <img src="{{asset('build/email/fb.png')}}" style="width: 100%;max-width: 20px;max-height: 20px;aspect-ratio: 1 / 1;padding:6px;object-fit: contain;">
                                                 <!-- for outlook logo code -->
                                            </div>
                                        </a>
                                    </div>

                                    <div style="max-height: 32px;margin-right:10px;float: left;">
                                        <!-- style="max-height:50px;height: 32px !important;width: 32px;" -->
                                        <a href="https://www.instagram.com/shilladutyfreesg/">
                                            <div class="iconsocial">

                                                <!-- <img src="{{asset('build/email/ig.png')}}" class="mediaicosocial"> -->
                                                
                                                <!-- for outlook logo code -->
                                                <img src="{{asset('build/email/ig.png')}}" style="width: 100%;max-width: 20px;max-height: 20px;aspect-ratio: 1 / 1;padding:6px;object-fit: contain;">
                                                <!-- for outlook logo code -->
                                                
                                            </div>
                                        </a>
                                    </div>

                                    <div style="max-height: 32px;">
                                        <a href="https://www.xiaohongshu.com/user/profile/5aa6374b4eacab58cdd6416e?xhsshare=CopyLink&appuid=5f7c33470000000001001e87&apptime=1702455842" style="max-height:50px;">

                                            <div class="iconsocial">
                                                <img src="{{asset('build/email/ch.png')}}" style="width: 100%;max-width: 20px;max-height: 20px;aspect-ratio: 1 / 1;padding:6px;object-fit: contain;">
                                            </div>
                                        </a>
                                    </div>

                                </div>
                                <div class="block">
                                    <p>For any inquiries, feel free to reach out to us at <a href="mailto:shilla.access@samsung.com">shilla.access@samsung.com</a> </p>
                                </div>

                               
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 0px 0px 0px 0px;overflow: hidden;margin:0 auto;">
            <tr>
                <td bgcolor="#da4f44" align="center" style="max-height:36px;padding: 8px 30px 8px 30px;color: #8F8F90;font-size: 10px;font-weight: 400;line-height: 20px;">
                    
                    <p style="margin: 0;color:#FFF;font-size: 10px;color: #FFF;font-weight: normal;letter-spacing: 0.5px;">This is an electronically generated e-mail, please do not reply.</p>
                    @if (isset($data['en_email']))
                   @php
                   $en_email = $data['en_email']
                       
                   @endphp 
                    @endif
                    @if (isset($en_email))
                    <a style="margin: 0;color:#FFF;font-size: 10px;color: #FFF;font-weight: normal;letter-spacing: 0.5px;" href="https://www.theshillaaccess.sg/unsubscribe?token={{$en_email}}">Click Here to unsubscribe</a>
                        
                    @endif
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;margin:0 auto;">
            <tr>
                <td align="left" style="padding: 10px 30px 10px 30px; color: #49433B; font-family: Helvetica, Arial, sans-serif; font-size: 10px; font-weight: 400; line-height: 18px;text-align:center;">
                    <p style="margin: 0;">© {{date('Y')}} Shilla Access. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>

</tbody>
</table>

</body>

</html>
