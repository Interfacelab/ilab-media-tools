<h1 id="setting-up-digitalocean-spaces">Setting Up DigitalOcean Spaces</h1>
<p>Before you can use DigitalOcean Spaces with Media Cloud, you&#8217;ll first need to go through some basic steps to get going. Thankfully, DigitalOcean is the easiest to setup of the major cloud storage providers. However, there are a few gotchas, so if you follow this guide you&#8217;ll have everything up and running correctly very quickly.</p>
<h2 class='track-pos' id='step-1-create-space'>Step 1 &#8211; Create Space</h2>
<p>Log into your DigitalOcean account and from the&nbsp;<strong>Create</strong>&nbsp;drop-down, select&nbsp;<strong>Spaces</strong>.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/6d6bw7W.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-1-1-configuring-the-space'>Step 1.1 &#8211; Configuring the Space</h3>
<p>On the next screen you&#8217;ll have various options you&#8217;ll need to set to propertly configure your space.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/xppVKhH.png" alt="image.png"/></figure>
<p>In the&nbsp;<strong>Choose a datacenter region</strong>&nbsp;you&#8217;ll want to select a region closest to you (if using Direct Upload functionality) or closest to where your servers are geographically hosted. If you are hosting with DigitalOcean, then you&#8217;ll likely select the same datacenter as your servers, if Spaces is available at that datacenter.</p>
<p>You should enabled the CDN and select a cache timing that works best for your situation.</p>
<p>You should enable&nbsp;<strong>Restrict File Listing</strong>&nbsp;to prevent people from listing the contents of your Space.</p>
<p>Finally, give the Space a name and click&nbsp;<strong>Create a Space</strong></p>
<h3 class='track-pos' id='step-1-2-determining-the-endpoint'>Step 1.2 &#8211; Determining the Endpoint</h3>
<p>Media Cloud needs to know the endpoint for your Space for it to work properly. After you&#8217;ve created your space, click on the&nbsp;<strong>Settings</strong>&nbsp;tab and look for the&nbsp;<strong>Endpoint</strong>&nbsp;section. Make note of this value as we will be using it later when setting up Media Cloud.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/d6Zt0Hb.png" alt="image.png"/></figure>
