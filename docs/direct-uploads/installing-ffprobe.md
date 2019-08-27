# Installing FFProbe
Installing FFProbe on your server is not a particularly trivial endevour.  Each server setup is going to be different and there is no way for us to document each different way.  Instead, we will document how we do it on WordPress sites we build for clients.  You will need to make adjustments based on what OS you are using, what web server, PHP setup, etc.

If you are on managed hosting, installing FFProbe might not even be a possibility and you will need to contact your hosting provider for help on getting it setup.

And, just to reiterate, this document is not a HOW-TO, it's simply *how we do it*.

Our typical server setup:

- Ubuntu 16.04 or 18.04
- Nginx
- PHP 7.3 with PHP-FPM

## Install FFMpeg
The first step is installing the `ffmpeg` package which contains `ffprobe` and related libraries.

```bash
sudo apt-get install ffmpeg
```

## Isolate FFProbe
We need to stick the `ffprobe` binary in a location that we can safely give PHP access to.  For us, we make directory in `/srv/www` called `bin` and then we copy the `ffprobe` binary to that location.

```bash
sudo mkdir /srv/www/bin
sudo chown web:www-data /srv/www/bin
cp /usr/bin/ffprobe /srv/www/bin/
```

In the above bit, we create the directory to hold the binary, we change the owner to the user and group that PHP runs in and then we copy the `ffprobe` binary to the directory.

## Configure PHP-FPM
We'll now need to make some changed to PHP-FPM's config to allow PHP to have access to that binary.  Our PHP-FPM config is found in `/etc/php/7.3/fpm/pool.d/wordpress.conf`.

As a standard practive, we use `open_basedir` to enforce the directories on the web server that PHP has access to.  In the config for PHP-FPM it looks like:

```ini
php_admin_value[open_basedir] = /srv/www/:/tmp
```

So in our FPM config, PHP has access to the root web server directory and to `/tmp`.  We don't need to change this line because we copied `ffprobe` to a subdirectory of `/srv/www`.

We do, however, need to add that path to the PATH environment variable.  Further down in our PHP-FPM config, we'll add this line:

```ini
env[PATH] = /srv/www/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
```

You'll notice our binary directory `/srv/www/bin` is the first in the list.  The other directories come from:

```bash
cat /etc/environment
```

At this point we can save our config.

## Restart PHP-FPM
To finish up, we'll restart PHP-FPM and now PHP has access to `ffprobe`.
