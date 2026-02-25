<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المركز المالي</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            text-align: right;
            color: #374151;
            line-height: 1.6;
        }

        @media print {
            .no-print { display: none !important; }

            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }

            .print-container {
                box-shadow: none !important;
                padding: 1rem !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        .section-header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
        }

        .stat-card {
            transition: box-shadow 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="bg-gray-50 p-4 md:p-8">

    {{-- Print button --}}
    <div class="no-print flex justify-center mb-6 gap-3">
        <button onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-700 transition-colors text-white py-2 px-6 rounded-lg shadow-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            طباعة / حفظ PDF
        </button>
    </div>

    <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md p-6 print-container">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8 border-b pb-6">
            <div>
                <h1 class="text-3xl font-bold text-blue-600 mb-1">تقرير المركز المالي</h1>
                <p class="text-gray-500 text-sm">تاريخ الطباعة: {{ now()->format('Y/m/d h:i A') }}</p>
            </div>
            <img src="/icon512_maskable.png" alt="Logo" class="w-20 h-20 object-contain">
        </div>

        {{-- ===== ASSETS SECTION ===== --}}
        <div class="mb-8">
            <div class="section-header text-white rounded-lg px-4 py-3 mb-4">
                <h2 class="text-lg font-bold">الأصول</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-3 px-4 font-semibold text-gray-700 border-b text-right">البيان</th>
                            <th class="py-3 px-4 font-semibold text-gray-700 border-b text-left">القيمة (جنيه)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">تكلفة المخزون</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['stock_cost'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">تكلفة الطلبات قيد التسليم</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['delivery_orders_cost'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">رصيد الخزينة النقدية</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['vault_balance'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">أرصدة الخزائن الأخرى</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['other_vaults_balance'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">أرصدة مندوبين التسليم</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['drivers_balance'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">إيرادات مستحقة غير مدفوعة</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['cash_settlements_in_unpaid'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">الأصول الثابتة</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['assets']['fixed_assets'], 2) }}</td>
                        </tr>

                        {{-- Tracked expense types --}}
                        @foreach ($data['tracked_expenses'] as $expense)
                            <tr class="border-b hover:bg-gray-50 bg-red-50">
                                <td class="py-3 px-4 text-red-700">مصروفات: {{ $expense['name'] }}</td>
                                <td class="py-3 px-4 text-left font-medium text-red-700">{{ number_format($expense['total'], 2) }}</td>
                            </tr>
                        @endforeach

                        {{-- Total Assets row --}}
                        <tr class="bg-blue-50 border-t-2 border-blue-300">
                            <td class="py-3 px-4 font-bold text-blue-800">إجمالي الأصول</td>
                            <td class="py-3 px-4 text-left font-bold text-blue-800 text-base">{{ number_format($data['assets']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== RESPONSIBILITIES SECTION ===== --}}
        <div class="mb-8">
            <div class="bg-red-700 text-white rounded-lg px-4 py-3 mb-4">
                <h2 class="text-lg font-bold">الالتزامات</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-3 px-4 font-semibold text-gray-700 border-b text-right">البيان</th>
                            <th class="py-3 px-4 font-semibold text-gray-700 border-b text-left">القيمة (جنيه)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">أرصدة الموردين</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['responsibilities']['suppliers_balance'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">التزامات قصيرة الأجل (غير مدفوعة)</td>
                            <td class="py-3 px-4 text-left font-medium">{{ number_format($data['responsibilities']['cash_settlements_out_unpaid'], 2) }}</td>
                        </tr>

                        {{-- Total Responsibilities --}}
                        <tr class="bg-red-50 border-t-2 border-red-300">
                            <td class="py-3 px-4 font-bold text-red-800">إجمالي الالتزامات</td>
                            <td class="py-3 px-4 text-left font-bold text-red-800 text-base">{{ number_format($data['responsibilities']['total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== FINAL RESULT SECTION ===== --}}
        <div class="mb-4">
            <div class="bg-gray-700 text-white rounded-lg px-4 py-3 mb-4">
                <h2 class="text-lg font-bold">النتيجة النهائية</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden text-sm">
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">إجمالي الأصول</td>
                            <td class="py-3 px-4 text-left font-medium text-blue-700">{{ number_format($data['assets']['total'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">إجمالي الالتزامات</td>
                            <td class="py-3 px-4 text-left font-medium text-red-700">{{ number_format($data['responsibilities']['total'], 2) }}</td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">إدخالات رأس المال</td>
                            <td class="py-3 px-4 text-left font-medium text-orange-600">{{ number_format($data['asset_entries'], 2) }}</td>
                        </tr>
                        <tr class="{{ $data['is_profit'] ? 'bg-green-50 border-t-2 border-green-400' : 'bg-red-50 border-t-2 border-red-400' }}">
                            <td class="py-4 px-4 font-bold text-xl {{ $data['is_profit'] ? 'text-green-800' : 'text-red-800' }}">
                                {{ $data['is_profit'] ? 'الربح النهائي' : 'الخسارة النهائية' }}
                            </td>
                            <td class="py-4 px-4 text-left font-bold text-xl {{ $data['is_profit'] ? 'text-green-800' : 'text-red-800' }}">
                                {{ number_format(abs($data['final_result']), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center text-gray-400 text-xs mt-8 border-t pt-4">
            تم إنشاء هذا التقرير بتاريخ {{ now()->format('Y/m/d h:i A') }}
        </div>

    </div>

</body>
</html>
