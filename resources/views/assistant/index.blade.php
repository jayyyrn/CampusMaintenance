@extends('layouts.app')
@section('title', 'AI Assistant')
@section('content')
<div x-data="aiAssistant()">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">🤖 AI Knowledge Assistant</h1>
    <p class="text-gray-500 mb-6">Ask about past fixes. The assistant learns from every saved diagnosis.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- LEFT: Ask + Answer --}}
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
                    <div class="border-t pt-5">
                        <div class="flex items-center gap-2 mb-4">
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

                        <div class="space-y-5">
                            <template x-for="(section, index) in formatAnswer(answer)" :key="index">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 mb-2"
                                        x-text="section.label"></h3>
                                    <div class="text-sm text-gray-800 leading-relaxed space-y-2"
                                         x-html="section.html"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- RIGHT: Knowledge Base --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Knowledge Base</h2>
                <span class="text-xs font-bold bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full">
                    {{ $kb->count() }}
                </span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 max-h-[640px] overflow-y-auto">
                @forelse($kb as $entry)
                    @php
                        $cat = $entry->request->category ?? 'general';
                        $catColor = match($cat) {
                            'aircon'      => 'bg-cyan-100 text-cyan-700',
                            'electrical'  => 'bg-yellow-100 text-yellow-700',
                            'plumbing'    => 'bg-blue-100 text-blue-700',
                            'carpentry'   => 'bg-orange-100 text-orange-700',
                            'fabrication' => 'bg-purple-100 text-purple-700',
                            default       => 'bg-slate-100 text-slate-700',
                        };
                    @endphp
                    <div class="p-4 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded {{ $catColor }}">
                                        {{ $cat }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400">
                                        {{ $entry->request->request_code ?? '' }}
                                    </span>
                                </div>

                                <div class="font-semibold text-sm text-slate-900 line-clamp-2">
                                    {{ $entry->request->title ?? 'Untitled request' }}
                                </div>

                                @if($entry->findings)
                                    <div class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed">
                                        {{ Str::limit($entry->findings, 120) }}
                                    </div>
                                @endif

                                @if($entry->is_verified)
                                    <div class="mt-2 flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        VERIFIED
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">No solutions recorded yet</p>
                        <p class="text-xs text-slate-400 mt-1">Save a diagnosis with solution steps to grow the knowledge base.</p>
                    </div>
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

        formatAnswer(raw) {
            if (!raw) return [];

            const sections = [];
            const regex = /\*\*([^*]+):\*\*\s*/g;
            let match;
            let lastIndex = 0;
            let lastLabel = null;

            while ((match = regex.exec(raw)) !== null) {
                if (lastLabel !== null) {
                    sections.push({
                        label: lastLabel,
                        html: this.renderBody(raw.slice(lastIndex, match.index).trim())
                    });
                } else {
                    const preamble = raw.slice(0, match.index).trim();
                    if (preamble) {
                        sections.push({ label: 'Answer', html: this.renderBody(preamble) });
                    }
                }
                lastLabel = match[1].trim();
                lastIndex = regex.lastIndex;
            }

            if (lastLabel !== null) {
                sections.push({
                    label: lastLabel,
                    html: this.renderBody(raw.slice(lastIndex).trim())
                });
            } else {
                sections.push({ label: 'General Guidance', html: this.renderBody(raw.trim()) });
            }

            return sections;
        },

        renderBody(text) {
            if (!text) return '<p class="text-gray-400">—</p>';

            const esc = (s) => s
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            const lines = esc(text).split(/\n/);
            let html = '';
            let listType = null;

            const closeList = () => {
                if (listType) { html += `</${listType}>`; listType = null; }
            };

            for (let rawLine of lines) {
                const line = rawLine.trim();

                if (line === '') { closeList(); continue; }

                // Strip leading step numbers like "1." "1)" "1 -" "Step 1:" so we don't double-number
                const cleaned = line.replace(/^(?:step\s+)?\d+\s*[\.\)\:\-]\s*/i, '');

                // Bullet list item
                const bullet = line.match(/^[-•*]\s+(.*)$/);
                if (bullet) {
                    if (listType !== 'ul') { closeList(); html += '<ul class="list-disc pl-5 space-y-1.5 my-2">'; listType = 'ul'; }
                    html += `<li>${this.inline(bullet[1])}</li>`;
                    continue;
                }

                // Numbered list item (also captures "Step 1:" lines)
                const numbered = line.match(/^(?:step\s+)?\d+\s*[\.\)\:\-]\s+(.*)$/i);
                if (numbered) {
                    if (listType !== 'ol') { closeList(); html += '<ol class="list-decimal pl-5 space-y-1.5 my-2">'; listType = 'ol'; }
                    html += `<li>${this.inline(numbered[1])}</li>`;
                    continue;
                }

                closeList();
                html += `<p>${this.inline(line)}</p>`;
            }

            closeList();
            return html;
        },

        inline(s) {
            s = s.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-gray-900">$1</strong>');
            s = s.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener" class="text-orange-600 hover:text-orange-700 underline">$1</a>');
            return s;
        },

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