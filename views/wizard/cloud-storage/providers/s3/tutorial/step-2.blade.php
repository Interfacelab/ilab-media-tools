<h1 class='track-pos' id='step-2-create-policy'>Step 2 &#8211; Create Policy</h1>
<p>For our next step, we&#8217;ll be creating a policy that controls how the AWS user account we&#8217;ll be creating in Step 3 accesses our bucket. We want to define the narrowest set of permissions possible to keep things secure.</p>
<p>Select&nbsp;<strong>IAM</strong>&nbsp;from the services menu in the AWS Console. Once the IAM console has loaded, click on&nbsp;<strong>Policies</strong>&nbsp;on the left hand side of the page and then click on&nbsp;<strong>Create Policy</strong>&nbsp;to get started.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/sV4BLCZ.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-2-1-define-the-policy'>Step 2.1 &#8211; Define the Policy</h3>
<p>When you click on&nbsp;<strong>Create Policy</strong>&nbsp;a wizard dialog will appear.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/kDC5D7G.png" alt="image.png"/></figure>
<p>Click on the&nbsp;<strong>JSON</strong>&nbsp;tab and paste the following JSON into it:</p>
<div class="wp-block-codemirror-blocks-code-block code-block"><pre class="CodeMirror cm-s-react" data-setting="{&quot;mode&quot;:&quot;javascript&quot;,&quot;mime&quot;:&quot;application/json&quot;,&quot;theme&quot;:&quot;react&quot;,&quot;lineNumbers&quot;:false,&quot;lineWrapping&quot;:true,&quot;readOnly&quot;:true}">{
    &quot;Version&quot;: &quot;2012-10-17&quot;,
    &quot;Statement&quot;: [
        {
            &quot;Effect&quot;: &quot;Allow&quot;,
            &quot;Action&quot;: [
                &quot;s3:DeleteObjectTagging&quot;,
                &quot;s3:ListBucketMultipartUploads&quot;,
                &quot;s3:DeleteObjectVersion&quot;,
                &quot;s3:ListBucket&quot;,
                &quot;s3:DeleteObjectVersionTagging&quot;,
                &quot;s3:GetBucketAcl&quot;,
                &quot;s3:ListMultipartUploadParts&quot;,
                &quot;s3:PutObject&quot;,
                &quot;s3:GetObjectAcl&quot;,
                &quot;s3:GetObject&quot;,
                &quot;s3:AbortMultipartUpload&quot;,
                &quot;s3:DeleteObject&quot;,
                &quot;s3:GetBucketLocation&quot;,
                &quot;s3:PutObjectAcl&quot;
            ],
            &quot;Resource&quot;: [
                &quot;arn:aws:s3:::YOURBUCKET/*&quot;,
                &quot;arn:aws:s3:::YOURBUCKET&quot;
            ]
        },
        {
            &quot;Effect&quot;: &quot;Allow&quot;,
            &quot;Action&quot;: &quot;s3:HeadBucket&quot;,
            &quot;Resource&quot;: &quot;*&quot;
        }
    ]
}</pre></div>
<p><strong>IMPORTANT!</strong>&nbsp;Make sure you replace&nbsp;<em>YOURBUCKET</em>&nbsp;with the name of the bucket you created in Step 1.</p>
<p>Click on&nbsp;<strong>Review Policy</strong>&nbsp;to continue.</p>
<h3 class='track-pos' id='step-2-2-name-the-policy'>Step 2.2 &#8211; Name the Policy</h3>
<p>On the next screen, give the policy a name and description that is meaningful to you.</p>
<figure class="wp-block-image"><img src="https://i.imgur.com/cQsurjz.png" alt="image.png"/></figure>
<p>Click on&nbsp;<strong>Create Policy</strong>&nbsp;to create the policy.</p>
