@include('email.inc.edmheader')
<tr>
    <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
        <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mbox">

            <tbody style="text-align: center;">
        <tr>
            <td bgcolor="#ffffff" align="left" style="border-radius: 0px 0px 4px 4px; color: #49433B; font-family: 'Arial', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: 400; line-height: 22px;text-align:left;">
                    <div class="mpadding" style="padding:20px 30px 0px 30px;max-width: 740px;">
                            <p style="margin:0;margin-bottom:20px">Dear <span style="font-weight:bold;">{{$name}}</span>,</p>
                            <p style="margin:0;color:#49433b;margin-bottom:15px">Lancôme invites you and your guest to experience the future of skincare at Lancôme’s Génifique Ultimate Travellers’ Repair Clinic, a Worldwide Exclusive concept at Changi Airport.</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px; text-decoration:underline"><b>Event Details:</b></p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Date: 28 September 2024, Saturday</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Venue: Changi Airport Terminal 1, Departure Transit Hall</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Time:</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Session 1: 3:30 PM - 4:30 PM</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Session 2: 5:30 PM - 6:30 PM</p>
                            <p style="margin:0;color:#49433b;margin-bottom:15px">Click <a href="https://bit.ly/SAxLancome24">here</a> to RSVP now!</p>
                            <p style="margin:0;color:#49433b;margin-bottom:15px;">Registration closes on 16 September 2024, Monday, 11:59PM.</p>
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Limited slots available. Sessions will be allocated on a first-come, first-served basis.
                            <p style="margin:0;color:#49433b;margin-bottom:5px">Click <a href="https://bit.ly/SAxLancome24">here</a> to RSVP now!</p>                        
                            <p style="margin:0;color:#49433b;margin-bottom:15px">We will contact you via e-mail or WhatsApp once your registration is confirmed.</p>
                            <p style="margin:0;color:#49433b;margin-bottom:15px">For more details on the event, please click <a href="https://bit.ly/SAxLancomeVIPEvent24">here</a>.</p>
                            <p style="margin-top: 15px;margin-bottom:10px !important;color:#49433b;">Thank you!</p>
                            <p style="margin-top: 0px;color:#49433b;margin-bottom: 0;">Best Regards,</p>
                            <p style="margin-top: 0px;color:#49433b;margin-bottom: 0;font-weight:bold;">The Safra Access Team</p>
                    </div>
            </td>
        </tr>
@include('email.inc.footer')