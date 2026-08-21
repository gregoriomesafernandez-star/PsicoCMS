<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $entrada)
    <url>
        <loc>{{ $entrada['url'] }}</loc>
        <lastmod>{{ $fecha }}</lastmod>
        <priority>{{ $entrada['prioridad'] }}</priority>
    </url>
@endforeach
</urlset>
