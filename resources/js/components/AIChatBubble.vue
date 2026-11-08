<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Cloud, MessageCircle, Send, Sparkles, X } from '@lucide/vue';

interface ChatMessage {
    id: string;
    role: 'user' | 'assistant';
    content: string;
    timestamp: number;
}

const props = defineProps<{
    startOpen?: boolean;
}>();

const page = usePage();
const isOpen = ref(false);
const showTooltip = ref(false);
const inputMessage = ref('');
const isLoading = ref(false);
const messagesContainer = ref<HTMLDivElement | null>(null);
const inputField = ref<HTMLInputElement | null>(null);
const chatModalRef = ref<HTMLDivElement | null>(null);
const whatsappUrl = ref<string>('https://wa.me/6283822458988');

const quickChips = [
    { label: '💬 Chat WhatsApp CS', query: 'Bagaimana cara terhubung langsung ke WhatsApp Customer Service Lette Little?' },
    { label: '👶 Rekomendasi Newborn (0-3M)', query: 'Boleh berikan rekomendasi baju bayi newborn terbaik untuk kado atau pemakaian harian?' },
    { label: '📏 Panduan Ukuran & Berat', query: 'Bagaimana panduan memilih ukuran baju bayi berdasarkan umur dan berat badan?' },
    { label: '🚚 Info Pengiriman & Ongkir', query: 'Bagaimana info pengiriman, estimasi waktu, dan kurir yang digunakan Lette Little?' },
    { label: '💳 Cara Pembayaran', query: 'Apa saja metode pembayaran yang tersedia di Lette Little?' },
];

const defaultWelcomeMessage: ChatMessage = {
    id: 'welcome-1',
    role: 'assistant',
    content: 'Halo Bunda & Ayah! Salam hangat dari **Lette Little** ☁️✨\n\nAku Lette Assistant — asisten virtual belanja Anda. Butuh rekomendasi ukuran si kecil, cek bahan katun organik, atau ada pertanyaan seputar pesanan? Silakan tanyakan di sini ya Bun! 👶',
    timestamp: Date.now(),
};

const messages = ref<ChatMessage[]>([defaultWelcomeMessage]);

// Hide bubble on admin dashboard
const isStorefront = computed(() => {
    const url = page.url || '';
    return !url.startsWith('/admin') && !url.startsWith('/dashboard');
});

const isMobile = ref(false);

function checkMobile() {
    isMobile.value = window.innerWidth < 768;
}

function handleOutsideClick(e: MouseEvent) {
    if (!isOpen.value || !isMobile.value) return;
    const modal = chatModalRef.value;
    if (modal && !modal.contains(e.target as Node)) {
        const btn = (e.target as HTMLElement)?.closest('[aria-label="Buka Chat Customer Support Lette Little"]');
        if (!btn) {
            isOpen.value = false;
        }
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
}

function toggleChat() {
    isOpen.value = !isOpen.value;
    showTooltip.value = false;
    if (isOpen.value) {
        scrollToBottom();
        nextTick(() => {
            inputField.value?.focus();
        });
    }
}

function openWhatsApp() {
    window.open(whatsappUrl.value, '_blank', 'noopener,noreferrer');
}

function formatTime(timestamp: number) {
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(timestamp));
}

function parseMarkdown(text: string) {
    let escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Bold **text**
    escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // Bullet lines
    const lines = escaped.split('\n');
    const processedLines = lines.map((line) => {
        if (line.trim().startsWith('- ') || line.trim().startsWith('* ')) {
            return `<li class="ml-4 list-disc">${line.trim().substring(2)}</li>`;
        }
        return line;
    });

    return processedLines.join('<br>');
}

async function sendMessage(textToSend?: string) {
    const text = textToSend || inputMessage.value.trim();
    if (!text || isLoading.value) return;

    // Check if user explicitly asked for WhatsApp
    if (text.toLowerCase().includes('whatsapp') || text.toLowerCase().includes('wa cs') || text.includes('💬 Chat WhatsApp CS')) {
        const userMsg: ChatMessage = {
            id: 'msg-' + Date.now(),
            role: 'user',
            content: text,
            timestamp: Date.now(),
        };
        messages.value.push(userMsg);
        inputMessage.value = '';
        scrollToBottom();

        setTimeout(() => {
            messages.value.push({
                id: 'reply-' + Date.now(),
                role: 'assistant',
                content: 'Tentu Bunda! Tim Customer Care Lette Little siap melayani konsultasi langsung via WhatsApp. Silakan klik tombol **"Buka WhatsApp CS"** di bawah ya Bun ☁️📱',
                timestamp: Date.now(),
            });
            scrollToBottom();
        }, 300);
        return;
    }

    const userMessage: ChatMessage = {
        id: 'msg-' + Date.now(),
        role: 'user',
        content: text,
        timestamp: Date.now(),
    };

    messages.value.push(userMessage);
    inputMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    // Prepare history for API
    const historyPayload = messages.value
        .slice(-8)
        .map((m) => ({
            role: m.role === 'assistant' ? 'model' : 'user',
            content: m.content,
        }));

    try {
        const response = await fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                message: text,
                history: historyPayload,
            }),
        });

        const data = await response.json();

        if (data.whatsapp_url) {
            whatsappUrl.value = data.whatsapp_url;
        }

        const replyContent = data.reply || 'Maaf Bunda, terjadi sedikit kendala koneksi. Silakan hubungi kami via WhatsApp untuk bantuan langsung ya.';

        messages.value.push({
            id: 'reply-' + Date.now(),
            role: 'assistant',
            content: replyContent,
            timestamp: Date.now(),
        });
    } catch {
        messages.value.push({
            id: 'error-' + Date.now(),
            role: 'assistant',
            content: 'Koneksi terputus sejenak Bun. Silakan hubungi admin kami via WhatsApp ya!',
            timestamp: Date.now(),
        });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
}

function handleChipClick(chip: { label: string; query: string }) {
    sendMessage(chip.query);
}

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    document.addEventListener('click', handleOutsideClick);

    if (props.startOpen) {
        isOpen.value = true;
        scrollToBottom();
    } else {
        // Show tooltip briefly after 4 seconds
        setTimeout(() => {
            if (!isOpen.value) {
                showTooltip.value = true;
                setTimeout(() => {
                    showTooltip.value = false;
                }, 6000);
            }
        }, 4000);
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    document.removeEventListener('click', handleOutsideClick);
});

watch(isOpen, (newVal) => {
    if (newVal) {
        scrollToBottom();
        nextTick(() => inputField.value?.focus());
    }
});
</script>

<template>
    <div v-if="isStorefront" class="fixed right-5 bottom-5 z-50 flex flex-col items-end">
        <!-- Floating Tooltip -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
        >
            <div
                v-if="showTooltip && !isOpen"
                class="mb-3 max-w-xs rounded-2xl border border-[#ded2c5] bg-[#fffdf8] px-4 py-2.5 text-xs text-[#4e453f] shadow-lg"
            >
                <div class="flex items-center gap-2 font-medium">
                    <Cloud class="size-3.5 text-[#6c5b4e]" />
                    <span>Butuh info baju & ukuran bayi? Tanya Lette AI yuk! ☁️</span>
                </div>
            </div>
        </transition>

        <!-- Chat Modal -->
        <transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-if="isOpen"
                ref="chatModalRef"
                class="mb-3 flex h-[540px] max-h-[85vh] w-[370px] max-w-[calc(100vw-32px)] flex-col overflow-hidden rounded-3xl border border-[#d1c4bc] bg-[#fffdf8] shadow-2xl"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-[#e5ded6] bg-[#fcf9f3] px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="grid size-10 place-items-center rounded-2xl bg-[#6c5b4e] text-white shadow-sm">
                            <Cloud class="size-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-serif text-sm font-semibold tracking-wide text-[#1c1c18]">Lette Assistant</h3>
                                <span class="size-2 rounded-full bg-emerald-500 ring-2 ring-emerald-100"></span>
                            </div>
                            <p class="text-[11px] text-[#6f645d]">Asisten Belanja & Info Baju Bayi</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="grid size-8 place-items-center rounded-full text-[#6f645d] transition-colors hover:bg-[#f6f3ed] hover:text-[#1c1c18]"
                            aria-label="Tutup Chat"
                            @click="isOpen = false"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Messages Container -->
                <div
                    ref="messagesContainer"
                    class="flex-1 space-y-3.5 overflow-y-auto bg-[#fcf9f3] p-4 text-xs leading-relaxed"
                >
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex flex-col"
                        :class="msg.role === 'user' ? 'items-end' : 'items-start'"
                    >
                        <div
                            class="max-w-[85%] rounded-2xl px-3.5 py-2.5 shadow-xs"
                            :class="
                                msg.role === 'user'
                                    ? 'rounded-tr-xs bg-[#6c5b4e] text-[#fffdf8]'
                                    : 'rounded-tl-xs border border-[#e5ded6] bg-[#fffdf8] text-[#28231f]'
                            "
                        >
                            <div
                                class="chat-content leading-relaxed"
                                v-html="parseMarkdown(msg.content)"
                            ></div>
                            <div
                                class="mt-1 text-[10px]"
                                :class="msg.role === 'user' ? 'text-white/70 text-right' : 'text-[#6f645d]'"
                            >
                                {{ formatTime(msg.timestamp) }}
                            </div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="isLoading" class="flex items-start">
                        <div class="flex items-center gap-1.5 rounded-2xl rounded-tl-xs border border-[#e5ded6] bg-[#fffdf8] px-4 py-3 shadow-xs">
                            <span class="size-1.5 animate-bounce rounded-full bg-[#6c5b4e]" style="animation-delay: 0ms"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-[#6c5b4e]" style="animation-delay: 150ms"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-[#6c5b4e]" style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompt Chips -->
                <div class="border-t border-[#e5ded6] bg-[#fffdf8] px-3 py-2">
                    <p class="mb-1.5 text-[10px] font-medium tracking-wider text-[#6f645d] uppercase">Pertanyaan Cepat:</p>
                    <div class="flex gap-1.5 overflow-x-auto pb-1 text-[11px] no-scrollbar">
                        <button
                            v-for="chip in quickChips"
                            :key="chip.label"
                            type="button"
                            class="shrink-0 rounded-full border border-[#ded2c5] bg-[#fcf9f3] px-2.5 py-1 text-[#4e453f] transition-colors hover:border-[#6c5b4e] hover:bg-[#6c5b4e] hover:text-white"
                            @click="handleChipClick(chip)"
                        >
                            {{ chip.label }}
                        </button>
                    </div>
                </div>

                <!-- Input Footer -->
                <form
                    class="flex items-center gap-2 border-t border-[#e5ded6] bg-[#fffdf8] p-3"
                    @submit.prevent="sendMessage()"
                >
                    <input
                        ref="inputField"
                        v-model="inputMessage"
                        type="text"
                        placeholder="Tanya seputar ukuran, bahan, pesanan..."
                        class="flex-1 rounded-full border border-[#d1c4bc] bg-[#fcf9f3] px-3.5 py-2 text-xs text-[#1c1c18] placeholder-[#a29890] outline-none transition focus:border-[#6c5b4e] focus:ring-1 focus:ring-[#6c5b4e]"
                        :disabled="isLoading"
                    />
                    <button
                        type="submit"
                        class="grid size-9 place-items-center rounded-full bg-[#6c5b4e] text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                        :disabled="isLoading || !inputMessage.trim()"
                        aria-label="Kirim Pesan"
                    >
                        <Send class="size-4" />
                    </button>
                    <button
                        type="button"
                        class="grid size-9 place-items-center rounded-full border border-emerald-600/30 bg-emerald-50 text-emerald-700 transition hover:bg-emerald-600 hover:text-white"
                        title="Chat WhatsApp Customer Care"
                        aria-label="Chat WhatsApp CS"
                        @click="openWhatsApp"
                    >
                        <span class="text-xs font-bold">WA</span>
                    </button>
                </form>
            </div>
        </transition>

        <!-- Floating Action Trigger Button -->
        <button
            type="button"
            class="group relative flex size-13 items-center justify-center rounded-full bg-[#6c5b4e] text-white shadow-xl transition-all duration-300 hover:scale-105 hover:bg-[#58483c] focus:outline-none focus:ring-2 focus:ring-[#6c5b4e] focus:ring-offset-2"
            aria-label="Buka Chat Customer Support Lette Little"
            @click="toggleChat"
        >
            <Cloud v-if="!isOpen" class="size-6 transition-transform group-hover:scale-110" />
            <X v-else class="size-6" />

            <!-- Online Indicator Badge -->
            <span class="absolute top-1 right-1 flex size-3">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex size-3 rounded-full border-2 border-white bg-emerald-500"></span>
            </span>
        </button>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
:deep(strong) {
    font-weight: 600;
    color: #1c1c18;
}
</style>
