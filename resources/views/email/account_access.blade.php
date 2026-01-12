@include('email.inc.edmheader')
<tr>
<td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;">
        <tbody>
        <tr>
        <td bgcolor="#ffffff" align="left" style="padding: 50px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                <p style="margin: 0;">Dear {{$data['name']}},</p>
                <p style="margin: 0;">As part of our The Safra Access ensuring the security of your account, we have assigned a new password for your account. This email contains the details you need to access your account.</p>
            </td>
        </tr>
        <tr>
                    <td bgcolor="#ffffff" align="left">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td bgcolor="#ffffff" align="center" style="padding: 20px 50px 60px 50px;">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="center" style="border-radius: 3px;" bgcolor="#49433B">
                                                <p style="user-select:all;-webkit-user-select:all;font-size: 18px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 2px; border: 1px solid #49433B; display: inline-block;font-weight:700;margin:0px">Your password is: {{$data['password']}}</p>
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
                    style="padding: 0px 30px 50px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                <p style="margin: 0;">Thank you for using The Safra Access. We appreciate your trust in us and are committed to providing you with a secure and seamless experience.</p>
            </td>
        </tr>
</td>
</tr>
@include('email.inc.footer')