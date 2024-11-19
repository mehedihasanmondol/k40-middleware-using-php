<?php
/**
* @name PWAssist.php
* Task: create service worker js code with all resources listed in "caching list"
* @author Alexander Selifonov, <alex [at] selifan {dot} ru>
* @link https://github.com/selifan/PWAssist
* @version 0.10
* Created 2017-12-03
* Updated 2018-01-10
*/

class PWAssistCloud extends PWAssist {

    public static function createIcons() {
        if (empty(self::$cfg['baseIcon'])) return '';
        if (!function_exists('imagecopyresized')) return 'GD not installed in Your PHP configuration, icons not created';


        $ftype = strtolower(substr(self::$cfg['baseIcon'], -4));
        switch ($ftype) {
            case '.png':
                $srcImg = imagecreatefrompng(self::$cfg['baseIcon']);
                break;
            case '.gif':
                $srcImg = imagecreatefromgif(self::$cfg['baseIcon']);
                break;
            case 'webp':
                $srcImg = imagecreatefromwebp(self::$cfg['baseIcon']);
                break;
            case '.jpg': case 'jpeg':
            $srcImg = imagecreatefromjpeg(self::$cfg['baseIcon']);
            break;
            default:
                return "Unsupported Icon image type";
        }

        list($srcWidth, $srcHeight) = getimagesize(self::$cfg['baseIcon']);
        $ret = '';
        foreach(explode(',', self::$cfg['iconResolutions']) as $res) {
            $newSize = (int)$res;
            $rr = "{$newSize}x{$newSize}";
            $iconName = str_replace('{size}', "$res" , self::$cfg['iconFilenameTemplate']);
            $img2 = imagecreatetruecolor($newSize,$newSize);
            imagealphablending($img2, false);
            imagesavealpha($img2, true); // saving transparent pixels
            // imagecopyresampled($newimg, $img,0,0,0,0,$sizeX,$sizeY,$old_x,$old_y); imagecopyresized
            imagecopyresampled($img2,$srcImg,0,0,0,0,$newSize,$newSize,$srcWidth,$srcHeight);
            ob_start();
            imagewebp($img2,null, self::$cfg['pngQuality']);
            $image_stream = ob_get_contents();
            ob_end_clean();
            $cloud_storage = new CloudStorageBucket();
            $cloud_storage->upload_raw_stream($image_stream,$iconName);

            imagedestroy($img2);
        }
        imagedestroy($srcImg);

        return $ret;
    }

    public static function createManifest() {
        $cloud_storage = new CloudStorageBucket();
        $web_site_instance = new WebSiteHandShack();
        $cfg = self::$cfg;
        $iconsbody = array();


        foreach(explode(',',$cfg['iconResolutions']) as $res) {
            $rr = "{$res}x{$res}";
            $iconName = str_replace('{size}', "$res" , $cloud_storage->file_prefix.$web_site_instance->customer_id."/".$cfg['iconFilenameTemplate']."?appVersion=".$cfg['appVersion']);
            $iconsbody[] = "{ \"src\": \"$iconName\", \"sizes\": \"$rr\", \"type\": \"image/webp\" }";
        }
        $iconsText = implode("\n    ,", $iconsbody);
        $manifestBody = <<< EOJSON
{
  "name": "$cfg[appName]",
  "short_name": "$cfg[appShortName]",
  "lang": "$cfg[lang]",
  "description": "$cfg[appDesc]",
  
  "start_url": "$cfg[startUri]",
  "Scope": "/",
  "display": "standalone",
  "background_color": "$cfg[backgroundColor]",
  "theme_color": "$cfg[themeColor]",
  "icons": [
    $iconsText
  ]
}
EOJSON;


        file_put_contents(($cfg['startFolder'] . self::$cfg['manifestFilename']), $manifestBody);
        return self::$cfg['manifestFilename'];

    }
    public static function setVersion($version) {
        self::$cfg['appVersion'] = $version;

    }

    public static function createSW() {
        $sw_tplname = PROJECT_ROOT.self::$cfg['swTemplate'];

        if (!empty(self::$cfg['swTemplate']) && is_file($sw_tplname)) $swCode = file_get_contents($sw_tplname);
        else {
            if (!empty(self::$cfg['swTemplate']))
                return ('SW generating failed: template file not found :' . self::$BR . $sw_tplname);
            $swCode = <<< EOJS
// Service Worker for PWA {app_name} {app_desc}
// Generated {created_date} by PWAssist.php
// Author {author}
// Copyright {copyright}
// version: {version}

service worker code here
EOJS;
        }
        $appname = (self::$cfg['appName']) ? self::$cfg['appName'] :
            ucwords(str_replace(array('-','_'), ' ', self::$cfg['cacheName']));
        $replarr = array(
            '{created_date}'  => date('d.m.Y H:i:s')
        ,'{version}'      => self::$cfg['appVersion']
        ,'{author}'       => self::$cfg['author']
        ,'{copyright}'    => self::$cfg['copyRight']
        ,'{cachename}'    => self::$cfg['cacheName']
        ,'{app_name}'     => $appname
        ,'{app_desc}'     => self::$cfg['appDesc']
        );

        # add user-defined cfg values to replace list
        foreach(self::$cfg as $key => $val) {
            if (is_string($val) && !in_array($key, self::$_skip_keys, true))
                $replarr["{{$key}}"] = $val;
        }

        $swCode = strtr($swCode, $replarr);
        file_put_contents(PROJECT_ROOT.self::$cfg['swFilename'], $swCode);
        return self::$cfg['swFilename'];

    }

} // PWAssist end
