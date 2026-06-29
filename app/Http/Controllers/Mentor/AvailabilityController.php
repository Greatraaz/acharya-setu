<?php
namespace App\Http\Controllers\Mentor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function show()
    {
        $slots = auth()->user()->preferences['weekly_slots'] ?? $this->defaultSlots();
        return view('mentor.availability', compact('slots'));
    }

    public function update(Request $request)
    {
        $request->validate(['weekly_slots' => 'required|array']);
        $prefs = auth()->user()->preferences ?? [];
        $prefs['weekly_slots'] = $request->weekly_slots;
        auth()->user()->update(['preferences' => $prefs]);
        return response()->json(['message' => 'Availability saved.']);
    }

    private function defaultSlots(): array
    {
        $days = ['monday','tuesday','wednesday','thursday','friday'];
        $times = ['09:00','10:00','11:00','14:00','15:00','16:00','17:00','18:00'];
        return array_fill_keys($days, $times);
    }
}