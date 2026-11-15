<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('isnad.admin');
        $password = $admin['password'] ?: Str::password(14, symbols: false);

        $user = User::firstOrNew(['email' => $admin['email']]);
        if (! $user->exists) {
            $user->name = $admin['name'];
            $user->password = $password;
            $user->save();
            $this->command?->info("Admin: {$admin['email']}");
            if (! $admin['password']) {
                $this->command?->warn("Generated password: {$password}  (غيّرها أو حط ADMIN_PASSWORD بملف .env)");
            }
        }

        // طرق الدفع (بتتعدل من لوحة إسناد ← الإعدادات)
        // بتعبّي بس الخانات الفاضية، وما بتمسح أي تعديل عملته من اللوحة.
        // بالخطوات: {amount} = المبلغ، {number} = رقم الحساب، {name} = اسم صاحب الحساب
        // الدفع صار بس عن طريق جوال باي (دفع لتاجر)، وبنك فلسطين انشال
        PaymentMethod::where('title', 'بنك فلسطين')->delete();

        $methods = [
            [
                'title' => 'جوال باي', 'type' => 'wallet', 'sort' => 1,
                'account_name' => 'الترا نت', 'account_number' => '0592471020',
                'notes' => 'اكتب اسمك الكامل بملاحظة الدفع.',
                'steps' => implode("\n", [
                    'افتح تطبيق جوال باي واختار "الدفع للتاجر".',
                    'امسح الباركود أو اكتب رقم التاجر {number}، وتأكد إنه الاسم: {name}.',
                    'اكتب المبلغ {amount} شيكل واسمك بالملاحظة، وأكّد الدفع.',
                    'خُد سكرين شوت لإشعار الدفع وارفعه تحت.',
                ]),
            ],
        ];
        // بيانات جوال باي القديمة (تحويل لمحفظة شخصية) بتتبدّل ببيانات التاجر
        $oldWalletNumber = '0594061657';
        foreach ($methods as $m) {
            $method = PaymentMethod::firstOrNew(['title' => $m['title']]);
            if (! $method->exists) {
                $method->active = true;
            }
            $replaceOld = preg_replace('/\D/', '', (string) $method->account_number) === $oldWalletNumber;
            foreach ($m as $key => $value) {
                if ($replaceOld || blank($method->{$key})) {
                    $method->{$key} = $value;
                }
            }
            $method->save();
        }

        // رقم واتس إسناد للتواصل
        if (blank(Setting::get('contact_whatsapp'))) {
            Setting::put('contact_whatsapp', config('isnad.defaults.contact_whatsapp'));
        }
    }
}
