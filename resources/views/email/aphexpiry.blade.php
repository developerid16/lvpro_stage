@include('email.inc.edmheader')

<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;">
            <tr>
                <td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
                <p style="margin: 0;">{{$data['text']}}</p>
                <p style="padding: 20px 0; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">Click <a href="https://appurl.io/CMbLZ9hLiW">here</a> to update your account details. OR <a href="https://sg.shilladutyfree.com/en/">"https://sg.shilladutyfree.com/en/"</a></p>
                </td>
            </tr>
        </table>
    </td>
</tr>
@include('email.inc.footer')