<div class="settings-container">
    <header class="all-settings">
        <div class="contents">
            <img class="logo" src="{{ILAB_PUB_IMG_URL}}/icon-cloud-w-type.svg">
            <div class="settings-select-container">
                <nav class="dropdown">
                    <div>Settings:</div>
                    <div class="dropdown">
                        <div class="current">
                            @if($tool->enabled())
                                <span class="tool-indicator tool-active"></span>
                            @elseif($tool->envEnabled())
                                <span class="tool-indicator tool-env-active"></span>
                            @else
                                <span class="tool-indicator tool-inactive"></span>
                            @endif
                            {{ $tool->toolInfo['name'] }}
                        </div>
                        <div class="items">
                            <ul>
                                @foreach($tools as $key => $atool)
                                    @if(!empty($atool->toolInfo['settings']))
                                        <li class="{{($tab == $key) ? 'active' : ''}}">
                                            <a class="tool" href="{{ilab_admin_url('admin.php?page=media-cloud-settings&tab='.$key)}}">
                                                @if($atool->enabled())
                                                    <span class="tool-indicator tool-active"></span>
                                                @elseif($atool->envEnabled())
                                                    <span class="tool-indicator tool-env-active"></span>
                                                @else
                                                    <span class="tool-indicator tool-inactive"></span>
                                                @endif
                                                {{$atool->toolInfo['name']}}
                                            </a>
                                            <a title="Pin these settings to the admin menu." data-tool-name="{{$atool->toolName}}" data-tool-title="{{$atool->toolInfo['name']}}" class="tool-pin {{($atool->pinned()) ? 'pinned' : ''}}" href="#"></a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="mcloud-settings-tabs">
            <div class="navwrap">
                <ul>
                    @foreach($tools as $key => $atool)
                        @if(!empty($atool->toolInfo['settings']))
                            <li class="{{($tab == $key) ? 'active' : ''}}">
                                <a class="tool" href="{{ilab_admin_url('admin.php?page=media-cloud-settings&tab='.$key)}}">
                                    @if(!$atool->alwaysEnabled())
                                        @if($atool->enabled())
                                            <span class="tool-indicator tool-active"></span>
                                        @elseif($atool->envEnabled())
                                            <span class="tool-indicator tool-env-active"></span>
                                        @else
                                            <span class="tool-indicator tool-inactive"></span>
                                        @endif
                                    @endif
                                    {{$atool->toolInfo['name']}}
                                </a>
                                <a title="Pin these settings to the admin menu." data-tool-name="{{$atool->toolName}}" data-tool-title="{{$atool->toolInfo['name']}}" class="tool-pin {{($atool->pinned()) ? 'pinned' : ''}}" href="#"></a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            <a class="tabs-nav tabs-prev hidden" href="#"><span>LEFT</span></a>
            <a class="tabs-nav tabs-next hidden" href="#"><span>RIGHT</span></a>
        </div>
    </header>
    <div class="settings-body @plan('free') show-upgrade @endplan">
        <div class="settings-interior">
            <div class="ilab-notification-container"></div>
            @network()
            <form action='edit.php?action=update_media_cloud_network_options' method='post' autocomplete="off">
            @else
            <form action='options.php' method='post' autocomplete="off">
            @endnetwork
                <?php
                settings_fields( $group );
                ?>
                @if(empty($tool->toolInfo['exclude']))
                <div class="ilab-settings-section ilab-settings-toggle">
                    <table class="form-table">
                        <tr>
                            <th scope="row">Enable {{$tool->toolInfo['name']}}</th>
                            <td>
                                @include('base/fields/enable-toggle', ['name' => $tab, 'manager' => $manager, 'tool' => $tool])
                            </td>
                        </tr>
                        @if(!empty($tool->toolInfo['related']))
                        @foreach($tool->toolInfo['related'] as $relatedKey)
                            @if(empty($manager->tools[$relatedKey]))
                                @continue
                            @endif
                            @if($loop->first)
                            <tr>
                                <td colspan="2" style="width:100%; padding: 0;"><hr></td>
                            </tr>
                            @endif
                            <?php $relatedTool = $manager->tools[$relatedKey]; ?>
                            <tr>
                                <th scope="row">Enable {{$relatedTool->toolInfo['name']}}</th>
                                <td>
                                    @include('base/fields/enable-toggle', ['name' => $relatedTool->toolInfo['id'], 'manager' => $manager, 'tool' => $relatedTool])
                                </td>
                            </tr>
                        @endforeach
                        @endif
                    </table>
                </div>
                @endif
                @if((count($sections) > 1) && !empty($jump_links))
                <div class="section-jumps">
                    <span class="label">Quick Jump</span>
                    @foreach($sections as $section)
                        @continue($loop->first)
                        <a href="#{{sanitize_title($section['title'])}}">{{$section['title']}}</a>
                        @if(!$loop->last)
                        <span class="sep">|</span>
                        @endif
                    @endforeach
                </div>
                @endif
                @foreach($sections as $section)
                <div id="{{sanitize_title($section['title'])}}" class="ilab-settings-section">
                    @if(!empty($section['title']))
                    <h2>
                        {{$section['title']}}
                        @if(!empty($section['doc_link']))
                            <a href="{{$section['doc_link']}}" class="help-beacon" data-article-inline="{{$section['doc_link']}}">
                                Help
                            </a>
                        @endif
                        @if (!empty($section['help']) && !empty($section['help']['data']) && (\MediaCloud\Plugin\Utilities\arrayPath($section['help'], 'target', 'footer') == 'header'))
                            <div class="ilab-section-title-doc-links">
                                @include('base.fields.help', $section['help'])
                            </div>
                        @endif
                    </h2>
                    @endif
                    @if(!empty($section['description']))
                    <div class="section-description">{!! $section['description'] !!}</div>
                    @endif
                    @if(!empty($section['custom']))
                    <div class="custom-setting">
                        <?php call_user_func($section['callback']) ?>
                    </div>
                    @else
                    <table class="form-table">
                        <?php do_settings_fields( $page, $section['id'] ) ?>
                    </table>
                    @endif
                    @if (!empty($section['help']) && !empty($section['help']['data']) && (\MediaCloud\Plugin\Utilities\arrayPath($section['help'], 'target', 'footer') == 'footer'))
                        <div class="ilab-section-doc-links">
                            @include('base.fields.help', $section['help'])
                        </div>
                    @endif
                    @if((empty($section['hide-save']) && (count($sections) > 1)))
                        <div class="section-submit">
	                        <?php submit_button('', 'primary small'); ?>
                        </div>
                    @endif
                </div>
                @endforeach

                @if((count($sections) <= 1) || (count($tool->actions()) > 0))
                <div class="ilab-settings-button">
                    @if(!empty($tool->actions()))
                        <div class="ilab-settings-batch-tools {{(count($sections) <= 1) ? 'has-submit' : ''}}">
                            @foreach($tool->actions() as $key => $action)
                            <a class="button ilab-ajax-button" data-ajax-action="{{str_replace('-','_',$key)}}" data-ajax-nonce="{{wp_create_nonce(str_replace('-','_',$key))}}" href="#">{{$action['name']}}</a>
                            @endforeach
                        </div>
                    @endif
                    @if(count($sections) <= 1)
                    <?php submit_button(); ?>
                    @endif
                </div>
                @endif
            </form>
        </div>
        @plan('free')
        @include('base/upgrade')
        @endplan
    </div>
</div>
<script>
    (function($){
        $('[data-conditions]').each(function(){
            var parent = this.parentElement;
            while (parent.tagName.toLowerCase() != 'tr') {
                parent = parent.parentElement;
                if (!parent) {
                    return;
                }
            }
            var name = this.getAttribute('id').replace('setting-','');
            var conditions = JSON.parse($('#'+name+'-conditions').html());

            var conditionTest = function() {
                var match = false;
                Object.getOwnPropertyNames(conditions).forEach(function(prop){
                    var val = $('#'+prop).val();

                    var trueCount = 0;
                    conditions[prop].forEach(function(conditionVal){
                        if (conditionVal[0] == '!') {
                            conditionVal = conditionVal.substring(1);
                            if (val != conditionVal) {
                                trueCount++;
                            }
                        } else {
                            if (val == conditionVal) {
                                trueCount++;
                            }
                        }
                    });

                    if (trueCount>0) {
                        match = true;
                    } else {
                        match = false;
                    }
                });

                return match;
            };

            if (!conditionTest()) {
                parent.style.display = 'none';
            }

            Object.getOwnPropertyNames(conditions).forEach(function(prop){
                $('#'+prop).on('change', function(e){
                    if (!conditionTest()) {
                        parent.style.display = 'none';
                    } else {
                        parent.style.display = '';
                    }
                });
            });
        });

        $('#ilab-media-settings-nav').on('change', function(e){
           document.location = $(this).val();
        });

        $('a.ilab-ajax-button').on('click', function(e){
            e.preventDefault();

            const data={
                action: $(this).data('ajax-action'),
                nonce: $(this).data('ajax-nonce')
            };

            $.post(ajaxurl, data, function(response){
                if (response.hasOwnProperty('message')) {
                    alert(response.message);
                } else {
                    document.location.reload();
                }
            });

            return false;
        });

        $('nav.dropdown').each(function(){
            var dropdown = $(this);
            var current = dropdown.find('div.current');
            var items = dropdown.find('div.items');
            current.on('click', function(e) {
               e.preventDefault();
               dropdown.addClass('active');
               items.addClass('visible');
               items.on('mouseleave', function(){
                   items.removeClass('visible');
                   dropdown.removeClass('active');
               });
               return false;
           });
        });

        var currentLabels = [];
        var lastPinnedItems = [];
        var menu = $('#toplevel_page_media-cloud');
        var menuUL = menu.find('ul');
        var firstItem = menuUL.find('li.wp-first-item').next();
        var pinnedSeparator = null;

        firstItem.next().next().find('span.ilab-admin-separator-settings').each(function(){
            if (pinnedSeparator == null) {
                pinnedSeparator = firstItem.next().next();
            }
        });

        $('a.tool-pin').each(function(){
            var pin = $(this);
            var pinToolName = pin.data('tool-name');
            var pinToolTitle = pin.data('tool-title');
            var pinItem = null;

            menuUL.find('li').each(function(){
               var item = $(this);
               item.find('a').each(function(){
                   var label = $(this).text();
                   if (currentLabels.indexOf(label) == -1) {
                    currentLabels.push(label);
                   }

                   const regex = /\page\=media\-cloud\-settings\-pinned\-(.*)$/gm;
                   var m = regex.exec($(this).attr('href'));
                   if ((m != null) && (m.length > 1)) {
                       var tool = m[m.length - 1];
                       if (tool == pinToolName) {
                           pinItem = item;
                           lastPinnedItems.push(pinItem);
                       }
                   }
               });
            });

            pin.on('click', function(e) {
                e.preventDefault();

                console.log('pin');

                const data={
                    action: 'ilab_pin_tool',
                    tool: pinToolName
                };

                $.post(ajaxurl, data, function(response){
                    console.log(response);

                    if (response.status == 'error') {
                        console.log(response);
                        return;
                    }

                    var pinned = (response.status == 'pinned');
                    if (!pinned) {
                        if (lastPinnedItems.indexOf(pinItem) >= 0) {
                            lastPinnedItems.splice(lastPinnedItems.indexOf(pinItem), 1);
                        }

                        if (pinItem) {
                            pinItem.remove();
                            pinItem = null;
                        }

                        if (currentLabels.indexOf(pinToolTitle) != -1) {
                            console.log('removing');
                            currentLabels.splice(currentLabels.indexOf(pinToolTitle), 1);
                        }


                        pin.removeClass('pinned');
                    } else {
                        pin.addClass('pinned');

                        if (currentLabels.indexOf(pinToolTitle) != -1) {
                            console.log('exiting');
                            return;
                        }

                        pinItem = $('<li id="pinned-tool-'+pinToolName+'"><a href="admin.php?page=media-cloud-settings-pinned-'+pinToolName+'" aria-current="page">'+pinToolTitle+'</a></li>');

                        if (lastPinnedItems.length > 0) {
                            pinItem.insertAfter(lastPinnedItems[lastPinnedItems.length - 1]);
                        } else {
                            pinItem.insertAfter(pinnedSeparator);
                        }

                        lastPinnedItems.push(pinItem);
                    }
                });


                return false;
            });
        });
    })(jQuery);
</script>
