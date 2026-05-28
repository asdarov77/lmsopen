<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Link;
use App\Models\Aukstructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DOMDocument;
use DOMXPath;

class SearchController2 extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'aircraft' => 'required|exists:aircrafts,id',
            'path' => 'required|string',
        ]);

        $searchTerm = mb_strtolower($request->input('query'));
        $aircraft = Aircraft::find($request->input('aircraft'))->path;
        $path = $request->input('path');
        $directory = "private/{$aircraft}/{$path}/";

        $matches = [];
        $files = Storage::disk('public')->files($directory);

        foreach ($files as $file) {
            if (!str_ends_with($file, '.html') || str_ends_with($file, 'index.html')) {
                continue;
            }

            $contents = Storage::get($file);
            $lowerContents = mb_strtolower($contents);

            if (mb_stripos($contents, $searchTerm) !== false) {
                $filename = basename($file);
                $link = Link::where('link', $filename)->first();

                if (!$link) {
                    continue;
                }

                $aukstructure = Aukstructure::find($link->aukstructure_id);
                $highlightedContents = $this->highlightWords($contents, $searchTerm);

                $matches[] = [
                    'file' => $file,
                    'position' => mb_stripos($contents, $searchTerm),
                    'length' => mb_strlen($searchTerm),
                    'contents' => $highlightedContents,
                    'title' => $aukstructure?->title ?? 'Без названия',
                ];
            }
        }

        return response()->json($matches);
    }

    private function highlightWords(string $html, string $searchTerm): string
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOWARNING | LIBXML_NOERROR);

        $xpath = new DOMXPath($dom);
        $textNodes = $xpath->query('//body//text()[not(ancestor::script)]');

        $id = 1;

        foreach ($textNodes as $node) {
            $text = $node->nodeValue;
            if (mb_stripos($text, $searchTerm) === false) {
                continue;
            }

            $parts = preg_split('/(' . preg_quote($searchTerm, '/') . ')/iu', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            $fragment = $dom->createDocumentFragment();

            foreach ($parts as $part) {
                if (mb_strtolower($part) === mb_strtolower($searchTerm)) {
                    $span = $dom->createElement('span', htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                    $span->setAttribute('class', 'highlighted');
                    $span->setAttribute('data-id', (string)$id++);
                    $fragment->appendChild($span);
                } else {
                    $fragment->appendChild($dom->createTextNode($part));
                }
            }

            $node->parentNode->replaceChild($fragment, $node);
        }

        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        return $body ? $dom->saveHTML($body) : $dom->saveHTML();
    }
}
