@include('email.inc.header')

<td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;">
    <tbody>
        <tr>
        <td bgcolor="#ffffff" align="left" style="padding: 50px 50px 30px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">Dear {{$name}},</p>
                <p style="margin: 0;">You are receiving this email because we received a password reset request for your account.</p>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" align="left">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
                            <table border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="border-radius: 3px;"><a href="{{$url}}"
                                            target="_blank"
                                            style="font-size: 18px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 2px; border: 1px solid #49433B; display: inline-block;font-weight:700;background-color:#49433B;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr> <!-- COPY -->
        
        <tr>
            <td bgcolor="#ffffff" align="left"
                style="padding: 20px 50px 0px 50px;color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                <p style="margin: 0;">This password reset link will expire in 60 minutes.</p>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" align="left" style="padding: 10px 50px 20px 50px;color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                <p style="margin: 0;">If you did not request a password reset, no further action is required.</p>
            </td>
        </tr>

    </tbody>

    </table>
</td>
</tr>
@include('email.inc.footer')