<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\FileHelper;
use Yii;

/*
 * Clean web/assets/ caches
 */
class CleanAssetsController extends Controller
{
	public $dry_run = false;
	public $verbose = false;
	public $silent = false;
	/*
	 * nbr asset dirs to keep; might be one or more for app, one for toolbar, etc
	 */
	public $keep = 5;
	public $asset_dir = 'web/assets';


	public function actionIndex()
    {
		foreach (Yii::$app->requestedParams as $param) {
			if (stripos($param, 'dry-run') !== false || stripos($param, 'dryrun') !== false) {
				$this->dry_run = true;
			} else if (stripos($param, 'verbose') !== false) {
				$this->verbose = true;
			} else if (stripos($param, 'silent') !== false) {
				$this->silent = true;
			} else if (stripos($param, 'keep=') !== false) {
				$keep = substr($param, strpos($param, '=') + 1);
				$this->keep = (int)$keep;
			}
		}

		$this->echo_msg('Checking '.$this->asset_dir.'/ to remove old caches .. ');

		$nbr_cleaned = self::cleanAssetDir();

		$this->echo_msg('Done. Removed '.$nbr_cleaned.' '.$this->asset_dir.'/ cache'.($nbr_cleaned == 1 ? '' : 's'));

		return 0;
    }

	private function echo_msg($msg, $show=true)
	{
		if (!$this->silent && ($this->dry_run || $this->verbose || $show)) {
			echo $msg."\n";
		}
	}

	/*
	 * remove prior asset dirs
	 *
	 * @return void
	 */
	public function cleanAssetDir()
	{

		$now = time();

		$asset_temp_dirs = glob($this->asset_dir . '/*' , GLOB_ONLYDIR);

		// check if less than want to keep
		if (count($asset_temp_dirs) <= $this->keep) {
			return 0;
		}

		// get all dirs and sort by modified
		$modified = [];
		foreach ($asset_temp_dirs as $asset_temp_dir) {
			$modified[$asset_temp_dir] = filemtime($asset_temp_dir);
		}
		asort($modified);
		$nbr_dirs = count($modified);

		// keep last dirs
		for ($i = min($nbr_dirs, $this->keep); $i > 0; $i--) {
			array_pop($modified);
		}

		if ($this->dry_run) {
			$msg_try = 'would have ';
		} else {
			$msg_try = '';
		}

		// remove dirs
		foreach ($modified as $dir => $mod) {
			$this->echo_msg($msg_try.'removed '.$dir.', last modified '.Yii::$app->formatter->asDatetime($mod));
			if (!$this->dry_run) {
				FileHelper::removeDirectory($dir);
			}
		}

		return $this->dry_run ? 0 : $nbr_dirs;
	}
}
