@include('email.inc.edmheader')

<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;">
        <!-- style="outline: 1px solid #D8D8D8;" -->
            <tbody>
                <tr>
                    <td bgcolor="#ffffff" align="left" style="padding: 50px 50px 40px 50px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                        <p style="margin: 0;margin-bottom:20px;">Hi There,</p> 
                        <p style="margin: 0;">Your Sales Reports is ready.  <a href="{{url('admin/report-queue')}}">Click Here</a> to Download it. </p>
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