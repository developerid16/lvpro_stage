@include('email.inc.edmheader')
<tr>
        <td bgcolor="#f4f4f4" align="center">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;overflow: hidden;">
                <tr>
                <td bgcolor="#da4f44" align="left" style="padding: 16px 32px; border-radius: 0px 0px 0px 0px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px;font-weight: 400; line-height: 22px;display: flex;justify-content:space-between;align-items:center;">
                    <img src="{{ asset('/build/images/logo-dark.png') }}" alt="" srcset="" style="max-width: 104px;">
                    <div style="font-size: 24px;font-weight:bolder;color:#FFFFFF;margin-top:10px;width:100%;text-align: right;">S10111997</div>
                    </td>
                </tr>
        </table>
        </td>
    </tr>
<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">

        <table border="0" cellpadding="0" cellspacing="0" width="100%"
            style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;border-collapse: revert !important;">
            <tbody style="outline: 1px solid #D8D8D8;">
                 
                <tr>
                    <td bgcolor="#ffffff" align="left"
                        style="padding: 50px 50px 0px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;margin-bottom:20px;">Dear *user's name*,</p>
                        <p style="margin: 0;">We are thrilled to announce the launch of our new and improved Shilla Access App! Explore the new design with enhanced
                        features such as real-time earning of Keys and more reward options.</p>
                        <br>
                        <p style="margin: 0;">Update now to enjoy the brand-new app today:</p>
                        
                        <p style="text-align: center">
                            <a href="https://apps.apple.com/sg/app/the-shilla-access/id1143405076"><img src="{{asset('build/email/ios.png')}}" alt="" srcset="" height="50" style="height: 40px;max-height:40px;"></a>
                           <a href="https://play.google.com/store/apps/details?id=com.trinax.shilla.app"> <img src="{{asset('build/email/android.png')}}" alt="" srcset="" height="50" style="margin-left: 8px;height: 40px;max-height:40px;" ></a>
                        </p>
                        <br>
                        <p style="margin: 0;">You'll be required to reset your password after upgrading your app.</p>
                        <br>
                        <p style="margin: 0;">For any assistance or question, please contact us at shilla.access@samsung.com. or WhatsApp at +65 9827 6714</p>
                        <br>
                        <p style="margin: 0;">Thank you!</p>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#ffffff" align="left"
                        style="padding: 30px 50px 50px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;">Best Regards,,<br>{{config('app.name')}} Team</p>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#DA4F44" align="center"
                        style="padding: 12px 32px; border-radius: 0px 0px 0px 0px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <div style="display:inline-flex;column-gap: 25px;">
                        <div style="max-height: 32px;">
                            <a href="https://www.facebook.com/Shilladutyfree.sg" style="max-height:50px;"><img src="{{asset('build/email/fb.svg')}}"  style="height: 32px !important;width: 32px;margin-right: 10px;"></a>
                        </div>
                        
                        <div style="max-height: 32px;">
                        <a href="https://www.instagram.com/shilladutyfreesg/" style="max-height:50px;"><img src="{{asset('build/email/ig.svg')}}"  style="height: 32px !important;width: 32px;margin-right: 10px;"></a>
                        </div>
                        <div style="max-height: 32px;">
                        <a href="https://wa.me/6598276714" style="max-height:50px;"><img src="{{asset('build/email/wp.svg')}}"  style="height: 32px !important;width: 32px;margin-right: 10px;"></a>
                        </div>
                        <div style="max-height: 32px;">
                        <a href="https://www.xiaohongshu.com/user/profile/5aa6374b4eacab58cdd6416e?xhsshare=CopyLink&appuid=5f7c33470000000001001e87&apptime=1702455842" style="max-height:50px;"> <img src="{{asset('build/email/socialico.svg')}}"  style="height: 32px !important;width: 32px;margin-right: 10px;"></a>
                        </div>
                        <div style="max-height: 32px;">
                        <a href="mailto:shilla.access@samsung.com" style="max-height:50px;"> <img src="{{asset('build/email/mailico.svg')}}"  style="height: 32px !important;width: 32px;"></a>
                        </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
@include('email.inc.footer')