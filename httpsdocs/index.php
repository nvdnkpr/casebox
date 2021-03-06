<?php
namespace CB;

require_once 'init.php';

if (empty($_SESSION['user'])) {
    exit(header('Location: /login.php'));
}

L\checkTranslationsUpToDate();

require_once(CONFIG\MINIFY_PATH . 'utils.php');
$projectTitle = @constant('CB\\CONFIG\\PROJECT_NAME_'.strtoupper(USER_LANGUAGE));

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="author" content="KETSE">
    <meta name="description" content="Casebox">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" href="/i/casebox-logo.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="/libx/ext/resources/css/ext-all.css" />
<?php

// echo '<link rel="stylesheet" type="text/css" href="/libx/ext/resources/css/ext-all.css" />' . "\n";
echo '<link rel="stylesheet" type="text/css" href="'. Minify_getUri('css') . '" />' . "\n";

// Custom CSS for the core
$css = Config::getCssList();
if (!empty($css)) {
    echo '<link rel="stylesheet" type="text/css" href="' . Minify_getUri(CORE_NAME . '_css') . '" />' . "\n";
}

echo '<title>' . $projectTitle . '</title>' . "\n";

?>
<style>
#loading {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1000;
    background-color: #fff;
}

#loading, #stb {
background-color: #f5f5f5;
}

.cmsg {
margin: 1em;
}

.msg {
    margin-top: 150px;
    text-align: center;
    font-weight: bold;
    margin-bottom: 5px;
}

.lpb {
    text-align: center;
    width: 320px;
    border: 1px solid #999;
    padding: 1px;
    height: 8px;
    margin-right: auto;
    margin-left: auto;
}


@-webkit-keyframes pb { 0% { background-position:0 0; } 100% { background-position:-16px 0; } }

#lpt {
width: 0;
height: 100%;
background-color: #6188f5;
background-repeat: repeat-x;
background-position: 0 0;
background-size: 16px 8px;
background-image: -webkit-linear-gradient(315deg,transparent,transparent 33%,rgba(0,0,0,0.12) 33%,rgba(0,0,0,0.12) 66%,transparent 66%,transparent);
background-image: -moz-linear-gradient(315deg,transparent,transparent 33%,rgba(0,0,0,0.12) 33%,rgba(0,0,0,0.12) 66%,transparent 66%,transparent);
background-image: -o-linear-gradient(315deg,transparent,transparent 33%,rgba(0,0,0,0.12) 33%,rgba(0,0,0,0.12) 66%,transparent 66%,transparent);
background-image: linear-gradient(315deg,transparent,transparent 33%,rgba(0,0,0,0.12) 33%,rgba(0,0,0,0.12) 66%,transparent 66%,transparent);
-webkit-animation: pb 0.8s linear 0 infinite;
}

.msgb {
    position: absolute;
    right: 0;
    font-size: 11px;
    font-weight: normal;
    color: #555;
    background: #fff;
    padding: 20px;
}

.msgb a {
    color: #777;
}
</style>


<script type="text/javascript">
    function setProgress(label, percentage) {
        document.getElementById('loading-msg').innerHTML = label + '…';
        document.getElementById('lpt').style.width = percentage;
    }
</script>
</head>

<body>



<div style="font-size:0px;color:white;z-index:-9;position:absolute;left:-999px">
</div>


<div id="loading">
    <div class="cmsg">
        <div class="msg" id="loading-msg">
            Loading Casebox (<?php echo $projectTitle ?>)…
        </div>
        <div class="lpb">
            <div id="lpt" style="width: 50%;"></div>
        </div>
    </div>

    <div id="stb" class="msgb" style="bottom:10px">
        <a href="https://www.casebox.org/">www.casebox.org</a> <span style="color: #AAA; padding-left: 2px; padding-right: 5px">&bull;</span>  <a href="http://forum.casebox.org/">Support forum</a>
    </div>

    <div id="loadingError" class="cmsg" style="clear:left;display:none">
        <p style="font-size:larger;margin:40px 0">
        This is taking longer than usual.
        <a href="https://core.casebox.org"><b>Try reloading the page</b></a>.
        </p>

        <div>
        ...
        </div>
    </div>
</div>

<!--

<div id="loading-mask"></div>
<div id="loading" style="width: 250px">
        <div>
        <img src="/css/default/d/loader.gif" width="32" height="32" style="margin-right:10px;float:left;vertical-align:top; margin-top: -5px" alt="Loading ..." />
        <span style="color: #003399; padding-right: 2px">Case</span><span style="color: #3AAF00;">Box</span><br /><span id="loading-msg"><?php echo L\get('Loading_CSS')?> ...</span>
        </div>
</div>
-->

<script type="text/javascript">setProgress('<?php echo L\get('Loading_ExtJS_Core')?>', '20%')</script>
<script type="text/javascript" src="<?php echo EXT_PATH ?>/adapter/ext/ext-base.js"></script>
<script type="text/javascript">setProgress('<?php echo L\get('Loading_ExtJS_Core')?>', '30%')</script>
<script type="text/javascript" src="<?php echo EXT_PATH ?>/ext-all<?php echo isDebugHost() ? '-debug' : ''; ?>.js"></script>

<?php

if (!empty($_SESSION['user']['language']) && ($_SESSION['user']['language'] != 'en')) {

    // ExtJS locale
    if (file_exists(DOC_ROOT.EXT_PATH . '/src/locale/ext-lang-' . $_SESSION['user']['language'] . '.js')) {
        echo '<script type="text/javascript" src="' . EXT_PATH . '/src/locale/ext-lang-' . $_SESSION['user']['language'] . '.js"></script>';
    }

    // Casebox locale
    echo '<script type="text/javascript" src="' . Minify_getUri('lang-' . $_SESSION['user']['language']) . '"></script>';
} else {
    // default Casebox locale
    echo '<script type="text/javascript" src="'.Minify_getUri('lang-en').'"></script>';
}

?>


<script type="text/javascript">setProgress('<?php echo L\get('Loading_ExtJS_UI')?>', '60%')</script>

<script type="text/javascript" src="/remote/api.php"></script>


<?php

echo '<script type="text/javascript" src="'.Minify_getUri('js').(isDebugHost() ? '&debug=1': '').'"></script>';
echo '<script type="text/javascript" src="'.Minify_getUri('jsdev').(isDebugHost() ? '&debug=1': '').'"></script>';
$js = Config::getJsList();
if (!empty($js)) {
    echo '<script type="text/javascript" src="'.Minify_getUri(CORE_NAME.'_js').(isDebugHost() ? '&debug=1': '').'"></script>';
}
$prc = Config::getPluginsRemoteConfig();
if (!empty($prc)) {
    echo '<script type="text/javascript">CB.plugins.config = '.json_encode($prc, JSON_UNESCAPED_UNICODE).';</script>';
}

?>
<script type="text/javascript" src="/js/CB.DB.php"></script>

<script type="text/javascript">setProgress('<?php echo L\get('Initialization')?>', '100%')</script>


</body>
</html>

<!-- #loading-mask{ position: absolute; left: 0; top: 0; width: 100%; height: 100%; z-index: 20000; background-color: white }
#loading{ position: absolute; left: 45%; top: 40%; padding: 2px; z-index:20001; height:auto }
#loading a{ color:#225588 }
#loading .loading-indicator{ background-color: white; color: #444; font: bold 18px tahoma,arial,helvetica; padding: 10; margin: 0; height: auto }
#loading-msg { font: normal 10 arial,tahoma,sans-serif }
 -->
