<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    public $timestamps = false;

    protected $fillable = [];

    public function getUrl() {
        if( 'hymn' == $this->entity_type ) {
            return route('get-edit-hymn', ['action'=>'load', 'hymnId'=>$this->entity_id]);
        } else if ( 'hinario' == $this->entity_type ) {
            return route('get-hinario', [$this->entity_id]);
        } else if ( 'person' == $this->entity_type ) {
            return route('get-edit-person', ['action'=>'load', 'personId'=>$this->entity_id]);
        }
    }
}
