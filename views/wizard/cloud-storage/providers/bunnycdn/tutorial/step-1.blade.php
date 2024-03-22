<h1 id="setting-up-bunnycdn">Setting Up Bunny CDN</h1>
<h2 class='track-pos' id='step-1-create-bucket'>Step 1 &#8211; Create Storage Zone</h2>
<p>Get started by logging into the Bunny CDN dashboard.&nbsp; If you don't have an account with Bunny CDN, you can <a href="https://bunny.net?ref=33lsyjqfr3" target="_blank" rel="noopener">create one here</a>.</p>
<p>Once in the dashboard, it should look something like this:</p>
<figure><a href="https://docs-media.s3.ap-southeast-1.amazonaws.com/0e352206-8817-4fdd-9268-5dd179e73b64.png" target="_blank" rel="noopener"><img src="https://docs-media.s3.ap-southeast-1.amazonaws.com/0e352206-8817-4fdd-9268-5dd179e73b64.png" alt="Step 1  - Bunny CDN Dashboard"></a></figure>
<p>Click on the <strong>Storage</strong> link on the left hand side (#1) and then click on <strong>Add Storage Zone</strong> (#2).&nbsp; When you click on that you'll be presented with this screen:</p>
<figure><a href="https://docs-media.s3.ap-southeast-1.amazonaws.com/ec6aebc0-60de-4c77-97e3-881e2be4a2b2.png" target="_blank" rel="noopener"><img src="https://docs-media.s3.ap-southeast-1.amazonaws.com/ec6aebc0-60de-4c77-97e3-881e2be4a2b2.png" alt="Step 2  - Create Storage Zone"></a></figure>
<p>In the&nbsp;<strong>Storage Zone Name</strong> field supply a name for your zone.&nbsp; You can use alphanumeric characters, dashes and underscores but no spaces.</p>
<p>Down a little further click on the <strong>Main Storage Region</strong>.&nbsp; This region should be closest to wherever your WordPress server is.</p>
<p>Next, you'll need to select one or more regions to replicate your data to.&nbsp; This step is optional but highly recommended.</p>
<p>Finally, scroll to the bottom of the page and click on the big orange&nbsp;<strong>Add Storage Zone</strong> button.</p>
<h3 class='track-pos' id='step-1a-get-api-key'>Get Your API Key</h3>
<p>After you've clicked that button, you'll be taken to your newly created storage zone's details page.</p>
<figure><a href="https://docs-media.s3.ap-southeast-1.amazonaws.com/0e2bab57-55c8-484b-8a65-a8c2221e7d3c.png" target="_blank" rel="noopener"><img src="https://docs-media.s3.ap-southeast-1.amazonaws.com/0e2bab57-55c8-484b-8a65-a8c2221e7d3c.png" alt="Step 2 - Get API Key"></a></figure>
<p>To get our API key, click on the&nbsp;<strong>FTP &amp; API Access</strong> link.&nbsp; Towards the bottom of that screen you'll see a section marked&nbsp;<strong>Password</strong>.&nbsp; Click on the copy icon next to the first entry marked&nbsp;<strong>Password.</strong>&nbsp; This is your API key.&nbsp; &nbsp;Store it somewhere safe until we set up Media Cloud further down the tutorial.</p>
<p>After you've saved your API key, we're going to need to create a pull zone.&nbsp; This is the CDN part of Bunny CDN.&nbsp; Click on the black&nbsp;<strong>Connect Pull Zone</strong> button in the upper right to move onto the next step.</p>