<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

class SanitizadorHtml
{
    private const PROHIBIDAS = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'link', 'meta'];

    private const PERMITIDAS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'span', 'div',
        'h1', 'h2', 'h3', 'h4', 'h5', 'blockquote', 'pre', 'code',
        'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
    ];

    private const ATRIBUTOS = [
        '*' => ['style', 'class'],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public function limpiar(?string $html): string
    {
        $html = trim((string) $html);

        $sinContenido = trim(strip_tags($html)) === '' && ! str_contains($html, '<img');

        if ($html === '' || $sinContenido) {
            return '';
        }

        $documento = new DOMDocument();
        $errores = libxml_use_internal_errors(true);
        $documento->loadHTML('<meta charset="utf-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($errores);

        $cuerpo = $documento->getElementsByTagName('body')->item(0);

        if (! $cuerpo) {
            return '';
        }

        $this->limpiarNodo($cuerpo);

        $salida = '';

        foreach ($cuerpo->childNodes as $hijo) {
            $salida .= $documento->saveHTML($hijo);
        }

        return trim($salida);
    }

    public function aTexto(?string $html, ?int $limite = null): string
    {
        $texto = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)));

        return $limite ? mb_substr($texto, 0, $limite) : $texto;
    }

    private function limpiarNodo(DOMNode $nodo): void
    {
        for ($i = $nodo->childNodes->length - 1; $i >= 0; $i--) {
            $hijo = $nodo->childNodes->item($i);

            if ($hijo instanceof DOMComment) {
                $nodo->removeChild($hijo);
                continue;
            }

            if (! $hijo instanceof DOMElement) {
                continue;
            }

            $etiqueta = strtolower($hijo->nodeName);

            if (in_array($etiqueta, self::PROHIBIDAS, true)) {
                $nodo->removeChild($hijo);
                continue;
            }

            $this->limpiarNodo($hijo);

            if (! in_array($etiqueta, self::PERMITIDAS, true)) {
                $this->desenvolver($hijo);
                continue;
            }

            $this->limpiarAtributos($hijo, $etiqueta);
        }
    }

    private function desenvolver(DOMElement $elemento): void
    {
        $padre = $elemento->parentNode;

        while ($elemento->firstChild) {
            $padre->insertBefore($elemento->firstChild, $elemento);
        }

        $padre->removeChild($elemento);
    }

    private function limpiarAtributos(DOMElement $elemento, string $etiqueta): void
    {
        $permitidos = array_merge(self::ATRIBUTOS['*'], self::ATRIBUTOS[$etiqueta] ?? []);

        for ($i = $elemento->attributes->length - 1; $i >= 0; $i--) {
            $atributo = $elemento->attributes->item($i);

            if (! in_array(strtolower($atributo->nodeName), $permitidos, true)
                || $this->valorPeligroso($atributo->nodeValue)) {
                $elemento->removeAttribute($atributo->nodeName);
            }
        }

        if ($etiqueta === 'a' && $elemento->getAttribute('target') === '_blank') {
            $elemento->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function valorPeligroso(string $valor): bool
    {
        $normalizado = strtolower(preg_replace('/\s+/', '', $valor));

        foreach (['javascript:', 'vbscript:', 'expression(', 'data:text/html', '<script'] as $peligro) {
            if (str_contains($normalizado, $peligro)) {
                return true;
            }
        }

        return false;
    }
}
