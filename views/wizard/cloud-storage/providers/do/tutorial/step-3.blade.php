<h1 class='track-pos' id='step-3-cors-configuration-optional'>Step 3 &#8211; CORS Configuration (Optional)</h1>
<p>If you intend to use Direct Upload, you&#8217;ll need to configure CORS on your Space to allow it.</p>
<p>Navigate to your Space, click on the&nbsp;<strong>Settings</strong>&nbsp;tab and then click on the&nbsp;<strong>Add</strong>button in the&nbsp;<strong>CORS Configurations</strong>&nbsp;section.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/iGyc2vO.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-3-1-cors-properties'>Step 3.1 &#8211; CORS Properties</h3>
<p>In the pop-up dialog, you&#8217;ll need to specify the following:</p>
<ul><li>For the&nbsp;<strong>Origin</strong>, enter in the base URL for your site here. If you have a development and/or staging environment, you&#8217;ll create separate CORS configurations for each.</li><li>For&nbsp;<strong>Allowed Methods</strong>, check the checkboxes for GET, PUT, POST and HEAD. DELETE is optional but not needed for Direct Uploads.</li><li>For&nbsp;<strong>Allowed Headers</strong>, set this to&nbsp;<code>*</code></li></ul>
<figure class="wp-block-image"><img src="https://i.imgur.com/pOQPXfk.png" alt="image.png"/></figure>
<p>Click on&nbsp;<strong>Save Options</strong>&nbsp;to save this configuration. Repeat this procedure for your development and staging environments.</p>
<h3 class='track-pos' id='step-3-2-verify-cors'>Step 3.2 &#8211; Verify CORS</h3>
<p>After you&#8217;ve saved the options, verify that the CORS Configuration is set and correct.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/Q2unTUw.png" alt="image.png"/></figure>
