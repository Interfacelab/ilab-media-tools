<p>This tool will migrate any media and documents you are currently hosting on your cloud storage and add it to your media library.</p>
@if(empty($description))
<p>Depending on the number of items you have, this could take anywhere from a minute to several hours.</p>
<p><strong>IMPORTANT:</strong> If you have many thousands or hundreds of thousands of items in cloud storage, you may need to up the memory limit for PHP as it requires quite a bit of memory to load that information in.  Alternatively, you may consider running this from the command line instead.</p>
<p><strong>Note:</strong></p>
<ol>
<li><strong>Always backup your database before performing any batch migration.</strong></li>
    <li>This process will try it's best to prevent duplicates, but a few might slip through.  Always double check the results.</li>
    <li>Did you backup your database?</li>
</ol>
@endif