@php
    /*
     | خط ثمانية (Thmanyah Sans)
     | رخصة الخط ما بتسمح بإعادة توزيع ملفاته، فما انحطّت مع المشروع.
     | نزّله من موقع ثمانية الرسمي وحط الملفات زي ما هي بمجلد: public/fonts/thmanyah
     | الصفحة بتلقطهم لحالها وبتعرف الوزن من اسم الملف (Light, Regular, Medium, Bold...).
     */
    $fontFaces = (function () {
        $dir = public_path('fonts/thmanyah');
        if (! is_dir($dir)) {
            return [];
        }
        $files = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (preg_match('/\.(woff2|woff|otf|ttf)$/i', $f->getFilename())) {
                $files[] = $f->getPathname();
            }
        }
        // لو في أكثر من عائلة (Sans / Serif / Display)، خُد ملفات الـ Sans بس
        $sans = array_values(array_filter($files, fn ($p) => stripos(basename($p), 'sans') !== false));
        $files = $sans ?: array_values(array_filter($files, fn ($p) => ! preg_match('/serif|display/i', basename($p))));
        $files = array_values(array_filter($files, fn ($p) => ! preg_match('/italic|oblique/i', basename($p))));

        $weights = [
            'extrablack' => 950, 'ultrablack' => 950, 'extrabold' => 800, 'ultrabold' => 800, 'semibold' => 600, 'demibold' => 600,
            'extralight' => 200, 'ultralight' => 200, 'hairline' => 100, 'thin' => 100, 'light' => 300, 'black' => 900, 'heavy' => 900,
            'bold' => 700, 'medium' => 500, 'regular' => 400, 'book' => 400, 'normal' => 400,
        ];
        $rank = ['woff2' => 0, 'woff' => 1, 'otf' => 2, 'ttf' => 3];
        $byWeight = [];
        foreach ($files as $p) {
            $name = strtolower(preg_replace('/[\s_\-]+/', '', pathinfo($p, PATHINFO_FILENAME)));
            $w = 400;
            foreach ($weights as $k => $v) {
                if (str_contains($name, $k)) { $w = $v; break; }
            }
            if (preg_match('/variable|\[wght\]|vf$/i', basename($p))) {
                $w = '100 950';
            }
            $byWeight[(string) $w][] = $p;
        }
        $faces = [];
        foreach ($byWeight as $w => $list) {
            usort($list, fn ($a, $b) => ($rank[strtolower(pathinfo($a, PATHINFO_EXTENSION))] ?? 9) <=> ($rank[strtolower(pathinfo($b, PATHINFO_EXTENSION))] ?? 9));
            $src = [];
            foreach ($list as $p) {
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $fmt = ['woff2' => 'woff2', 'woff' => 'woff', 'otf' => 'opentype', 'ttf' => 'truetype'][$ext];
                $rel = 'fonts/thmanyah/'.str_replace(DIRECTORY_SEPARATOR, '/', ltrim(substr($p, strlen($dir)), '\\/'));
                $src[] = "url('".implode('/', array_map('rawurlencode', explode('/', $rel)))."') format('{$fmt}')";
            }
            $faces[] = ['weight' => $w, 'src' => implode(', ', $src)];
        }

        return $faces;
    })();
@endphp
@if ($fontFaces)
<style>
@foreach ($fontFaces as $f)
@font-face { font-family: 'Thmanyah Sans'; src: {!! str_replace("url('", "url('".rtrim(asset('/'), '/').'/', $f['src']) !!}; font-weight: {{ $f['weight'] }}; font-style: normal; font-display: swap; }
@endforeach
</style>
@endif
