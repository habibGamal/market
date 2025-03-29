import { Card, CardContent } from "@/Components/ui/card";
import { Phone, Mail, MapPin, MessageCircle } from "lucide-react";
import { usePage } from "@inertiajs/react";
import { PageProps } from "@/types";

interface SupportSettings {
    phone: string;
    hours: string;
    email: string;
    address: string;
    chatHours: string;
}

interface SupportContactProps extends PageProps {
    supportSettings: SupportSettings;
}

export function SupportContact() {
    const { supportSettings } = usePage<SupportContactProps>().props;

    return (
        <div className="grid md:grid-cols-1 gap-6">
            <Card className="shadow-sm" dir="rtl">
                <CardContent className="p-6">
                    <h3 className="text-xl font-bold mb-4 text-primary-700">
                        معلومات الاتصال
                    </h3>

                    <div className="space-y-4">
                        <div className="flex items-start space-x-4 space-x-reverse">
                            <div className="bg-primary-100 p-2 rounded-full">
                                <Phone className="h-5 w-5 text-primary-600" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">اتصل بنا</p>
                                <p className="text-sm text-secondary-600">
                                    {supportSettings.phone}
                                </p>
                                <p className="text-sm text-secondary-600">
                                    {supportSettings.hours}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-4 space-x-reverse">
                            <div className="bg-primary-100 p-2 rounded-full">
                                <Mail className="h-5 w-5 text-primary-600" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">البريد الإلكتروني</p>
                                <p className="text-sm text-secondary-600">
                                    {supportSettings.email}
                                </p>
                                <p className="text-sm text-secondary-600">
                                    الرد خلال 24 ساعة
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-4 space-x-reverse">
                            <div className="bg-primary-100 p-2 rounded-full">
                                <MapPin className="h-5 w-5 text-primary-600" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">العنوان</p>
                                <p className="text-sm text-secondary-600">
                                    {supportSettings.address}
                                </p>
                            </div>
                        </div>

                        <div className="flex items-start space-x-4 space-x-reverse">
                            <div className="bg-primary-100 p-2 rounded-full">
                                <MessageCircle className="h-5 w-5 text-primary-600" />
                            </div>
                            <div className="flex-1">
                                <p className="font-medium">الدردشة المباشرة</p>
                                <p className="text-sm text-secondary-600">
                                    {supportSettings.chatHours}
                                </p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
