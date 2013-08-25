#############################################################
INSTALLATION.

	Note: If you have CPanel based hosting, you may find these online
	installation instructions useful: 
	http://www.milliondollarscript.com/installation/tutorials/cpanel-install
	
	More help and support is available at the forum
	http://www.milliondollarscript.com/forum/
	
	Knowledge Base / FAQ
	http://www.milliondollarscript.com/faq/
	
#############################################################

1. Setup your database.

	Create a new database on your hosting account.
	(Make sure that your MYSQL database user has privileges to INSERT, UPDATE,
	REPLACE and LOCK TABLES.)
	
	If you need detailed instructions for setting up a MySQL database, then 
	these can be found in the manual / docs of your web hosting provider.
	
	Here is an overview of the steps:
	 a. Create a new database
	 b. Create a new database user
	 c. Add the new MySQL user to the database, giving the MySQL user all
	 privileges.


2. Upload all the script files to your server

	This is best done with an FTP client such as FileZilla.
	Please make sure to keep the original directory structure.
	
	Note: It is not always necessary to have full permissions on all server
	configurations.  Especially if your server is running fcgi or suExec.  In
	some cases you might be able to get away with using only more restrictive
	permissions.  For example 700, 750, 770, 755 for directories or for files
	600, 640, 660, 644.
	
	More info: 
	http://en.wikipedia.org/wiki/Chmod
	http://en.wikipedia.org/wiki/Filesystem_permissions
	
	
	Set write permissions for the following directories: (chmod 777)
	
	admin/temp/
	pixels/
	
	(Reason for the permissions: The script needs to be able to create images in
	the admin/temp/ and pixels/ directories)
	
	Set write permissions to config.php (chmod 666). The script needs to write
	to this file during installation and configuration.
	
	Tips:
	
	*nix via SSH: Change permissions from the command line using the "chmod" 
	command. eg. chmod 777 pixels
	
	*nix via FTP: Use an FTP client which allows you to set permissions. 
	eg. Right click on the file.
	
	See tutorials on www.milliondollarscript.com
	
	Windows: Make sure the web-server/php process can write to pixels/ and 
	admin/temp directories


3. Run the installation install.php script
	
	Go to http://yourwebsite.com/admin/install.php
	
	The installation script will install the database for you.
	Fill in the database details from step 1.
	
	Please delete the install.php file after successful installation.


4. Edit your configuration.
	
	Go to http://yourwebsite.com/admin/ and use 'ok' as the default password
	
	Here is what should be configured:
	
	 a. Main Config (Configuration menu.)
	 b. Languages (Edit labels, email templates, etc using the editing tool)
	 c. Payment Modules
	 d. Currencies
	
	 After configuration, go to 'Pixel Admin' and process pixels. Click on the
	 'Process Images(s)' button to generate the images.
	
	Additionally, you can set up your pixel inventory, prices, background 
	blending, etc.
	
	The admin has many possibilities, be creative ;)


5. Test the script.
	
	If running the script for the first time, go to Admin and process the pixels
	from the 'Pixel Admin' menu. 
	
	Then look at /index.php to see if the images were
	installed correctly. Finally, sign up as a new user and make a test order.
	You can set all the payment modules to demo mode if needed.


6. Create your site / integrate the script to your site.

	Look at index.php as an example of integration.


Note for 2C0 settings

	The script requires 2CO account version 2. Also you will need to set the 
	return URLs in your 2C0 account (under the Look and Feel). 2C0 agreement 
	allows only one account per website, so it is better to have the script 
	running on the same domain that you registered your 2C0 account with!


Note of PayPal settings

	To get the Instant Payment Notifications to work, ensure that 
	IPN is enabled for your PayPal account. Log into your paypal account and 
	click on 'Profile' -> 'Instant Payment Notification Preferences' and hit 
	Edit.  Enter the URL of your website. (Any URL will do, the script will 
	automatically tell PayPal the URL when an order is made)


-----------------------------

Knowledge Base / FAQ:

- There are over 100 articles in the knowledge base covering many topics.

http://www.milliondollarscript.com/faq/

-----------------------------


Tips for Use
##########################################################

> How to order without paying via Paypal / 2C0

1. Select some pixels to place them on the order. 
2. Confirm the order
3. Go into the admin/ and complete the order from the 'Orders Waiting' menu

Also, you can set an account to be in 'privileged mode, and the users
will never need to pay for the order.

##########################################################

> Sometimes people make orders but do not pay. What should I do?

The system keeps the pixels reserved for the client. If the client does not
complete the payment on time, you can delete the order from the
Admin. Deleting the order will free the pixels up for everyone else.
Non-paid orders are automatically cancelled after a number of days
as specified in config.

The script will also automatically delete unpaid orders after specified time.
The time can be specified in Main Config.

##########################################################

> Tips for Approving pixels & Processing the grid(s).

You should process your image(s) and publish it/them every time
AFTER approving your client's links.

##########################################################

> How do I log in to another user's account??

You can use the Admin password as the master password to log
in to a user's account.

The master password is the password that you have set in the config.
To log in, enter the users' username, and enter the master password.

##########################################################

> Can I edit the images for the blocks?

There are three different sets of images for blocks.
They are separated into the following directories.
You are welcome to edit them, but do not delete them - they
are needed by the script.


users/

block.png - available block
not_for_sale_block.png - Not for sale (white)
ordered_block.png - block on a confirmed order (orange)
reserved_block.png - reserved, but not confirmed yet. (yellow)
selected_block.png - Selected by user (green)
sold_block.png - completed order (red)

admin/

block.png - available block, used for preview
not_for_sale_block.png - the color of N.F.S. block for preview (green)
sold_block.png - sold block used for preview (red)

admin/temp/

block.png - available block, used for final processed grid  (white)
not_for_sale_block.png - N.F.S. block used for final processed grid (white)


##########################################################

> Having Problems generating the image?

99% of problems are due to the following:

1. Incorrect path and URL settings in Main Config. Please edit this carefully
and follow the recommendations

2. Permission problems. This is a common problem. Please check your permission 
settings.

3. Your PHP is too old. Check to make sure you have at least 5.2.x

4. The files were not uploaded with the original directory structure in tact.

5. Have you approved the pixels? Pixels need to be approved before they are
processed.

6. Don't delete any files from the admin/temp directory

######################################################

> I change header.php and footer.php, but there is no effect. Why?

The files forgot.php, signup.php, validate.php and the login form 
(login_functions.php) do not use the header and footer files because they are
external from the 'main' application.  Please modify the header tags directly in
these files. Note that the css file still affects these files.

######################################################

> I am getting 'page not found' error when i'm trying to process the pixels.

The cause is incorrectly compiled PHP on the server which is causing PHP to
crash when using the GD library bundled with PHP.
 
The problem is described here: http://bugs.php.net/bug.php?id=29568
See the 2nd last comment: 

 "pdflib uses a bundled version of png (old version), which makes php
 (using newer png) crash. Either remove --with-pdf when compiling PHP, or
 compile pdflib with "--with-pnglib --with-zlib" to make pdflib use the
 newer png version."


Please try to upgarde your PHP, or disable pdflib from PHP.

######################################################

> I am getting an error: "Fatal error: Call to undefined function imagecreate()"

If you are hosting on Windows then it means that you do not have the GD library 
turned on. 
 
Please see PHP the documentation to learn how to enable extensions in Windows.

Roughly, here is what you need to do:
 
1. Copy the file php_gd2.dll from your PHP's ext/ directory to where you have
your php extensions. (No need to copy if php_gd2.dll is in the extension dir
already. Look in php.ini for 'extension_dir' directive to find what is your
current extension directory)
 
2. Modify your php.ini and change the following line:
 
;extension=php_gd2.dll
 
to:
 
extension=php_gd2.dll
 
3. Restart your web server.

######################################################

> I would like to approve the links one at a time.

This is not possible.

Links are grouped by advertiser, and all links for that advertiser need
to be approved in one go. This makes things simple and faster to administer.

Why are the links grouped?

If you approve each link individually then the images might end up 
looking like Swiss cheese. This is because an image can have different 
links, and if you don't approve some, then you will end up having holes in the 
image.  Grouping them eliminates this problem.

########################################################

> My background image too big and I cannot upload it. What can I do?

PHP may limit upload file size to 2MB by default and will not let you to
upload a background image via the admin.

You can upload the image by FTP.

Upload the file to the admin/temp directory

Then, rename the file to background1.png (The number 1 is to identify that the 
background is to be used with the first grid. Rename to background2.png if you 
want to identify with the 2nd grid and so on)

You will be able to see the background in the Admin after it is uploaded.

########################################################

> My hosting company does not allow me to set chmod 777 permissions

Does the script show any problem, saying that it cannot write to a directory
or file? If it does not, then everything is OK.

If it cannot write to a directory or file and you can only set to 666, then 
try to do this:

Create empty files with the following names:

in admin/temp/

temp1.png
temp1.jpg
temp1.gif
background1.png

in pixels/

main1.png
main1.jpg
main1.gif

Then give chmod 666 to all the files above.

These files can be just plain text files. For example upload myfile.txt and 
rename to temp1.png, upload myfile.txt and rename to temp1.jpg, and so on...

The above example creates the files for grid 1. If you have a 2nd grid, replace
the 1 with a 2, and so on..

	Note: It is not always necessary to have full permissions on all server
	configurations.  Especially if your server is running fcgi or suExec.  In
	some cases you might be able to get away with using only more restrictive
	permissions.  For example 700, 750, 770, 755 for directories or for files
	600, 640, 660, 644.
	
	More info: 
	http://en.wikipedia.org/wiki/Chmod
	http://en.wikipedia.org/wiki/Filesystem_permissions

###################################################

> I get error SQL when registering a new user

Note: As of version 2.1 MySQL 5 is recommended.

This problem can also be solved by opening the file 'my.ini' in your MySQL 
install folder - the config file. And commenting out the command near the 
top that turns on the 'strict' mode.

>> -gl

####################################################

> I get errors that look like this: 
> Fatal error: Allowed memory size of 8388608 bytes exhausted
> (tried to allocate 3000 bytes)

Note: As of version 2.1 the default memory limit is set to 32M.

It looks like your PHP memory limit is set to 8MB and the script needs more. 
Please contact your hosting service to increase the limit.
Increasing to 12MB or more usually helps.

If you are running apache, you can try this:

Create a .htaccess file in your web directory (or modify if the file is 
there) and add the following line to the file:

php_value memory_limit 16M



If your hosting accound does not support .htaccess, add the following
line to include/functions.php file below the first <?php line:

ini_set('memory_limit', '12M');


If that does not work, then how big is your grid??
The script can get very thirsty if there are more than 10,000 blocks
per grid (1 million pixels) so check if you didn't set it to something
really big! Or...you may have a really big background image that's 
causing the trouble.

####################################################

> When I tried running the installation install.php script, 
> I got these notices. I'm unable to install database
>
> Notice: Use of undefined constant action - assumed 'action' 
> in d:\accounts\millionpix\admin\install.php on line 3


It looks like you have Notices turned on in your PHP php.ini file. 
This occurs when you first install php on your server, and the notices are 
turned on by default.

Notices should always be turned off in production environments.

Please edit your php.ini file and make sure that the error_reporting directive
is set to:

error_reporting  =  E_ALL & ~E_NOTICE & ~E_STRICT

Please make sure to restart your web server after editing the php.ini file.

###################################################

UPGRADES

Please check the website for updates.  http://www.milliondollarscript.com

Notices and instructions will be posted on the site when updates are released.
 
Unless otherwise stated, it is not required for you to upgrade.

If you want to keep your script upgradeable, please limit your customization of 
the script to only the following files: main.css, labels.php, header.php, 
footer.php and config.php
 
If you have made significant customizations, use a program such as WinDiff or
the very excellent WinMerge http://winmerge.sourceforge.net/
to help you with merging the new files.

######################################################################


/**
 * @version		$Id: README.txt 142 2011-08-17 21:54:59Z ryan $
 * @package		mds
 * @copyright	(C) Copyright 2010 Ryan Rhode, All rights reserved.
 * @author		Ryan Rhode, ryan@milliondollarscript.com
 * @license		This program is free software; you can redistribute it and/or modify
 *		it under the terms of the GNU General Public License as published by
 *		the Free Software Foundation; either version 3 of the License, or
 *		(at your option) any later version.
 *
 *		This program is distributed in the hope that it will be useful,
 *		but WITHOUT ANY WARRANTY; without even the implied warranty of
 *		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *		GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License along
 *		with this program;  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *		Million Dollar Script
 *		A pixel script for selling pixels on your website.
 *
 *		For instructions see README.txt
 *
 *		Visit our website for FAQs, documentation, a list team members,
 *		to post any bugs or feature requests, and a community forum:
 * 		http://www.milliondollarscript.com/
 *
 */


Acknowledgments

SMTP code is based on MIME E-Mail class, released under LGPL by Manuel Lemos.
AREA Map code contributed by Martin Diekhoff,  http://www.onecentads.com/

Thank you to everyone else who helps out!