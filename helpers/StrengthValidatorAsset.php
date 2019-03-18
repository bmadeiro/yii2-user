<?php
namespace dektrium\user\helpers;

use yii\web\AssetBundle;

class StrengthValidatorAsset extends AssetBundle
{
    public $sourcePath = '@dektrium/user/assets';

    public $js = [
        'js/strength-validation.js',
    ];
}
