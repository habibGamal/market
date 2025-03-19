<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'exists:customers,phone'],
            'password' => ['required', 'string'],
        ], [
            'phone.exists' => 'رقم الهاتف غير مسجل لدينا'
        ]);

        $customer = Customer::where('phone', $validated['phone'])->first();

        if (!$customer || !Hash::check($validated['password'], $customer->password)) {
            return back()->withErrors([
                'phone' => 'بيانات الدخول غير صحيحة'
            ]);
        }

        auth()->guard('customer')->login($customer, true);

        return redirect('/');
    }

    public function destroy()
    {
        auth()->guard('customer')->logout();
        return redirect('/');
    }
}
