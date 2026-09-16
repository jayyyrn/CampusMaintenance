@extends('layouts.app')
@section('title', 'AI Assistant')
@section('content')
<div x-data="aiAssistant()">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">🤖 AI Knowledge Assistant</h1>
    <p class="text-gray-500 mb-6">Ask about past fixes. The assistant learns from every saved diagnosis.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="mb-4">
                    <textarea x-model="question" @keydown.enter.prevent="ask()" rows="3"
                              placeholder="e.g., How to fix aircon not cooling? How to replace broken light switch?"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none"></textarea>
                    <button @click="ask()" :disabled="loading || !question"
                            class="mt-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-bold px-6 py-2.5 rounded-lg">
                        <span x-show="!loading">Ask Assistant</span>
                        <span x-show="loading">Thinking...</span>
                    </button>
                </div>

                <template x-if="answer">
                    <div class="border-t pt-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-bold text-gray-700">Answer</span>
                            <template x-if="confidence > 0">
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">
                                    Confidence: <span x-text="confidence"></span>%
                                </span>
                            </template>
                            <template x-if="confidence === 0">
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">No exact match</span>
                            </template>
                        </div>
                        <pre class="whitespace-pre-wrap text-sm bg-gray-50 p-4 rounded-lg" x-text="answer"></pre>
                    </div>
                </template>
            </div>
        </div>

        <div>
            <h2 class="text-sm font-bold text-gray-500 uppercase mb-3">Knowledge Base ({{ $kb->count() }})</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 max-h-[600px] overflow-y-auto divide-y">
                @forelse($kb as $entry)
                    <div class="p-4">
                        <div class="text-xs font-mono text-gray-500">{{ $entry->request->request_code ?? '' }}</div>
                        <div class="font-semibold text-sm capitalize">{{ $entry->request->category ?? '' }} — {{ $entry->request->title ?? '' }}</div>
                        <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ Str::limit($entry->findings, 100) }}</div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-400 text-sm">No solutions recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
function aiAssistant() {
    return {
        question: '',
        answer: '',
        confidence: 0,
        loading: false,
        async ask() {
            if (!this.question.trim()) return;
            this.loading = true;
            this.answer = '';
            try {
                const res = await fetch('{{ route("assistant.ask") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ question: this.question })
                });
                const data = await res.json();
                this.answer = data.answer;
                this.confidence = data.confidence;
            } catch (e) {
                this.answer = 'Error: ' + e.message;
            }
            this.loading = false;
        }
    }
}
</script>
@endsection