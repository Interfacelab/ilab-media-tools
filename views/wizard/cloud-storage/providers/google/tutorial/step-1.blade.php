<h1 id="setting-up-google-cloud-storage">Setting Up Google Cloud Storage</h1>
<p>Before you can use Google Cloud Storage with Media Cloud, you&#8217;ll first need to go through some basic steps to get going. It looks complicated, but if you stick with the steps outlined, you should be able to get through the process in about 10-15 minutes.</p>
<h2 class='track-pos' id='step-1-create-role'>Step 1 &#8211; Create Role</h2>
<p>The very first thing we need to do is create a role that defines what capabilities are going to be granted to the user of the bucket.</p>
<p>Log into your Google Cloud Platform console, navigate to the IAM section and select&nbsp;<strong>Roles</strong>&nbsp;from the side navigation. When the Roles page has loaded, click on&nbsp;<strong>Create Role</strong>.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/KRufzYn.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-1-1-role-properties'>Step 1.1 &#8211; Role Properties</h3>
<p>On the&nbsp;<em>Create Role</em>&nbsp;page, give the role a name and, optionally, set it&#8217;s ID to something that is going to be more clear for you. Set the&nbsp;<em>Role launch stage</em>&nbsp;to&nbsp;<em>General Availability</em>.</p>
<p>When you&#8217;ve done all that, click on&nbsp;<em>Add Permissions</em></p>
<figure class="wp-block-image"><img src="https://i.imgur.com/w4IAAYl.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-1-2-role-permissions'>Step 1.2 &#8211; Role Permissions</h3>
<p>A dialog window will appear that will allow us to assign the relevant permissions to our new role. To make it easier, filter the list of permissions to any role that has&nbsp;<em>Storage</em>&nbsp;in the title.</p>
<p>Once you&#8217;ve filtered the permissions, add the following permissions to our new role by click on the checkbox next to it. You will want to add the following:</p>
<ul><li>storage.buckets.get</li><li>storage.buckets.update</li><li>storage.objects.create</li><li>storage.objects.delete</li><li>storage.objects.get</li><li>storage.objects.getIamPolicy</li><li>storage.objects.list</li><li>storage.objects.setIamPolicy</li><li>storage.objects.update</li></ul>
<figure class="wp-block-image"><img src="https://i.imgur.com/0IwMIUu.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-1-3-create-the-role'>Step 1.3 &#8211; Create the Role</h3>
<p>Verify that the correct permissions have been assigned and click on&nbsp;<strong>Create</strong>.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/V2k4cCz.png" alt="image.png"/></figure>