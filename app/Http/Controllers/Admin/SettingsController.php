<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings', [
            'fees' => Setting::fees(),
            'open' => Setting::registrationOpen(),
            'contact' => Setting::get('contact_whatsapp'),
            'seats' => Setting::seats(),
            'tracks' => config('isnad.tracks'),
            'trackMeta' => config('isnad.track_meta'),
            'taken' => collect(array_keys(config('isnad.tracks')))->mapWithKeys(fn ($t) => [$t => \App\Models\Application::seatsTaken($t)])->all(),
            'methods' => PaymentMethod::orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $trackKeys = array_keys(config('isnad.tracks'));
        $data = $request->validate([
            'fees' => ['required', 'array'],
            'fees.*' => ['required', 'integer', 'min:0', 'max:10000'],
            'contact_whatsapp' => ['nullable', 'string', 'max:32'],
            'seats' => ['required', 'array'],
            'seats.*' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        Setting::put('fees', array_intersect_key(array_map('intval', $data['fees']), array_flip(config('isnad.hours_options'))));
        Setting::put('contact_whatsapp', $data['contact_whatsapp'] ?? '');
        Setting::put('seats', array_intersect_key(array_map('intval', $data['seats']), array_flip($trackKeys)));

        return back()->with('success', 'انحفظت الإعدادات.');
    }

    private function methodRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(array_keys(PaymentMethod::TYPES))],
            'account_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'iban' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:500'],
            'steps' => ['nullable', 'string', 'max:3000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function storeMethod(Request $request): RedirectResponse
    {
        $data = $request->validate($this->methodRules());
        $data['active'] = $request->boolean('active');
        PaymentMethod::create($data);

        return back()->with('success', 'انضافت طريقة الدفع.');
    }

    public function updateMethod(Request $request, PaymentMethod $method): RedirectResponse
    {
        $data = $request->validate($this->methodRules());
        $data['active'] = $request->boolean('active');
        $method->update($data);

        return back()->with('success', 'انحفظت '.$method->title.'.');
    }

    public function destroyMethod(PaymentMethod $method): RedirectResponse
    {
        $method->delete();

        return back()->with('success', 'انحذفت طريقة الدفع.');
    }
}
