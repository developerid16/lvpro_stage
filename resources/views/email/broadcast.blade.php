@include('email.inc.edmheader')
 
<tr>
<td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;max-width:800px;"> 
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;padding: 40px;padding-bottom:12px;background-color: #FFFFFF;border-collapse: revert !important;">
    <tbody>
        <tr>
        <td bgcolor="#ffffff" align="left"
                    style="padding: 30px 50px 50px 50px; color: #666666; font-family: 'Lato', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;">
                {!! $data['email_content'] !!}
            </td>
        </tr>
    
@include('email.inc.footer')