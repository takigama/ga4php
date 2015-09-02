# GA4PHP RoadMap #

GA4PHP is originally a simple php class for authenticating oath tokens such as the google authenticator (availble on android, iphone and others). The project has since spawned an authentication system which is in the process of a re-write.

I originally wrote v1 as a proof of concept to see how viable it was and after finding the task not too onerous i've decided to continue on with it and do a rewrite from scratch.

The new version is heavily focused on Active Directory co-existence (but not dependence) at the present, but will also include a simple internal db and other forms of backend authentication. This new version shouldn't even result in a complete rewite.

# Currently Implemented #
  1. GA4PHP class which provides a simple interface and simple data management
  1. lots of example provided for ways of provisioning users in various backends
  1. Can support non-google authenticators where the key is provided in some format that leads to hex


# Current Problems #
  1. GA4PHP class needs exceptions
  1. generating QRcodes - currently only really works on linux based stuff and uses the qrencode binary
  1. many possible lock-up scenarios in the authserver/authclient relationship
  1. would love to see people actually using it for something interesting


# Upcoming Features #
  1. add a soap connector for auth/authclient
  1. forcing ssl
  1. QRCode generator entirely in PHP (yeah right...)
  1. mOTP