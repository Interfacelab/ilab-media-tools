<h1 id="setting-up-amazon-s3">Setting Up Amazon S3</h1>
<p>Before we can use Media Cloud, you&#8217;ll first need to go through some basic steps to create a bucket on S3 and a user account we can use to access that bucket.</p>
<p>Using the Amazon console can be a little intimidating at first, but if you stick to these steps you should be able to breeze right through it.</p>
<h2 class='track-pos' id='step-1-create-an-s3-bucket'>Step 1. Create an S3 Bucket</h2>
<p>The first thing we&#8217;ll need to do is create the bucket we&#8217;re going to use for storing our media and files. If you haven&#8217;t already, log into your Amazon AWS account:&nbsp;<a href="https://console.aws.amazon.com/">Amazon AWS Console</a>.</p>
<p>Once you&#8217;ve logged in, select the S3 service. When the S3 Console has loaded, select&nbsp;<strong>Create Bucket</strong>&nbsp;to get started:</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/21AuEHf.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-1-1-bucket-name-region'>Step 1.1 &#8211; Bucket Name/Region</h3>
<p>When you click on&nbsp;<strong>Create Bucket</strong>, you&#8217;ll be presented with a multi-step wizard dialog. On the first step of this wizard, enter in the following information:</p>
<ul><li>Bucket Name</li><li>Region</li></ul>
<figure class="wp-block-image"><img src="https://i.imgur.com/wMsuAkZ.png" alt="image.png"/></figure>
<p>You should select a region that is closest geographically to either your server or to yourself (if using Direct Uploads).</p>
<p>Once you&#8217;ve specified the name and region, click on&nbsp;<strong>Next</strong>&nbsp;to continue to the next screen.</p>
<h3 class='track-pos' id='step-1-2-bucket-properties'>Step 1.2 &#8211; Bucket Properties</h3>
<p>Generally speaking, you can skip this page by clicking&nbsp;<strong>Next</strong>.</p>
<h3 class='track-pos' id='step-1-3-bucket-permissions'>Step 1.3 &#8211; Bucket Permissions</h3>
<p>For this screen, it&#8217;s important that you uncheck the following options:</p>
<ul><li>Uncheck&nbsp;<strong>Block new public ACLs and uploading public objects</strong></li><li>Uncheck&nbsp;<strong>Remove public acess granted through public ACLs</strong></li><li>Uncheck&nbsp;<strong>Block public and cross-account access if bucket has public policies</strong></li><li>Uncheck&nbsp;<strong>Block new public bucket policies</strong></li></ul>
<figure class="wp-block-image"><img src="https://i.imgur.com/thfCIhv.png" alt="image.png"/></figure>
<p>Click on&nbsp;<strong>Create Bucket</strong>&nbsp;to create your bucket.</p>
<h3 class='track-pos' id='step-1-4-transfer-acceleration-optional'>Step 1.4 &#8211; Transfer Acceleration (Optional)</h3>
<p>It&#8217;s highly recommended that you enable transfer acceleration on your bucket to improve upload and download speeds. There will be an extra charge incurred for having it enabled, however.</p>
<p>To enable Transfer Acceleration, select your bucket in the S3 console and select the&nbsp;<strong>Properties</strong>&nbsp;tab. Scroll down until you find a panel named&nbsp;<strong>Transfer acceleration</strong>. Click on it to expand it and select the&nbsp;<strong>Enabled</strong>&nbsp;option.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/gfYcp2H.png" alt="image.png"/></figure>
<p>Click on&nbsp;<strong>Save</strong>&nbsp;to save the setting.</p>
