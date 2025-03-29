import { Card, CardContent } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";
import { ShieldCheck, TruckIcon, ArrowLeft, CreditCard } from "lucide-react";
import { usePage } from "@inertiajs/react";
import { ReactNode } from "react";
import { PageProps } from "@/types";

// Define types for the policy data
type PolicyKey = 'privacy' | 'shipping' | 'return' | 'payment';

interface PolicyContent {
    [key: string]: string;
}

interface SupportPoliciesProps extends PageProps {
    policies: PolicyContent;
}

export function SupportPolicies() {
    const { policies } = usePage<SupportPoliciesProps>().props;

    const createMarkup = (html: string) => {
        return { __html: html };
    };

    const policyIcons: Record<PolicyKey, ReactNode> = {
        privacy: <ShieldCheck className="h-6 w-6 text-primary-600" />,
        shipping: <TruckIcon className="h-6 w-6 text-primary-600" />,
        return: <ArrowLeft className="h-6 w-6 text-primary-600" />,
        payment: <CreditCard className="h-6 w-6 text-primary-600" />
    };

    const policyTitles: Record<PolicyKey, string> = {
        privacy: "سياسة الخصوصية",
        shipping: "سياسة الشحن والتوصيل",
        return: "سياسة الإرجاع والاستبدال",
        payment: "سياسة الدفع"
    };

    // Default content to use if settings are empty
    const getDefaultContent = (key: PolicyKey): ReactNode => {
        switch(key) {
            case 'privacy':
                return (
                    <div className="space-y-4">
                        <p>نحن نلتزم بحماية خصوصية عملائنا. نهتم بحماية بياناتك الشخصية ونتعامل معها بسرية تامة.</p>
                        <h4 className="text-md font-medium">البيانات التي نجمعها:</h4>
                        <ul className="list-disc list-inside space-y-1 mr-4 text-secondary-700">
                            <li>المعلومات الشخصية (الاسم، البريد الإلكتروني، رقم الهاتف)</li>
                            <li>معلومات الشركة ونوع النشاط التجاري</li>
                            <li>معلومات العنوان للتوصيل</li>
                            <li>سجل الطلبات والمشتريات</li>
                        </ul>
                        <p>نحن لا نبيع أو نؤجر معلوماتك الشخصية لأطراف ثالثة.</p>
                    </div>
                );
            case 'shipping':
                return (
                    <div className="space-y-4">
                        <p>نسعى لتوصيل منتجاتك بأمان وفي الوقت المحدد.</p>
                        <h4 className="text-md font-medium">مواعيد التوصيل:</h4>
                        <ul className="list-disc list-inside space-y-1 mr-4 text-secondary-700">
                            <li>المدن الرئيسية: 2-3 أيام عمل</li>
                            <li>المدن الأخرى: 3-5 أيام عمل</li>
                        </ul>
                    </div>
                );
            case 'return':
                return (
                    <div className="space-y-4">
                        <p>نحن نضمن جودة منتجاتنا ونسمح بإرجاع أو استبدال المنتجات.</p>
                        <h4 className="text-md font-medium">فترة الإرجاع:</h4>
                        <p className="text-secondary-700">يمكن إرجاع المنتجات خلال 14 يوماً من تاريخ الاستلام.</p>
                    </div>
                );
            case 'payment':
                return (
                    <div className="space-y-4">
                        <p>نوفر عدة طرق دفع آمنة ومريحة لتناسب احتياجاتك.</p>
                        <h4 className="text-md font-medium">طرق الدفع المتاحة:</h4>
                        <ul className="list-disc list-inside space-y-1 mr-4 text-secondary-700">
                            <li>الدفع عند الاستلام</li>
                            <li>التحويل البنكي</li>
                            <li>بطاقات الائتمان (فيزا، ماستركارد)</li>
                        </ul>
                    </div>
                );
            default:
                return <p>لم يتم تحديد محتوى لهذه السياسة بعد.</p>;
        }
    };

    // Get all policy keys
    const policyKeys = Object.keys(policyTitles) as PolicyKey[];

    return (
        <Card className="shadow-sm">
            <CardContent className="p-6">
                <div className="space-y-6">
                    {policyKeys.map((key, index) => (
                        <div key={key} className="pb-6">
                            <div className="flex items-center gap-3 mb-4">
                                <div className="bg-primary-100 p-2 rounded-full">
                                    {policyIcons[key as PolicyKey]}
                                </div>
                                <h3 className="text-xl font-bold text-primary-700">{policyTitles[key as PolicyKey]}</h3>
                            </div>

                            {policies[key] ? (
                                <div
                                    className="policy-content"
                                    dangerouslySetInnerHTML={createMarkup(policies[key])}
                                />
                            ) : (
                                getDefaultContent(key as PolicyKey)
                            )}

                            {index < policyKeys.length - 1 && (
                                <Separator className="mt-6" />
                            )}
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
