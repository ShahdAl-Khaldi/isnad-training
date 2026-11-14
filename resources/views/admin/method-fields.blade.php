@php $p = $m ? 'm'.$m->id.'_' : 'new_'; @endphp
<div class="grid-2">
    <div class="field">
        <label for="{{ $p }}title">الاسم</label>
        <input class="input" id="{{ $p }}title" name="title" value="{{ $m?->title }}" placeholder="بنك فلسطين / جوال باي" required>
    </div>
    <div class="field">
        <label for="{{ $p }}type">النوع</label>
        <select class="input" id="{{ $p }}type" name="type">
            @foreach (\App\Models\PaymentMethod::TYPES as $k => $l)
                <option value="{{ $k }}" @selected(($m?->type ?? 'bank') === $k)>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label for="{{ $p }}account_name">اسم صاحب الحساب</label>
        <input class="input" id="{{ $p }}account_name" name="account_name" value="{{ $m?->account_name }}">
    </div>
    <div class="field">
        <label for="{{ $p }}account_number">رقم الحساب / المحفظة</label>
        <input class="input" id="{{ $p }}account_number" name="account_number" value="{{ $m?->account_number }}" dir="ltr" style="text-align:right">
    </div>
    <div class="field">
        <label for="{{ $p }}iban">IBAN <span class="muted small">(للبنك، اختياري)</span></label>
        <input class="input" id="{{ $p }}iban" name="iban" value="{{ $m?->iban }}" dir="ltr" style="text-align:right">
    </div>
    <div class="field">
        <label for="{{ $p }}sort">الترتيب</label>
        <input class="input" id="{{ $p }}sort" type="number" name="sort" min="0" value="{{ $m?->sort ?? 0 }}">
    </div>
</div>
<div class="field">
    <label for="{{ $p }}notes">ملاحظة داخلية <span class="muted small">(ما بتظهر للطالب)</span></label>
    <input class="input" id="{{ $p }}notes" name="notes" value="{{ $m?->notes }}" placeholder="مثلاً: اكتب كود الطلب بملاحظة التحويل">
</div>
<label class="switch">
    <input type="checkbox" name="active" value="1" @checked($m?->active ?? true)><span class="tr"></span> ظاهرة للطلاب
</label>
