<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    /** تبويبات اللوحة: كل تبويب بيفرجي قائمة وحدة */
    public const TABS = [
        'review' => [Application::PAYMENT_SUBMITTED],
        'confirmed' => [Application::CONFIRMED],
        'pending' => Application::AWAITING_PAYMENT,
        'rejected' => [Application::REJECTED, Application::EXPIRED, Application::WAITLISTED],
    ];

    public function index(Request $request): View
    {
        Application::expireOverdue();

        $track = $request->query('track');
        $track = $track && array_key_exists($track, config('isnad.tracks')) ? $track : null;
        $q = trim((string) $request->query('q'));

        $counts = Application::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');
        $tabCounts = collect(self::TABS)->map(fn ($sts) => collect($sts)->sum(fn ($st) => $counts[$st] ?? 0))->all();
        $tabCounts['all'] = $counts->sum();

        // التبويب الافتراضي: التحويلات اللي بحاجة تأكيد، وإذا ما في، الكل
        $tab = $request->query('tab');
        if (! in_array($tab, ['review', 'confirmed', 'pending', 'rejected', 'all', 'seats'], true)) {
            $tab = $tabCounts['review'] > 0 ? 'review' : 'all';
        }

        $query = Application::query()->with('paymentMethod')
            ->when(isset(self::TABS[$tab]), fn ($qb) => $qb->whereIn('status', self::TABS[$tab]))
            ->when($track, fn ($qb) => $qb->where('track', $track))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('university', 'like', "%{$q}%"));
            })
            ->orderByRaw("CASE status
                WHEN 'payment_submitted' THEN 0
                WHEN 'accepted' THEN 1
                WHEN 'submitted' THEN 2
                WHEN 'interview_scheduled' THEN 2
                WHEN 'confirmed' THEN 3
                ELSE 4 END")
            ->orderByDesc('payment_submitted_at')
            ->orderByDesc('created_at');

        $seats = Setting::seats();
        $perTrack = [];
        foreach (config('isnad.tracks') as $key => $label) {
            $perTrack[$key] = [
                'label' => $label,
                'taken' => Application::seatsTaken($key),
                'confirmed' => Application::where('track', $key)->where('status', Application::CONFIRMED)->count(),
                'seats' => $seats[$key],
            ];
        }

        return view('admin.dashboard', [
            'applications' => $query->paginate(30)->withQueryString(),
            'tab' => $tab,
            'tabCounts' => $tabCounts,
            'perTrack' => $perTrack,
            'filters' => compact('track', 'q'),
        ]);
    }

    public function export(): StreamedResponse
    {
        $filename = 'isnad-applications-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // عشان العربي يظهر صح بإكسل
            fputcsv($out, ['الكود', 'الاسم', 'الواتس', 'الجامعة', 'التخصص', 'المسار', 'الساعات', 'المبلغ', 'مؤهل', 'الحالة', 'طريقة الدفع', 'رقم الحوالة', 'وقت رفع التحويل', 'تاريخ التثبيت', 'رابط الأعمال', 'ملاحظات الطالب', 'تاريخ التسجيل']);
            Application::with('paymentMethod')->orderBy('created_at')->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $a) {
                    fputcsv($out, [
                        $a->code, $a->name, $a->phone, $a->university, $a->major, $a->trackName(), $a->hours, $a->amountDue(),
                        $a->eligible ? 'نعم' : 'لا', $a->statusLabel(),
                        optional($a->paymentMethod)->title, $a->payment_reference,
                        optional($a->payment_submitted_at)->format('Y-m-d H:i'),
                        optional($a->confirmed_at)->format('Y-m-d H:i'),
                        $a->portfolio_url, $a->notes, $a->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
