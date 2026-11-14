<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function show(Application $application): View
    {
        Application::expireOverdue();
        $application->refresh()->load('paymentMethod');

        return view('admin.show', [
            'app' => $application,
            'seatsLeft' => (Setting::seats()[$application->track] ?? 0) - Application::seatsTaken($application->track),
        ]);
    }

    public function receipt(Application $application)
    {
        abort_unless($application->receipt_path && Storage::disk('local')->exists($application->receipt_path), 404);

        return Storage::disk('local')->response($application->receipt_path, null, [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function interview(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'interview_at' => ['required', 'date'],
            'interview_location' => ['nullable', 'string', 'max:255'],
        ]);

        $application->scheduleInterview(Carbon::parse($data['interview_at']), $data['interview_location'] ?? null);

        return back()->with('success', 'تم تحديد موعد المقابلة.');
    }

    public function accept(Request $request, Application $application): RedirectResponse
    {
        $wasHolding = in_array($application->status, Application::HOLDS_SEAT, true);
        if (! $wasHolding && ! Application::hasFreeSeat($application->track) && ! $request->boolean('force')) {
            return back()->with('error', 'مقاعد مسار '.$application->trackName().' كاملة. زِد عدد المقاعد من الإعدادات.');
        }

        $application->accept();

        return back()->with('success', 'رجع الطلب بانتظار التحويل.');
    }

    public function reject(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate(['student_message' => ['nullable', 'string', 'max:500']]);
        $application->reject($data['student_message'] ?? null);

        return back()->with('success', 'انرفض الطلب.');
    }

    public function confirm(Application $application): RedirectResponse
    {
        abort_unless(in_array($application->status, [Application::PAYMENT_SUBMITTED, Application::SUBMITTED, Application::INTERVIEW, Application::ACCEPTED, Application::EXPIRED], true), 422);
        $application->confirmPayment();

        return back()->with('success', 'تأكّد الدفع وتثبّت مقعد '.$application->name.'.');
    }

    public function note(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $application->admin_note = $data['admin_note'] ?? null;
        $application->save();

        return back()->with('success', 'انحفظت الملاحظات.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        if ($application->receipt_path) {
            Storage::disk('local')->delete($application->receipt_path);
        }
        $application->delete();

        return redirect()->route('admin.dashboard')->with('success', 'انحذف الطلب.');
    }
}
