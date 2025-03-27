<?php

namespace App\Http\Controllers;

use App\Services\Reports\CustomerReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerReportController extends Controller
{
    protected $customerReportService;

    public function __construct(CustomerReportService $customerReportService)
    {
        $this->customerReportService = $customerReportService;
    }

    /**
     * Show the customer report page
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function show(Request $request)
    {
        $customer = auth('customer')->user();

        // Get date range from request if provided
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get customer statistics
        $reportData = $this->customerReportService->getCustomerStats($customer, $startDate, $endDate);

        return Inertia::render('Reports/CustomerReports', [
            'reportData' => $reportData
        ]);
    }
}
