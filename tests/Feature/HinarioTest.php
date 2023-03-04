<?php

namespace Tests\Feature;

use App\Models\Hinario;
use App\Models\Hymn;
use App\Models\HymnHinario;
use App\Models\HymnNotationTranslation;
use App\Models\HymnTranslation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HinarioTest extends TestCase
{

    public function test_getLastUpdate() {
        
        $h = Hinario::factory()->create();
        $time = new Carbon(time()-20);
        $h->forceFill([
            'updated_at'=>$time
        ]);
        $h->save();
        $this->assertEquals($time, $h->getLastUpdate());


        $hymn = Hymn::factory()->create([
            'received_hinario_id'=>$h->id,
        ]);
        $time = new Carbon(time()-15);
        $hymn->forceFill([
            'updated_at'=>$time
        ]);
        $hymn->save();
        HymnHinario::insert([
            'hymn_id'=>$hymn->id,
            'hinario_id'=>$h->id,
        ]);
        $this->assertEquals($time, $h->getLastUpdate(), "now: ". now());

        
        $t = HymnTranslation::insertGetId([
            'hymn_id'=>$hymn->id
        ]);
        $t = HymnTranslation::find($t);
        $time = new Carbon(time()-10);
        $t->forceFill([
            'updated_at'=>$time
        ]);
        $t->save();
        $this->assertEquals($time, $h->getLastUpdate(), "now: ". now());
        
    }
}
