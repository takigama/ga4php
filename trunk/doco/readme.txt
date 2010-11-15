The GA4PHP Project
==================

GA4PHP means Google Authenticator for PHP.

The purpose of this "library" is to provde a convienient and
hopefully simple way of provisioning and authenticating users
using the Google Authenticator mechanism. For now this lib
will only support GA's hotp methods.


Why?
====

Mostly cause I can. Recently i've been heavily interesting in
what is "out there" for FOSS based authentication projects and
the Google Authenticator is based on OATH - HOTP/TOTP. It also
has a few kewl features, like providing its secret key via QR
codes. But looking at the App's available for smartphones
(android, iphone, etc), the google auth app was the only one
that fullfilled some realistic criteria - easy to use, easy to
provision.

READ THIS BIT
=============

in the example page, i send a url off to google charts to create
the QRCode. NEVER EVER EVER EVER EVER do this. I do it cause for
the example it doesnt matter, and if i find a better way of doing
it i'll do it. BUT creating a qrcode on a page aint terribly easy.
The point is, that QR code is a URL containing the tokens SECRET
KEY and should remain secret. You can generate qrcodes anyway you
like, BUT MAKE SURE ITS SECURE (i.e. never save them on the FS,
and send them all over ssl).


How?
====

Ultimately, this is just a library with a web based example,
the purpose of which is to integrate into your exisint applications,
those that are PHP.


Complications
=============

The only real complication i've had so far is that alot of the
HOTP based keys out there provide the secret key as HEX where
google choose to use base32, of which there exist no single
or simple implementation. So I wrote my (rather terrible so far)
own as all i really want to do is convert from base32 back to
hex, this I do by converting to binary first... Lets how thats
going to work in the long run. The sad thing is is that if I
were writing it in C/C++/etc, bit math is easy and while im sure
its not too different in PHP, I've never done it, so this seeemed
easier.   


Acknowledgements
================

Google, for producing google authenticator
The guys at mOTP who got me all excited about token authenticators
The guys on this page who spell out how to do hotp
	http://php.net/manual/en/function.hash-hmac.php