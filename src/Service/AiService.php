<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(
        private HttpClientInterface $httpClient,
        string $openaiApiKey = ''
    ) {
        $this->apiKey = $openaiApiKey;
    }

    public function generate(string $prompt, string $model = 'gpt-4o-mini', int $maxTokens = 2048): ?string
    {
        try {
            $response = $this->httpClient->request('POST', "{$this->baseUrl}/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.7,
                ],
                'timeout' => 120,
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur IA: ' . $e->getMessage());
        }
    }

    public function chat(string $message, array $history = [], string $systemPrompt = ''): ?string
    {
        $messages = [];
        
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = $this->httpClient->request('POST', "{$this->baseUrl}/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => $messages,
                    'max_tokens' => 2048,
                    'temperature' => 0.7,
                ],
                'timeout' => 60,
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur IA: ' . $e->getMessage());
        }
    }

    public function improveDocument(string $content, string $tone = 'formel'): ?string
    {
        $prompt = <<<PROMPT
Tu es un assistant juridique français expert. Améliore ce document juridique pour le rendre plus {$tone} et professionnel. Garde la même structure.

Réponds UNIQUEMENT avec le document amélioré complet, en français.

Document à améliorer :
{$content}
PROMPT;

        return $this->generate($prompt, 'gpt-4o-mini', 4096);
    }

    public function generateClause(string $type, array $context = []): ?string
    {
        $contextStr = '';
        foreach ($context as $key => $value) {
            $contextStr .= "- {$key}: {$value}\n";
        }

        $prompt = <<<PROMPT
Tu es un assistant juridique français. Génère une clause "{$type}" pour un contrat français.
{$contextStr}
Réponds UNIQUEMENT avec la clause en français, sans explication.
PROMPT;

        return $this->generate($prompt, 'gpt-4o-mini', 1024);
    }

    public function reformulate(string $content, string $style): ?string
    {
        $styleInstructions = match ($style) {
            'simple' => 'Simplifie le langage, évite le jargon juridique.',
            'formel' => 'Utilise un langage juridique formel.',
            'concis' => 'Rends le texte plus court et direct.',
            'detaille' => 'Développe chaque point avec plus de détails.',
            default => 'Améliore la clarté.',
        };

        $prompt = <<<PROMPT
Tu es un assistant juridique français. {$styleInstructions}

Texte original :
{$content}

Texte reformulé en français (complet) :
PROMPT;

        return $this->generate($prompt, 'gpt-4o-mini', 4096);
    }

    public function summarize(string $content): ?string
    {
        $prompt = <<<PROMPT
Tu es un assistant juridique français. Résume ce document en points clés :
- Parties impliquées
- Objet du contrat  
- Obligations principales
- Durée et conditions

Document :
{$content}

Résumé structuré en français :
PROMPT;

        return $this->generate($prompt, 'gpt-4o-mini', 1024);
    }

    public function checkCompliance(string $content): ?string
    {
        $prompt = <<<PROMPT
Tu es un juriste français. Analyse ce document et liste :
1. Clauses manquantes importantes
2. Formulations à améliorer
3. Points d'attention juridiques

Document :
{$content}

Analyse en français :
PROMPT;

        return $this->generate($prompt, 'gpt-4o-mini', 1024);
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function generateStream(string $prompt, string $model = 'gpt-4o-mini', int $maxTokens = 2048): \Generator
    {
        $response = $this->httpClient->request('POST', "{$this->baseUrl}/chat/completions", [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
                'stream' => true,
            ],
        ]);

        foreach ($this->httpClient->stream($response) as $chunk) {
            $content = $chunk->getContent();
            foreach (explode("\n", $content) as $line) {
                if (str_starts_with($line, 'data: ') && $line !== 'data: [DONE]') {
                    $json = json_decode(substr($line, 6), true);
                    if (isset($json['choices'][0]['delta']['content'])) {
                        yield $json['choices'][0]['delta']['content'];
                    }
                }
            }
        }
    }
}