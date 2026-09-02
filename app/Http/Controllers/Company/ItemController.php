<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\ItemRequest;
use App\Models\HsCode;
use App\Models\Item;
use App\Models\SaleType;
use App\Models\TaxRate;
use App\Models\UnitOfMeasure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Company Dashboard PRD #12. Always scoped to the authenticated
     * user's own company (PRD #18).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Item::class);

        $query = Item::query()->where('company_id', $request->user()->company_id);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(20)->withQueryString();

        return view('company.items.index', ['items' => $items]);
    }

    public function create(): View
    {
        $this->authorize('create', Item::class);

        return view('company.items.create', $this->formData());
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $item = Item::create([
            ...$data,
            'company_id' => $request->user()->company_id,
        ]);

        return redirect()
            ->route('company.items.index')
            ->with('status', "Item \"{$item->item_name}\" created.");
    }

    public function edit(Item $item): View
    {
        $this->authorize('update', $item);

        return view('company.items.edit', [
            ...$this->formData(),
            'item' => $item,
        ]);
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($request->validated());

        return redirect()
            ->route('company.items.index')
            ->with('status', "Item \"{$item->item_name}\" updated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'saleTypes' => SaleType::orderBy('name')->pluck('name'),
            'hsCodes' => HsCode::orderBy('code')->get(['code', 'description']),
            'unitsOfMeasure' => UnitOfMeasure::orderBy('name')->pluck('name'),
            'taxRates' => TaxRate::orderBy('rate')->get(['rate', 'label']),
        ];
    }
}
