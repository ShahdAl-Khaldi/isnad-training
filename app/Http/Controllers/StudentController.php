<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function create(Request $request): View
    {
        // المسارات اللي مقاعدها خلصت
        $fullTracks = array_values(array_filter(array_keys(config('isnad.tracks')), fn ($t) => ! Application::hasFreeSeat($t)));

        return view('student.apply', [
            'tracks' => config('isnad.tracks'),
            'hoursOptions' => config('isnad.hours_options'),
            'fees' => Setting::fees(),
            'methods' => PaymentMethod::active()->get(),
            'contact' => Setting::get('contact_whatsapp'),
            'fullTracks' => $fullTracks,
            'open' => Setting::registrationOpen(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Setting::registrationOpen(), 403, 'التسجيل مسكّر حالياً.');

        // تسامح بالإدخال: أرقام عربية بالجوال، ورابط بدون https://
        $phoneIn = strtr((string) $request->input('phone'), ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9', '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9']);
        $phoneIn = trim(preg_replace('/[^\d+\-\s]/u', '', $phoneIn));
        $link = trim((string) $request->input('portfolio_url'));
        if ($link !== '' && ! preg_match('#^https?://#i', $link)) {
            $link = 'https://' . $link;
        }
        $request->merge(['phone' => $phoneIn, 'portfolio_url' => $link === '' ? null : $link]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s\-]{9,20}$/'],
            'university' => ['required', 'string', 'max:150'],
            'major' => ['required', 'string', 'max:150'],
            'track' => ['required', Rule::in(array_keys(config('isnad.tracks')))],
            'hours' => ['required', Rule::in(config('isnad.hours_options'))],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'eligible' => ['nullable', Rule::in(['1', '0'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('active', true)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ], [
            'phone.regex' => 'اكتب رقم واتس صحيح، مثلاً 0599123456 أو ‎+970599123456',
            'receipt.required' => 'ارفع صورة التحويل.',
            'receipt.mimes' => 'الصورة لازم تكون JPG أو PNG أو WEBP أو PDF.',
            'receipt.max' => 'حجم الملف لازم يكون أقل من 5 ميجا.',
            'payment_method_id.required' => 'اختار على شو حوّلت.',
        ]);

        // منع التكرار: نفس الرقم ونفس المسار وطلبه لسا فعّال
        $phone = Application::normalizePhone($data['phone']);
        $existing = Application::query()
            ->where('track', $data['track'])
            ->whereNotIn('status', [Application::REJECTED, Application::EXPIRED])
            ->get()
            ->first(fn ($a) => $a->whatsappNumber() === $phone);
        if ($existing) {
            return back()->withInput()->withErrors(['phone' => 'إنت مسجّل قبل هيك بنفس المسار، طلبك واصلنا ورح نتواصل معك على الواتس.']);
        }

        if (! Application::hasFreeSeat($data['track'])) {
            return back()->withInput()->withErrors(['track' => 'مقاعد هالمسار كاملة حالياً، اختار مسار تاني أو تواصل معنا.']);
        }

        $app = new Application(collect($data)->except(['payment_method_id', 'payment_reference', 'receipt'])->all());
        $app->eligible = ($data['eligible'] ?? '1') === '1';
        $app->fee = Setting::feeFor((int) $data['hours']);
        $app->save();

        $path = $request->file('receipt')->store('receipts', 'local');
        $app->submitPayment(PaymentMethod::findOrFail($data['payment_method_id']), $data['payment_reference'] ?? null, $path);

        return redirect()->route('apply.done')->with('registered', explode(' ', trim($app->name))[0]);
    }

    /** رسالة بعد التسجيل: تم تسجيلك وسيتم التواصل معك */
    public function done(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('registered')) {
            return redirect()->route('apply');
        }

        return view('student.done', ['name' => $request->session()->get('registered'), 'noindex' => true]);
    }
}
