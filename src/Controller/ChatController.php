<?php

namespace App\Controller;

use App\Service\AiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/chat')]
#[IsGranted('ROLE_USER')]
class ChatController extends AbstractController
{
    public function __construct(
        private AiService $aiService
    ) {}

    #[Route('/send', name: 'app_chat_send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $history = $data['history'] ?? [];

        if (empty($message)) {
            return $this->json(['error' => 'Message vide', 'success' => false], 400);
        }

        $systemPrompt = "Tu es un assistant juridique français expert. Tu aides les utilisateurs à rédiger des documents légaux, comprendre des termes juridiques, et répondre à leurs questions sur le droit des affaires, les contrats, et la conformité. Réponds toujours en français de manière claire et professionnelle.";

        try {
            $response = $this->aiService->chat($message, $history, $systemPrompt);

            if ($response === null) {
                return $this->json([
                    'error' => 'L\'IA n\'a pas pu générer de réponse',
                    'success' => false
                ], 500);
            }

            return $this->json([
                'response' => $response,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}