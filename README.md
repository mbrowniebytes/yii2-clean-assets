Yii2 command extension to clean Yii web/assets/ directory
===================================

While developing an app, and using Yii2 assets, as you make changes to js and css files, the web/assets/ directory will fill up with cached assets.  

This extension removes old caches from Yii's web/assets/ directory

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require mbrowniebytes/yii2-clean-assets:0.1.0
```

or add

```json
"mbrowniebytes/yii2-clean-assets": "0.1.0"
```

to the require section of your composer.json.


Usage
-----

To use this extension, add the following code in your application configuration 
- basic template: config/console.php:
- advanced template: common/config/main-local.php
```php
'controllerMap' => [
    'clean-assets' => [
        'class' => 'mbrowniebytes\yii2cleanassets\CleanAssetsController',
    ],
],
```

Then call the extension from the command line 

```
/path/yii/web/assets> ls -l
08/2/2017  05:07 PM    <DIR>          1069cc51
08/2/2017  05:07 PM    <DIR>          324e82f6
08/4/2017  09:29 AM    <DIR>          8443a260
08/4/2017  10:39 AM    <DIR>          a66e14a3
08/4/2017  09:28 AM    <DIR>          a7594ccf

/path/yii> php yii clean-assets dry-run verbose keep=2
Checking web/assets/ to remove old caches ..
would have removed web/assets/1069cc51, last modified Aug 2, 2017 05:07:21 PM
would have removed web/assets/324e82f6, last modified Aug 2, 2017 05:07:22 PM
would have removed web/assets/a7594ccf, last modified Aug 4, 2017 09:28:26 PM
Done. Removed 0 web/assets/ caches
```

Since keep=2 was supplied, the 2 newest caches would be kept.

You could also call the extension in a common event so assets are 'auto' cleaned
```php
<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\commands\CleanAssetsController as CleanAssets;

class BaseController extends Controller
{
	private function cleanAssets()
	{
		// keep dev/test env clean; remove old web/assets/ caches
		if (YII_ENV != 'prod' && rand(0, 100) < 30) {
			$clean_assets = new CleanAssets('CleanAssetsController', 'command');
			$clean_assets->keep = 4;
			$clean_assets->silent = true;
			$nbr_cleanded = $clean_assets->cleanAssetDirs();
		}
	}

	public function afterAction($action, $result)
	{
		$this->cleanAssets();
		
		return parent::afterAction($action, $result);
	}
}

<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class MyController extends BaseController
{
    public function actionIndex()
    {
    }
}

```

Additional arguments
-------------------
```
dry-run     	show what would happen; do not delete anything
verbose     	show more info; echo rules being run, dir/file being deleted
silent		do not echo anything
keep=#		nbr asset dirs to keep; might be one or more for app, one for toolbar, etc
structure=a 	based on yii2 recommended structures, set asset dirs to clean; advanced, basic, auto (default)
dirs=a,b    	list of custom asset dirs to clean, comma seperated
```

Change log
-----
08-25-2015 tag 0.0.1  
- exposes cleanAssetDir()
- options have a dash ie `-verbose` for earlier yii 2.0.?

08-07-2017 tag 0.1.0 
- issue [#1](../../issues/1) support advanced template
- exposes new cleanAssetDirs()
- cleanAssetDir($dir) now requires a param
- options are now without a dash ie `verbose` for yii 2.0.13  

