<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $template->getTitle() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --text-color: #374151;
            --border-color: #e5e7eb;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            text-align: right;
            color: var(--text-color);
            line-height: 1.6;
        }

        @media print {
            .no-print {
                display: none;
            }

            body * {
                visibility: hidden;
            }

            .print-container,
            .print-container * {
                visibility: visible;
            }

            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }

            .print-container {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0;
                padding: 2rem;
                box-shadow: none;
                width: 100%;
            }

            .print-container img {
                width: 80px;
                height: 80px;
            }

            .print-container h1 {
                font-size: 28px;
                color: var(--primary-color);
            }

            .print-container p,
            .print-container th,
            .print-container td {
                font-size: 14px;
            }

            .print-container th {
                background-color: #f8fafc;
                font-weight: 600;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .info-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .info-item {
                flex: 1 1 200px;
                min-width: 200px;
                padding: 0.5rem;
                border-bottom: 1px solid var(--border-color);
            }

            .info-item:last-child {
                border-bottom: none;
            }

            .info-section {
                break-inside: avoid;
                margin-bottom: 1.5rem;
            }

            .info-label {
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-label,
            .info-value {
                padding: 0.4rem 0.8rem;
                font-size: 12px;
            }
        }

        .sortable:after {
            content: ' \25B2';
            opacity: 0.5;
            font-size: 0.8em;
        }

        .sortable.desc:after {
            content: ' \25BC';
        }

        .data-table {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .data-table th {
            transition: background-color 0.2s;
        }

        .data-table th:hover {
            background-color: #e2e8f0;
        }

        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 2rem;
            border-collapse: collapse;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 0.5rem 1rem;
            font-weight: 600;
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            width: 30%;
            color: #374151;
        }

        .info-value {
            display: table-cell;
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            color: #4b5563;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

            const comparer = (idx, asc) => (a, b) => {
                const v1 = getCellValue(a, idx);
                const v2 = getCellValue(b, idx);
                // If both values are numbers, do numeric comparison
                if (v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2)) {
                    return asc ? v1 - v2 : v2 - v1;
                }
                // Otherwise do string comparison
                return asc ? v1.toString().localeCompare(v2) : v2.toString().localeCompare(v1);
            };

            document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
                const table = th.closest('table');
                const tbody = table.querySelector('tbody');
                const colIndex = Array.from(th.parentNode.children).indexOf(th);
                this.asc = th.classList.contains('sortable') && !th.classList.contains('desc');

                // Select only rows from tbody for sorting
                Array.from(tbody.querySelectorAll('tr'))
                    .sort(comparer(colIndex, this.asc))
                    .forEach(tr => tbody.appendChild(tr));

                // Toggle sorting direction class
                document.querySelectorAll('th').forEach(header => {
                    header.classList.remove('desc', 'sortable');
                });
                th.classList.toggle('desc', this.asc);
                th.classList.add('sortable');
            })));
        });
    </script>
</head>

<body class="bg-gray-50 p-4 md:p-8">
    <button onclick="window.print()"
        class="mb-6 bg-blue-600 hover:bg-blue-700 transition-colors text-white py-2 px-6 rounded-lg shadow-sm no-print flex items-center mx-auto">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        طباعة
    </button>

    <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow-md print-container">
        <div class="flex items-center justify-between mb-8 border-b pb-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-blue-600 mb-2">{{ $template->getTitle() }}</h1>
                <div class="text-gray-500 text-sm">تاريخ الطباعة: {{ now()->format('Y/m/d') }}</div>
            </div>
            <img src="{{ $template->getLogoUrl() }}" alt="Logo" class="w-24 h-24 object-contain">
        </div>

        <div class="info-section">
            @foreach ($template->getInfos() as $key => $value)
                <div class="info-row">
                    <div class="info-label">{{ $key }}</div>
                    <div class="info-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        @if ($template->getItemHeaders() && $template->getItems())
            <div class="overflow-x-auto data-table">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            @foreach ($template->getItemHeaders() as $header)
                                <th
                                    class="py-4 px-6 font-semibold text-gray-700 border-b cursor-pointer hover:bg-gray-200 transition-colors">
                                    {{ $header }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($template->getItems() as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @foreach ($item as $value)
                                    <td class="px-6 border-b border-gray-100">{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($template->getTotal())
            <div class="mt-8 border-t pt-6">
                <p class="text-xl font-bold text-blue-600 text-left">
                    <span class="text-gray-600">المجموع الكلي:</span>
                    {{ $template->getTotal() }}
                </p>
            </div>
        @endif
    </div>
</body>

</html>
