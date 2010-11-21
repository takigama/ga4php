<?php
/*
 * This is the web component of the GA4PHP radius server. This web app should be able to configure freeradius and itself.
 * 
 * This app will try to do the following:
 * 1) initialise tokens
 * 2) pull accounts from some backend (such as AD)
 * 3) allow users to self-enroll.
 * 
 * I wonder if we can store data in the backend database itself? that would be interesting
 * then user admin would be less disconnected. I.e. if a user was deleted from AD, their token
 * data should disappear with them.
 */

?>