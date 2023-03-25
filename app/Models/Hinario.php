<?php
namespace App\Models;

use App\Enums\HinarioTypes;
use App\Enums\Languages;
use App\Enums\MediaTypes;
use App\Services\GlobalFunctions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class Hinario extends ModelWithTranslations
{
    use HasFactory;

    // public $timestamps = false;

    protected $fillable = [];

    public $return_pdf_content = true;

    public function __construct()
    {
        parent::__construct();
        $this->entityName = 'hinario';
    }

    public function getOtherMedia()
    {
        $media = [];
        foreach ($this->mediaFiles as $mediaFile) {
            if ($mediaFile->media_type_id != MediaTypes::HINARIO_ZIP) {
                $media[] = $mediaFile;
            }
        }

        return $media;
    }

    public function getPreviousHymn($hymnId)
    {
        $currentHymnHinario = HymnHinario::where('hinario_id', $this->id)->where('hymn_id', $hymnId)->first();
        $previousHymnHinario = HymnHinario::where('hinario_id', $this->id)
            ->where('list_order', '<', $currentHymnHinario->list_order)
            ->orderBy('list_order', 'DESC')
            ->first();
        if (!empty($previousHymnHinario)) {
            return $previousHymnHinario->hymn;
        }

        return null;
    }

    public function getNextHymn($hymnId)
    {
        $currentHymnHinario = HymnHinario::where('hinario_id', $this->id)->where('hymn_id', $hymnId)->first();
        $nextHymnHinario = HymnHinario::where('hinario_id', $this->id)
            ->where('list_order', '>', $currentHymnHinario->list_order)
            ->orderBy('list_order', 'ASC')
            ->first();
        if (!empty($nextHymnHinario)) {
            return $nextHymnHinario->hymn;
        }

        return null;
    }

    public function getRecordingSources()
    {
        $counts = [];
        $sources = [];

        foreach ($this->hymns as $hymn) {
            $recordings = $hymn->getRecordings();
            foreach ($recordings as $recording) {
                $sources[$recording->source->id] = $recording->source;
                if (isset($counts[$recording->source->id])) {
                    $counts[$recording->source->id] += count($recording->upvotes);
                } else {
                    $counts[$recording->source->id] = count($recording->upvotes);
                }
            }
        }

        if ($this->type_id == HinarioTypes::COMPILATION && file_exists('/home/dh_nossa/nossairmandade.com/public/media/hinarios/' . $this->id . '/-1/' .$this->getName($this->original_language_id) .'.zip')) {
            $mixed = MediaSource::where('id', -1)->first();
            $sources[-1] = $mixed;
            $counts[-1] = 10000000;
        }

        asort($counts);

        $finalSources = [];

        foreach (array_keys($counts) as $sourceId) {
            foreach ($sources as $source) {
                if ($source->id == $sourceId) {
                    $finalSources[] = $source;
                }
            }
        }

        return $finalSources;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        $name = $this->getName($this->original_language_id);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        $name = 'hinario/' . $this->id . '/' . $name;
        return $name;
    }

    public function getSections()
    {
        $sections = [];
        foreach ($this->hymnHinarios as $hymnHinario) {
            $sections[$hymnHinario->section_number] = $hymnHinario->getSection();
        }

        ksort($sections);

        return array_values($sections);
    }

    public function getHymnsForSection($sectionNumber)
    {
        $hymnsQuery = Hymn::join('hymn_hinarios', 'hymns.id', '=', 'hymn_hinarios.hymn_id')
            ->where('hymn_hinarios.hinario_id', '=', $this->id)
            ->select('hymns.*')
            ->with('notationTranslations', 'translations', 'hymnHinarios')
            ->orderBy('hymn_hinarios.list_order');

        if ($sectionNumber != 0) {
            $hymnsQuery->where('hymn_hinarios.section_number', '=', $sectionNumber);
        }

        $hymns = $hymnsQuery->get();

        return $hymns;
    }

    public function hasTranslationsForLanguage($language_id)
    {
        $totalHymns = count($this->hymns);
        $translatedHymns = 0;
        foreach ($this->hymns as $hymn) {
            if (!empty($hymn->getTranslation($language_id))) {
                $translatedHymns++;
            }
        }

        if ($translatedHymns == $totalHymns) {
            return true;
        } else {
            return false;
        }
    }

    public function getLastUpdate() {
        $time = $this->updated_at;
        //@todo
        // $this->image_path
        // I'M NOT SURE HOW THE image_path IS SAVED, SO DON'T KNOW IF WE NEED public_path(), ETC
        if( $this->receivedBy ) {
            $file_updated_at = \Carbon\Carbon::createFromTimestamp(
                filemtime($this->receivedBy->getPortraitImage())
            );
            if($file_updated_at > $time) {
                $time = $file_updated_at;
            }
        }
        foreach($this->refresh()->hymns as $hymn) {
            if($hymn->updated_at > $time) {
                $time = $hymn->updated_at;
            }
            foreach($hymn->translations as $t) {
                if($t->updated_at > $time) {
                    $time = $t->updated_at;
                }
            }
        }
        if(is_null($time)) {
            return null;
        } else {
            return $time->timestamp;
        }
    }


    /**************************
     **    Relationships     **
     **************************/

    public function hymns()
    {
        return $this->hasManyThrough(
            Hymn::class,
            HymnHinario::class,
            'hinario_id',
            'id',
            'id',
            'hymn_id')
            ->orderBy('hymn_hinarios.list_order');
    }

    public function sections()
    {
        return $this->hasMany(HinarioSection::class, 'hinario_id', 'id')
            ->orderBy('section_number');
    }

    public function hymnHinarios()
    {
        return $this->hasMany(HymnHinario::class, 'hinario_id', 'id')
            ->with('hymn', 'section')
            ->orderBy('section_number')
            ->orderBy('list_order');
    }

    public function media()
    {
        return $this->hasManyThrough(
            MediaFile::class,
            HinarioMediaFile::class,
            'hinario_id',
            'id',
            'id',
            'media_file_id');
    }

    public function church()
    {
        return $this->hasOne(Church::class, 'id', 'link_id');
    }

    public function mediaFiles()
    {
        return $this->hasManyThrough(
            MediaFile::class,
            HinarioMediaFile::class,
            'hinario_id',
            'id',
            'id',
            'media_file_id')
            ->with('source');
    }

    public function receivedBy()
    {
        return $this->hasOne(Person::class, 'id', 'link_id')
            ->with('translations', 'images', 'hinarios', 'personImages');
    }

    public function personLocalHinario()
    {
        return $this->hasOne(PersonLocalHinario::class, 'hinario_id', 'id');
    }


    public function getPdf() {
        if($pdf = Storage::disk('hinario_pdfs')->get($this->id)) {
            $lastModified = Storage::disk('hinario_pdfs')->lastModified($this->id);
            //IF HINARIO HAS BEEN UPDATED, RECACHE IT
            // Log::info(__FILE__.":".__LINE__);
            // Log::info( $lastModified . " " . $this->getLastUpdate() );
            // dd( $lastModified . " " . $this->getLastUpdate() );
            if ( $lastModified < $this->getLastUpdate()) {
                return $this->cachePdf();
            } else {
                if(!$this->return_pdf_content) {
                    return "pdf is already newer than last hinario modification";
                }
                return $pdf;
            }
        } else {
            return $this->cachePdf();
        }
    }



    public function writeStanza($mpdf, $stanzas, $loops = 0) {

        $loops++;
        $lineCount = 0;
        $preparedStanzas = [];
        $preparedStanzas2 = [];

        // Walk through remaining stanzas checking to see it there's room to add them
        for ($x = 0; $x < count($stanzas); $x++) {

            // if there is room for the new stanza, add it

            // dd($stanzas[$x]->getLines());
            // dd(count($stanzas[$x]->getLines()));

            if ($lineCount + count($stanzas[$x]->getLines()) <= 29) {
                $preparedStanzas[] = $stanzas[$x];
                $lineCount += count($stanzas[$x]->getLines());
                if ($x < (count($stanzas)-1)) {
                    $lineCount++;
                }

                if ($this->hasTranslation) {
                    if (!isset($this->stanzas2[$x])) {
                        // echo $x; die(print_r($this->stanzas2));
                    } else {
                        $preparedStanzas2[] = $this->stanzas2[$x];
                    }
                }

                // mamae o mamae
                if (count($preparedStanzas) == 4 && $this->hymn->pattern_id == 46
                ) {
                    $lineCount = 30;
                    $x = 1000;
                }

                // Joao Pereira #43
                if ($this->hymn->pattern_id == 52 && count($preparedStanzas) == 5) {
                    $lineCount = 30;
                    $x = 1000;
                }

                // hymn patterns that require starting each page on an even number
                if (in_array($this->hymn->pattern_id, [48, 52]) && (count($preparedStanzas) % 4 == 0)) {
                    $lineCount = 30;
                    $x = 1000;
                }

            } else {
                $x = 1000;
                $lineCount = 1000;
            }

        }

        // remove the prepared stanzas from the list
        array_splice($stanzas, 0, count($preparedStanzas));
        if ($this->hasTranslation) {
            array_splice($this->stanzas2, 0, count($preparedStanzas2));
        }

        // translated page
        if (count($preparedStanzas2) > 0) {

            if ($this->pageNumber == 1) {
                $mpdf->WriteHTML(view('hinarios.print.hymn_header', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => GlobalFunctions::getCurrentLanguage(), 'pageCount' => $this->totalPageCount])->render());
                $this->html .= view('hinarios.print.hymn_header', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => GlobalFunctions::getCurrentLanguage(), 'pageCount' => $this->totalPageCount])->render();
            } else {
                $mpdf->WriteHTML(view('hinarios.print.hymn_header_continued', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => GlobalFunctions::getCurrentLanguage(), 'pageCount' => $this->totalPageCount])->render());
                $this->html .= view('hinarios.print.hymn_header_continued', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => GlobalFunctions::getCurrentLanguage(), 'pageCount' => $this->totalPageCount])->render();
            }

            if (view()->exists('hymns.patterns.print.' . $this->hymn->pattern_id)) {
                $mpdf->WriteHTML(view('hymns.patterns.print.' . $this->hymn->pattern_id, [ 'stanzas' => $preparedStanzas2, 'pageNumber' => $this->pageNumber ])->render());
                $this->html .= view('hymns.patterns.print.' . $this->hymn->pattern_id, [ 'stanzas' => $preparedStanzas2, 'pageNumber' => $this->pageNumber ])->render();
            } else {
                $mpdf->WriteHTML(view('hymns.patterns.print.0', [ 'stanzas' => $preparedStanzas2, 'pageNumber' => $this->pageNumber ])->render());
                $this->html .= view('hymns.patterns.print.0', [ 'stanzas' => $preparedStanzas2, 'pageNumber' => $this->pageNumber ])->render();
            }

            if (count($this->stanzas2) == 0) {
                $mpdf->WriteHTML(view('hinarios.print.hymn_footer', ['hymn' => $this->hymn])->render());
                $this->html .= view('hinarios.print.hymn_footer', ['hymn' => $this->hymn])->render();
            } else {
                $mpdf->WriteHTML(view('hinarios.print.hymn_continued', ['hymn' => $this->hymn])->render());
                $this->html .= view('hinarios.print.hymn_continued', ['hymn' => $this->hymn])->render();
            }

            $this->totalPageCount++;
        }

        // page header - original language
        if ($this->pageNumber == 1) {
            $mpdf->WriteHTML(view('hinarios.print.hymn_header', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => $this->hinario->original_language_id, 'pageCount' => $this->totalPageCount ])->render());
            $this->html .= view('hinarios.print.hymn_header', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => $this->hinario->original_language_id, 'pageCount' => $this->totalPageCount])->render();
        } else {
            $mpdf->WriteHTML(view('hinarios.print.hymn_header_continued', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => $this->hinario->original_language_id, 'pageCount' => $this->totalPageCount])->render());
            $this->html .= view('hinarios.print.hymn_header_continued', ['hymn' => $this->hymn, 'hinario' => $this->hinario, 'language' => $this->hinario->original_language_id, 'pageCount' => $this->totalPageCount])->render();
        }

        // first page
        if (view()->exists('hymns.patterns.print.' . $this->hymn->pattern_id)) {
            $mpdf->WriteHTML(view('hymns.patterns.print.' . $this->hymn->pattern_id, [ 'stanzas' => $preparedStanzas, 'pageNumber' => $this->pageNumber ])->render());
            $this->html .= view('hymns.patterns.print.' . $this->hymn->pattern_id, [ 'stanzas' => $preparedStanzas, 'pageNumber' => $this->pageNumber ])->render();
        } else {
            $mpdf->WriteHTML(view('hymns.patterns.print.0', [ 'stanzas' => $preparedStanzas, 'pageNumber' => $this->pageNumber ])->render());
            $this->html .= view('hymns.patterns.print.0', [ 'stanzas' => $preparedStanzas, 'pageNumber' => $this->pageNumber ])->render();
        }

        if (count($stanzas) == 0) {
            $mpdf->WriteHTML(view('hinarios.print.hymn_footer', ['hymn' => $this->hymn])->render());
            $this->html .= view('hinarios.print.hymn_footer', ['hymn' => $this->hymn])->render();
        } else {
            $mpdf->WriteHTML(view('hinarios.print.hymn_continued', ['hymn' => $this->hymn])->render());
            $this->html .= view('hinarios.print.hymn_continued', ['hymn' => $this->hymn])->render();
        }

        if (count($stanzas) > 0) {
            $this->pageNumber++;
        }
        $this->totalPageCount ++;
        // if($loops > 100) return var_export($stanzas, 1); //return 'broken';
        
        if($loops > count($stanzas)) return;
        $this->writeStanza($mpdf, $stanzas, $loops);
        

    }

    public $pageNumber;
    public $totalPageCount;
    public $hasTranslation;
    public $hymn;
    public $hinario;
    public $html;
    public $stanzas2;


    public function cachePdf() {

        Log::info(__FILE__.":".__LINE__);
        Log::info("hinario id: " . $this->id);

        $this->html = '';
        $this->hinario = Hinario::where('id', $this->id)
            ->with(
                'translations',
                'sections',
                'hymns',
                'hymnHinarios',
                'hymnHinarios.hinario',
                'receivedBy',
                'hymns.mediaFiles',
                'hymns.translations',
                'hymns.hymnHinarios',
                'hymns.notationTranslations'
            )
            ->first();

        $sections = $this->hinario->getSections();

        $this->hinarioHasTranslations = $this->hinario->hasTranslationsForLanguage(GlobalFunctions::getCurrentLanguage());

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => [139, 216]]);

        $mpdf->WriteHTML(view('hinarios.print.title_page', [ 'hinario' => $this->hinario ])->render());
        $this->html .= view('hinarios.print.title_page', [ 'hinario' => $this->hinario ])->render();
        $this->totalPageCount = 2;

        $mpdf->setFooter('{PAGENO}');

        foreach ($sections as $section) {
            if (count($sections) > 1) {
                $mpdf->WriteHTML(view('hinarios.print.section_page', ['section' => $section])->render());
                $this->html .= view('hinarios.print.section_page', ['section' => $section])->render();
                $this->totalPageCount++;
            }

            foreach ($this->hinario->getHymnsForSection($section->section_number) as $this->hymn) {

                $stanzas = $this->hymn->stanzas($this->hymn->original_lanaguage_id);
                if ($this->hymn->original_language_id != GlobalFunctions::getCurrentLanguage()) {
                    $this->stanzas2 = $this->hymn->stanzas(GlobalFunctions::getCurrentLanguage());
                    if (!empty($this->stanzas2)) {
                        $this->hasTranslation = true;
                    } else {
                        $this->hasTranslation = false;
                    }
                } else {
                    $this->stanzas2 = [];
                    $this->hasTranslation = false;
                }

                if (($this->totalPageCount % 2) != 0 && count($this->stanzas2) > 0) {
                    $mpdf->WriteHTML(view('hinarios.print.blank_page')->render());
                    $this->totalPageCount++;
                }

                $this->pageNumber = 1;
                // $loops = 0;

                // while we still have stanzas
                if (count($stanzas) > 0) {
                    $this->writeStanza($mpdf, $stanzas);
                }
            }
        }

        if (GlobalFunctions::getCurrentLanguage() == Languages::ENGLISH) {
            $printDate = date('F j, Y');
        } else {
            $printDate = date('d-m-Y');
        }

        $mpdf->setFooter('');
        $mpdf->WriteHTML(view('hinarios.print.final_page', [ 'printDate' => $printDate ])->render());
        $this->html .= view('hinarios.print.final_page')->render();


        
        $pdfContent = $mpdf->Output($this->id.'filename.pdf', \Mpdf\Output\Destination::STRING_RETURN);

        Storage::put('hinario_pdfs/'.$this->id, $pdfContent);

        // $mpdf->Output();
        if(!$this->return_pdf_content) {
            return "cached " . $this->id;
        }
        return $pdfContent;
    }

}
