import { Button } from "@/Components/ui/button";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/Components/ui/card";
import { ChevronLeft, ChevronRight, Settings } from "lucide-react";
import { router } from "@inertiajs/react";
import { PageProps } from "@/types";
import { useState } from "react";
import { PageTitle } from "@/Components/ui/page-title";

export default function ProfileSettings({ auth }: PageProps) {
    return (
        <>
            <PageTitle>
                <Settings className="h-6 w-6 ml-2" />
                إعدادات الحساب
            </PageTitle>

            <div className="space-y-4">
                <SettingsCard
                    title="المعلومات الشخصية"
                    description="تعديل الاسم والبريد الإلكتروني ورقم الواتساب ونوع النشاط التجاري"
                    href="/profile/personal-info"
                />

                <SettingsCard
                    title="كلمة المرور"
                    description="تغيير كلمة المرور الخاصة بك"
                    href="/profile/change-password"
                    requiresOtp
                />

                <SettingsCard
                    title="العنوان"
                    description="تغيير المحافظة والمدينة والمنطقة والقرية والموقع"
                    href="/profile/address"
                    requiresOtp
                />
            </div>
        </>
    );
}

interface SettingsCardProps {
    title: string;
    description: string;
    href: string;
    requiresOtp?: boolean;
}

function SettingsCard({
    title,
    description,
    href,
    requiresOtp = false,
}: SettingsCardProps) {
    const [isLoading, setIsLoading] = useState(false);

    const handleNavigate = () => {
        if (isLoading) return;

        setIsLoading(true);
        router.visit(href);
    };

    return (
        <Card
            className="hover:bg-accent/50 transition-colors cursor-pointer"
            onClick={handleNavigate}
        >
            <CardHeader className="flex gap-2 flex-row items-center justify-between p-4">
                <div>
                    <CardTitle className="text-right text-lg">
                        {title}
                    </CardTitle>
                    <CardDescription className="text-right mt-1">
                        {description}
                        {requiresOtp && (
                            <div className="text-xs text-primary mt-1">
                                * يتطلب التحقق عبر رمز OTP
                            </div>
                        )}
                    </CardDescription>
                </div>
                <ChevronLeft className="ml-2 h-5 w-5 text-muted-foreground" />
            </CardHeader>
        </Card>
    );
}
