@extends('../templates/sub-page', ['title' => $title])

@section('main')
    <div class="settings-body">
        <div id="s3-importer-manual-warning" style="display:none">
            <p><strong>IMPORTANT:</strong> You are running the import process in the web browser.  <strong>Do not navigate away from this page or the import may not finish.</strong></p>
        </div>
        <div id="s3-importer-instructions" {!! ($status=="running") ? 'style="display:none"':'' !!}>
            {!! $instructions !!}
            <div class="wp-cli-callout">
                <h3>Using WP-CLI</h3>
                <p>You can run this importer process from the command line using <a href="https://wp-cli.org" target="_blank">WP-CLI</a>:</p>
                <code>
                    {{$commandLine}}
                </code>
                @if(!empty($commandLink))
                <p><a href="{{$commandLink}}" target="_blank">Command documentation</a></p>
                @endif
            </div>
            @if(!empty($options))
                <div id="s3-importer-options">
                    <h3>Options</h3>
                    <ul>
                        @foreach($options as $optionName => $option)
                            <li>
                                <div>
                                    {!! $option['title'] !!}
                                </div>
                                <div>
                                    <div>
                                    @if($option['type'] == 'checkbox')
                                        @include('base/ui/checkbox', ['name' => $optionName, 'value' => $option['default'], 'description' => '', 'enabled' => true])
                                    @elseif($option['type'] == 'select')
                                        <select name="{{$optionName}}">
                                            @foreach($option['options'] as $suboptionValue => $suboptionName)
                                            <option value="{{$suboptionValue}}">{{$suboptionName}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    </div>
                                    <div class="description">{!! $option['description'] !!}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div style="margin-top: 2em;">
                @if($enabled)
                    <div style="display:flex; align-items:center">
                        <a id="s3-importer-start-import" href="#" class="ilab-ajax button button-primary">{{$commandTitle}}</a><img id="s3-importer-start-spinner" src="{!! admin_url('/images/spinner-2x.gif') !!}" height="24px" style="margin-left:10px; display: none;">
                    </div>
                @else
                    <strong class="tool-disabled">Please <a href="admin.php?page=media-tools-top">{{$disabledText}}</a> before using this tool.</strong>
                @endif
            </div>

        </div>
        <div id="s3-importer-progress" {!! ($status!="running") ? 'style="display:none"':'' !!}>
            <div id="s3-importer-progress-text">
                <p id="s3-importer-cancelling-text" style="display:{{($shouldCancel) ? 'block':'none'}}">Cancelling ... This may take a minute ...</p>
            </div>
            <div id="s3-importer-thumbnails">
                <div id="s3-importer-thumbnails-container">
                </div>
                <div id="s3-importer-thumbnails-fade"></div>
                <img id="s3-importer-thumbnails-cloud" src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
            </div>
            <div class="s3-importer-progress-container">
                <div id="s3-importer-progress-bar" style="width: {{$progress}}%;"></div>
                <div id="s3-importer-status-text" style="visibility:{{($shouldCancel) ? 'hidden':'visible'}}">
                    <div>Processing '<span id="s3-importer-current-file">{{$currentFile}}</span>' (<span id="s3-importer-current">{{$current}}</span> of <span id="s3-importer-total">{{$total}}</span>).  <span id="s3-timing-stats"><span id="s3-timing-ppm">{{number_format($postsPerMinute, 1)}}</span> posts per minute, ETA: <span id="s3-timing-eta">{{number_format($eta, 2)}}</span>.</span></div>
                </div>
            </div>
            <button id="s3-importer-cancel-import" class="button button-whoa" title="Cancel">{{$cancelCommandTitle}}</button>
        </div>
    </div>
@endsection

<script>
    (function($){
        $(document).ready(function(){
            var importing={{($status == 'running') ? 'true' : 'false'}};
            var totalIndex = -1;
            var currentIndex = -1;
            var currentPage = 1;
            var totalPages = {{$pages}};
            var totalItems = {{$total}};
            var manualStart = 0;
            var fromSelection = {{($fromSelection) ? 'true' : 'false'}};
            var autoStart = {{($shouldRun) ? 'true' : 'false'}};
            var displayedThumbs = [];

            const backgroundImport = {{ ($background) ? 'true' : 'false' }};
            var postsToImport = {!! json_encode($posts, JSON_PRETTY_PRINT) !!};

            var lastThumb = {
                id: null,
                url: null
            };

            /**
             * @param {object} thumb - {id, url}
             * @param {boolean} icon
             */
            const displayNextThumbnail = function(thumb, icon) {
                if (thumb == undefined || thumb == null || !thumb.hasOwnProperty('url') || thumb.url == null || !thumb.hasOwnProperty('id') || thumb.id == null) {
                    return;
                }

                if (!icon && (lastThumb.id === thumb.id)) {
                    return;
                }

                if (!icon && (displayedThumbs.length > 0)) {
                    if (displayedThumbs[displayedThumbs.length - 1].attr('src') == thumb.url) {
                        return;
                    }
                }

                var image = null;
                if (!icon) {
                    lastThumb.url = thumb.url;
                    lastThumb.id = thumb.id;
                    image = $('<div class="s3-importer-thumb ilab-hidden" style="background-image: url('+thumb.url+')"></div>');
                } else {
                    image = $('<div class="s3-importer-image-icon ilab-hidden"><img src="'+thumb.url+'"></div>');
                }

                image.prependTo('#s3-importer-thumbnails-container');
                setTimeout(function() {
                    image.removeClass('ilab-hidden');
                }, 100);
                displayedThumbs.push(image);

                displayedThumbs.forEach(function(ele, index) {
                    if (ele == image) {
                        return;
                    }

                    $(ele).css({"transform": "translateX("+(((displayedThumbs.length - 1) - index) * 160)+"px)"});
                });

                if (displayedThumbs.length >= 20) {
                    var firstImage = displayedThumbs.shift();
                    firstImage.remove();
                }
            }

            const nextBatch = function(callback) {
                if (!importing) {
                    return;
                }

                currentPage++;
                if (currentPage > totalPages) {
                    callback(false);
                    return;
                }

                const data={
                    action: '{{$nextBatchAction}}',
                    page: currentPage
                };

                $.post(ajaxurl,data,function(response){
                    postsToImport = response.posts;
                    currentIndex = 0;

                    callback((postsToImport.length >  0));
                });

            };

            const importNextManual = function () {
                if (!importing) {
                    return;
                }

                currentIndex++;
                if (currentIndex == postsToImport.length) {
                    nextBatch(function(success){
                        if (success) {
                            importNextManual();
                        } else {
                            importing = false;
                            $('#s3-importer-instructions').css({display: 'block'});
                            $('#s3-importer-progress').css({display: 'none'});
                            $('#s3-importer-manual-warning').css('display', 'none');
                            document.location.reload();
                        }
                    });

                    return;
                }

                totalIndex++;

                displayNextThumbnail(
                    {
                        id: postsToImport[currentIndex].id,
                        url: postsToImport[currentIndex].thumb
                    },
                    postsToImport[currentIndex].icon
                );

                $('#s3-importer-status-text').css({'visibility':'visible'});
                $('#s3-importer-current').text((totalIndex + 1));
                $('#s3-importer-current-file').text(postsToImport[currentIndex].title);
                $('#s3-importer-total').text(totalItems);

                const progress = Math.min(100, ((totalIndex + 1) / totalItems) * 100);
                $('#s3-importer-progress-bar').css({width: progress+'%'});

                const data={
                    action: '{{$manualAction}}',
                    post_id: postsToImport[currentIndex].id
                };

                $('#s3-importer-options input[type=checkbox]').each(function(){
                    if (!$(this).attr('checked')) {
                        return;
                    }

                    var name = $(this).attr('name');
                    data[name] = 'on';
                });

                $('#s3-importer-options select').each(function(){
                    var name = $(this).attr('name');
                    data[name] = $(this).val();
                });

                $.post(ajaxurl,data,function(response){
                    if (response.status == 'error') {
                        document.location.reload();
                    }

                    const totalTime = (performance.now() - manualStart) / 1000.0;
                    const postsPerSecond = totalTime / (totalIndex + 1);
                    const postsPerMinute = 60 / postsPerSecond;
                    const eta = (totalItems - (totalIndex + 1)) / postsPerMinute;

                    $('#s3-timing-stats').css({display: 'inline-block'});
                    $('#s3-timing-ppm').text(postsPerMinute.toFixed(1));

                    const date = new Date();
                    date.setSeconds(date.getSeconds() + (eta * 60.0));

                    $('#s3-timing-eta').text(date.toLocaleTimeString());

                    importNextManual();
                });

            };

            const startImport = function() {
                if (importing) {
                    return false;
                }

                if (currentPage > 1) {
                    currentPage = 0;
                    nextBatch(function(success){
                        if (success) {
                            startImport();
                        } else {
                            return;
                        }
                    });

                    return;
                }

                currentIndex = -1;
                totalIndex = -1;
                importing=true;
                displayedThumbs = [];

                $('#s3-importer-thumbnails-container').empty();

                if (backgroundImport) {
                    $('#s3-importer-start-import').css({'pointer-events': 'none', opacity: 0.5});

                    var oldButtonText = $('#s3-importer-start-import').text();
                    $('#s3-importer-start-import').text('Starting {{$commandTitle}} ...');
                    $('#s3-importer-start-spinner').css({'display': 'block'});

                    var data={
                        action: '{{$startAction}}'
                    };

                    $('#s3-importer-options input[type=checkbox]').each(function(){
                        if (!$(this).attr('checked')) {
                            return;
                        }

                        var name = $(this).attr('name');
                        data[name] = 'on';
                    });

                    $('#s3-importer-options select').each(function(){
                        var name = $(this).attr('name');
                        data[name] = $(this).val();
                    });

                    if (fromSelection) {
                        postIds = [];
                        for(var i=0; i<postsToImport.length; i++) {
                            postIds.push(postsToImport[i].id);
                        }

                        data['selection'] = postIds;

                        $('#s3-importer-cancel-import').attr('disabled', false);
                        $('#s3-importer-cancelling-text').css({'display':'none'});
                        $('#s3-importer-status-text').css({'visibility':'visible'});

                        $('#s3-importer-instructions').css({display: 'none'});
                        $('#s3-importer-progress').css({display: 'block'});

                        displayNextThumbnail(
                            {
                                id: postsToImport[0].currentID,
                                url: postsToImport[0].thumb
                            },
                            postsToImport[0].icon
                        );

                        $('#s3-importer-status-text').css({'visibility':'visible'});
                        $('#s3-importer-current').text(1);
                        $('#s3-importer-current-file').text(postsToImport[0].title);
                        $('#s3-importer-total').text(totalItems);
                    }

                    $.post(ajaxurl,data,function(response){
                        $('#s3-importer-start-import').css({'pointer-events': null, opacity: 1});
                        $('#s3-importer-start-import').text(oldButtonText);
                        $('#s3-importer-start-spinner').css({'display': 'none'});

                        if (response.status != 'running') {
                            document.location.reload();
                        }

                        if (response.status == 'running') {
                            $('#s3-importer-cancel-import').attr('disabled', false);
                            $('#s3-importer-cancelling-text').css({'display':'none'});
                            $('#s3-importer-status-text').css({'visibility':'visible'});

                            $('#s3-importer-instructions').css({display: 'none'});
                            $('#s3-importer-progress').css({display: 'block'});

                            displayNextThumbnail(
                                {
                                    id: response.first.currentID,
                                    url: response.first.thumb
                                },
                                response.first.icon
                            );

                            totalItems = response.total;
                            $('#s3-importer-status-text').css({'visibility':'visible'});
                            $('#s3-importer-current').text(1);
                            $('#s3-importer-current-file').text(response.first.title);
                            $('#s3-importer-total').text(totalItems);

                            setTimeout(checkStatus, 3000);
                        }
                    }).fail(function() {
                        document.location.reload();
                    });
                } else {
                    manualStart = performance.now();

                    $('#s3-importer-manual-warning').css('display', 'block');
                    $('#s3-importer-progress-bar').css({width: '0%'});
                    $('#s3-importer-status-text').css({'visibility':'hidden'});

                    $('#s3-importer-cancel-import').attr('disabled', false);
                    $('#s3-importer-cancelling-text').css({'display':'none'});

                    $('#s3-importer-instructions').css({display: 'none'});
                    $('#s3-importer-progress').css({display: 'block'});

                    importNextManual();
                }
            };

            const cancelImport = function () {
                if (backgroundImport) {
                    var data={
                        action: '{{$cancelAction}}'
                    };

                    $.post(ajaxurl,data,function(response){
                        $('#s3-importer-cancelling-text').css({'display':'block'});
                        $('#s3-importer-status-text').css({'visibility':'hidden'});
                        $('#s3-importer-cancel-import').attr('disabled', true);
                    });
                } else {
                    importing = false;
                    $('#s3-importer-manual-warning').css('display', 'none');
                    $('#s3-importer-instructions').css({display: 'block'});
                    $('#s3-importer-progress').css({display: 'none'});
                }
            };

            const checkStatus = function() {
                if (importing) {
                    const data={
                        action: '{{$progressAction}}'
                    };

                    $.post(ajaxurl,data,function(response){
                        if (response.shouldCancel) {
                            $('#s3-importer-cancelling-text').css({'display':'block'});
                            $('#s3-importer-status-text').css({'visibility':'hidden'});
                        } else {
                            $('#s3-importer-cancelling-text').css({'display':'none'});
                            $('#s3-importer-status-text').css({'visibility':'visible'});
                        }

                        if (response.status != 'running') {
                            importing = false;
                            $('#s3-importer-instructions').css({display: 'block'});
                            $('#s3-importer-progress').css({display: 'none'});
                            document.location.reload();
                        } else {
                            if (response.total > 0) {
                                var progress = (response.current / response.total) * 100;
                                $('#s3-importer-progress-bar').css({width: progress+'%'});
                            }

                            if (response.thumb != null) {
                                displayNextThumbnail({
                                    id: response.currentID,
                                    url: response.thumb
                                });
                            }

                            $('#s3-timing-stats').css({display: 'inline-block'});

                            $('#s3-importer-current').text(response.current);
                            $('#s3-importer-current-file').text(response.currentFile);
                            $('#s3-importer-total').text(response.total);
                            $('#s3-timing-ppm').text(parseFloat(response.postsPerMinute).toFixed(1));

                            var date = new Date();
                            date.setSeconds(date.getSeconds() + (parseFloat(response.eta) * 60.0));

                            $('#s3-timing-eta').text(date.toLocaleTimeString());
                        }
                    });
                }

                setTimeout(checkStatus, 3000);
            };

            $('#s3-importer-start-import').on('click',function(e){
                e.preventDefault();

                startImport();

                return false;
            });


            $('#s3-importer-cancel-import').on('click', function(e){
                e.preventDefault();

                if (confirm("Are you sure you want to cancel?")) {
                    cancelImport();
                }

                return false;
            });

            if (autoStart && importing) {
                importing = false;
                startImport();
            } else if (importing && backgroundImport) {
                checkStatus();
            }
        });
    })(jQuery);
</script>