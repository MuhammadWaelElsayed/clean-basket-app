
    {{-- Header --}}
    @include('mails.elements.header')
    
    
    
    {{-- Main Body --}}
    <table class="row row-3" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
        <tbody>
            <tr>
                <td>
                    <table class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #ffffff; color: #000000; width: 640px; margin: 0 auto;" width="640">
                        <tbody>
                            <tr>
                                <td class="column column-1" width="100%" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: top; border-top: 0px; border-right: 0px; border-bottom: 0px; border-left: 0px;">
                                
                                    
                                    <div class="spacer_block block-5" style="height:40px;line-height:40px;font-size:1px;">&#8202;</div>
                                    <table class="heading_block block-6" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;">
                                        <tr>
                                            <td class="pad" style="padding-bottom:5px;padding-left:20px;padding-right:20px;padding-top:5px;text-align:center;width:100%;">
                                                <p style="color:#1C1C1C;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:150%;text-align:center;mso-line-height-alt:24px;">
                                                    Hey, {{$data['user']['name']}} 
                                                </p>
                                                <h1 style="margin: 0; color: #1C1C1C; direction: ltr; font-family: Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif; font-size: 22px; font-weight: 600; letter-spacing: 2px; line-height: 150%; text-align: center; margin-top: 0; margin-bottom: 0; mso-line-height-alt: 33px;">
                                                    <span class="tinyMce-placeholder">{!!$data['title']!!} <br></span></h1>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="pad" style="padding-bottom:10px;padding-left:20px;padding-right:20px;padding-top:10px;">
                                                <div style="color:#1C1C1C;font-family:Montserrat, Trebuchet MS, Lucida Grande, Lucida Sans Unicode, Lucida Sans, Tahoma, sans-serif;font-size:16px;line-height:150%;text-align:center;mso-line-height-alt:24px;">
                                                    <p style="margin: 0; word-break: break-word;"> {!!$data['message']!!}</span></p>
                                                    <div class="spacer_block block-7" style="height:20px;line-height:20px;font-size:1px;">&#8202;</div>
                                                    <p style="margin: 0; word-break: break-word;">For futher assistance contact - legalplatformlp@gmail.com</span></p>
    
                                                </div>
                                               
                                            </td>
                                        </tr>
                                    </table>
                                    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    

    {{-- Footer --}}
    @include('mails.elements.footer')
