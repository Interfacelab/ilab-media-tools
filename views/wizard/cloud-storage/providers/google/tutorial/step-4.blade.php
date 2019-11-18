<h1 class='track-pos' id='step-4-cors-configuration-optional'>Step 4 &#8211; CORS Configuration (Optional)</h1>
<p>If you are planning on using the Direct Upload feature, you&#8217;ll need to set a CORS policy on your bucket in Google Cloud Storage.</p>
<p>To do this, you&#8217;ll need to install the&nbsp;<code>gsutil</code>&nbsp;command line tool. You can download and install that from here:&nbsp;<a href="https://cloud.google.com/storage/docs/gsutil_install">Download and Install gsutil</a>. Be sure to follow the steps to setup authentication.</p>
<h3 class='track-pos' id='step-4-1-create-the-cors-json-file'>Step 4.1 &#8211; Create the CORS JSON file</h3>
<p>You&#8217;ll need to define your CORS policy in a JSON file. Use this sample policy as a starting point:</p>
<div class="wp-block-codemirror-blocks-code-block code-block"><pre class="CodeMirror cm-s-react" data-setting="{&quot;mode&quot;:&quot;javascript&quot;,&quot;mime&quot;:&quot;application/json&quot;,&quot;theme&quot;:&quot;react&quot;,&quot;lineNumbers&quot;:false,&quot;lineWrapping&quot;:true,&quot;readOnly&quot;:true}">[
    {
      &quot;origin&quot;: [&quot;https://yourdomain.com&quot;],
      &quot;responseHeader&quot;: [&quot;*&quot;],
      &quot;method&quot;: [&quot;GET&quot;, &quot;HEAD&quot;, &quot;DELETE&quot;, &quot;POST&quot;, &quot;PUT&quot;, &quot;OPTIONS&quot;],
      &quot;maxAgeSeconds&quot;: 3600
    }
]</pre></div>
<p>Be sure to change&nbsp;<code>https://yourdomain.com</code>&nbsp;to your actual domain, and possibly add more domains for your dev or staging environments.</p>
<h3 class='track-pos' id='step-4-2-set-the-cors-policy'>Step 4.2 &#8211; Set the CORS Policy</h3>
<p>Crack open a terminal and type the following:</p>
<div class="wp-block-codemirror-blocks-code-block code-block"><pre class="CodeMirror cm-s-react" data-setting="{&quot;mode&quot;:&quot;shell&quot;,&quot;mime&quot;:&quot;text/x-sh&quot;,&quot;theme&quot;:&quot;react&quot;,&quot;lineNumbers&quot;:false,&quot;lineWrapping&quot;:true,&quot;readOnly&quot;:true}">gsutil cors set yourjsonfile.json gs://your-bucket-name </pre></div>
<p>Obviously replace&nbsp;<code>yourjsonfile.json</code>&nbsp;with the name of the JSON file you created in the previous step and change&nbsp;<code>gs://your-bucket-name</code>&nbsp;to the name of your bucket.</p>
<p>Verify the CORS policy has been set correctly with:</p>
<div class="wp-block-codemirror-blocks-code-block code-block"><pre class="CodeMirror cm-s-react" data-setting="{&quot;mode&quot;:&quot;shell&quot;,&quot;mime&quot;:&quot;text/x-sh&quot;,&quot;theme&quot;:&quot;react&quot;,&quot;lineNumbers&quot;:false,&quot;lineWrapping&quot;:true,&quot;readOnly&quot;:true}">gsutil cors get gs://your-bucket-name</pre></div>
<p>That&#8217;s it, CORS has been set and you now are capable of performing Direct Uploads with Media Cloud.</p>
