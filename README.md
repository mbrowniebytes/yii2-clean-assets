Yii2 command extension to clean Yii web/assets/ directory
===================================

While developing an app, and using Yii2 assets, as you make changes to js and css files, the web/assets/ directory will fill up with cached assets.  

This extension removes old caches from Yii's web/assets/ directory

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mbrowniebytes/yii2-clean-assets:dev-master
```

or add

```json
"mbrowniebytes/yii2-clean-assets": "dev-master"
```

to the require section of your composer.json.


Usage
-----

To use this extension, add the following code in your application configuration (console.php):
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
08/18/2015  05:07 PM    <DIR>          1069cc51
08/18/2015  05:07 PM    <DIR>          324e82f6
08/20/2015  09:28 AM    <DIR>          8443a260
08/20/2015  10:39 AM    <DIR>          a66e14a3
08/20/2015  09:28 AM    <DIR>          a7594ccf

/path/yii> php yii clean-assets -dry-run -verbose -keep=2
Checking web/assets/ to remove old caches ..
would have removed web/assets/1069cc51, last modified Aug 18, 2015 10:07:20 PM
would have removed web/assets/324e82f6, last modified Aug 18, 2015 10:07:20 PM
would have removed web/assets/a7594ccf, last modified Aug 20, 2015 2:28:26 PM
Done. Removed 0 web/assets/ caches
```

Since -keep=2 was supplied, the 2 newest caches would be kept.

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
			$clean_assets = new CleanAssets();
			$clean_assets->keep = 4;
			$clean_assets->silent = true;
			$nbr_cleanded = $clean_assets->cleanAssetDir();
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
-dry-run	do not delete anything
-verbose	echo rules being run, dir/file being deleted
-silent		do not echo anything
-keep=#		nbr asset dirs to keep; might be one or more for app, one for toolbar, etc
```
