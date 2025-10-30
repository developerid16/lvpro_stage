@include('email.inc.edmheader')

<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;">
        <!-- style="outline: 1px solid #D8D8D8;" -->
            <tbody>
                <tr>
                    <td bgcolor="#ffffff" align="left" style="padding: 50px 50px 40px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;margin-bottom:20px;">Hi {{$data['name']}},</p>
                        <p style="margin: 0;">OTP Verification Code:</p>
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
                                                <p style="user-select:all;-webkit-user-select:all;font-size: 18px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 2px; border: 1px solid #49433B; display: inline-block;font-weight:700;margin:0px">{{$data['otp']}}</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr> <!-- COPY -->


                <tr>
                    <td bgcolor="#ffffff" align="left" style="padding: 30px 50px 0px 50px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;">This OTP will only be valid for next 15 minutes.
                        </p>
                        <p style="margin: 0;">If you receive this email notification but you have not requested for otp yourself,
                            we recommend that you change your password.</p>
                    </td>
                </tr>
                <tr>
                    <td bgcolor="#ffffff" align="left" style="padding: 30px 50px 30px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;">Cheers,<br>{{config('app.name')}} Team</p>
                    </td>
                </tr>
        </table>
    </td>
</tr>


@include('email.inc.footer')