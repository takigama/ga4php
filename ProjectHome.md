This project is a set of php code designed to work with Google's Authenticator app system and/or support most (if not all) OATH based HOTP/TOTP systems.

Primarily its a class you would use to extend some existing application in order to use the google auth app as a 2nd factor for authentication

It also includes a authentication server presented via radius (thanks to freeradius), with a provisioning engine that is not yet production ready but is usable.

If you find it interesting, i'd love to hear from you. If your using it, i'd love to hear from you even more!

Acknowledgements:
  1. The people who give the code examples for hash\_hmac for hotp on the php website (http://php.net/manual/en/function.hash-hmac.php)
  1. Google for creating the google authenticator
  1. All the people involved in HOTP/TOTP and OATH
  1. The people over at mOTP who originally got me interested in the whole concept to begin with (http://motp.sourceforge.net/).
  1. The people at freeradius (http://freeradius.org/)