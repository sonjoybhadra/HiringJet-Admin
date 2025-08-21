<?php
use App\Helpers\Helper;
use App\Models\GeneralSetting;
$generalSetting             = GeneralSetting::find('1');
?>
<!doctype html>
<html lang="en">
  <head>
    <title><?=Helper::getSettingValue('site_name')?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  </head>
  <body style="padding: 0; margin: 0; box-sizing: border-box;">
    <section style="padding: 80px 0; height: 380vh; margin: 0 3px;">
        <div style="max-width: 900px; background: #ffffff; margin: 0 auto; border-radius: 15px; padding: 20px 15px; box-shadow: 0 0 30px -5px #ccc;">
          <div style="text-align: center;">
            <img src="<?=env('UPLOADS_URL').Helper::getSettingValue('site_logo')?>" alt="<?=Helper::getSettingValue('site_name')?>" style=" width: 100%; max-width: 250px;">
          </div>
          <div>
            <?=$content?>
          </div>
        </div>
        <div style="border-top: 2px solid #ccc; margin-top: 50px; text-align: center; font-family: sans-serif;">
          <div style="text-align: center; margin: 15px 0 10px;">All right reserved: © <?=date('Y')?> <?=Helper::getSettingValue('site_name')?></div>
        </div>
      </div>
    </section>
  </body>
</html>