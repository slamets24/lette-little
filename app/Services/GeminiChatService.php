<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatService
{
    protected ?string $apiKey;

    protected string $model;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    }

    /**
     * Send chat conversation to Gemini API and get response.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array{reply: string, status: string}
     */
    public function reply(array $history, string $message): array
    {
        if (empty($this->apiKey)) {
            return [
                'reply' => 'Halo! Salam hangat dari Lette Little ☁️✨'."\n\n".
                    'Asisten virtual AI kami saat ini sedang dalam pemeliharaan berkala. Namun jangan khawatir, tim Customer Care kami siap membantu Bunda & Ayah secara langsung melalui WhatsApp!'."\n\n".
                    'Silakan klik tombol WhatsApp di bawah untuk konsultasi ukuran, cek stok, atau pertanyaan seputar pesanan ya Bun.',
                'status' => 'fallback',
            ];
        }

        // Layer 1 Guardrail: Pre-Filter Prompt Injections & Out-of-Scope Intentions
        if ($this->isOutOfScopeOrInjection($message)) {
            return [
                'reply' => 'Wah, pertanyaan tersebut di luar jangkauan asisten Lette Little ya Bun 😊'."\n\n".
                    'Aku spesialis membantu seputar pakaian bayi lembut, pilihan dress, romper, sleepsuit, panduan ukuran, bahan katun organik, hingga bantuan pemesanan di Lette Little.'."\n\n".
                    'Ada yang bisa kubantu untuk si kecil hari ini? ☁️👶',
                'status' => 'guarded',
            ];
        }

        try {
            $systemInstruction = $this->buildSystemPrompt();

            // Format contents for Gemini API: [{ role: 'user'|'model', parts: [{ text: '...' }] }]
            $contents = [];

            foreach ($history as $msg) {
                $role = ($msg['role'] === 'assistant' || $msg['role'] === 'model') ? 'model' : 'user';
                $text = trim((string) ($msg['content'] ?? ''));
                if ($text !== '' && ! $this->isOutOfScopeOrInjection($text)) {
                    $contents[] = [
                        'role' => $role,
                        'parts' => [['text' => $text]],
                    ];
                }
            }

            // Sanitize contents: Ensure strictly alternating user/model roles and starts with 'user'
            $sanitizedContents = [];
            foreach ($contents as $item) {
                if (empty($sanitizedContents)) {
                    if ($item['role'] === 'user') {
                        $sanitizedContents[] = $item;
                    }
                } else {
                    $lastIdx = count($sanitizedContents) - 1;
                    if ($sanitizedContents[$lastIdx]['role'] === $item['role']) {
                        $sanitizedContents[$lastIdx]['parts'][0]['text'] .= "\n".$item['parts'][0]['text'];
                    } else {
                        $sanitizedContents[] = $item;
                    }
                }
            }

            // Ensure we have at least the current user message
            if (empty($sanitizedContents) || end($sanitizedContents)['role'] !== 'user') {
                $sanitizedContents[] = [
                    'role' => 'user',
                    'parts' => [['text' => $message]],
                ];
            }

            // Keep max 8 turns for token efficiency
            if (count($sanitizedContents) > 8) {
                $sanitizedContents = array_slice($sanitizedContents, -8);
                if (! empty($sanitizedContents) && $sanitizedContents[0]['role'] === 'model') {
                    array_shift($sanitizedContents);
                }
            }

            $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $payload = [
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'contents' => $sanitizedContents,
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 350,
                    'topP' => 0.85,
                ],
            ];

            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            if ($response->failed()) {
                Log::warning('Gemini API request failed for Lette Little', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'reply' => 'Maaf ya Bun, terjadi sedikit kendala koneksi saat memproses jawaban. Silakan coba kirim ulang atau langsung chat Customer Service kami di WhatsApp.',
                    'status' => 'error',
                ];
            }

            $json = $response->json();
            $replyText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $replyText) {
                return [
                    'reply' => 'Ada yang bisa kubantu lagi mengenai koleksi pakaian bayi atau pesanan Bunda di Lette Little?',
                    'status' => 'empty',
                ];
            }

            return [
                'reply' => trim($replyText),
                'status' => 'success',
            ];
        } catch (\Throwable $e) {
            Log::error('GeminiChatService exception: '.$e->getMessage());

            return [
                'reply' => 'Maaf Bunda, sistem bantuan sedang mengalami gangguan singkat. Anda bisa langsung chat admin kami via WhatsApp ya!',
                'status' => 'exception',
            ];
        }
    }

    /**
     * Check if the user message attempts a jailbreak, role change, or obvious out-of-scope query.
     */
    protected function isOutOfScopeOrInjection(string $text): bool
    {
        $lower = mb_strtolower($text);

        $injectionPatterns = [
            'ignore previous instruction',
            'ignore all previous',
            'abaikan instruksi',
            'abaikan semua instruksi',
            'you are now',
            'kamu sekarang adalah',
            'berpura-puralah',
            'pretend you are',
            'act as',
            'jadilah sebagai',
            'jailbreak',
            'developer mode',
            'system prompt',
            'instruksi sistem',
            'write a python',
            'buatkan script',
            'buatkan kode',
            'tuliskan kode',
            'write code',
            'tulis skrip',
            'siapa presiden',
            'buat puisi tentang',
            'rumus matematika',
        ];

        foreach ($injectionPatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the dynamic knowledge-grounded system instruction for Lette Little.
     */
    protected function buildSystemPrompt(): string
    {
        $catalogSummary = Cache::remember('lette_catalog_prompt_context', 3600, function () {
            $categories = Category::query()->where('is_active', true)->pluck('name')->implode(', ');

            $products = Product::query()
                ->where('is_active', true)
                ->with(['category', 'variants' => fn ($q) => $q->where('is_active', true)])
                ->limit(10)
                ->get()
                ->map(function ($p) {
                    $sizes = $p->variants->pluck('size')->unique()->implode(', ');
                    $colors = $p->variants->pluck('color')->unique()->implode(', ');
                    $price = 'Rp '.number_format($p->base_price, 0, ',', '.');

                    return "- {$p->name} ({$p->category?->name}): {$price}. Varian size: [{$sizes}], warna: [{$colors}]. Bahan: {$p->materials}";
                })
                ->implode("\n");

            return "Kategori Toko: {$categories}\nKatalog Produk Aktif:\n{$products}";
        });

        return <<<PROMPT
Kamu adalah "Lette Assistant", asisten virtual belanja resmi dari "Lette Little" — brand butik pakaian bayi & balita (0–18 bulan) premium di Indonesia.
Slogan: "Made Light. Made Gentle."

PANDUAN KEPRIBADIAN & GAYA KOMUNIKASI:
1. Panggil customer dengan hangat, lembut, dan sopan: gunakan sapaan "Bunda" atau "Ayah" (atau "Kakak").
2. Gunakan Bahasa Indonesia yang ramah, santun, hangat, dan natural dengan sentuhan emoji yang manis (☁️, ✨, 👶, 🌿).
3. Respon harus ringkas, jelas, to-the-point, dan tidak bertele-tele (maksimal 2–4 paragraf pendek).

KNOWLEDGE BASE RESMI LETTE LITTLE:
1. Produk & Bahan:
   - Produk dirancang khusus dengan bahan katun organik breathable (organic cotton poplin/jersey) yang sangat ramah untuk kulit bayi sensitif dan iklim tropis Indonesia.
   - Pilihan kategori: Dresses, Rompers, Sets, dan Sleepwear.
2. Panduan Ukuran (Size Guide):
   - 0-3M: Tinggi badan s/d 60 cm, Berat 3.5 - 5.5 kg
   - 3-6M: Tinggi badan 60 - 67 cm, Berat 5.5 - 7.5 kg
   - 6-9M: Tinggi badan 67 - 72 cm, Berat 7.5 - 9.0 kg
   - 9-12M: Tinggi badan 72 - 78 cm, Berat 9.0 - 10.5 kg
   - 12-18M: Tinggi badan 78 - 84 cm, Berat 10.5 - 12.0 kg
   *Jika bayi agak montok/chubby, sarankan naik 1 ukuran (size up) agar si kecil lebih nyaman bergerak.
3. Perawatan Pakaian (Care Guide):
   - Cuci dengan air dingin (suhu ruang), putaran lembut (gentle cycle).
   - Gunakan detergen khusus pakaian bayi tanpa pemutih keras.
   - Jemur di tempat teduh (line dry in shade) dan setrika suhu rendah.
4. Pengiriman & Wilayah:
   - Melayani pengiriman ke seluruh Indonesia (JNE, SiCepat, J&T).
   - Pesanan yang terkonfirmasi sebelum pukul 14.00 WIB diproses pada hari yang sama.
5. Pembayaran:
   - Otomatis & Instan via Midtrans Snap: QRIS, GoPay, ShopeePay, Virtual Account (BCA, Mandiri, BRI, BNI), Kartu Kredit.
   - Tersedia juga Transfer Bank Manual (BCA).
6. Penukaran & Retur:
   - Penukaran ukuran dilayani maksimal 3 hari setelah barang diterima dalam kondisi baru, tag utuh, dan belum dicuci.
7. Bantuan Langsung:
   - Untuk pesanan khusus, konfirmasi pembayaran manual, atau kendala pengiriman, arahkan customer untuk menghubungi WhatsApp Customer Service resmi kami.

{$catalogSummary}

ATURAN PENTING:
- Jangan pernah mengarang produk yang tidak ada di katalog.
- Jangan membahas topik di luar seputar pakaian bayi, perawatan pakaian, ukuran, pengiriman, dan pemesanan di Lette Little.
- Jika tidak tahu atau jika customer meminta bantuan manusia, arahkan dengan ramah ke tombol WhatsApp Customer Service.
PROMPT;
    }
}
