wix_php_sdk
===========

## Synopsis

This is a bare bones wix api.  WIX does not have an official api. 

This PHP5 API covers these apis: 

http://dev.wix.com/docs/wixhive/contacts

http://dev.wix.com/docs/wixhive/using-the-rest-api

http://dev.wix.com/docs/wixhive/rest-api

## Code Example

`$wix = new Wix(YOUR_KEY, YOUR_SECRET);`

`$wix->get_contacts($instance_id);`

For getting your instance ID please see: http://dev.wix.com/docs/infrastructure/app-instance-id/

## License

It is licensed under WTFPL. 

https://en.wikipedia.org/wiki/WTFPL
# wix_php_sdk
