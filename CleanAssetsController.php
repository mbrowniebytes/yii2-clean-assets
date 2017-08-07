<?php
namespace mbrowniebytes\yii2cleanassets;

use yii\console\Controller;
use yii\console\Exception;
use yii\helpers\FileHelper;
use Yii;

/*
 * Clean web/assets/ caches
 */
class CleanAssetsController extends Controller
{
    // show what would happen; do not delete anything
    public $dry_run = false;
    
    // show more info; echo rules being run, dir/file being deleted
    public $verbose = false;
    
    // do not echo anything
    public $silent = false;
    
    // nbr asset dirs to keep; might be one or more for app, one for toolbar, etc
    public $keep = 5;
    
    // based on yii2 recommended structures, set asset dirs to clean
    // advanced, basic, auto (both)
    public $structure = 'auto';
    
    // list of custom asset dirs to clean, comma seperated
    public $dirs = [];


    /**
     * console controller
     * 
     * @return int
     */
    public function actionIndex()
    {
        try {            
            $this->parseRequestedParams();

            $this->setAssetDirs();

            $this->cleanAssetDirs();
            
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
            return $e->getCode();
        }
        return 0;
    }

    private function echo_msg($msg, $show=true)
    {
        if (!$this->silent && ($this->dry_run || $this->verbose || $show)) {
            echo $msg."\n";
        }
    }

    /**
     * parse the requested params
     */
    private function parseRequestedParams() 
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
            } else if (stripos($param, 'structure=') !== false) {
                $structure = substr($param, strpos($param, '=') + 1);
                if (!in_array($structure, ['advanced', 'auto', 'basic'])) {
                  $this->structure = 'auto';
                }
                $this->structure = $structure;
            } else if (stripos($param, 'dirs=') !== false) {
                $dirs = substr($param, strpos($param, '=') + 1);
                $this->dirs = explode(',', $dirs);
            }
        }
    }
    
    /**
     * set asset dirs
     */
    private function setAssetDirs() 
    {
        if ($this->structure == 'basic') {
            array_push($this->dirs, 'web/assets');
        } else if ($this->structure == 'advanced') {
            array_push($this->dirs, 'frontend/web/assets');
            array_push($this->dirs, 'backend/web/assets');
        } else { // 'auto'
            array_push($this->dirs, 'web/assets');
            array_push($this->dirs, 'frontend/web/assets');
            array_push($this->dirs, 'backend/web/assets');
        }
    }
    
    /**
     * clean all asset dirs
     */
    public function cleanAssetDirs()
    {
        foreach ($this->dirs as $asset_dir) {
            if (!is_dir($asset_dir)) {
                $this->echo_msg('Did not find '.$asset_dir.'/ .. skipping');
                continue;
            }
            $this->echo_msg('Checking '.$asset_dir.'/ to remove old caches .. ');

            $nbr_cleaned = self::cleanAssetDir($asset_dir);

            $this->echo_msg('Removed '.$nbr_cleaned.' '.$asset_dir.'/ cache'.($nbr_cleaned == 1 ? '' : 's'));
        }
        $this->echo_msg('Finished');
    }
    
    /**
     * clean asset dir
     * may remove subdirs in asset dir 
     *
     * @param string $asset_dir
     * @return int
     */
    public function cleanAssetDir($asset_dir)
    {

        $now = time();

        $asset_temp_dirs = glob($asset_dir . '/*' , GLOB_ONLYDIR);

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
