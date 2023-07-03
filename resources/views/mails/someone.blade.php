@include('mails.includes.header')
    <div style="background-color:transparent;">
      <div style="Margin: 0 auto;min-width: 320px;max-width: 600px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: #FFFFFF;" class="block-grid ">
        <div style="border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;">
          <!--[if (mso)|(IE)]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:transparent;" align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 600px;"><tr class="layout-full-width" style="background-color:#FFFFFF;"><![endif]-->

              <!--[if (mso)|(IE)]><td align="center" width="600" style=" width:600px; padding-right: 0px; padding-left: 0px; padding-top:0px; padding-bottom:5px; border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent;" valign="top"><![endif]-->
            <div class="col num12" style="min-width: 320px;max-width: 600px;display: table-cell;vertical-align: top;">
              <div style="background-color: transparent; width: 100% !important;">
              <!--[if (!mso)&(!IE)]><!--><div style="border-top: 0px solid transparent; border-left: 0px solid transparent; border-bottom: 0px solid transparent; border-right: 0px solid transparent; padding-top:0px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;"><!--<![endif]-->
                    <div class="">
                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"><![endif]-->
                        <div style="color:#575962;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:120%; padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"> 
                            <div style="font-size:12px;line-height:14px;color:#575962;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;text-align:left;">
                                <p style="margin: 0;font-size: 14px;line-height: 17px;text-align: center">
                                <span style="font-size: 28px; line-height: 33px;">
                                <strong>
                                    <span style="line-height: 33px; font-size: 28px;">Hello</span>
                                </strong>
                                </span>
                                <br><span style="font-size: 28px; line-height: 33px;">Taxi Ride Request</span>
                                <span style="font-size: 28px; line-height: 33px;"> From :{{$user->first_name}} </span>
                                <span style="line-height: 33px; font-size: 28px;"></span></span>
                                <span style="font-size: 28px; line-height: 33px;">
                                <span style="line-height: 33px; font-size: 28px;"></span></span>
                                </p>
                            </div>  
                        </div>
                        <!--[if mso]></td></tr></table><![endif]-->
                    </div>

                    <div class="">
                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"><![endif]-->
                        <div style="color:#7e8085;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:150%; padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"> 
                            <div style="font-size:12px;line-height:18px;color:#7e8085;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;text-align:left;">
                                    <p style="margin: 0;font-size: 14px;line-height: 21px;text-align: center;font-weight: 600;">With {{ $settings->site->site_title  }}, you have entered a part of a <br> world that gives you the best of services to choose from.</p>
                            </div>    
                        </div>

                        <!--[if mso]></td></tr></table><![endif]-->
                    </div>  

                      <div style="color: #3943B7; border: 2px dashed #3943B7; border-radius: 4px; -webkit-border-radius: 4px; -moz-border-radius: 4px; max-width: 145px; width: 105px;width: auto; padding-top: 5px; padding-right: 20px; padding-bottom: 5px; padding-left: 20px; font-family: 'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif; text-align: center; mso-border-alt: none;">
                    <span style="font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;font-size:16px;line-height:32px;"><strong><span style="font-size: 20px; line-height: 40px;">Ride OTP <br /> <?php echo $otp ?></span></strong></span>
                    </div>        
                                   
                  
                    <div class="">
                        <!--[if mso]><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"><![endif]-->
                        <div style="color:#7e8085;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;line-height:150%; padding-right: 10px; padding-left: 10px; padding-top: 10px; padding-bottom: 10px;"> 
                            <div style="font-size:12px;line-height:18px;color:#7e8085;font-family:'Montserrat', 'Trebuchet MS', 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', Tahoma, sans-serif;text-align:left;"><p style="margin: 0;font-size: 14px;line-height: 21px;text-align: center"><span style="color: #ffffff; font-size: 14px; line-height: 21px;"><strong>&#160;</strong></span></p></div>  
                        </div>
                        <!--[if mso]></td></tr></table><![endif]-->
                    </div>
                  
              <!--[if (!mso)&(!IE)]><!--></div><!--<![endif]-->
              </div>
            </div>
          <!--[if (mso)|(IE)]></td></tr></table></td></tr></table><![endif]-->
        </div>
      </div>
    </div>
@include('mails.includes.footer')
 


