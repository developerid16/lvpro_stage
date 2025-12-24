<!DOCTYPE html>
<html>

<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style type="text/css">
        .fa {
            padding: 10px;
            font-size: 30px;
            width: 50px;
            text-align: center;
            text-decoration: none;
            margin: 5px 2px;
            border-radius: 14%;
        }
        .fa-facebook {
            background: #3B5998;
            color: white;
        }
        .fa-whatsapp {
            background: #55ACEE;
            color: white;
        }
        .fa-instagram {
            background: #125688;
            color: white;
        }
        .fa-envelope {
            background: #f7057a;
            color: white;
        }
        .fa-whatsapp {
            background: #00b489;
            color: white;
        }
        @media screen {
            @font-face {
                font-family: 'Lato';
                font-style: normal;
                font-weight: 400;
                src: local('Lato Regular'), local('Lato-Regular'), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format('woff');
            }
            @font-face {
                font-family: 'Lato';
                font-style: normal;
                font-weight: 700;
                src: local('Lato Bold'), local('Lato-Bold'), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format('woff');
            }
            @font-face {
                font-family: 'Lato';
                font-style: italic;
                font-weight: 400;
                src: local('Lato Italic'), local('Lato-Italic'), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format('woff');
            }
            @font-face {
                font-family: 'Lato';
                font-style: italic;
                font-weight: 700;
                src: local('Lato Bold Italic'), local('Lato-BoldItalic'), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format('woff');
            }
        }
        /* CLIENT-SPECIFIC STYLES */
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }
        /* RESET STYLES */
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        
        table {
            border-collapse: collapse !important;
        }
        body {
            height: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        /* iOS BLUE LINKS */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        /* MOBILE STYLES */
        @media(max-width: 480px){
            h1 {
                font-size: 32px !important;
                line-height: 32px !important;
            }
            .mpadding{
                padding: 35px 35px 35px 35px !important;
            }
            #mpadding{
                padding: 50px 18px !important;
            }
            .restpwdimg{
                max-width: 350px;
                height: auto;
                width: 100%;
            }
        }
        img.restpwdimg{
            max-width:750px !important;
        }
        div[style*="margin: 16px 0;"] {
            margin: 0 !important;
        }
    </style>
     <style>
        .mpaddingl {
            padding: 42px 50px 42px 50px;
        }

        .followusico {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .followusico {
            max-width: 120px;
            margin: 0 auto;
        }

        .righttitle {
            font-size: 24px;
            font-weight: bolder;
            color: #FFFFFF !important;
            margin-top: 10px;
            width: 100%;
            text-align: right !important;
        }

        .iconsocial {
            background: #e05245;
            border-radius: 50%;
            height: 32px;
            width: 32px;
            max-height: 32px;
            max-width: 32px;
            overflow: hidden;
            overflow: hidden;
            float: left;
        }

        .iconsocial img {
            object-fit: contain;
        }

        .socialiconsm {
            background: #e05245;
            border-radius: 50%;
            height: 20px;
            width: 20px;
            max-height: 20px;
            max-width: 20px;
            overflow: hidden;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
        }

        /* .mailsocialico{display:inline-flex;text-align: center;} */
        .mediaicosocial {
            width: 100%;
            max-width: 20px;
            max-height: 20px;
            padding: 6px;
            object-fit: contain !important;
            aspect-ratio: 1 / 1;
        }

        .inline-flex {
            display: inline-flex !important;
        }

        .mbox {
            max-width: 800px;
            padding: 0px;
            background-color: #FFFFFF;
            border-collapse: revert !important;
            text-align: center;
        }

        .logom,.outlook_logo {
            max-width: 104px;
            width: 100%;
            object-fit: contain;
        }

        @media only screen and (max-device-width:675px) {
            .logom {
                max-width: 74px;
            }

            .socialiconsm {
                margin-top: 3px;
            }

            .mpadding {
                padding: 0 !important;
                margin-top: 4px !important;
            }

            .mbox {
                padding: 10px !important;
            }

            .righttitle {
                font-size: 12px !important;
                margin-top: 5px !important;
                float:right !important;
            }

            .followusico {
                width: 100%;
            }
            .outlook_logo{
                max-width: 60px !important;
            }
        }
    </style>
</head>
 
<body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
    <!-- HIDDEN PREHEADER TEXT -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- LOGO -->
        <tr>
            <td bgcolor="#f4f4f4" align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%"
                    style="max-width: 800px;margin-bottom: 30px;overflow: hidden;">
                    <tr>
                        <td align="center" valign="top" style="padding: 0px 0px 20px 0px;"> </td>
                    </tr>
                </table>
            </td>
        </tr>
 
        <tr>
    <td bgcolor="#f4f4f4" align="center">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 800px;overflow: hidden;">
            <tr>
                <td bgcolor="#da4f44" align="left" style="padding: 16px 32px; border-radius: 0px 0px 0px 0px; color: #666666; font-family: 'Arial', Helvetica, Arial, sans-serif; font-size: 12px;font-weight: 400; line-height: 22px;display: flex;justify-content:space-between;align-items:center;">
                @if ((isset($provider) && ($provider == "hotmail.com" || $provider == "outlook.com" || $provider == "outlook.sg")) || (isset($data['provider']) && ($data['provider'] == "hotmail.com" || $data['provider'] == "outlook.com" || $data['provider'] == "outlook.sg")) )
                    <img src="{{ asset('/build/images/logo-dark.png') }}" alt="" srcset="" style="max-width:95px;float:left;">

                    @if(isset($unique_id) )
                    <div style="font-size: 12px;font-weight: bolder;color: #FFFFFF !important;margin-top: 10px;width: 100%;text-align: right !important;">APHID:{{$unique_id}}</div>
                    @endif
                    @if(isset($data['unique_id']) )
                    <div  style="font-size: 12px;font-weight: bolder;color: #FFFFFF !important;margin-top: 10px;width: 100%;text-align: right !important;">APHID:{{$data['unique_id']}}</div>
                    @endif

                @else 

                <img class="logom" src="{{ asset('/build/images/logo-dark.png') }}" alt="" srcset="">
                <!-- for outlook logo code -->
                <!-- for outlook logo code -->
                    @if(isset($unique_id) )
                    <div class="righttitle">APHID:{{$unique_id}}</div>
                    @endif
                    @if(isset($data['unique_id']) )
                    <div class="righttitle">APHID:{{$data['unique_id']}}</div>
                    @endif
                    
                @endif
                </td>
            </tr>
        </table>
    </td>
</tr>