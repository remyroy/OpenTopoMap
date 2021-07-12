<!doctype html> <?php
    $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    $langdef = json_decode(file_get_contents("l/lang.json"),false);
    if (array_search($lang, $langdef->languages) === false) {
      $lang = $langdef->defaultLanguage;
    }
    echo '<html lang="' . $lang . '">';
    $locdef = json_decode(file_get_contents("l/" . $lang . ".json"),false);
  ?> <head><title><?php echo $locdef->sitehead->title; ?></title> <?php echo '<meta name="description" content="' . $locdef->sitehead->description . '">'; ?> <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,viewport-fit=cover"/><meta name="apple-mobile-web-app-capable" content="yes"/><meta name="apple-mobile-web-app-status-bar-style" content="black"/><meta http-equiv="Content-Type" content="text/html;charset=utf-8"/><meta http-equiv="x-ua-compatible" content="IE=edge"/><link rel="shortcut icon" type="image/x-icon" href="https://www.mountainpanoramas.com/____otm-test/favicon.ico"></head><body><div id="map"><noscript><table style="width:100%;height:100%;"><tr style="vertical-align:middle;text-align:center;"><td>ERROR:<br><br>Javascript not activated<br><br></td></tr></table></noscript></div><script src="https://www.mountainpanoramas.com/____otm-test/293698f812104c6bbbd7.js"></script></body>