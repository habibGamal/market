<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Models\User;
use App\Services\OrderServices;
use App\Services\StockServices;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductReportSeeder extends Seeder
{
    public function run(): void
    {
        // الخدمات المطلوبة
        $orderServices = app(OrderServices::class);
        $stockServices = app(StockServices::class);

        // إنشاء مجموعة من العملاء
        $customers = Customer::all()->count() > 10
            ? Customer::inRandomOrder()->limit(10)->get()
            : Customer::factory(10)->create();

        // إنشاء مستخدم لاستخدامه في عمليات المبيعات
        $user = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'بائع تجريبي',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // أسماء منتجات بالعربية
        $arabicProducts = [
            'حليب طازج',
            'خبز عربي',
            'جبنة بيضاء',
            'زيت زيتون',
            'أرز بسمتي',
            'سكر أبيض',
            'شاي أحمر',
            'قهوة عربية',
            'معكرونة',
            'صابون غسيل',
            'منظف أرضيات',
            'معجون أسنان',
            'شامبو شعر',
            'مناديل ورقية',
            'بسكويت شاي',
        ];

        // إنشاء المنتجات أو استخدام المنتجات الموجودة
        $products = collect();
        foreach ($arabicProducts as $arabicProduct) {
            $product = Product::factory()->create(
                ['name' => $arabicProduct]
            );

            // إضافة مخزون كبير للمنتج
            $stockServices->addTo($product, [
                Carbon::now()->addYear()->format('Y-m-d') => 500000,
            ]);

            $products->push($product);
        }

        // تحديد فترة سنة للبيانات
        $startDate = Carbon::now()->subMonths(5);
        $endDate = Carbon::now();

        // إنشاء طلبات على مدار سنة
        $this->info("Starting to generate orders data...");

        // تقسيم الفترة إلى أشهر لتوزيع البيانات
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            dump("Generating data for {$currentDate->format('D F Y')}...");
            $monthEnd = $currentDate->copy()->endOfMonth();
            if ($monthEnd > $endDate)
                $monthEnd = $endDate;

            // عدد الطلبات هذا الشهر - يزيد تدريجيا كل شهر
            $monthProgress = $currentDate->diffInMonths($startDate) * -1 + 1;
            dump("Month progress: $monthProgress", $currentDate->format('Y-M-D'), $startDate->format('Y-M-D'));
            $ordersInMonth = rand(40, 60) * $monthProgress / 6;

            // إنشاء طلبات لهذا الشهر
            $this->createOrdersForPeriod($currentDate, $monthEnd, (int) $ordersInMonth, $customers, $products, $orderServices, $user);

            // الانتقال للشهر التالي
            $currentDate = $currentDate->addDays(rand(1, 5));
        }

        $this->info("Data generation completed successfully");
    }

    /**
     * إنشاء طلبات لفترة محددة
     */
    private function createOrdersForPeriod(
        Carbon $startDate,
        Carbon $endDate,
        int $ordersCount,
        $customers,
        $products,
        OrderServices $orderServices,
        User $user
    ): void {
        // توزيع الطلبات بشكل عشوائي على الفترة
        $period = $endDate->diffInDays($startDate) + 1;
        $dayDistribution = $this->distributeOrdersOverDays($ordersCount, -1 * $period);

        $currentDay = 0;
        $currentDate = $startDate->copy();

        foreach ($dayDistribution as $dayOrders) {
            // تعيين تاريخ اليوم الحالي
            $orderDate = $currentDate->copy()->addDays($currentDay);

            // إنشاء طلبات لهذا اليوم
            for ($i = 0; $i < $dayOrders; $i++) {
                $customer = $customers->random();

                // إنشاء طلب جديد
                $order = Order::factory()->create([
                    'customer_id' => $customer->id,
                    'created_at' => $orderDate->copy()->addHours(rand(9, 20))->addMinutes(rand(1, 59)),
                    'status' => OrderStatus::PENDING,
                    'total' => 0,
                ]);

                // إضافة منتجات للطلب - عدد عشوائي من المنتجات (2-7)
                $orderItems = [];
                $selectedProducts = $products->random(rand(2, 7));

                foreach ($selectedProducts as $product) {
                    // تحديد كمية عشوائية
                    $packetsQuantity = rand(0, 3);
                    $pieceQuantity = $packetsQuantity > 0 ? rand(0, $product->packet_to_piece - 1) : rand(1, $product->packet_to_piece - 1);

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'packets_quantity' => $packetsQuantity,
                        'packet_price' => $product->packet_price,
                        'packet_cost' => $product->packet_cost,
                        'piece_quantity' => $pieceQuantity,
                        'piece_price' => $product->piece_price,
                    ];
                }

                // إضافة المنتجات للطلب
                $createdItems = $orderServices->addOrderItems($order, $orderItems);

                // إنشاء مرتجعات لبعض الطلبات (حوالي 10% من الطلبات)
                if (rand(1, 100) <= 10) {
                    $returnDate = $orderDate->copy()->addDays(rand(1, 7));
                    if ($returnDate <= $endDate) {
                        $this->createReturnOrder($order, $createdItems, $orderServices, $returnDate);
                    }
                }
            }

            $currentDay++;
        }
    }

    /**
     * إنشاء مرتجعات للطلب باستخدام OrderServices
     */
    private function createReturnOrder(Order $order, $orderItems, OrderServices $orderServices, Carbon $returnDate): void
    {
        // اختيار عناصر عشوائية من الطلب الأصلي للإرجاع
        $itemsToReturn = $orderItems->random(rand(1, min(3, $orderItems->count())));

        $returnItems = [];
        foreach ($itemsToReturn as $orderItem) {
            // إرجاع جزء من الكمية أو كلها
            $returnPackets = $orderItem->packets_quantity > 0 ? rand(0, $orderItem->packets_quantity) : 0;
            $returnPieces = 0;

            if ($returnPackets < $orderItem->packets_quantity) {
                $returnPieces = $orderItem->piece_quantity > 0 ? rand(0, $orderItem->piece_quantity) : 0;
            } else if ($returnPackets == $orderItem->packets_quantity) {
                $returnPieces = $orderItem->piece_quantity > 0 ? rand(0, $orderItem->piece_quantity) : 0;
            }

            // إضافة فقط إذا كانت هناك كميات للإرجاع
            if ($returnPackets > 0 || $returnPieces > 0) {
                // استخدام الـOrderServices لإضافة المرتجعات
                $returnItems[] = [
                    'order_item' => $orderItem,
                    'product_id' => $orderItem->product_id,
                    'packets_quantity' => $returnPackets,
                    'packet_price' => $orderItem->packet_price,
                    'piece_quantity' => $returnPieces,
                    'piece_price' => $orderItem->piece_price,
                    'return_reason' => fake()->randomElement(['منتج تالف', 'خطأ في الطلب', 'منتج منتهي الصلاحية', 'العميل غير راضٍ']),
                    'notes' => rand(0, 1) ? fake()->sentence() : null,
                    'created_at' => $returnDate
                ];
            }
        }

        // إضافة المرتجعات للطلب إذا كانت هناك عناصر للإرجاع
        if (!empty($returnItems)) {
            $orderServices->returnItems($order, $returnItems);
        }
    }

    /**
     * توزيع الطلبات على أيام الفترة
     */
    private function distributeOrdersOverDays(int $totalOrders, int $days): array
    {
        $distribution = array_fill(0, 7, 0);

        // توزيع أولي بناءً على منحنى طبيعي (أيام الأسبوع أكثر نشاطًا)
        $weekdayWeights = [0.7, 1.0, 1.3, 1.5, 1.8, 1.2, 0.5]; // من الأحد إلى السبت

        for ($day = 0; $day < $days; $day++) {
            $dayOfWeek = $day % 7;
            $weight = $weekdayWeights[$dayOfWeek];
            $distribution[$day] = $weight;
        }

        // تطبيع التوزيع
        $total = array_sum($distribution);
        for ($i = 0; $i < $days; $i++) {
            $distribution[$i] = round(($distribution[$i] / $total) * $totalOrders);
        }

        // ضمان أن المجموع النهائي يساوي العدد الإجمالي للطلبات
        $currentTotal = array_sum($distribution);
        $diff = $totalOrders - $currentTotal;

        if ($diff != 0) {
            // توزيع الفرق على الأيام بشكل عشوائي
            for ($i = 0; $i < abs($diff); $i++) {
                $index = array_rand($distribution);
                if ($diff > 0) {
                    $distribution[$index]++;
                } elseif ($distribution[$index] > 0) {
                    $distribution[$index]--;
                }
            }
        }

        return $distribution;
    }

    /**
     * طباعة معلومات في وحدة التحكم
     */
    private function info(string $message): void
    {
        if (app()->runningInConsole()) {
            $this->command->info($message);
        }
    }
}
