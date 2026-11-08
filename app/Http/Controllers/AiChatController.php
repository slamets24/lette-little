<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __construct(
        protected GeminiChatService $chatService
    ) {}

    /**
     * Handle incoming chat message for customer support.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:15'],
            'history.*.role' => ['required', 'string', 'in:user,model,assistant'],
            'history.*.content' => ['required', 'string', 'max:4000'],
        ]);

        $message = strip_tags(trim($validated['message']));
        $history = $validated['history'] ?? [];

        $result = $this->chatService->reply($history, $message);

        $waNumber = config('services.whatsapp.number', '6283822458988');
        $waNumberSanitized = preg_replace('/\D+/', '', (string) $waNumber);
        if (str_starts_with($waNumberSanitized, '0')) {
            $waNumberSanitized = '62'.substr($waNumberSanitized, 1);
        }

        $waUrl = 'https://wa.me/'.$waNumberSanitized.'?text='.rawurlencode('Halo Admin Lette Little, saya butuh bantuan terkait produk / pesanan.');

        return response()->json([
            'success' => true,
            'reply' => $result['reply'],
            'status' => $result['status'],
            'whatsapp_url' => $waUrl,
        ]);
    }
}
