<h1 class='track-pos' id='step-4-cors-configuration-optional'>Step 4 &#8211; CORS Configuration (Optional)</h1>
<p>If you intend to use Direct Upload functionality, you&#8217;ll need to set the CORS policy on your bucket.</p>
<h3 class='track-pos' id='step-4-1-bucket-properties'>Step 4.1 &#8211; Bucket Properties</h3>
<p>Log into the Amazon S3 Console and select your bucket. Click on the&nbsp;<strong>Permissions</strong>&nbsp;tab and then select&nbsp;<strong>CORS Configuration</strong></p>
<figure class="wp-block-image"><img src="https://i.imgur.com/UwXWGCU.png" alt="image.png"/></figure>
<h3 class='track-pos' id='step-4-2-set-the-cors-configuration'>Step 4.2 &#8211; Set the CORS Configuration</h3>
<p>Copy and paste the example CORS configration found below into the CORS configration editor in the S3 console.</p>
<div class="wp-block-codemirror-blocks-code-block code-block"><pre class="CodeMirror cm-s-react" data-setting="{&quot;mode&quot;:&quot;htmlmixed&quot;,&quot;mime&quot;:&quot;text/html&quot;,&quot;theme&quot;:&quot;react&quot;,&quot;lineNumbers&quot;:false,&quot;lineWrapping&quot;:true,&quot;readOnly&quot;:true}">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;CORSConfiguration xmlns=&quot;http://s3.amazonaws.com/doc/2006-03-01/&quot;&gt;
    &lt;CORSRule&gt;
        &lt;AllowedOrigin&gt;*&lt;/AllowedOrigin&gt;
        &lt;AllowedMethod&gt;GET&lt;/AllowedMethod&gt;
        &lt;AllowedMethod&gt;PUT&lt;/AllowedMethod&gt;
        &lt;AllowedMethod&gt;POST&lt;/AllowedMethod&gt;
        &lt;AllowedMethod&gt;HEAD&lt;/AllowedMethod&gt;
        &lt;MaxAgeSeconds&gt;3000&lt;/MaxAgeSeconds&gt;
        &lt;AllowedHeader&gt;*&lt;/AllowedHeader&gt;
    &lt;/CORSRule&gt;
&lt;/CORSConfiguration&gt;</pre></div>
<figure class="wp-block-image"><img src="https://i.imgur.com/9q4P1RW.png" alt="image.png"/></figure>
<p>Click&nbsp;<strong>Save</strong>.</p>
<p>Your CORS configuration has now been set and you should be able to perform direct uploads when you&#8217;ve enabled that feature.</p>
