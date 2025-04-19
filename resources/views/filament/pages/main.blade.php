<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center min-h-[60vh] py-8 px-4 sm:px-0">
        <div class="w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg  p-4 flex flex-col items-center gap-4">
            <img src="{{ asset('images/main.png') }}" alt="الرئيسية" class="w-40 h-40 object-contain rounded-xl shadow mb-2" loading="lazy" />
            <div class="w-full text-center">
                <h1 class="text-2xl font-bold text-primary-600 mb-2">مرحبا بك في {{ config('app.name') }}</h1>
                @auth
                    <div class="flex flex-col items-center gap-1 text-base text-secondary-700">
                        <span><span class="font-semibold">الاسم:</span> {{ auth()->user()->name }}</span>
                        <span><span class="font-semibold">البريد الإلكتروني:</span> {{ auth()->user()->email }}</span>
                        @if(auth()->user()->phone ?? false)
                            <span><span class="font-semibold">رقم الهاتف:</span> {{ auth()->user()->phone }}</span>
                        @endif
                    </div>
                @else
                    <div class="text-secondary-500 text-base">يرجى تسجيل الدخول لعرض معلوماتك الشخصية</div>
                @endauth
            </div>
        </div>
    </div>
</x-filament-panels::page>
