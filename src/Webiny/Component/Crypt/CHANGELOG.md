CHANGELOG
=========

1.0.0-RC1
---------
* Initial version

1.2.
---------
* Removed the ircmaxell/CryptLit bridge do to outdate library
* Created Webiny/Crypt bridge
* Removed initialization vector from encrypt/decrypt methods, this is now handled internally
* If decrypt method fails, an exception is thrown, instead of return false