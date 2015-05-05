# MiniGal Nano NG

## About this project
This project is a fork of [MiniGal Nano](http://www.minigal.dk/minigal-nano.html "MiniGal Nano by Thomas Rybak") written by [Thomas Rybak](http://www.minigal.dk/)

As the projects last update is end 2010 I decided to fork it and extend it for my needs.

The project is available under the terms of the [Creative Commons Attribution-Share Alike 2.5 Denmark License](http://creativecommons.org/licenses/by-sa/2.5/)
For commercial use, please see the [MiniGal project page](http://www.minigal.dk/commercial-license.html)

Thomas Rybak, if you read this and disagree with this project, please contact me to sort this out.


## Features
See a list of original features [here](http://www.minigal.dk/minigal-nano.html)


## Features of MiniGal Nano NG

### Implemented
* thumbnail caching
* reduced-size image preview
* HTML5 video support (.mp4)
* fullscreen view
* semantic URLs
* other minor enhancements

### To come
* better templates

## Semantic URLs

Some server configuration is required to activate the Semantic URLs feature. It will give you the following advantages:
* Human-friendly URLs
* Transparent path that conveniently points to the actual directory location on your server. E.g. http://example.com/minigal-nano-ng/?dir=newdir/christmas/ with actual directory location /var/www/html/minigal-nano-ng/photos/newdir/christmas/ would translate into http://example.com/minigal-nano-ng/photos/newdir/christmas/.
* Fail-safe. Even if you remove PHP from the system, your gallery will still be reachable with the old URL with the index provided by your web server.

### Apache

If you are using Apache web server, then all necessary configuration is already done in the supplied .htaccess file. You just need to enable it with "AllowOverride All" for the corresponding Directory in your apache configuration file. You will also need mod_rewrite module installed and loaded, which is usually default.

### Nginx

Please add the following lines into your nginx configuration for the corresponding location (note that you may need to change the rewrite expressions to explicitly point to the location if you have "/photos/", "/small/" or "/thumb/" in your location path):

	location /minigal-nano-ng/ {
		if (-d $request_filename) {
			rewrite ^(.*[^/])$ $1/ permanent;
			rewrite ^(.*?)/photos/(.*)$ $1/?rewrite=1&dir=$2 last;
		}
		rewrite ^(/.*?)/small/(.*)$ $1/getimage.php?mode=small&filename=photos/$2 last;
		rewrite ^(/.*?)/thumb/(.*)$ $1/getimage.php?mode=thumb&filename=photos/$2 last;
		if ($query_string ~ "^dir=([^&]*)$") {
			set $dir $1;
			rewrite ^(/.*)/$ $1/photos/$dir? permanent;
		}
		if ($query_string ~ "^dir=([^&]*)&(.*)$") {
			set $dir $1;
			set $extraargs $2;
			rewrite ^(/.*)/$ $1/photos/$dir?$extraargs? permanent;
		}
		if ($query_string !~ "^rewrite=1") {
			rewrite ^(/((?!photos).)*)/$ $1/photos/ permanent;
		}
	}
