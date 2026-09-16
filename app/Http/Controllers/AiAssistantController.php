<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        if ($best && $bestScore > 0) {
            $answer  = "**Category:** {$best->request->category}\n\n";
            $answer .= "**Similar Issue:** {$best->request->title}\n\n";
            $answer .= "**Findings:**\n{$best->findings}\n\n";
            if ($best->recommended_action) $answer .= "**Recommended Action:**\n{$best->recommended_action}\n\n";
            if ($best->materials_needed)   $answer .= "**Materials Needed:**\n{$best->materials_needed}\n\n";
            if ($best->solution_steps)     $answer .= "**Step-by-Step Solution:**\n{$best->solution_steps}\n\n";
            $confidence = min(100, $bestScore * 20);
            return response()->json(['success' => true, 'answer' => $answer, 'confidence' => $confidence, 'matched' => true]);
        }

        $answer  = "I couldn't find an exact match in the knowledge base. General guidance:\n\n";
        $answer .= "1. **Safety first** – Turn off power/water before inspection.\n";
        $answer .= "2. **Inspect** the equipment and note visible damage.\n";
        $answer .= "3. **Check** if a similar past diagnosis exists in the system.\n";
        $answer .= "4. **Request materials** through the inventory officer.\n";
        $answer .= "5. **Record** your findings and solution so future technicians can learn.\n\n";
        $answer .= "Tip: Ask more specific questions like 'How to fix aircon not cooling?'";

        return response()->json(['success' => true, 'answer' => $answer, 'confidence' => 0, 'matched' => false]);
    }
}