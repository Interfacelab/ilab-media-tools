!function(e){var t={};function n(i){if(t[i])return t[i].exports;var o=t[i]={i:i,l:!1,exports:{}};return e[i].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,i){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:i})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(i,o,function(t){return e[t]}.bind(null,o));return i},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=532)}({532:function(e,t,n){n(533),n(942),n(948),n(950),n(954),e.exports=n(956)},533:function(e,t){function n(e,t,n){return t>=e.left&&t<=e.right&&n>=e.top&&n<=e.bottom}!function(e){var t=function(){e(".info-panel-tabs").each((function(){var t=e(this),n=null,i=null;t.find("li").each((function(){var t=e(this),o=e("#"+t.data("tab-target"));t.hasClass("active")&&(n=t,i=o),t.on("click",(function(e){n!=t&&(n.removeClass("active"),i.css({display:"none"}),t.addClass("active"),o.css({display:""}),n=t,i=o)}))}))}));var t=null;e(".info-file-info-size").each((function(){t||(t=e(this))})),e("#ilab-other-sizes").on("change",(function(n){var i=e("#info-size-"+e(this).val());i!=t&&(t.css({display:"none"}),i.css({display:""}),t=i)})),e(".ilab-info-regenerate-thumbnails").on("click",(function(t){var n=e(this);return n.data("imgix-enabled")&&!confirm("You are currently using Imgix, which makes this function rather unnecessary.  Are you sure you want to continue?")||(e(document).trigger("ilab-regeneration-started"),t.preventDefault(),n.css({display:"none"}),e("#ilab-info-regenerate-status").css({display:""}),e.post(ajaxurl,{action:"ilab_regenerate_thumbnails_manual",post_id:n.data("post-id")},(function(t){n.css({display:""}),e("#ilab-info-regenerate-status").css({display:"none"}),"success"!=t.status&&alert("There was a problem trying to regenerate the thumbnails.  Please try again."),e(document).trigger("ilab-regeneration-ended")}))),!1}))};t();var i=!0,o=!0,a=e('<div class="ilab-loader-container"><div class="ilab-loader ilab-loader-dark"></div></div>'),s=e('<div id="ilab-media-grid-info-popup" class="hidden" style="left:0px; top:0px;"></div>');s.addClass("popup-left");var r=e('<div class="ilab-media-grid-info-popup-content"></div>'),u=e('<div class="ilab-media-popup-arrow-left"><div></div></div>'),l=e('<div class="ilab-media-popup-arrow-right"><div></div></div>'),d=null,c=!1,p=0,f=function(){if(i){var n=e(this),o=n.data("post-id");n.data("mime-type").startsWith("image")?s.removeClass("ilab-popup-document"):s.addClass("ilab-popup-document"),c=!1,e("li.attachment").each((function(){var t=e(this);t.data("id")==o?(t.removeClass("info-unfocused"),t.addClass("info-focused")):(t.removeClass("info-focused"),t.addClass("info-unfocused"))})),r.text(""),r.append(a);var p=e(this).offset(),f=(parseInt(p.top),parseInt(e(this).height()),parseInt(e(this).height()),parseInt(p.left)),m=(parseInt(p.left),parseInt(e(this).width()),parseInt(p.top)),h=(parseInt(e(this).width()),parseInt(p.top),parseInt(p.left),parseInt(s.width())),v=parseInt(s.height()),g=m-v/2-16,b=f+8,y=1,x=g,C=Math.max(document.documentElement.clientHeight,window.innerHeight||0),w=Math.max(document.documentElement.clientWidth,window.innerWidth||0),_=g-window.scrollY;_<0?x=window.scrollY+8:_+v>C&&(x-=_+v-C+40),b+h>w&&(b=f-h+32,y=-1),1===y?(d=u,s.removeClass("popup-right"),s.addClass("popup-left")):(d=l,s.removeClass("popup-left"),s.addClass("popup-right"));var I=Math.max(-1*(v/2-10),Math.min(v/2-10,g-x));d.css({transform:"translateY("+I+"px)"}),s.css({left:b+"px",top:x+"px"}),s.removeClass("hidden"),setTimeout((function(){var n={action:"ilab_s3_get_media_info",id:o};e.post(ajaxurl,n,(function(n,i){if(0!=n.length){var o=e(n);r.text(""),r.append(o),t(),setTimeout((function(){c=!0}),500)}}),"html")}),300)}};s.append(u),s.append(r),s.append(l),e("body").append(s),e(document).on("mouseenter","img.ilab-s3-logo",(function(e){clearTimeout(p),p=setTimeout(f.bind(this),250)})),e(document).on("mouseleave","img.ilab-s3-logo",(function(e){clearTimeout(p)})),e(document).on("mouseenter",".media-cloud-info-link",(function(e){clearTimeout(p),p=setTimeout(f.bind(this),250)})),e(document).on("mouseleave",".media-cloud-info-link",(function(e){clearTimeout(p)})),e(document).on("mousemove",(function(t){!function(t){if(c&&o){var a=d.get(0).getBoundingClientRect();a.top-=5,a.bottom+=5;var u=r.get(0).getBoundingClientRect(),l=t.pageX,p=t.pageY-document.body.scrollTop;n(a,l,p)||n(u,l,p)||(i=!1,c=!1,s.addClass("hidden"),e("li.attachment").each((function(){var t=e(this);t.removeClass("info-focused"),t.removeClass("info-unfocused")})),setTimeout((function(){i=!0}),300))}}(t)})),e(document).on("media-cloud-regenerate-started",(function(){o=!1})),e(document).on("media-cloud-regenerate-ended",(function(){o=!0}))}(jQuery)},942:function(e,t){},948:function(e,t){},950:function(e,t){},954:function(e,t){},956:function(e,t){}});