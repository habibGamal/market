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
            'businessTypes' => BusinessType::select('id', 'name')->get(),
            'govs' => Gov::with('cities.areas')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'gov_id' => ['required', 'exists:govs,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'village' => [function ($attribute, $value, $fail) use ($request) {
                $area = Area::find($request->input('area_id'));
                if ($area && $area->has_village && empty($value)) {
                    $fail('حقل القرية مطلوب.');
                }
            }],
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
