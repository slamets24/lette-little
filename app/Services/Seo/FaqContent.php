<?php

namespace App\Services\Seo;

class FaqContent
{
    /**
     * The FAQ entries rendered on the FAQ page and in its FAQPage schema.
     *
     * Each answer is a list of parts. A part with an `href` is rendered as a
     * link: `internal` links use Inertia navigation, other links are plain
     * anchors. The schema answer is the parts' text joined together.
     *
     * @return list<array{question: string, answer: list<array{text: string, href?: string, internal?: bool}>}>
     */
    public static function items(): array
    {
        return [
            [
                'question' => 'How do I choose a size?',
                'answer' => [
                    ['text' => 'Use the '],
                    ['text' => 'size guide', 'href' => route('size-guide', absolute: false), 'internal' => true],
                    ['text' => " and your baby's current height and weight. When you are between sizes, pick the larger one."],
                ],
            ],
            [
                'question' => 'How should I wash the clothes?',
                'answer' => [
                    ['text' => 'Cold gentle wash, mild detergent, and line dry in the shade. See the '],
                    ['text' => 'care guide', 'href' => route('care-guide', absolute: false), 'internal' => true],
                    ['text' => ' for the full notes.'],
                ],
            ],
            [
                'question' => 'When do I pay shipping?',
                'answer' => [
                    ['text' => 'The product total is paid at checkout. Courier shipping is quoted after we have your address. Read '],
                    ['text' => 'shipping & returns', 'href' => route('shipping-returns', absolute: false), 'internal' => true],
                    ['text' => ' for timing and return rules.'],
                ],
            ],
            [
                'question' => 'What payment methods do you accept?',
                'answer' => [
                    ['text' => 'You can pay instantly through Midtrans, or by bank transfer to our account. Bank transfer is useful when you want to skip the payment gateway fee, or when Midtrans is temporarily unavailable.'],
                ],
            ],
            [
                'question' => 'How do I contact you?',
                'answer' => [
                    ['text' => 'Email '],
                    ['text' => 'hello@lettelittle.id', 'href' => 'mailto:hello@lettelittle.id'],
                    ['text' => ' or send a message on Instagram '],
                    ['text' => '@lettelittle.id', 'href' => 'https://instagram.com/lettelittle.id'],
                    ['text' => '.'],
                ],
            ],
        ];
    }

    /**
     * Build the schema.org FAQPage payload from the same FAQ entries.
     *
     * @return array<string, mixed>
     */
    public static function jsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => self::plainAnswer($item['answer']),
                ],
            ], self::items()),
        ];
    }

    /**
     * Join an answer's parts into plain text.
     *
     * @param  list<array{text: string, href?: string, internal?: bool}>  $answer
     */
    public static function plainAnswer(array $answer): string
    {
        return implode('', array_column($answer, 'text'));
    }
}
