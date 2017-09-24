<div class="settings-container">
    <header>
        <img src="{{ILAB_PUB_IMG_URL}}/icon-cloud.svg">
        <h1>{{$title}}</h1>
    </header>
    <div class="settings-body">
        <form action='options.php' method='post' autocomplete="off">
            <?php
            settings_fields( $group );
            echo '<!-- hey -->';
            do_settings_sections( $page );
            echo '<!-- now -->';
            submit_button();
            ?>
        </form>
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

                    if (trueCount==conditions[prop].length) {
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
    })(jQuery);
</script>