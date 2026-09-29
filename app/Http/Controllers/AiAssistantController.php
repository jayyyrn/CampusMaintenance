<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Diagnosis;

class AiAssistantController extends Controller
{
    public function index() {
        $kb = Diagnosis::with(['request','technician'])
            ->whereNotNull('solution_steps')
            ->where('solution_steps','!=','')
            ->latest()->limit(50)->get();
        return view('assistant.index', compact('kb'));
    }

    public function ask(Request $request) {
        $request->validate(['question' => 'required|string|min:3']);
        $q = strtolower(trim($request->question));

        $kb = Diagnosis::with(['request'])
            ->whereNotNull('solution_steps')
            ->where('solution_steps','!=','')
            ->latest()->limit(200)->get();

        // ── Keyword search ──
        $best = null;
        $bestScore = 0;
        $words = preg_split('/\s+/', $q);

        foreach ($kb as $entry) {
            $hay = strtolower(
                $entry->request->category.' '.
                $entry->request->title.' '.
                $entry->findings.' '.
                ($entry->recommended_action ?? '')
            );
            $score = 0;
            foreach ($words as $w) {
                if (strlen($w) > 2 && str_contains($hay, $w)) $score++;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }
        }

        // ── Strong KB match: return it directly ──
        if ($best && $bestScore > 0) {
            $answer  = "**Category:** {$best->request->category}\n\n";
            $answer .= "**Similar Issue:** {$best->request->title}\n\n";
            $answer .= "**Findings:**\n{$best->findings}\n\n";
            if ($best->recommended_action) $answer .= "**Recommended Action:**\n{$best->recommended_action}\n\n";
            if ($best->materials_needed)   $answer .= "**Materials Needed:**\n{$best->materials_needed}\n\n";
            if ($best->solution_steps)     $answer .= "**Step-by-Step Solution:**\n{$best->solution_steps}\n\n";
            $confidence = min(100, $bestScore * 20);

            return response()->json([
                'success'    => true,
                'answer'     => $answer,
                'confidence' => $confidence,
                'matched'    => true,
                'source'     => 'knowledge_base',
            ]);
        }

        // ── No KB match: show the fallback + AI answer ──
        $fallback  = "I couldn't find an exact match in the knowledge base. General guidance:\n\n";
        $fallback .= "1. **Safety first** – Turn off power/water before inspection.\n";
        $fallback .= "2. **Inspect** the equipment and note visible damage.\n";
        $fallback .= "3. **Check** if a similar past diagnosis exists in the system.\n";
        $fallback .= "4. **Request materials** through the inventory officer.\n";
        $fallback .= "5. **Record** your findings and solution so future technicians can learn.\n";

        $aiAnswer = $this->askGroq($request->question, $kb);

        if ($aiAnswer) {
            $combined  = $fallback . "\n---\n\n";
            $combined .= "**AI Assistant Suggestion:**\n\n";
            $combined .= $aiAnswer;

            return response()->json([
                'success'    => true,
                'answer'     => $combined,
                'confidence' => 0,
                'matched'    => false,
                'source'     => 'ai',
            ]);
        }

        // ── AI also failed: show only the fallback ──
        $fallback .= "\nTip: Ask more specific questions like 'How to fix aircon not cooling?'";

        return response()->json([
            'success'    => true,
            'answer'     => $fallback,
            'confidence' => 0,
            'matched'    => false,
            'source'     => 'fallback',
        ]);
    }

    /**
     * Ask Groq (Llama 3.1 8B) to answer the user's question.
     * Uses the knowledge base as context when available.
     */
    private function askGroq(string $question, $kb): ?string
    {
        try {
            $apiKey = env('GROQ_API_KEY');
            if (!$apiKey) return null;

            $context = $kb->take(10)->map(function ($d) {
                $lines  = "Request: " . ($d->request->title ?? 'Unknown');
                $lines .= "\nCategory: " . ($d->request->category ?? 'general');
                $lines .= "\nFindings: " . ($d->findings ?? '');
                if ($d->recommended_action) $lines .= "\nRecommended action: " . $d->recommended_action;
                if ($d->materials_needed)   $lines .= "\nMaterials: " . $d->materials_needed;
                if ($d->solution_steps)     $lines .= "\nSolution steps: " . $d->solution_steps;
                return $lines;
            })->implode("\n\n---\n\n");

            $prompt = "You are the CampusFix AI Assistant for a campus maintenance system.\n\n"
                    . "The user asked: \"{$question}\"\n\n";

            if ($context) {
                $prompt .= "Below are the most recent diagnoses from our knowledge base:\n\n"
                         . $context . "\n\n"
                         . "Answer the user's question using this knowledge base first. ";
            } else {
                $prompt .= "The knowledge base is currently empty — no past diagnoses are saved yet. ";
            }

            $prompt .= "If the knowledge base does not contain a relevant answer, "
                      . "provide a clear, helpful response using your general knowledge instead. "
                      . "You may answer any question the user asks — not only maintenance topics. "
                      . "If the answer is a code sample or technical explanation, format it cleanly "
                      . "using markdown-style code blocks. "
                      . "For maintenance topics, use labels like **Findings:**, **Recommended Action:**, "
                      . "**Step-by-Step Solution:** when applicable. Keep it concise.";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'openai/gpt-oss-20b',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.5,
                'max_tokens'  => 1024,
            ]);

            if (!$response->successful()) {
                \Log::warning('Groq assistant error: ' . $response->body());
                return null;
            }

            $content = $response->json('choices.0.message.content');
            return $content ? trim($content) : null;

        } catch (\Throwable $e) {
            \Log::error('Groq assistant failed: ' . $e->getMessage());
            return null;
        }
    }
}