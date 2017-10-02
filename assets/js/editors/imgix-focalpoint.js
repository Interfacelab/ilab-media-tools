/**
 * Focal Point Editor
 * @param {jQuery} $
 * @param {ILabImageEdit} imgixEditor
 * @constructor
 */
var ILabFocalPointEditor=function($, imgixEditor){
    //region Variables
    var focalPointIcon = $('<div class="ilabm-focal-point-icon"></div>');

    var focalPointX = 0.5;
    var focalPointY = 0.5;
    if (imgixEditor.settings.settings.hasOwnProperty('fp-x')) {
        focalPointX = imgixEditor.settings.settings['fp-x'];
    }
    if (imgixEditor.settings.settings.hasOwnProperty('fp-y')) {
        focalPointY = imgixEditor.settings.settings['fp-y'];
    }

    var canSetFocalPoint = false;
    //endregion

    //region Methods
    this.updateFocalPointPosition = function() {
        var cb = imgixEditor.editorArea.get(0).getBoundingClientRect();
        var imageCb = imgixEditor.previewImage.get(0).getBoundingClientRect();

        var imageRect = {
            top: imageCb.top - cb.top,
            left: imageCb.left - cb.left,
            right: imageCb.right - cb.left,
            bottom: imageCb.bottom - cb.top,
            width: imageCb.width,
            height: imageCb.height
        };

        var l = imageRect.left + (imageRect.width * focalPointX);
        var t = imageRect.top + (imageRect.height * focalPointY);

        l -= 12;
        t -= 12;

        focalPointIcon.css({
            "left": l + 'px',
            "top": t + 'px'
        });
    };

    this.buildFocalPoint=function() {
        focalPointIcon.remove();
        imgixEditor.editorArea.append(focalPointIcon);
        this.updateFocalPointPosition();
    };
    this.disable = function() {
        $(document).trigger('change-focalpoint', [false]);
        canSetFocalPoint = false;
        focalPointIcon.remove();
    };

    this.save = function(postData) {
        if (postData.hasOwnProperty('focalpoint')) {
            postData['fp-x'] = focalPointX;
            postData['fp-y'] = focalPointY;
        }

        return postData;
    };
    //endregion

    //region UI Events
    $(document).on('focalpoint-selected', function(e){
        $(document).trigger('change-entropy', [false]);
        $(document).trigger('change-edges', [false]);
        imgixEditor.faceEditor.disable();
        canSetFocalPoint = true;
        this.buildFocalPoint();
    }.bind(this));

    $(document).on('focalpoint-deselected', function(e){
        canSetFocalPoint = false;
        focalPointIcon.remove();
    }.bind(this));

    imgixEditor.editorArea.on('mousedown', function(e){
        e.preventDefault();

        if (!canSetFocalPoint) {
            return false;
        }

        this.buildFocalPoint();

        var cb = imgixEditor.editorArea.get(0).getBoundingClientRect();
        var imageCb = imgixEditor.previewImage.get(0).getBoundingClientRect();

        var imageRect = {
            top: imageCb.top - cb.top,
            left: imageCb.left - cb.left,
            right: imageCb.right - cb.left,
            bottom: imageCb.bottom - cb.top,
            width: imageCb.width,
            height: imageCb.height
        };

        imgixEditor.editorArea.on('mousemove', function(e){
            e.preventDefault();

            var l = (e.clientX - cb.left);
            if (l<imageRect.left) {
                l = imageRect.left;
            } else if (l>imageRect.right) {
                l = imageRect.right;
            }

            var t = (e.clientY - cb.top);
            if (t<imageRect.top) {
                t = imageRect.top;
            } else if (t>imageRect.bottom) {
                t = imageRect.bottom;
            }

            focalPointX = (l-imageRect.left) / imageRect.width;
            focalPointY = (t-imageRect.top) / imageRect.height;

            l -= 12;
            t -= 12;

            focalPointIcon.css({
                "left": l + 'px',
                "top": t + 'px'
            });

            return false;
        }.bind(this));

        imgixEditor.editorArea.on('mouseup', function(e){
            e.preventDefault();
            imgixEditor.editorArea.off('mouseup');
            imgixEditor.editorArea.off('mousemove');
            imgixEditor.preview();
            return false;
        }.bind(this));

        return false;
    }.bind(this));

    $(window).on('resize', function(){
        this.updateFocalPointPosition();
    }.bind(this));
    //endregion

    //region Startup
    if (imgixEditor.settings.settings.hasOwnProperty('focalpoint')) {
        if (imgixEditor.settings.settings.focalpoint == 'focalpoint') {
            canSetFocalPoint = true;
            setTimeout(function(){
                this.buildFocalPoint();
            }.bind(this), 300);
        }
    }
    //endregion
};