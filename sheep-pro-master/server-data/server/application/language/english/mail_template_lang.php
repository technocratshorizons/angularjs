<?php

//register Mail to User
$lang['Registration_mail_subject'] = 'Sheep Pro Registration';
$lang['Registration_mail_body'] = 'Welcome __FULL_NAME,<br/><br/>Yoir account has been created with us as a  __TYPE.<br/><br/> Simply log in with this email address and the password.<br/><br/><b>Email:</b>__EMAIL<br/><br/><b>Password:</b>__PASSWORD<br/><br/>If you have any questions, send us an email at <a href="mailto:__ADMIN_EMAIL">__ADMIN_EMAIL</a><br /><br />Thanks & Regards<br/><b>SheepTimer Team</b>';


//Forget Password to user
$lang['forgot_password_mail_subject'] = 'Reset Password';
$lang['forgot_password_mail_body'] = 'Password has been changed for account:email__ </br></br>New password for your account is: password__  <br/><br/>Regards,<br/>Mark Jennings</b>';


//Booking Mail
$lang['Booking_mail_subject'] = 'Sheep Pro Booking Confirmation';
// $lang['Booking_edit_mail_subject'] = 'Sheep Pro Booking Edit Confirmation';
$lang['Booking_mail_body'] = 'Dear <b>__FIRST_NAME</b>.<br/><br/>
Thank you for the opportunity to scan your ewes. <br/> <br/>

I would like to reconfirm the following details:<br/><br/>

__SCANNING_DATES_MODULE

The following will make the day run smoothly;<br/><br/>

<b>Power:</b><br/>
240 volt power supply, if area is over 60m from power supply a generator will be required. A generator can be supplied through prior arrangement.<br/><br/>
 
<b>Staff</b><br/>
A minimum of 2 people will be required to aid sheep flow.<br/><br/>
 
<b>Stock Emptying</b><br/>
Sheep to be yarded the night before off feed and water.  It is also important that there hasn’t been any supplement feeding for approximately 48 hours prior to scanning please.	<br/><br/>
 
<b>Stock Identification</b><br/>
Drafting is now possible at the same time as scanning pending  on facilities.  Please have
spray marker or raddle available in case drafting is not possible.<br/><br/>
 
<b>Weather Conditions</b><br/>
Inclement weather rarely delays scanning.<br/><br/>
 
I will contact you the week prior to finalise this booking. If this date doesn’t suit please contact me as soon as possible to arrange another.<br/><br/>
 
Regards,<br/>
Mark Jenkinson';


//Unavaivale Date Mismatch Mail

$lang['Booking_mail_date_subject'] = 'Appointment Date Change Notification';
$lang['Booking_mail_date_body'] = 'Hello Admin <br/><br/>
Your booking appointment date has changed from Google Calendar which is clashing with unavailable dates. Please change appointment date. Click on the following link to change the appointment date.<br/><br/> __LINK <br/><br/>
<b>Booking details are:-</b><br/><br/> __BOOKING_DETAILS <br/><br/>
Regards,<br/>
Mark Jenkinson';


//Booking Form Mail Content start here
$lang['Booking_form_mail_date_admin_subject'] = 'New Booking Created';
$lang['Booking_form_mail_date_admin_body'] = 'Hello Admin <br/><br/>A new booking has been generated by client name "__CLINET_NAME". Please add appointment date. Click on the following link to add the appointment date.<br/><br/> __LINK <br/><br/>
Regards,<br/>
Mark Jenkinson';