<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CustomerRequest;
use App\Models\Customer;
use App\Models\Province;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Company Dashboard PRD #11. Always scoped to the authenticated
     * user's own company (PRD #18).
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()->where('company_id', $request->user()->company_id);

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('ntn_cnic', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('company.customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('company.customers.create', $this->formData());
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $customer = Customer::create([
            ...$data,
            'company_id' => $request->user()->company_id,
        ]);

        return redirect()
            ->route('company.customers.index')
            ->with('status', "Customer \"{$customer->business_name}\" created.");
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('company.customers.edit', [
            ...$this->formData(),
            'customer' => $customer,
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()
            ->route('company.customers.index')
            ->with('status', "Customer \"{$customer->business_name}\" updated.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'provinces' => Province::orderBy('name')->pluck('name'),
        ];
    }
}
