/**
 *
 * @param {jQuery} $
 * @param {ILabImageEdit} imgixEditor
 * @constructor
 */
var ILabFaceEditor=function($, imgixEditor){
    //region Variables/Setup
    var faces = [];
    var allFaces = null;
    var currentFaceIndex = 0;

    if (imgixEditor.settings.meta.hasOwnProperty('faces')) {
        var faceLeft = Number.MAX_VALUE;
        var faceTop = Number.MAX_VALUE;
        var faceRight = 0;
        var faceBottom = 0;

        var faceIndex = 0;
        while(true) {
            if (!imgixEditor.settings.meta.faces.hasOwnProperty(faceIndex)) {
                break;
            }

            var face = imgixEditor.settings.meta.faces[faceIndex];

            faceLeft = Math.min(faceLeft, face.BoundingBox.Left);
            faceTop = Math.min(faceTop, face.BoundingBox.Top);
            faceRight = Math.max(faceRight, face.BoundingBox.Left + face.BoundingBox.Width);
            faceBottom = Math.max(faceBottom, face.BoundingBox.Top + face.BoundingBox.Height);

            var faceData = {
                index: faceIndex + 1,
                left: face.BoundingBox.Left,
                top: face.BoundingBox.Top,
                right: face.BoundingBox.Left + face.BoundingBox.Width,
                bottom: face.BoundingBox.Top + face.BoundingBox.Height,
                width: face.BoundingBox.Width,
                height: face.BoundingBox.Height,
                element: $("<div class='ilab-face-outline hidden'><span>"+(faceIndex+1)+"</span></div>")
            };

            faces.push(faceData);

            var self = this;
            (function(fi){
                faceData.element.on('click', function(e){
                    currentFaceIndex = fi;
                    self.displayFaces();
                    $(document).trigger('change-faceindex', [fi]);
                    imgixEditor.preview();
                });
            })(faceIndex + 1);

            imgixEditor.editorArea.append(faceData.element);

            faceIndex++;
        }


        if (faces.length > 1) {
            var faceWidth = faceRight - faceLeft;
            var faceHeight = faceBottom - faceTop;
            allFaces = {
                left: faceLeft,
                top: faceTop,
                right: faceRight,
                bottom: faceBottom,
                width: faceWidth,
                height: faceHeight,
                element: $("<div class='ilab-all-faces-outline hidden'></div>")
            };

            imgixEditor.editorArea.append(allFaces.element);
        } else {
            allFaces = null;
        }
    }
    //endregion

    //region Methods
    this.updateFacePositions = function() {
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

        if (allFaces != null) {
            var allL = imageRect.left + (imageRect.width * allFaces.left);
            var allT = imageRect.top + (imageRect.height * allFaces.top);
            var allW = imageRect.width * allFaces.width;
            var allH = imageRect.height * allFaces.height;

            allFaces.element.css({
                'left': allL+'px',
                'top': allT+'px',
                'width': allW+'px',
                'height': allH+'px'
            });
        }

        faces.forEach(function(face){
            var allL = imageRect.left + (imageRect.width * face.left);
            var allT = imageRect.top + (imageRect.height * face.top);
            var allW = imageRect.width * face.width;
            var allH = imageRect.height * face.height;

            face.element.css({
                'left': allL+'px',
                'top': allT+'px',
                'width': allW+'px',
                'height': allH+'px'
            });
        });
    };

    this.displayFaces=function() {
        this.updateFacePositions();

        if (currentFaceIndex == 0) {
            if (faces.length == 1) {
                faces[0].element.removeClass('hidden');
                faces[0].element.addClass('active');
            } else {
                if (allFaces != null) {
                    allFaces.element.removeClass('hidden');
                }

                faces.forEach(function(face){
                    face.element.addClass('hidden');
                });
            }
        } else {
            if (allFaces != null) {
                allFaces.element.addClass('hidden');
            }

            faces.forEach(function(face, index){
               face.element.removeClass('hidden');
               face.element.removeClass('active');
               if (index == currentFaceIndex - 1) {
                   face.element.addClass('active');
               }
            });
        }
    };

    this.hideFaces=function() {
        if (allFaces != null) {
            allFaces.element.addClass('hidden');
        }

        faces.forEach(function(face){
            face.element.addClass('hidden');
        });
    };

    this.disable = function() {
        $(document).trigger('change-usefaces', [false]);
        this.hideFaces();
    };

    this.save = function(postData) {
        return postData;
    };

    //endregion

    //region UI Events
    $(document).on('usefaces-selected', function(e){
        if (faces.length == 0) {
            alert("No faces have been detected in this image.  To use this feature, you will need to have the Rekognition tool enabled if it isn't already.");
            $(document).trigger('change-usefaces', [false]);
            return;
        }

        $(document).trigger('change-entropy', [false]);
        $(document).trigger('change-edges', [false]);
        imgixEditor.focalPointEditor.disable();
        this.displayFaces();
    }.bind(this));

    $(document).on('usefaces-deselected', function(e){
        this.hideFaces();
    }.bind(this));

    $(document).on('faceindex-changed', function(event, newIndex) {
        currentFaceIndex = newIndex;
        this.displayFaces();
    }.bind(this));

    $(window).on('resize', function(){
        this.updateFacePositions();
    }.bind(this));
    //endregion

    //region Startup
    if (imgixEditor.settings.settings.hasOwnProperty('focalpoint')) {
        if (imgixEditor.settings.settings.focalpoint == 'usefaces') {
            if (imgixEditor.settings.settings.hasOwnProperty('faceindex')) {
                currentFaceIndex = imgixEditor.settings.settings.faceindex;
            }

            setTimeout(function(){
                this.updateFacePositions();
                this.displayFaces();
            }.bind(this), 300);
        }
    }
    //endregion
};