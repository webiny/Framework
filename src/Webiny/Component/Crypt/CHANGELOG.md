CHANGELOG
=========

1.3
---------
* Improved the internal driver
* The component no longer accepts constructor arguments
* Component settings are handled internally
* Changed the default block mode from cbc to cfb
* Refactored component trait, the component is no longer used as a service

1.2.
---------
* Removed the ircmaxell/CryptLit bridge do to outdate library
* Created Webiny/Crypt bridge
* Removed initialization vector from encrypt/decrypt methods, this is now handled internally
* If decrypt method fails, an exception is thrown, instead of return false

1.0.0-RC1
---------
* Initial version