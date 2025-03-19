<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Area;
use App\Models\BusinessType;
use App\Models\Gov;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Register', [
            'areas' => Area::select('id', 'name', 'has_village', 'city_id')->with('city')->get(),
            'businessTypes' => BusinessType::select('id', 'name')->get(),
            'govs' => Gov::with('cities.areas')->get(),
            'cities' => City::select('id', 'name', 'gov_id')->get(),
            'citiesWithVillages' => Area::where('has_village', true)->pluck('city_id')->unique(),
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
