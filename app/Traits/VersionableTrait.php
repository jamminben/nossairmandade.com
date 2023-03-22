<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class VersionableTrait
 * @package Mpociot\Versionable
 */
trait VersionableTrait 
{
    use \Mpociot\Versionable\VersionableTrait;


    public static function initializeVersionOnAllRows()
    {
        foreach (self::all() as $obj) {
            $obj->createInitialVersion();
        }
        return true;
    }

    /**
     * Save a new version.
     * @return void
     */
    public function createInitialVersion()
    {
        if(true === $this->versions->isEmpty()) {

            $class                     = $this->getVersionClass();
            $version                   = new $class();
            $version->versionable_id   = $this->getKey();
            $version->versionable_type = method_exists($this, 'getMorphClass') ? $this->getMorphClass() : get_class($this);
            $version->user_id          = $this->getAuthUserId();
            
            $versionedHiddenFields = $this->versionedHiddenFields ?? [];
            $this->makeVisible($versionedHiddenFields);
            $version->model_data       = serialize($this->attributesToArray());
            $this->makeHidden($versionedHiddenFields);

            if (!empty( $this->reason )) {
                $version->reason = $this->reason;
            }

            $version->save();
        }
    }

}
