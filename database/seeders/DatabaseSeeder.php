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
        $methods = [
            [
                'title' => 'بنك فلسطين', 'type' => 'bank', 'sort' => 1,
                'account_name' => 'محمود أبو حمام', 'account_number' => '0594061657',
                'notes' => 'اكتب اسمك الكامل بملاحظة التحويل.',
                'steps' => implode("\n", [
                    'افتح تطبيق بنك فلسطين وادخل على حسابك.',
                    'اختار "تحويل" وبعدين التحويل لرقم جوال / عميل بنك فلسطين.',
                    'اكتب الرقم {number} وتأكد إنه الاسم اللي بيطلع: {name}.',
                    'اكتب المبلغ {amount} شيكل.',
                    'اكتب اسمك الكامل بملاحظة التحويل وأكّد العملية.',
                    'خُد سكرين شوت لإشعار التحويل وارفعه تحت.',
                ]),
            ],
            [
                'title' => 'جوال باي', 'type' => 'wallet', 'sort' => 2,
                'account_name' => 'محمود أبو حمام', 'account_number' => '0594061657',
                'notes' => 'اكتب اسمك الكامل بملاحظة التحويل.',
                'steps' => implode("\n", [
                    'افتح تطبيق جوال باي.',
                    'اختار "تحويل أموال".',
                    'اكتب رقم المحفظة {number} وتأكد إنه الاسم: {name}.',
                    'اكتب المبلغ {amount} شيكل وأكّد العملية.',
                    'خُد سكرين شوت لإشعار التحويل وارفعه تحت.',
                ]),
            ],
        ];
        foreach ($methods as $m) {
            $method = PaymentMethod::firstOrNew(['title' => $m['title']]);
            if (! $method->exists) {
                $method->active = true;
            }
            foreach ($m as $key => $value) {
                if (blank($method->{$key})) {
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
