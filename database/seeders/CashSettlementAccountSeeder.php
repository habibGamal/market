<?php

namespace Database\Seeders;

use App\Models\CashSettlement;
use App\Models\CashSettlementAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashSettlementAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Create some cash settlement accounts
        $accounts = [
            [
                'name' => 'البنك الأهلي المصري',
                'inlet_name_alias' => 'إيداع البنك الأهلي',
                'outlet_name_alias' => 'سحب البنك الأهلي',
            ],
            [
                'name' => 'بنك مصر',
                'inlet_name_alias' => 'إيداع بنك مصر',
                'outlet_name_alias' => 'سحب بنك مصر',
            ],
            [
                'name' => 'الخزينة النقدية',
                'inlet_name_alias' => 'إيراد الخزينة',
                'outlet_name_alias' => 'مصروف الخزينة',
            ],
            [
                'name' => 'محفظة فودافون كاش',
                'inlet_name_alias' => 'تحصيل فودافون كاش',
                'outlet_name_alias' => 'دفع فودافون كاش',
            ],
            [
                'name' => 'محفظة أورانج كاش',
                'inlet_name_alias' => 'تحصيل أورانج كاش',
                'outlet_name_alias' => 'دفع أورانج كاش',
            ],
        ];
         CashSettlementAccount::factory()->createMany($accounts);
    }
}
