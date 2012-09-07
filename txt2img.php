<?php
/*
Plugin Name: txt2img (山寨长微博)
Plugin URI: http://forcefront.com/txt2img-plugin/
Description: Convert WordPress post/page into image and share on <a href="http://weibo.com/">Weibo</a>. 把 WordPress 文章转发成新浪长微博。
Version: 1.0.8
Author: Leo Deng (@米粽my)
Author URI: http://forcefront.com/
License: GPLv2 or later
*/


function txt2img_custom_box() {
    echo '<p id="txt2img_buttons">';
    echo '<a href="#" id="txt2img_generate" class="button-secondary">生成长微博图片</a>';
    echo '<img id="txt2img_spinning" src="' . plugin_dir_url(__FILE__) . 'wpspin.gif" alt="生成中……">';
    echo '<a href="#" id="txt2img_share">转发到新浪微博</a>';
    echo '</p>';
    echo '<p><a href="#" id="txt2img_preview" target="_blank">预览长微博图片</a></p>';
}
function txt2img_add_custom_box() {
    global $post;
    if($post->post_status == 'publish') {
        add_meta_box('txt2img', '分享长微博', 'txt2img_custom_box', 'post', 'side', 'high');
        add_meta_box('txt2img', '分享长微博', 'txt2img_custom_box', 'page', 'side', 'high');
    }
}
add_action('adminmenu', 'txt2img_add_custom_box');


function txt2img_style() {
    if(basename($_SERVER['SCRIPT_NAME']) == 'post.php') {
        echo '<style type="text/css">';
        echo "@font-face { font-family:'Droid Sans Fallback'; src:local('Droid Sans Fallback'), url('" . plugin_dir_url(__FILE__) . "droid.ttf') format('ttf'); }";
        echo '#txt2img_buttons { overflow:hidden; line-height:24px; }';
        echo '#txt2img_generate { float:left; display:inline-block; margin-right:15px; }';
        echo '#txt2img_spinning { float:left; margin-right:15px; width:16px; height:16px; display:none; }';
        echo '#txt2img_share { width:106px; height:24px; float:left; font-size:0; text-decoration:none; background:url(' . plugin_dir_url(__FILE__) . 'weibo.png) no-repeat; display:none; }';
        echo '#txt2img_preview { padding-left: 10px; display:none; }';
        echo '#txt2img_generater { display:none; }';
        echo "#txt2img_content { font-family:'Droid Sans Fallback'; font-size:12pt; width:405px; height:450px; overflow-y:scroll; position:absolute; left:-9000px; top:0; z-index:1000; }";
        echo '<style>';
    }
}
add_action('admin_head', 'txt2img_style');


function txt2img_main() {
    if(basename($_SERVER['SCRIPT_NAME']) == 'post.php') { ?>
    <form id="txt2img_form" action="<?php echo plugin_dir_url(__FILE__) ?>txt2img_generater.php" method="post" target="txt2img_generater">
        <p><input id="txt2img_pid" name="txt2img_pid" type="hidden" value="<?php global $post; echo $post->ID; ?>"></p>
        <p><textarea id="txt2img_content" name="txt2img_content" cols="48" rows="20" wrap="hard"></textarea></p>
    </form>
    <iframe id="txt2img_generater" name="txt2img_generater" src=""></iframe>
    <script>
        (function() {
            var post_title        = document.getElementById('title'),
                post_content      = document.getElementById('content'),
                txt2img_generate  = document.getElementById('txt2img_generate'),
                txt2img_spinning  = document.getElementById('txt2img_spinning'),
                txt2img_share     = document.getElementById('txt2img_share'),
                txt2img_preview   = document.getElementById('txt2img_preview'),
                txt2img_form      = document.getElementById('txt2img_form'),
                txt2img_content   = document.getElementById('txt2img_content'),
                txt2img_generater = document.getElementById('txt2img_generater'),
                txt2img_url;
            var listen = function(element, type, handler) {
                if(element.addEventListener) {
                    listen = function(el, evt, fn) { el.addEventListener(evt, fn, false); };
                } else if (element.attachEvent) {
                    listen = function(el, evt, fn) { el.attachEvent("on" + evt, fn); };
                } else {
                    listen = function(el, evt, fn) { el["on" + evt] = fn; };
                }
                listen(element, type, handler);
            };
            var strip_tags = function(input) {
                return input.replace(/^<p>|<\/p>$/g, '').replace(/<p>|<\/p>/g, "\n").replace(/^\s+|\s+$/g, '').replace(/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi, '').replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, function ($0, $1) {
                    return ''.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
                }).replace(/\n(\n)+/ig, "\n\n");
            };
            var prepare = function(e) {
                txt2img_content.value = post_title.value + "\n\n" + strip_tags(post_content.value);
                txt2img_share.style.display = 'none';
                txt2img_preview.style.display = 'none';
                txt2img_spinning.style.display = 'inline-block';
                setTimeout(function() {
                    txt2img_form.submit();
                }, 100);
                if(e && e.preventDefault) {
                    e.preventDefault();
                } else {
                    window.event.returnValue = false;
                }
            };
            var render = function() {
                txt2img_url = txt2img_generater.contentWindow.document.body.innerHTML;
                if(txt2img_url != '') {
                    txt2img_spinning.style.display = 'none';
                    txt2img_share.style.display = 'inline-block';
                    txt2img_preview.style.display = 'inline-block';
                    txt2img_preview.href = txt2img_url;
                }
            };
            var preview = function(e) {
                var mask = document.createElement('div');
                mask.style.width = document.documentElement.clientWidth + 'px';
                mask.style.height = document.documentElement.clientHeight + 'px';
                mask.style.background = '#000';
                mask.style.opacity = '0.7';
                mask.style.filter = 'alpha(opacity=70)';
                mask.style.position = 'fixed';
                mask.style.zIndex = '999990';
                mask.style.top = '0px';
                mask.style.left = '0px';
                var frame = document.createElement('div');
                frame.style.width = '500px';
                frame.style.height = (document.documentElement.clientHeight - 240) + 'px';
                frame.style.overflowY = 'scroll';
                frame.style.background = '#f3f3f3 url(<?php echo plugin_dir_url(__FILE__); ?>wpspin.gif) center center no-repeat';
                frame.style.position = 'fixed';
                frame.style.zIndex = '999996';
                frame.style.top = '120px';
                frame.style.left = (document.documentElement.clientWidth/2 - 250) + 'px';
                frame.innerHTML = '<img style="display:block;margin:25px auto" src="' + txt2img_preview.href + '" alt="预览长微博" />';
                var close = document.createElement('img');
                close.style.width = '30px';
                close.style.height = '30px';
                close.style.cursor = 'pointer';
                close.style.position = 'fixed';
                close.style.zIndex = '999999';
                close.style.top = '105px';
                close.style.left = (document.documentElement.clientWidth/2 - 265) + 'px';
                close.src = '<?php echo plugin_dir_url(__FILE__); ?>close.png';
                document.body.appendChild(mask);
                document.body.appendChild(frame);
                document.body.appendChild(close);
                listen(close, 'click', function() {
                    document.body.removeChild(mask);
                    document.body.removeChild(frame);
                    document.body.removeChild(close);
                });
                if(e && e.preventDefault) {
                    e.preventDefault();
                } else {
                    window.event.returnValue = false;
                }
                return false;
            };
            var share = function(e) {
                var post_url = document.getElementById('view-post-btn').getElementsByTagName('a')[0].href;
                window.open("http://service.weibo.com/share/share.php?url=" + post_url + "&appkey=&title=" + post_title.value + "%20（山寨长微博工具%20by%20@米粽my）&pic=" + txt2img_url + "&ralateUid=&language=zh_cn", "txt2img", "width=615,height=505");
                if(e && e.preventDefault) {
                    e.preventDefault();
                } else {
                    window.event.returnValue = false;
                }
            };
            listen(txt2img_generate, 'click', prepare);
            listen(txt2img_generater, 'load', render);
            listen(txt2img_preview, 'click', preview);
            listen(txt2img_share, 'click', share);
        })();
    </script>
    <?php }
}
add_action('admin_footer', 'txt2img_main');

?>