<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Str;
use Laravel\Ai\Prompts\AgentPrompt;

class FewShotExamplesMiddleware
{
    private static ?array $cachedExamples = null;

    public function handle(AgentPrompt $prompt, Closure $next): mixed
    {
        $examples = $this->loadExamples();
        $relevant = $this->findRelevant($prompt->prompt, $examples);

        if (empty($relevant)) {
            return $next($prompt);
        }

        $block = $this->formatExamplesBlock($relevant);

        return $next($prompt->prepend($block));
    }

    private function loadExamples(): array
    {
        if (self::$cachedExamples !== null) {
            return self::$cachedExamples;
        }

        $path = storage_path('ai/dp-assistant-examples.json');

        if (! file_exists($path)) {
            return self::$cachedExamples = [];
        }

        $json = json_decode(file_get_contents($path), true);

        return self::$cachedExamples = is_array($json) ? $json : [];
    }

    private function findRelevant(string $question, array $examples, int $limit = 2): array
    {
        $normalized = $this->normalize($question);
        $scored = [];

        foreach ($examples as $example) {
            $score = 0;
            foreach ($example['keywords'] ?? [] as $keyword) {
                if (Str::contains($normalized, $this->normalize($keyword))) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scored[] = ['example' => $example, 'score' => $score];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_map(
            fn ($item) => $item['example'],
            array_slice($scored, 0, $limit),
        );
    }

    private function formatExamplesBlock(array $examples): string
    {
        $lines = ['EXEMPLOS DE REFERÊNCIA (use como guia de formato e qualidade, não copie os dados):'];

        foreach ($examples as $example) {
            $lines[] = '';
            $lines[] = "Pergunta: \"{$example['question']}\"";
            $lines[] = '';
            $lines[] = 'BOM exemplo de resposta:';
            $lines[] = $example['good_answer'];
            $lines[] = '';
            $lines[] = 'RUIM exemplo de resposta:';
            $lines[] = $example['bad_answer'];
            $lines[] = "Por quê é ruim: {$example['why_bad']}";
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = 'PERGUNTA DO USUÁRIO:';

        return implode("\n", $lines);
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[áàâã]/u', 'a', $text);
        $text = preg_replace('/[éèê]/u', 'e', $text);
        $text = preg_replace('/[íìî]/u', 'i', $text);
        $text = preg_replace('/[óòôõ]/u', 'o', $text);
        $text = preg_replace('/[úùû]/u', 'u', $text);
        $text = preg_replace('/[ç]/u', 'c', $text);

        return $text;
    }
}
