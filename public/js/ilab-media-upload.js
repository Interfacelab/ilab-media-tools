!function(t){var e={};function a(i){if(e[i])return e[i].exports;var n=e[i]={i:i,l:!1,exports:{}};return t[i].call(n.exports,n,n.exports,a),n.l=!0,n.exports}a.m=t,a.c=e,a.d=function(t,e,i){a.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:i})},a.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},a.t=function(t,e){if(1&e&&(t=a(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var i=Object.create(null);if(a.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)a.d(i,n,function(e){return t[e]}.bind(null,n));return i},a.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return a.d(e,"a",e),e},a.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},a.p="/",a(a.s=183)}({183:function(t,e,a){t.exports=a(184)},184:function(t,e){window.ilabAttachmentInfo=function(t,e,a){var i=this;this.info=t(e.attachmentTemplate(a)),this.data=a,this.attachmentTitle=this.info.find('input[name="attachment-title"]'),this.attachmentCaption=this.info.find('textarea[name="attachment-caption"]'),this.attachmentAlt=this.info.find('input[name="attachment-alt"]'),this.attachmentDescription=this.info.find('textarea[name="attachment-description"]'),this.attachmentAlign=this.info.find('select[name="attachment-align"]'),this.attachmentLinkURL=this.info.find('input[name="attachment-link-url"]'),this.attachmentSize=this.info.find('select[name="attachment-size"]'),this.attachmentLinkType=this.info.find('select[name="attachment-link-type"]'),this.saveToken=null;var n=function(){var t={action:"save-attachment",id:i.data.id,nonce:i.data.nonces.update,post_id:window.parent.wp.media.model.settings.post.id,"changes[title]":i.attachmentTitle.val(),"changes[caption]":i.attachmentCaption.val(),"changes[alt]":i.attachmentAlt.val(),"changes[description]":i.attachmentDescription.val()};jQuery.post(ajaxurl,t,function(t){})};this.save=function(){clearTimeout(i.saveToken),i.saveToken=setTimeout(n,1e3)};var o=function(t){i.save()};this.attachmentTitle.on("change",o),this.attachmentCaption.on("change",o),this.attachmentAlt.on("change",o),this.attachmentDescription.on("change",o),this.attachmentLinkType.on("change",function(){var t=i.attachmentLinkType.val();"none"==t?(i.attachmentLinkURL.css({display:"display"}),i.attachmentLinkURL.val("")):"file"==t?(i.attachmentLinkURL.prop("readonly","readonly"),i.attachmentLinkURL.css({display:""}),i.attachmentLinkURL.val(i.data.url)):"post"==t?(i.attachmentLinkURL.prop("readonly","readonly"),i.attachmentLinkURL.css({display:""}),i.attachmentLinkURL.val(i.data.link)):"custom"==t&&(i.attachmentLinkURL.prop("readonly",null),i.attachmentLinkURL.css({display:""}),i.attachmentLinkURL.val(""))}),this.insert=function(){var t={action:"send-attachment-to-editor","attachment[id]":i.data.id,nonce:window.parent.wp.media.view.settings.nonce.sendToEditor,post_id:window.parent.wp.media.model.settings.post.id,"attachment[post_content]":i.attachmentDescription.val(),"attachment[post_excerpt]":i.attachmentCaption.val(),"attachment[image_alt]":i.attachmentAlt.val(),"attachment[image-size]":i.attachmentSize.val(),"attachment[align]":i.attachmentAlign.val(),"attachment[url]":i.attachmentLinkURL.val(),html:""};jQuery.post(ajaxurl,t,function(t){t.hasOwnProperty("data")&&window.parent.send_to_editor(t.data)})},this.attachmentLinkURL.css({display:"none"}),e.attachmentContainer.empty(),e.attachmentContainer.append(this.info)},window.ilabMediaUploadItem=function(t,e,a,i){var n=this;this.cell=t(e.uploadItemTemplate()),this.background=this.cell.find(".ilab-upload-item-background"),this.status=this.cell.find(".ilab-upload-status"),this.progress=this.cell.find(".ilab-upload-progress"),this.progressTrack=this.cell.find(".ilab-upload-progress-track"),this.uploadDirectory=i,this.status.text("Waiting ..."),this.progressTrack.css({width:"0%"}),this.cell.css({opacity:0}),this.cell.addClass("no-mouse"),this.state="waiting",this.postId=null,this.loader=this.cell.find(".ilab-loader-container"),0==a.type.indexOf("image/")&&a.size<15728640?this.background.css({opacity:.33,"background-image":"url("+URL.createObjectURL(a)+")"}):0==a.type.indexOf("image/")?this.cell.addClass("ilab-upload-cell-image"):0==a.type.indexOf("video/")?this.cell.addClass("ilab-upload-cell-video"):"application/x-photoshop"==a.type?this.cell.addClass("ilab-upload-cell-image"):this.cell.addClass("ilab-upload-cell-doc"),this.deselect=function(){this.cell.removeClass("ilab-upload-selected")},this.updateProgress=function(t){this.progressTrack.css({width:Math.floor(t)+"%"})},this.itemUploaded=function(t,a){if(t){if(this.progress.css({display:"none"}),this.status.css({display:"none"}),this.background.css({opacity:""}),this.state="ready",this.postId=a.data.id,a.data.thumb){this.loader.css({opacity:1});var i=new Image;i.onload=function(){this.background.css({"background-image":"url("+a.data.thumb+")"}),this.loader.css({opacity:0})}.bind(this),i.src=a.data.thumb}this.cell.removeClass("no-mouse")}else this.progress.css({display:"none"}),this.status.text("Error."),this.cell.addClass("upload-error");e.uploadFinished(this)},this.updateStatusText=function(t){n.status.text("Uploading ...")},this.startUpload=function(){t(document).trigger("ilab.upload-started"),n.uploadToStorage(a,null,a.type,n)},this.set=function(t){t.hasOwnProperty("progress")&&n.updateProgress(t.progress),t.hasOwnProperty("state")&&"uploading"==t.state&&n.updateStatusText("Uploading ...")},this.uploadFinished=function(t,e){var a={action:"ilab_upload_import_cloud_file",key:t};null!=e&&(a.faces=e),jQuery.post(ajaxurl,a,function(t){n.itemUploaded("success"==t.status,t)})},this.uploadError=function(){n.progress.css({display:"none"}),n.status.text("Error uploading."),e.uploadFinished(n)},e.cellContainer.append(this.cell),setTimeout(function(){n.cell.css({opacity:""})},1e3/30),this.cell.on("click",function(t){"ready"==n.state&&(e.settings.insertMode?(n.cell.addClass("ilab-upload-selected"),e.uploadSelected(n)):window.open("post.php?post="+n.postId+"&action=edit","_blank").focus());return t.preventDefault(),!1})},window.ilabMediaUploader=function(t,e){var a=this;this.settings=e,this.clickToUpload=!e.hasOwnProperty("clickToUpload")||e.clickToUpload,this.insertButton=e.insertButton,this.uploadTarget=e.uploadTarget,this.cellContainer=e.cellContainer,this.attachmentContainer=e.attachmentContainer,this.uploadItemTemplate=e.uploadItemTemplate,this.attachmentTemplate=e.attachmentTemplate,this.uploadDirections=this.uploadTarget.find(".ilab-upload-directions"),this.hiddenFileInput=t('<input type="file" style="visibility:hidden" multiple="multiple">'),this.uploadDirectory=null,this.waitingQueue=[],this.uploadingQueue=[],this.watchToken=null,this.currentSelection=null,this.attachmentInfo=null,this.watchQueue=function(){if(a.uploadingQueue.length<a.settings.maxUploads&&a.waitingQueue.length>0)for(var t=a.settings.maxUploads-a.uploadingQueue.length,e=0;e<t;e++)if(a.waitingQueue.length>0){var i=a.waitingQueue.shift();a.uploadingQueue.push(i),i.startUpload()}a.watchToken=setTimeout(a.watchQueue,500)},this.uploadSelected=function(e){if(a.currentSelection!=e){a.insertButton.prop("disabled",!0),a.currentSelection&&a.currentSelection.deselect(),a.currentSelection=e;var i={action:"ilab_upload_attachment_info",postId:e.postId};jQuery.post(ajaxurl,i,function(e){t("body").addClass("ilab-item-selected"),a.attachmentInfo=new ilabAttachmentInfo(t,a,e),a.insertButton.prop("disabled",!1)})}},this.uploadFinished=function(e){var i=a.uploadingQueue.indexOf(e);i>-1&&a.uploadingQueue.splice(i,1),clearTimeout(a.watchToken),a.watchQueue(),t(document).trigger("ilab.upload-finished")},this.openUpload=function(){a.hiddenFileInput.click()},this.setUploadDirectory=function(t){console.log("upload directory",t),a.uploadDirectory=t},this.addFile=function(i){if(""==i.type)return!1;var n=i.type;if("application/x-photoshop"==n&&(n="image/psd"),-1==e.allowedMimes.indexOf(n))return!1;var o=n.split("/"),s=o[0],l=o[1];if("image"==s){if(!e.imgixEnabled)return!1;if(-1==["jpeg","gif","png"].indexOf(l)){if(!e.extrasEnabled)return!1;if(-1==["psd","tiff","bmp"].indexOf(l))return!1}}else if("video"==s){if(!e.videoEnabled)return!1}else if(!e.docsEnabled)return!1;a.waitingQueue.push(new ilabMediaUploadItem(t,a,i,a.uploadDirectory))},this.uploadTarget.on("dragenter dragover",function(t){a.uploadTarget.addClass("drag-inside"),t.stopPropagation(),t.preventDefault()}),this.uploadTarget.on("dragleave drageexit",function(t){a.uploadTarget.removeClass("drag-inside"),t.stopPropagation(),t.preventDefault()}),this.uploadTarget.on("drop",function(t){a.uploadTarget.removeClass("drag-inside"),a.uploadDirections.css({display:"none"}),t.preventDefault();var e=t.originalEvent.dataTransfer.files;_.each(e,a.addFile)}),this.clickToUpload&&this.uploadTarget.on("click",function(t){a.hiddenFileInput.click()}),this.hiddenFileInput.on("change",function(t){a.uploadDirections.css({display:"none"});for(var e=0;e<this.files.length;e++)a.addFile(this.files[e])}),t("#ilab-open-editor").on("click",function(t){return wp.media({frame:"select"}).open(),t.preventDefault(),!1}),this.insertButton.on("click",function(t){return a.attachmentInfo&&a.attachmentInfo.insert(),t.preventDefault(),!1}),e.insertMode&&t("body").addClass("ilab-upload-insert-mode"),this.watchToken=setTimeout(this.watchQueue,500)}}});