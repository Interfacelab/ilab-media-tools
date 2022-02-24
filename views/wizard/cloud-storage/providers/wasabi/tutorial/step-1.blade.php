<h1 id="setting-up-wasabi">Setting Up Wasabi</h1>
<h2 class='track-pos' id='step-1-create-iam-user'>Step 1. Create IAM User</h2>
<p>The first thing we need to do when setting up Wasabi is to create a set of credentials that Media Cloud can use to access Wasabi.</p>
<h3 class='track-pos' id='step-1-1-create-user'>Step 1.1 Create User</h3>
<p>Navigate to the IAM section of the&nbsp;<a href="https://console.wasabisys.com/#/users">Wasabi console</a>. Click on&nbsp;<strong>Users</strong>&nbsp;in the left hand navigation and then click on&nbsp;<strong>Create User</strong>.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/On3nJQQ.jpg" alt="Step 1.1 - Create User"/></figure>
<h3 class='track-pos' id='step-1-2-user-name-and-access'>Step 1.2 User Name and Access</h3>
<p>In the&nbsp;<strong>Add User</strong>&nbsp;pop up, set the user name to anything you like and make sure that the&nbsp;<strong>Programmatic (create API key)</strong>&nbsp;option is checked.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/NArfvrQ.png" alt="Step 1.2 - User Name and Access"/></figure>
<p>Click&nbsp;<strong>Next</strong>&nbsp;and on the&nbsp;<strong>Groups</strong>&nbsp;section click&nbsp;<strong>Next</strong>&nbsp;again.</p>
<h3 class='track-pos' id='step-1-3-policies'>Step 1.3 Policies</h3>
<p>In this section of&nbsp;<strong>Add User</strong>&nbsp;make sure to select&nbsp;<strong>WasabiReadOnlyAccess</strong>&nbsp;and&nbsp;<strong>WasabiWriteOnlyAccess</strong>&nbsp;by clicking on the + sign. Click on&nbsp;<strong>Next</strong>&nbsp;when you&#8217;ve done this.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/PKvxjyl.jpg" alt="Step 1.3 Policies"/></figure>
<h3 class='track-pos' id='step-1-4-review'>Step 1.4 Review</h3>
<p>Finally, review your choices and click on&nbsp;<strong>Create User</strong>&nbsp;when everything looks OK.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/tJsaQvI.png" alt="Step 1.4 Review"/></figure>
<p>Once the user is created you will be shown the Access and Secret Key. Click obn&nbsp;<strong>Download CSV</strong>&nbsp;to download these keys to your computer. Keep this file in a safe and secure place. We will be referring to this CSV file in later steps.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/LLdV7TS.png" alt="Step 1.4 Review"/></figure>
