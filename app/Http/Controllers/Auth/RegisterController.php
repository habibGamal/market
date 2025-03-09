<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Area;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Register', [
            'areas' => Area::select('id', 'name')->get(),
            'businessTypes' => BusinessType::select('id', 'name')->get(),
            'govs' => [
                ['id' => '1', 'name' => 'القاهرة'],
                ['id' => '2', 'name' => 'الجيزة'],
                ['id' => '3', 'name' => 'الإسكندرية'],
                ['id' => '4', 'name' => 'الدقهلية'],
                // Add more governorates as needed
            ],
            'cities' => [
                ['id' => '1', 'govId' => '1', 'name' => 'مدينة نصر'],
                ['id' => '2', 'govId' => '1', 'name' => 'المعادي'],
                ['id' => '3', 'govId' => '2', 'name' => 'الدقي'],
                ['id' => '4', 'govId' => '2', 'name' => '6 أكتوبر'],
                // Add more cities as needed
            ],
            'citiesWithVillages' => ['1', '4'], // IDs of cities that require village
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'gov' => ['required', 'string'],
            'city' => ['required', 'string'],
            'village' => ['required_if:city,1,4'], // Required only for specific cities
            'area_id' => ['required', 'exists:areas,id'],
            'address' => ['required', 'string', 'min:10'],
            'location' => ['nullable', 'string'], // Will contain geo coordinates
            'phone' => ['required', 'string', 'min:11', 'unique:customers'],
            'whatsapp' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:customers'],
            'password' => ['required', Rules\Password::defaults()],
            'business_type_id' => ['required', 'exists:business_types,id'],
        ]);

        $customer = Customer::create($validated);

        auth()->guard('customer')->login($customer);

        return redirect()->route('otp.verify');
    }
}
