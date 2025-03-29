import { Head } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { HelpCircle, Phone, Mail, MapPin, MessageCircle, Globe, Info } from "lucide-react";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger
} from "@/Components/ui/tabs";
import { SupportPolicies } from "@/Components/Support/SupportPolicies";
import { SupportContact } from "@/Components/Support/SupportContact";

export default function Index() {
    return (
        <>
            <Head title="الدعم والمساعدة" />
            <PageTitle>
                <HelpCircle className="h-6 w-6 mr-2" />
                الدعم والمساعدة
            </PageTitle>

            <Tabs defaultValue="contact" className="w-full" dir="rtl">
                <TabsList >
                    <TabsTrigger value="contact">اتصل بنا</TabsTrigger>
                    <TabsTrigger value="policies">سياسات الاستخدام</TabsTrigger>
                </TabsList>

                <TabsContent value="contact">
                    <SupportContact />
                </TabsContent>

                <TabsContent value="policies">
                    <SupportPolicies />
                </TabsContent>
            </Tabs>
        </>
    );
}
