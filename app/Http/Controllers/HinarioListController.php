<?php
namespace App\Http\Controllers;

use App\Enums\HinarioTypes;
use App\Models\Hinario;
use App\Models\UserHinario;
use App\Services\PersonService;
use Illuminate\Support\Facades\Auth;

class HinarioListController extends Controller
{
    /**
     * @var PersonService
     */
    private $personService;

    public function __construct(PersonService $personService)
    {
        $this->personService = $personService;
    }

    public function individual()
    {
        $hinarios = Hinario::where('type_id', HinarioTypes::INDIVIDUAL)
            ->with('receivedBy')
            ->get();

        $people = [];
        $tableOfContents = [];
        foreach($hinarios as $hinario) {
            $people[$hinario->receivedBy->list_order][$hinario->receivedBy->display_name] = $hinario->receivedBy;
            $tableOfContents[$hinario->receivedBy->display_name] = $hinario->receivedBy;
        }

        $listOrders = array_keys($people);
        rsort($listOrders);

        $peopleList = [];
        foreach ($listOrders as $listOrder) {
            ksort($people[$listOrder]);
            $peopleList = array_merge($peopleList, $people[$listOrder]);
        }

        ksort($tableOfContents);

        return view('hinarios.individuals', [ 'people' => array_values($peopleList), 'tableOfContents' => $tableOfContents ]);
    }

    public function compilations()
    {
        $hinarios = Hinario::where('type_id', HinarioTypes::COMPILATION)
            ->orderBy('list_order')
            ->get();

        return view('hinarios.compilations', [ 'hinarios' => $hinarios ]);
    }

    public function local()
    {
        $hinarioList = Hinario::where('type_id', HinarioTypes::LOCAL)->with('church', 'church.country', 'translations', 'church.translations', 'church.country.translations')->get();

        $hinarios = [];
        foreach ($hinarioList as $hinario) {
            $hinarios[$hinario->church->country->getName()][$hinario->church->state][$hinario->church->city][$hinario->church->getName($hinario->church->original_language_id)][$hinario->getName($hinario->original_language_id)] = $hinario;
        }

        foreach (array_keys($hinarios) as $country) {
            foreach (array_keys($hinarios[$country]) as $state) {
                foreach (array_keys($hinarios[$country][$state]) as $city) {
                    foreach (array_keys($hinarios[$country][$state][$city]) as $church) {
                        ksort($hinarios[$country][$state][$city][$church]);
                    }
                    ksort($hinarios[$country][$state][$city]);
                }
                ksort($hinarios[$country][$state]);
            }
            ksort($hinarios[$country]);
        }
        ksort($hinarios);

        return view('hinarios.local', [ 'hinarios' => $hinarios ]);
    }

    public function personal()
    {
        $hinarios = UserHinario::where('user_id', Auth::user()->getAuthIdentifier())->orderBy('name')->get();

        return view('hinarios.personal', [ 'hinarios' => $hinarios ]);
    }
}
