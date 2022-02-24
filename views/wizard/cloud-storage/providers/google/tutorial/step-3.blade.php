<h1 class='track-pos' id='step-3-create-the-bucket'>Step 3 &#8211; Create the Bucket</h1>
<p>The final step is to create the bucket we&#8217;ll be using with Media Cloud.</p>
<p>In the Google Cloud console, navigate to the Storage Browser.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/Ffrg3KY.png" alt="image.png"/></figure>
<p>When the Storage Browser loads, click on&nbsp;<strong>Create Bucket</strong>.</p>
<h2 class='track-pos' id='step-3-1-bucket-properties'>Step 3.1 &#8211; Bucket Properties</h2>
<p>In the&nbsp;<strong>Creat a bucket</strong>&nbsp;screen, give the bucket a name, determine the storage class and select the location of the bucket. Recommended to pick a location that is close to you (if using Direct Uploading) or close to your server.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/5wKMGKW.png" alt="image.png"/></figure>
<p>When done, click&nbsp;<strong>Create</strong>.</p>
<h2 class='track-pos' id='step-3-2-bucket-permissions'>Step 3.2 &#8211; Bucket Permissions</h2>
<p>Once the bucket has been created, you&#8217;ll be viewing the bucket in the storage browser. We&#8217;ll need to assign the service account to the bucket to give it access.</p>
<p>Click on&nbsp;<strong>Permissions</strong>&nbsp;and then click on&nbsp;<strong>Add members</strong>.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/okPjE6h.png" alt="image.png"/></figure>
<h2 class='track-pos' id='step-3-3-assign-user'>Step 3.3 &#8211; Assign User</h2>
<p>In the modal dialog that appears, simply enter the name of the user we created in Step 2 and then select the role we created in Step 1. Click on&nbsp;<strong>Add</strong>&nbsp;to finish.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/uG6v9Ft.png" alt="image.png"/></figure>
<p>We are now done setting things up in Google Cloud. The next step is setting up Media Cloud.</p>
<h3 class='track-pos' id='step-3-4-allow-public-access-optional'>Step 3.4 &#8211; Allow public access (Optional)</h3>
<p>If you are using a bucket with the &#8216;Bucket Policy Only&#8217; option set, you need to grant permission to access the items publicly.&nbsp;</p>
<p>Click on&nbsp;<strong>Permissions</strong>&nbsp;and then click on&nbsp;<strong>Add members</strong>.</p>
<p>In the modal dialog that appears, enter&nbsp;<strong>allUsers</strong>, and select the&nbsp;<strong>Storage Object Viewer</strong>&nbsp;option. Click&nbsp;<strong>Save.</strong></p>
