import React, { useState } from "react";
import { Head } from "@inertiajs/react";
import { router } from "@inertiajs/react";
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import {
    CalendarIcon,
    BarChart3,
    ShoppingBag,
    ArrowDownLeft,
} from "lucide-react";
import { Calendar } from "@/Components/ui/calendar";
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/Components/ui/popover";
import { cn } from "@/lib/utils";
import { format } from "date-fns";
import { PageTitle } from "@/Components/ui/page-title";

interface MonthlyStatItem {
    month: string;
    order_count: number;
    order_amount: number;
    return_count: number;
    return_amount: number;
}

interface ReportData {
    order_stats: {
        total_orders: number;
        total_orders_amount: number;
    };
    return_stats: {
        total_returns: number;
        total_returns_amount: number;
    };
    monthly_stats: MonthlyStatItem[];
    date_range: {
        start: string;
        end: string;
    };
}

interface Props {
    reportData: ReportData;
}

export default function CustomerReports({ reportData }: Props) {
    // Parse date strings to Date objects
    const [startDate, setStartDate] = useState<Date | undefined>(
        reportData.date_range.start
            ? new Date(reportData.date_range.start)
            : undefined
    );
    const [endDate, setEndDate] = useState<Date | undefined>(
        reportData.date_range.end
            ? new Date(reportData.date_range.end)
            : undefined
    );

    const handleApplyDateFilter = () => {
        router.get(route("my-reports.show"), {
            start_date: startDate ? format(startDate, "yyyy-MM-dd") : "",
            end_date: endDate ? format(endDate, "yyyy-MM-dd") : "",
        });
    };

    // Format currency
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat("ar-EG", {
            style: "currency",
            currency: "EGP",
        }).format(amount);
    };

    // Format month names in Arabic
    const formatMonth = (monthStr: string) => {
        if (!monthStr) return "";
        const date = new Date(monthStr + "-01");
        return date.toLocaleDateString("ar-EG", {
            month: "long",
            year: "numeric",
        });
    };

    return (
        <>
            <Head title="التقارير" />

            <PageTitle>التقارير</PageTitle>

            {/* Date Filter */}
            <Card className="mb-8">
                <CardHeader>
                    <CardTitle className="text-right">
                        تصفية حسب التاريخ
                    </CardTitle>
                    <CardDescription className="text-right">
                        اختر فترة زمنية محددة لعرض التقارير
                    </CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col space-y-4 md:flex-row md:space-y-0 md:space-x-4 md:space-x-reverse">
                    <div className="flex flex-col space-y-2 w-full md:w-1/2">
                        <Label htmlFor="start-date" className="text-right">
                            من تاريخ
                        </Label>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    className="w-full justify-between text-right"
                                    id="start-date"
                                >
                                    {startDate ? (
                                        format(startDate, "PPP")
                                    ) : (
                                        <span>اختر التاريخ</span>
                                    )}
                                    <CalendarIcon className="ml-2 h-4 w-4" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <Calendar
                                    mode="single"
                                    selected={startDate}
                                    onSelect={setStartDate}
                                    initialFocus
                                />
                            </PopoverContent>
                        </Popover>
                    </div>

                    <div className="flex flex-col space-y-2 w-full md:w-1/2">
                        <Label htmlFor="end-date" className="text-right">
                            إلى تاريخ
                        </Label>
                        <Popover>
                            <PopoverTrigger asChild>
                                <Button
                                    variant="outline"
                                    className="w-full justify-between text-right"
                                    id="end-date"
                                >
                                    {endDate ? (
                                        format(endDate, "PPP")
                                    ) : (
                                        <span>اختر التاريخ</span>
                                    )}
                                    <CalendarIcon className="ml-2 h-4 w-4" />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent className="w-auto p-0">
                                <Calendar
                                    mode="single"
                                    selected={endDate}
                                    onSelect={setEndDate}
                                    initialFocus
                                />
                            </PopoverContent>
                        </Popover>
                    </div>
                </CardContent>
                <CardFooter className="flex justify-end">
                    <Button onClick={handleApplyDateFilter}>تطبيق</Button>
                </CardFooter>
            </Card>

            {/* Summary Stats */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <Card className="bg-gradient-to-br from-slate-50 to-slate-100">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-lg text-right flex items-center ">
                            <ShoppingBag className="ml-2 h-5 w-5" />
                            إجمالي الطلبات
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-right">
                            {reportData.order_stats.total_orders}
                        </div>
                        <div className="text-lg mt-2 text-right">
                            {formatCurrency(
                                reportData.order_stats.total_orders_amount
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card className="bg-gradient-to-br from-slate-50 to-slate-100">
                    <CardHeader className="pb-2">
                        <CardTitle className="text-lg text-right flex items-center ">
                            <ArrowDownLeft className="ml-2 h-5 w-5" />
                            إجمالي المرتجعات
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="text-3xl font-bold text-right">
                            {reportData.return_stats.total_returns}
                        </div>
                        <div className="text-lg mt-2 text-right">
                            {formatCurrency(
                                reportData.return_stats.total_returns_amount
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Monthly Stats */}
            <Card>
                <CardHeader>
                    <CardTitle className="text-right flex items-center ">
                        <BarChart3 className="ml-2 h-5 w-5" />
                        الإحصائيات الشهرية
                    </CardTitle>
                    <CardDescription className="text-right">
                        إحصائيات الطلبات والمرتجعات حسب الشهر
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {reportData.monthly_stats.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse">
                                <thead>
                                    <tr className="border-b">
                                        <th className="py-3 px-4 text-right">
                                            الشهر
                                        </th>
                                        <th className="py-3 px-4 text-right">
                                            عدد الطلبات
                                        </th>
                                        <th className="py-3 px-4 text-right">
                                            إجمالي الطلبات
                                        </th>
                                        <th className="py-3 px-4 text-right">
                                            عدد المرتجعات
                                        </th>
                                        <th className="py-3 px-4 text-right">
                                            إجمالي المرتجعات
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {reportData.monthly_stats.map(
                                        (stat, index) => (
                                            <tr
                                                key={index}
                                                className="border-b hover:bg-slate-50"
                                            >
                                                <td className="py-3 px-4 text-right">
                                                    {formatMonth(stat.month)}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    {stat.order_count}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    {formatCurrency(
                                                        stat.order_amount
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    {stat.return_count}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    {formatCurrency(
                                                        stat.return_amount
                                                    )}
                                                </td>
                                            </tr>
                                        )
                                    )}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="text-center py-10">
                            <p className="text-gray-500">
                                لا توجد بيانات متاحة للفترة المحددة
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </>
    );
}
