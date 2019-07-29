# Batch Processing
Media Cloud can perform a variety of tasks asynchronously in the background.  The tasks include uploading media to cloud storage, processing media with computer vision, rebuilding thumbnails and a variety of other things.

However, due to the wide variety of different hosting configurations used in the WordPress world, background processing may have to be done in different ways for it to work.  While WordPress does offer a system for running tasks, called WP-Cron, it's not very robust and isn't guaranteed to work on every WordPress install.  In fact, most modern WordPress installs disable it because it's a pretty poorly designed system.

The background processing Media Cloud employs is mostly a kludgey hack.  However, this kludgey hack is used by a lot of WordPress plugins to accomplish the same goal of being able to schedule and run tasks in the background.  But, as mentioned before, each hosting setup is different.  To that end, Media Cloud has a separate set of settings for Batch Processing that will allow you to tweak it to work on your system.

## Batch Processing Settings
### Verify SSL
This is probably the most common issue on managed hosting and VPS setups.   Setting this to **Off** is the first step we recommend.

Technically speaking, what happens is that when Media Cloud runs a batch, it will attempt to make a background ajax call from your WordPress server to your WordPress server.  A lot of managed hosting providers will have mapped your domain to 127.0.0.1, or to localhost, and the SSL certificates are further up their network stack at the reverse proxy level.  Because your server is actually HTTP (at the localhost level), trying to verify SSL will fail because SSL verification actually happens at the reverse-proxy level.

### Connection Timeout
This is the number of section to wait for a connection to occur.  If you use the System Compatibility Tool and see an error complaining about `cURL error 2x`, try setting to 5 to 10 seconds or more.

### Timeout
This is the number of seconds to wait for a response once a connection has happened.  Similar to **Connection Timeout** if you see `cURL error 2x` and you've changed the **Connection Timeout** setting, set this anything from 0.2 up to 10 to see if it helps.

### Skip DNS
DNS is sometimes a big issue on cheaper managed hosting providers.  Enabling this option will skip DNS resolution all together.  

### Process in Background
If all else fails, you can disable background processing altogether.  You will still be able to perform many batch related tasks, but you will be required to keep the page running the tasks open in your browser for the duration of the process.