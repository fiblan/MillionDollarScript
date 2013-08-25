<?php
/*
COPYRIGHT 2008 - see www.milliondollarscript.com for a list of authors

This file is part of the Million Dollar Script.

Million Dollar Script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Million Dollar Script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Million Dollar Script.  If not, see <http://www.gnu.org/licenses/>.

*/


# ads.inc.php

$label['ads_inc_pixels_col']  = "Pixels";
$label['ads_inc_expires_col'] = 'Expires';
$label['ads_inc_expied_stat'] = "Expired!";
$label['ads_inc_nyp_stat'] = 'Not Yet Published';
$label['ads_inc_elapsed_stat'] = "%ELAPSED% elapsed<br> %TO_GO% to go";
$label['ads_inc_nev_stat']='Never';
$label['ads_inc_pub_stat']='Published';
$label['ads_inc_npub_stat']='Not Published';
$label['ads_inc_app_stat']='Approved';
$label['ads_inc_napp_stat']='Not Approved';

$label['ad_list_status'] ='Status';


$label['ad_list_st_pending'] = 'Awaiting Payment...';
$label['ad_list_st_completed'] = 'Active';
$label['ad_list_st_cancelled'] = 'Cancelled';
$label['ad_list_st_confirmed'] = 'Confirmed';
$label['ad_list_st_new'] = 'New';
$label['expired'] = 'Expired';
$label['deleted'] = 'Deleted';

  
# order_pixels.php
 
$label['min_pixels_required'] = "Sorry, you are required to upload an image with at least %MIN_PIXELS% pixels. This image only has %COUNT% pixels...";

$label['max_pixels_required'] = "Sorry, the uploaded image is too big. This image has %COUNT% pixels... A limit of %MAX_PIXELS% pixels per order is set.";


$label['pixel_upload_failed'] = "Upload failed. Please try again, or try a different file.";
$label['pixel_uploaded_head'] = "Upload your pixel image";
$label['no_order_in_progress']='No new orders in progress';
$label['no_order_in_progress_go_here']='You do not have have any new orders in progress. Please go <a href="%ORDER_PAGE%">here to select your pixels</a>.';

$label['upload_image_size']="The uploaded image is %WIDTH% pixels wide and %HEIGHT% pixels high.";

$label['your_uploaded_pix'] = "Your uploaded pixes:";

$label['upload_pix_description'] = "- Upload a GIF, JPEG or PNG graphics file<br>
- Click 'Browse' to find your file on your computer, then click 'Upload'.<br>
- Once uploaded, you will be able to position your file over the grid.<br>";
$label['upload_your_pix'] = "Upload your pixels:";

$label['pix_upload_button'] = "Upload";

# confirm_order.php

$label['not_logged_in']="You are not logged in. Please log in or sign up for a new account. The pixels that you have placed on order are saved and will be available once you log in.";
$label['conirm_signup']="Sign Up";
$label['confirm_instructions']="Fill in your details, it will only take a minute! You will be given a customer account where you can manage your pixels that you have just selected!";
$label['confirm_login']="Log in";
$label['confirm_member']="Already a Member? Login here.";

# checkout.php

$label['sorry_head']='Pixel Reservation Not Yet Completed...';
$label['sorry_head2']='We are sorry, it looks like the pixels we tried to reserve for you were snapped up by someone else in the mean time! Please go <a href="%ORDER_PAGE%">here</a> to move your pixels to another area.';

# check_selection.php

$label['check_sel_notavailable'] =  "The are where you placed your pixels is not available! Please try to place your pixels in a different area.";

###########

$label["ads_list_days_ago"] = "Days ago";  #  - lists.inc.php (list ads)
$label["ads_list_day_ago"] = "Day ago";  #  - lists.inc.php (list ads)
$label["ads_list_today"] = "Today!"; #  - lists.inc.php (list ads)
$label["ads_list_no_image"] = "No Image."; # Employer - resumes.inc.php (list resumes)

$label['ads_col_id']='Order ID';
$label['ads_col_grid_id']='Grid ID';
$label['ads_col_user_id']='User ID';
$label['ads_col_ad_id']='Ad ID';
$label['ads_col_date']='Date';
########
   
$label['max_blocks_selected']='Maximum blocks selected. (%MAX_BLOCKS% allowed per order)';



$label['sold_stats']='Sold';    
$label['available_stats']='Available';
 
$label["navigation_page"] = "Page %CUR_PAGE% of %PAGES% - "; # label for navigational links for browsing lists of ads
$label["navigation_prev"] = "&lt;-Previous"; # label for navigational links for browsing lists of ads
$label["navigation_next"] = "Next -&gt;"; # label for navigational links for browsing lists of ads

$label["adv_login_new_link"] = "New users, start ordering your pixels here!";
$label["adv_login_pay_later"] = "(Reserve and upload your pixels now, sign up and pay later)";
$label["advertiser_loginform_title"] = "User's Login";
$label["advertiser_section_heading"] = "Already got an account? Log in here:<br/> ";
$label["advertiser_section_newusr"] = "New Users:";
$label["advertiser_go_buy_now"] = '(Reserve and upload your pixels now, sign up and pay later!)';

$label["advertiser_signup_heading1"] = "Welcome to %SITE_NAME% "; # Advertiser's - signup.php
$label["advertiser_signup_heading2"] = "Sign up for an account"; # Advertiser's - signup.php
$label["advertiser_signup_first_name"] = "First Name"; # Advertiser's - signup.php
$label["advertiser_signup_last_name"] = "Last Name"; # Advertiser's - signup.php
$label["advertiser_signup_business_name"] = "Business Name"; # Advertiser's - signup.php
$label["advertiser_signup_business_name2"] = "Enter company name"; # Advertiser's - signup.php
$label["advertiser_signup_member_id"] = "Member ID"; # Advertiser's - signup.php
$label["advertiser_signup_member_id2"] = "(Choose a unique Member ID that you will use to log in, but do not use spaces)"; # Advertiser's - signup.php
$label["advertiser_signup_password"] = "Password"; # Advertiser's - signup.php
$label["advertiser_signup_password_confirm"] = "Confirm Password"; # Advertiser's - signup.php
$label["advertiser_signup_your_email"] = "E-mail"; # Advertiser's - signup.php

$label["advertiser_signup_newsletter"] = "Receive Newsletter?"; # Advertiser's - signup.php
$label["advertiser_signup_new_resumes"] = "Notification on new Resumes?"; # Advertiser's - signup.php
$label["advertiser_signup_submit"] = "Submit"; # Advertiser's - signup.php
$label["advertiser_signup_reset"] = "Reset"; # Advertiser's - signup.php
$label["advertiser_signup_error"] = "Cannot continue due to the following errors:<p>"; # Advertiser's - signup.php
$label["advertiser_signup_error_name"] = "* Please fill in your first name<br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_ln"] = "* Please fill in your last name<br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_user"] = "* Please fill in your Member I.D.<br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_inuse"] = '* The Member I.D. \'%username%\' is in use. Please choose a different Member I.D. <br/>'; # Advertiser's - signup.php
$label["advertiser_signup_error_p"] = "* Please fill in your password <br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_p2"] = "* Please fill in confirm your password <br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_email"] = "* Please fill in your Email <br/>"; # Advertiser's - signup.php
$label["advertiser_signup_error_pmatch"] = "* Passwords do not match <br/>"; # Advertiser's - signup.php
$label['advertiser_forgot_subject']='Your password on %SITE_NAME%';
$label["advertiser_signup_success_1"] = "%FirstName% %LastName%, You have successfully signed up to the %SITE_NAME%  Advertiser's System. If you ever encounter any problems, bugs or just have any questions or suggestions, feel free to contact %SITE_CONTACT_EMAIL%"; # Advertiser's - signup.php
$label["advertiser_signup_success_2"] = "%FirstName% %LastName%, You have successfully signed up to the %SITE_NAME%  Advertiser's System. You will soon receive a validation email to verify your email address. If you ever encounter any problems, bugs or just have any questions or suggestions, feel free to use contact  %SITE_CONTACT_EMAIL%"; # Advertiser's - signup.php 
$label["advertiser_signup_email_in_use"] = "* Cannot create a new account: the Email address is already in use. "; # Advertiser's - signup.php


 
$label['advertiser_login_error']='Error: Username/Password combination is incorrect. <a href="index.php">Try again...</a><p> If you have forgotten your password, please <a href=\'forgot.php\'>Click Here</a>.<br>Please <a href=\'signup.php\'>Sign Up</a> if you are a new user. '; # Advertiser's - login.php
$label["advertiser_login_disabled"] = "Note: this account is not validated. Please check your email for the validation message"; # Advertiser's - login.php
$label["advertiser_login_success"] = "Welcome back %firstname% %lastname%. You have successfully signed in as '%username%' <br/>Processing Login... If this page appears for more than 5 seconds <a href='index.php'>click here to reload.</a><p>"; # Advertiser's - login.php
$label["advertiser_login_success2"] = "Welcome back %firstname% %lastname%. You have successfully signed in as '%username%' <br/>Processing Login... If this page appears for more than 5 seconds <a href='%target_page%'>click here to reload.</a><p>"; # Advertiser's - login.php
$label["advertiser_logging_in"] = "Logging in to %SITE_NAME% ...  "; # Advertiser's - login.php
$label["advertiser_loginform_title"] = "Advertiser's Login"; # Advertiser's - Login form (login_functions.php)
$label['advertiser_new_user_created']= "New user created"; # Advertiser's - Signup (login_functions.php)
$label['advertiser_could_not_signup'] = "Could not sign up, try using another Member ID or contact bug support by clicking here"; # Advertiser's - Signup error (login_functions.php)
$label['advertiser_signup_goback']='<a href=\'index.php\'>Continue</a>'; 
$label["yes_option"] = "Yes"; # Advertiser's : signup.php (Option for radio buttons)
$label["no_option"] = "No"; # Advertiser's: signup.php (Option for radio button)

$label["advertiser_signup_language"] = "Select your preferred language:";  # Advertiser's - signup.php; Candidates - signup.php


$label["advertiser_pass_forgotten"] = "Forgotten your Password"; # Advertiser - login form (login_functions.php) this is shown directly below the login form...
$label["advertiser_forgot_title"] = "Forgot my password"; # Advertiser - forgot.php (Page heading)
$label["advertiser_forgot_enter_email"] = "Enter your Email address";  # Advertiser - forgot.php
$label["advertiser_forgot_email_notfound"] = "Email not found on the system. Try again";  # Advertiser - forgot.php
$label["advertiser_forgot_submit"] = "Submit";  # Advertiser - forgot.php (submit button)
$label["advertiser_forgot_error1"]= "You cannot reset your password because your account is not enabled. Please wait for your account to be validated. Contact %SITE_CONTACT_EMAIL% if you have any questions."; # Advertiser - forgot.php
$label["advertiser_forgot_success1"] = "A new password was sent to your email address. Please allow some time for your new password to arrive. You will be able to log in with the new password here: <a href='%BASE_HTTP_PATH%users/'>Advertiser's Login</a>"; # Advertiser - forgot.php
$label["advertiser_forgot_fail1"] = "Failed sending an email, please contact support by clicking here and  include your username, first name and last name in the error report."; # Advertiser - forgot.php 
$label["advertiser_forgot_go_back"] = "Main Page"; # Advertiser - forgot.php (Link to the front page)

$label["advertiser_login"] = 'Login'; # Advertiser's - Button to log in. (advertisters/login_functions.php)

$label["advertiser_join_now"] = 'Create a new account'; # Advertiser's - displayed when a user who is not logged in tries to access the Advertiser's section (advertisters/login_functions.php)
$label["advertiser_signup_continue"] = "Click Here to Continue";
$label['advertiser_logout_ok'] = "You have logged out."; # Advertiser's - logout.php
$label['advertiser_logout_home'] = "%SITE_NAME% Home"; # Advertiser's - logout.php (link to home page)

# validate

$label['advertiser_valid_login']="Log in";
$label['advertiser_valid_entemail']="Please enter your Email Address:";
$label['advertiser_valid_entcode']="Please enter your Validation Code:";
$label['advertiser_valid_complete']="Validation completed. Thank you. You may log in and purchase some pixels.";
$label['advertiser_valid_error']="Validation failed. Please contact support.";

# Select pixels 
$label['selection_mode']="Selection Mode:";
$label['select1']="1 block at a time";
$label['select4']="4 blocks at a time (2x2 square)";
$label['select6']="6 blocks at a time (3x2 rectangle)";
$label['advertiser_sel_nfs_error']="Sorry, cannot select this block of pixels because it is not for sale!";
$label['advertiser_sel_sold_error']="Sorry, cannot slect block #%BLOCK_ID% because it is on order / sold!";
$label['advertiser_max_order']='Cannot place pixels on order. You have reached the order limit for this grid. Please review your Order History.';
$label['advertiser_max_order_html']='<b><font color="red">Cannot place pixels on order.</font> You have reached the order limit for this grid. Please review your <a href="orders.php">Order History.</a></b>';
$label['advertiser_sel_trail']="1. <b>Select Your Pixels</b> -> 2. Confirm Order -> 3. Payment -> 4. Image Upload & Publish your pixels";

$label['advertiser_nav_status1'] = "Upload Your pixels";
$label['advertiser_nav_status2'] = "Write Your Ad";
$label['advertiser_nav_status3'] = "Confirm";
$label['advertiser_nav_status4'] = "Payment";
$label['advertiser_nav_status5'] = "Thank you!";


$label['advertiser_sel_pixel_inv_head']="Available Grids";
$label['advertiser_sel_select_intro']="There are <b>%IMAGE_COUNT%</b> different images served by this website! Select the image which you would like to publish your pixels to:";
$label['advertiser_order_not_confirmed']="Note: You have placed some pixels on order but it was not confirmed (green blocks). <a href='orders.php'>View Order History</a>";
$label['advertiser_select_pixels_head']="Select Pixels";


$label['advertiser_select_instructions2']=
"<h3>Instructions:</h3>
<p>
Each square represents a block of %PIXEL_C% pixels (%BLK_WIDTH%x%BLK_HEIGHT%). Select the blocks that you want, and then press the 'Buy Pixels Now' button.<br><br>
- Click on a White block to select it.<br>
- Green blocks are selected by you.<br>
- Click on a Green block to un-select.<br>
- Red blocks have been sold.<br>
- Yellow blocks are reserved by someone else, Orange have been ordered by you.<br>
- Click the 'Buy Pixels Now' button at the bottom of the page when you are finished.<br>
</p>";

$label['advertiser_buy_button']="Buy Pixels Now";
$label['advertiser_write_ad_button']="Write Your Add -&gt;&gt;";
$label['advertiser_require_pur'] = "The uploaded image will require you to purchase %PIXEL_COUNT% pixels from the map which is exactly %BLOCK_COUNT% blocks.";

# write_ad
$label['write_ad_instructions']="Please write your advertisement and click 'Save Ad' when done.";
$label['write_ad_saved']="Ad Saved.";
$label['write_ad_continue_button']="Continue -&gt;&gt;";

# Publish pixels

$label['advertiser_publish_free_order']="Your order was completed.";
$label['advertiser_publish_not_owner']="Error: you do not own this block! Please click on your block.";
$label['advertiser_publish_fnf']="File not found / file upload error";
$label['advertiser_publish_url_blank']="- URL is blank. Please fill it in.";
$label['advertiser_publish_bad_url']="- Bad URL.";
$label['advertiser_publish_alt_blank']="- ALT link text is blank. Please fill it in.";
$label['advertiser_publish_no_image']="- No Image uploaded. Please select an image to upload.";
$label['advertiser_publish_error']="ERROR:";

$label['advertiser_publish_file_support']="File Not Supported:";
$label['advertiser_publish_upload_head']="Image Upload";
$label['advertiser_publish_supp_formats']="Supported formats:";
$label['advertiser_publish_cur_image']="The current Image for this selection is:";
$label['advertiser_publish_max_size']="The maximum image size is %MAX_X% pixels wide and %MAX_Y% pixels high";
$label['advertiser_publish_upload_label']="Upload Image:";
$label['advertiser_publish_my_url']="My URL:";
$label['advertiser_my_link_text']="My link text:";
$label['advertiser_publish_upload']="Upload";
$label['advertiser_publish_cancel']="Cancel";
$label['advertiser_publish_pixinv_head']="Available Grids";
$label['advertiser_publish_select_init2']="You own pixels on <b>%GRID_COUNT%</b> different grids served by this website. Select the image which you would like to publish your pixels to:";
$label['advertiser_publish_head']="Manage your pixels";

$label['advertiser_publish_instructions2'] ='Your blocks are shown on the grid below. <br>
- Click your block to edit it. Only your blocks are shown.<br>
- <b>Red</b> blocks have no pixels yet. Click on them to upload your pixels. <br>
</p>';

$label['advertiser_publish_published']="Note: Your pixels are now published and live!";
$label['advertiser_publish_waiting']="Your pixels are now approved! They are now waiting for the webmaster to publish them on to the public grid.";

$label['advertiser_publish_pixwait']="Note: Your pixels are waiting for approval. They will be scheduled to go live once they are approved.";


$label['advertiser_file_type_not_supp'] = "File type not supported.";

$label['adv_pub_sizewrong'] = "The size of the uploaded image is incorrect. It needs to be %SIZE_X% wide and %SIZE_Y% high (or less)";

$label['adv_pub_editad_head']='Edit your Ad / Change your pixels'; 

$label['adv_pub_editad_desc'] = "Here you can edit your ad or change your pixels.";
$label['adv_pub_yourpix'] = "Your Pixels:";
$label['adv_pub_piximg'] = "Pixels";
$label['adv_pub_pixinfo'] = "Pixel Info";
$label['adv_pub_pixcount'] = "%PIXEL_COUNT% pixels<br>(%SIZE_X% wide,  %SIZE_Y% high)";
$label['adv_pub_pixchng'] = "Change Pixels";
$label['adv_pub_pixtochng']='To change these pixels, select an image %SIZE_X% pixels wide & %SIZE_Y% pixels high and click \'Upload\''; 

$label['adv_pub_pixupload'] = "Upload";

$label['adv_pub_edityourad'] = "Edit Your Ad:";
$label['adv_pub_adsaved'] = "Ad Saved";
$label['adv_pub_yourads'] = "Your Ads";

# payment.php    

$label['advertiser_pay_navmap']="1. Select Your pixels -> 2. Confirm Order -> 3. <b>Payment</b> -> 4. Image Upload & Publish your pixels";
$label['advertiser_pay_sel_method']="Select Your Payment Method:";

# orders.php
 
$label['advertiser_ord_cancel'] = "Cancel, are you sure?";
$label['advertiser_ord_cancel_button'] = "Cancel";

$label['advertiser_ord_history']="My Order History";
$label['advertiser_ord_explain']=
"'Completed' Orders - Orders where the transaction was sucessfully completed.<br>
'Confirmed' Orders - Orders confirmed by you, but the transaction has not been completed.<br>
'Pending' Orders - Orders confirmed by you, but the transaction has not been approved.<br>
'Cancelled' Orders - Cancelled by the administrator.<br>
'Expired' Orders - Pixels were expired after the specified term. You can renew this order.<br>";

$label['advertiser_ord_hist_list']="Order History";
$label['advertiser_ord_prderdate']="Order Date";
$label['advertiser_ord_custname']="Customer Name";
$label['advertiser_ord_usernid']="Username & ID";
$label['advertiser_ord_orderid']="OrderID";
$label['advertiser_ord_quantity']="Quantity";
$label['advertiser_ord_image']="Grid";
$label['advertiser_ord_amount']="Amount";
$label['advertiser_status']="Status";
$label['advertiser_ord_noordfound']="No orders found.";
$label['advertiser_ord_confnow']="Confirm now..";
$label['advertiser_ord_awaiting']="Awaiting Payment..";
$label['advertiser_ord_manage_pix']="Manage..";
$label['advertiser_ord_renew']="Renew Now! %DAYS_TO_RENEW% days left to renew";
$label['adv_ord_inprogress'] = 'In progress';
// order status
$label['completed']='Completed'; 
$label['confirmed']='Confirmed'; 
$label['pending']='Pending'; 
$label['expired']='Expired'; 
$label['cancelled']='Cancelled'; 
$label['deleted']='Deleted'; 
# order.php

$label['order_min_blocks']="Not enough blocks selected";
$label['order_min_blocks_req']="You are required to select at least %MIN_BLOCKS% blocks form the grid. Please go back to select more pixels.";

$label['advertiser_o_navmap']= "1. <a href='select.php?BID=%BID%'>Select Your pixels</a> -> 2. <b>Confirm Order</b> -> 3. Payment -> 4. Image Upload & Publish your pixels";
$label['advertiser_o_nopixels']="You have no pixels selected on order! Please <a href='select.php?BID=%BID%'>select some pixels here";
//$label['advertiser_o_completebutton']="Complete Order";
$label['advertiser_o_confpay_button']="Confirm &amp; Pay &gt;&gt;";
$label['advertiser_o_edit_button']="Edit Order";

$label['advertiser_pack_select_button']='Next &gt;&gt;';
$label['advertiser_pack_prev_button']='&lt;&lt; Previous';
$label['advertiser_o_completebutton']="Complete Order &gt;&gt;";

# displaying the order:
$label['advertiser_ord_order_id']="Order ID:";
$label['advertiser_ord_date']="Date:";
$label['advertiser_ord_name']="Grid Name:";
$label['advertiser_ord_quantity']="Quantity:";
$label['advertiser_ord_pix']="pixels";
$label['advertiser_ord_expired']="Expires:";
$label['advertiser_ord_never']="Never";
$label['advertiser_ord_days_exp']="In %DAYS_EXPIRE% days from date of publishment";
$label['advertiser_ord_price']="Price:";
$label['advertiser_ord_status']="Status:";



# Index.php

$label['advertiser_home_welcome']="Welcome to your account";
$label['advertiser_home_line2']="Here you can manage your pixels.";
$label['advertiser_home_youown']="You own %PIXEL_COUNT% pixels. <a href='publish.php'>Manage my Pixels</a>"; 
$label['advertiser_home_onorder']="You have %PIXEL_ORD_COUNT% pixels on order. <a href='select.php'>Order Pixels</a> / <a href='orders.php'>View Order History</a>";
 
$label['advertiser_home_blkyouown']="You own %PIXEL_COUNT% blocks. <a href='publish.php'>Manage my Pixels</a>";
$label['advertiser_home_blkonorder']="You have %PIXEL_ORD_COUNT% blocks on order. <a href='select.php'>Order Pixels</a> / <a href='orders.php'>View Order History</a>";

$label['advertiser_home_click_count']="Your pixels were clicked %CLICK_COUNT% times.";
$label['advertiser_home_sub_head']="Here is what you can do:";
$label['advertiser_home_selectlink']="- <a href='%ORDER_PAGE%'>Order</a>: Choose and order new pixels.";
$label['advertiser_home_managelink']="- <a href='publish.php'>Manage</a>: Manage pixels owned by you.";
$label['advertiser_home_ordlink']="- <a href='orders.php'>View Orders</a>: View your order history, and status of each order.";
$label['advertiser_home_editlink']="- <a href='edit.php'>Edit Account Details</a>: Edit your personal details, change your password.";
$label['advertiser_home_quest']="Questions? Email us:";


# Header

$label['advertiser_header_nav1']="My Account";
$label['advertiser_header_nav2']="Order Pixels";
$label['advertiser_header_nav3']="Manage My Pixels";
$label['advertiser_header_nav4']="My Order History";
$label['advertiser_header_nav5']="Logout";


#Edit
$label['advertiser_edit_head']="Edit My Account Details";
$label['advertiser_edit_intro']="Here you can edit your name, email, company name and change your password.";
$label['advertiser_edit_passok']="OK: Your password was changed.";
$label['advertiser_edit_pssnomatch']="Error: New passwords do not match";
$label['advertiser_edit_badpass']="Error: Incorrect current password.";
$label['advertiser_edit_chpass']="Change Password";
$label['advertiser_edit_curpass']="Current Password";
$label['advertiser_edit_newpass']="New Password";
$label['advertiser_edit_retypepass']="Re-type Password";
$label['advertiser_edit_changebutton']="Change Password";
$label['advertiser_edit_details_updated']="OK: Your details were updated.";
$label['advertiser_edit_upd_personald']="Update Personal Details";
$label['advertiser_edit_fname']="First Name";
$label['advertiser_edit_lname']="Last Name";

$label['advertiser_edit_comp_n']="Company Name";
$label['advertiser_edit_email']="Email Address";
$label['advertiser_edit_savebutton']="Save";

# price_functions.php

$label['advertiser_pf_table']="Price Table";
$label['advertiser_pf_intro']="The following table shows the different price regions for the selected grid.";
$label['advertiser_pf_price']="Price / 100 pixels";
$label['advertiser_pf_color']="Color";
$label['advertiser_pf_fromrow']="From row";
$label['advertiser_pf_torow']="To row";
$label['advertiser_pf_fromcol']='From column'; 
$label['advertiser_pf_tocol']='To column'; 

# package_functions.php

$label['advertiser_package_table']="Price Options";
$label['advertiser_pa_intro_sel']="Please select your preferred package from the following list:";
$label['advertiser_pa_intro_show']="Here are the available price options for this grid:";
$label['free'] = "free";
$label['pack_never'] = "Never";
$label['pack_expires_in'] = "Expires in %DAYS_EXPIRE% days.";
$label['pack_unlimited'] = "Unlimited";
$label['pack_head_select'] ="Select";
$label['pack_head_price'] ="Price";
$label['pack_head_exp'] ="Expires";
$label['pack_head_mo'] ="Max Orders";


$label['pack_price_per100']= "/ 100 pixels";

$label['pack_cannot_select']= "<font color='red'>Error: Cannot place order. This price option is limited to %MAX_ORDERS% per customer.</font><br>Please select another option, or check your <a href='orders.php'>Order History.</a>";

#paypal

$label['payment_paypal_name'] = "PayPal";
$label['payment_paypal_descr'] =  "PayPal Secure Credit Card Payment";
$label['payment_paypal_head'] = "Pay with PayPal (Secure credit card payment)";
$label['payment_paypal_accepts'] = "PayPal accepts: Visa, Mastercard";
$label['payment_paypal_bttn_alt'] = "Make payments with PayPal - it's fast, free and secure!";

# 2 checkout

$label['payment_2co_name']="2Checkout";
$label['payment_2co_descr']= "2Checkout - Accepts: Visa, Mastercard, American Express, Discover, JCB, Diners";
$label['payment_2co_submit_butt']="Buy From 2Checkout.com";

# Bannk payment


$label['payment_bank_name'] ="Bank:";
$label['payment_bank_addr'] ="Bank Address:";
$label['payment_bank_ac_name']="Account Name:";
$label['payment_bank_ac_number']="Account Number:";
$label['payment_bank_branch_number']="Branch number:";
$label['payment_bank_swift']="SWIFT code:";
$label['payment_bank_note']="To speed up your payment, please quote your Order code (%INVOICE_CODE%). Send an email to %CONTACT_EMAIL% after you have completed making the payment. Thank you.";
$label['payment_bank_button']="Wire Transfer";
$label['payment_bank_go_back'] = '<a href="../users/">Go back</a> to your account';
$label['payment_bank_heading']="Please deposit %INVOICE_AMOUNT% to the following account:";

# Check / money Order
$label['payment_check_button']="Check / Money Order";
$label['payment_check_heading']="Send %INVOICE_AMOUNT% to the following:";

//$label['payment_check_note']="To speed up your payment, please quote your Order code (%INVOICE_CODE%). Send an email to %CONTACT_EMAIL% after you have completed making the payment. Thank you.";
$label['payment_check_payable'] = "Payable to:";
$label['payment_check_address'] = "Address to:";


# CC Avenue
$label['pay_by_ccavenue_button']="Pay by CCAvenue";

$label['payment_ccave_go_back'] = '<a href="../users/">Go back</a> to your account';

$label['payment_ccave_note_b'] = "Thank you for your purchase. Your transaction is still pending and will complete once the funds are cleared. We will keep you posted regarding the status of your order through e-mail";

$label['payment_ccave_note_y'] = "Thank you for your purchase. Your credit card has been charged and your transaction is successful. You can continue and upload your pixels.";

# Money bookers
$label['pay_by_moneybookers_button']="Pay by moneybookers.com";
$label['payment_moneybookers_descr']="Payment to:";

$label['payment_moneybookers_description']="moneybookers.com Secure Credit Card Payment";
$label['payment_moneybookers_name'] = "MoneyBookers.com";
# egold

$label['pay_by_egold_button']="Pay by e-gold.com";
$label['payment_egold_description'] = "Internet payments backed by 100% Gold";

# Authorize.net (SIM)
$label['payment_authnet_description'] = "Authorize.Net - Secure credit card payments";
$label['pay_by_authnet_button']="Pay via Authorize.net";
$label['payment_authnet_name'] = "Authorize.Net";

## payment_manager.php

$label['payment_mab_btt']="Payment Button - Click to complete payment.";
$label['payment_man_pt']="Payment Type";
$label['payment_man_descr']="Description";

$label['payment_return_thanks'] = 'Thank you!';

# Ad form
$label['ad_save_error'] = "ERROR: Cannot save your ad for the following reasons: ";
$label['ad_save_button'] = "Save Ad";
$label['delete_image_button'] = "Delete Image";
$label['upload_image'] = "Upload an additional Image";
$label['bytes'] = "bytes";
$label['no_file_uploaded'] = "No file uploaded";
$label['delete_file_button'] = "Delete";
$label['upload_file'] = 'Upload File';
$label['bad_words_not_accept'] = 'Bad words not accepted';
$label['vaild_file_ext_error'] = "%EXT% files are not allowed. Only files with the following extensions are allowed: %EXT_LIST%";
$label['vaild_image_ext_error'] = "%EXT% images are not allowed. Only images with the following extensions are allowed: %EXT_LIST%";
$label['valid_file_size_error'] = "%FILE_NAME% is too big.";
$label["find_button"] = "Find"; # include/dynamic_forms.php (button for search)

$label['sel_month_1'] =  "Jan"; # include/dynamic_forms.php (date field - month)
$label['sel_month_2'] =  "Feb"; # include/dynamic_forms.php (date field - month)
$label['sel_month_3'] =  "Mar"; # include/dynamic_forms.php (date field - month)
$label['sel_month_4'] =  "Apr"; # include/dynamic_forms.php (date field - month)
$label['sel_month_5'] =  "May"; # include/dynamic_forms.php (date field - month)
$label['sel_month_6'] =  "Jun"; # include/dynamic_forms.php (date field - month)
$label['sel_month_7'] =  "Jul"; # include/dynamic_forms.php (date field - month)
$label['sel_month_8'] =  "Aug"; # include/dynamic_forms.php (date field - month)
$label['sel_month_9'] =  "Sep"; # include/dynamic_forms.php (date field - month)
$label['sel_month_10'] =  "Oct"; # include/dynamic_forms.php (date field - month)
$label['sel_month_11'] =  "Nov"; # include/dynamic_forms.php (date field - month)
$label['sel_month_12'] =  "Dec"; # include/dynamic_forms.php (date field - month)
$label['sel_box_select'] = "[Select]";
$label['sel_category_select'] = "[Select]"; # include/dynamic_forms.php ([select] - 1st line in categories selection)
$label['sel_category_select_all'] = "Select All"; # include/dynamic_forms.php (select all in categories)
###########################################
# Emails

$label['order_confirmed_email_subject']="Order Confirmed";
$label['order_completed_email_subject']="Order Completed";
$label['order_pending_email_subject']="Order Pending";
$label['order_expired_email_subject']="Order Expired";
$label['confirmation_email_subject']="Account Confirmation";


$label["order_confirmed_email_template"]=

"Dear %FNAME% %LNAME%,

You have successfully placed an order at %SITE_NAME%.

========================
ORDER DETAILS
=========================
Order ID: #%ORDER_ID%
Pixels: %PIXEL_COUNT%
Days: %PIXEL_DAYS%
Price: %PRICE%
Status: Confirmed
--------------------------

Your order will be Completed as soon as the payment is cleared.

Once your order is Completed, log into your account and
upload your pixels and link.

Feel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. 

Thank you!



%SITE_NAME% team.
%SITE_URL%

Note: This is an automated email.
";


$label["order_completed_email_template"]=

"RE: Order Status Change Notification.

Dear %FNAME% %LNAME%,

Your order was completed on %SITE_NAME%.

========================
ORDER DETAILS
=========================
Order ID: #%ORDER_ID%
Pixels: %PIXEL_COUNT%
Days: %PIXEL_DAYS%
Price: %PRICE%
Status: Completed
--------------------------.

Please Log into your account and
upload your pixels and link.

Feel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. 

Thank you!


%SITE_NAME% team.
%SITE_URL%

Note: This is an automated email.
";

########################################

$label["order_pending_email_template"]=

"RE: Order Status Change Notification.

Dear %FNAME% %LNAME%,

Your order status changed to 'Pending' on %SITE_NAME%.

This means that you payment was recived, and the funds
are clearing. Once the funds are cleared, you will be
able to manage your pixels.


========================
ORDER DETAILS
=========================
Order ID: #%ORDER_ID%
Pixels: %PIXEL_COUNT%
Days: %PIXEL_DAYS%
Price: %PRICE%
Status: Pending
--------------------------.


Feel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. 

Thank you!


%SITE_NAME% team.
%SITE_URL%

Note: This is an automated email.
";
###########################################3

$label["order_expired_email_template"]=

"RE: Order Status Change Notification.

Dear %FNAME% %LNAME%,

Your order status changed to 'Expired' on %SITE_NAME%.

This means that your pixels have expired and will 
no longer be show when the grid is next updated.
You may renew your order, Here is how to do it:

1. Log in to your account

2. Go to 'My Order History'

3. Click on 'Renew' on the Order shown as 'expired'.

4. Complete the payment


========================
ORDER DETAILS
=========================
Order ID: #%ORDER_ID%
Pixels: %PIXEL_COUNT%
Days: %PIXEL_DAYS%
Price: %PRICE%
Status: Expired
--------------------------.


Feel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. 

Thank you!


%SITE_NAME% team.
%SITE_URL%

Note: This is an automated email.
";

$label["order_completed_renewal_email_template"]=

"RE: Order Status Change Notification.

Dear %FNAME% %LNAME%,

Your order was renewed on %SITE_NAME%.

========================
ORDER DETAILS
=========================
Order ID: #%ORDER_ID% (Carried over from %ORIGINAL_ORDER_ID%)
Pixels: %PIXEL_COUNT%
Days: %PIXEL_DAYS%
Price: %PRICE%
Status: Completed
--------------------------.

Please Log into your account and
upload your pixels and link.

Feel free to contact %SITE_CONTACT_EMAIL% if you have any questions / problems. 

Thank you!


%SITE_NAME% team.
%SITE_URL%

Note: This is an automated email.
";
###########################################

############################################

$label["confirmation_email_templaltev2"]=

"Dear %FNAME% %LNAME%,

Thank you for signing-up to %SITE_NAME%! 


IMPORTANT:

To complete the sign up process, you will need to visit the
following link:

%VERIFY_URL%

Your Validation Code is: %VALIDATION_CODE%


Regards,

Webmaster,
%SITE_NAME%
%SITE_URL%";

$label['confirmation_html_email_templaltev2']='Dear %FNAME% %LNAME%,
<p>
Thank you for signing-up to %SITE_NAME%! <br>
</p>
<p>
IMPORTANT:
</p><p>
To complete the sign up process, you will need to visit the
following link:<p>
<p>
<a href="%VERIFY_URL%">%VERIFY_URL%</a>
</p><p>
Your Validation Code is: %VALIDATION_CODE%
</p>
<br><p>
Regards,
</p><p>
Webmaster,<br>
%SITE_NAME%<br>
%SITE_URL%<br>'; 

############################################

$label["forget_pass_email_template"]=

"Dear %FNAME% %LNAME%,

Your %SITE_NAME% password has been reset!

Here is your new password:

Member ID: %MEMBERID%
Password: %PASSWORD%


Regards,

Webmaster,
%SITE_NAME%
%SITE_URL%
";

$label['publish_pixels_email_subject'] = "New published pixels on %SITE_NAME%";


$label['publish_pixels_email_template'] =

"

New pixels published on %SITE_NAME%!

Grid: %GRID_NAME%
Member ID: %MEMBERID%

URLS: 
%URL_LIST%

- To view and approve the pixels, please visit here:
%VIEW_URL%


";

$label['publish_pixels_html_email_template'] =

"

<p>New pixels published on %SITE_NAME%!</p>

Grid: %GRID_NAME%<br>
Member ID: %MEMBERID%<br>

URLS:<br>
%URL_LIST%<br>
<p>
- To view and approve the pixels, please visit here:
<a href='%VIEW_URL%'>Click Here...</a>
</p>

";
  
$label['mouseover_ad_template']=

"%ALT_TEXT%<br>
<font color='green'>%URL%</font><br>
%IMAGE%<br>
";
	

?>