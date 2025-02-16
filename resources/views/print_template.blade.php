<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $template->getTitle() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            direction: rtl;
            text-align: right;
        }

        @media print {
            .no-print {
                display: none;
            }
            body * {
                visibility: hidden;
            }

            .print-container, .print-container * {
                visibility: visible;
            }

            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }

            .print-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .print-container img {
                width: 50px;
                height: 50px;
            }

            .print-container h1 {
                font-size: 24px;
                color: black;
            }

            .print-container p,
            .print-container th,
            .print-container td {
                font-size: 14px;
            }

            .print-container th {
                background-color: #f2f2f2;
            }
        }

        .sortable:after {
            content: ' \25B2'; /* Up arrow */
        }
        .sortable.desc:after {
            content: ' \25BC'; /* Down arrow */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

            const comparer = (idx, asc) => (a, b) => ((v1, v2) =>
                v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
            )(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));

            document.querySelectorAll('th').forEach(th => th.addEventListener('click', (() => {
                const table = th.closest('table');
                Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
                    .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                    .forEach(tr => table.appendChild(tr));

                // Toggle sorting direction class
                document.querySelectorAll('th').forEach(header => header.classList.remove('desc', 'sortable'));
                th.classList.toggle('desc', !this.asc);
                th.classList.add('sortable');
            })));
        });
    </script>
</head>

<body class="bg-gray-100 p-8">
    <button onclick="window.print()" class="mt-4 bg-blue-500 text-white py-2 px-4 rounded no-print">طباعة</button>
    <div class="mx-4 bg-white p-6 rounded-lg shadow-lg print-container">
        <div class="flex items-center mb-4">
            <img src="{{ $template->getLogoUrl() }}" alt="Logo" class="w-24 h-24 mr-4">
            <h1 class="text-3xl font-bold text-blue-600">{{ $template->getTitle() }}</h1>
        </div>
        @foreach ($template->getInfos() as $key => $value)
            <p class="mb-2 text-lg"><span class="font-bold text-gray-700">{{ $key }}:</span>
                {{ $value }}</p>
        @endforeach

        @if ($template->getItemHeaders() && $template->getItems())
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-200">
                    <tr>
                        @foreach ($template->getItemHeaders() as $header)
                            <th class="py-3 px-4 border-b cursor-pointer">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($template->getItems() as $item)
                        <tr>
                            @foreach ($item as $value)
                                <td class="py-3 px-4 border-b">{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($template->getTotal())
            <p class="mt-4 font-bold text-xl">المجموع الكلي: {{ $template->getTotal() }}</p>
        @endif
    </div>
</body>

</html>
